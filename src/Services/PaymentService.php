<?php

namespace Services;

use Stripe\StripeClient;
use Models\Payment;
use Models\PaymentAccount;
use Models\Registration;
use DateTime;
use Exception;
use Throwable;

class PaymentService
{
    private EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService();
    }

    private function getStripeClient(): StripeClient
    {
        $secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY') ?? '';
        if (empty($secretKey)) {
            throw new Exception("Stripe secret key is not configured. Set STRIPE_SECRET_KEY in .env");
        }
        $mode = strtolower($_ENV['STRIPE_MODE'] ?? getenv('STRIPE_MODE') ?? 'sandbox');
        if ($mode === 'sandbox' && strpos($secretKey, 'sk_live_') === 0) {
            throw new Exception("STRIPE_MODE is sandbox but a live (sk_live_) key is set. Use an sk_test_ key.");
        }
        if ($mode === 'live' && strpos($secretKey, 'sk_test_') === 0) {
            throw new Exception("STRIPE_MODE is live but a test (sk_test_) key is set. Use an sk_live_ key.");
        }
        return new StripeClient($secretKey);
    }

    private function runStripe(callable $fn)
    {
        set_error_handler(function ($severity, $message) {
            if (stripos((string)$message, 'Accounts v2') !== false) {
                return true;
            }
            return false;
        }, E_USER_WARNING);
        try {
            return $fn();
        } finally {
            restore_error_handler();
        }
    }

    private function frontendHost(): string
    {
        return $_ENV['FRONTEND_HOST'] ?? getenv('FRONTEND_HOST') ?? ($_ENV['DOMAIN'] ?? getenv('DOMAIN') ?? 'localhost');
    }

    private function getPaymentAccount(int $userId): ?array
    {
        $rows = PaymentAccount::where(['userId' => $userId]);
        return count($rows) > 0 ? $rows[0] : null;
    }

    private function isAccountConnected(array $row): bool
    {
        $value = $row['isConnected'] ?? '0';
        return $value === '1' || $value === 1 || $value === true;
    }

    private function getOrganizerConnectedAccount(int $userId): ?string
    {
        $rows = PaymentAccount::where(['userId' => $userId]);
        if (count($rows) === 0) {
            return null;
        }
        $account = $rows[0];
        return $this->isAccountConnected($account) ? $account['accountId'] : null;
    }

    public function connectAccount(int $userId, string $email): array
    {
        $stripe = $this->getStripeClient();
        $existing = $this->getPaymentAccount($userId);

        if (!$existing) {
            $result = $this->runStripe(function () use ($stripe, $email, $userId) {
                $account = $stripe->accounts->create([
                    'type' => 'express',
                    'email' => $email,
                    'metadata' => [
                        'userId' => (string)$userId,
                    ],
                ]);

                $accountLink = $stripe->accountLinks->create([
                    'account' => $account->id,
                    'refresh_url' => rtrim($this->frontendHost(), '/') . "/connect/reauth",
                    'return_url' => rtrim($this->frontendHost(), '/') . "/connect/return",
                    'type' => 'account_onboarding',
                ]);

                return [
                    'accountId' => $account->id,
                    'onboardingUrl' => $accountLink->url,
                    'expiresAt' => $accountLink->expires_at,
                ];
            });

            $paymentAccount = new PaymentAccount($userId, $result['accountId'], $email);
            $paymentAccount->save();
            return $result;
        }

        $accountId = $existing['accountId'];

        if ($this->isAccountConnected($existing)) {
            $loginLink = $this->runStripe(fn() => $stripe->accounts->createLoginLink($accountId));
            $result = [
                'accountId' => $accountId,
                'onboardingUrl' => $loginLink->url,
                'expiresAt' => null,
            ];
        } else {
            $accountLink = $this->runStripe(function () use ($stripe, $accountId) {
                return $stripe->accountLinks->create([
                    'account' => $accountId,
                    'refresh_url' => rtrim($this->frontendHost(), '/') . "/connect/reauth",
                    'return_url' => rtrim($this->frontendHost(), '/') . "/connect/return",
                    'type' => 'account_onboarding',
                ]);
            });
            $result = [
                'accountId' => $accountId,
                'onboardingUrl' => $accountLink->url,
                'expiresAt' => $accountLink->expires_at,
            ];
        }

        if (($existing['email'] ?? '') !== $email) {
            PaymentAccount::updateRecord(['userId' => $userId], [
                'email' => $email,
                'updatedAt' => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
        }

        return $result;
    }

    public function getConnectStatus(int $userId): array
    {
        $row = $this->getPaymentAccount($userId);

        if (!$row) {
            return [
                "connected" => false,
                "pending" => false,
                "account" => null,
            ];
        }

        $accountId = $row['accountId'];
        $details = null;
        try {
            $stripe = $this->getStripeClient();
            $sa = $this->runStripe(fn() => $stripe->accounts->retrieve($accountId));
            $details = [
                'chargesEnabled' => (bool)($sa->charges_enabled ?? false),
                'payoutsEnabled' => (bool)($sa->payouts_enabled ?? false),
                'detailsSubmitted' => (bool)($sa->details_submitted ?? false),
                'country' => $sa->country ?? null,
                'defaultCurrency' => $sa->default_currency ?? null,
                'email' => $sa->email ?? null,
                'businessName' => !empty($sa->business_profile) ? ($sa->business_profile->name ?? null) : null,
            ];
        } catch (Throwable $th) {
            $details = null;
        }

        $isConnected = $this->isAccountConnected($row);
        if ($details) {
            $liveConnected = $details['chargesEnabled'] && $details['payoutsEnabled'];
            if ($liveConnected !== $isConnected) {
                $now = (new DateTime())->format('Y-m-d H:i:s');
                $set = [
                    'isConnected' => $liveConnected ? '1' : '0',
                    'accountDetails' => json_encode($details),
                    'updatedAt' => $now,
                ];
                if ($liveConnected && empty($row['connectedAt'])) {
                    $set['connectedAt'] = $now;
                }
                PaymentAccount::updateRecord(['userId' => $userId], $set);
            }
            $isConnected = $liveConnected;
        }

        return [
            "connected" => $isConnected,
            "pending" => !$isConnected,
            "account" => [
                "accountId" => $accountId,
                "email" => ($details['email'] ?? null) ?: ($row['email'] ?? ''),
                "chargesEnabled" => $details ? $details['chargesEnabled'] : null,
                "payoutsEnabled" => $details ? $details['payoutsEnabled'] : null,
                "detailsSubmitted" => $details ? $details['detailsSubmitted'] : null,
                "country" => $details ? $details['country'] : null,
                "defaultCurrency" => $details ? $details['defaultCurrency'] : null,
                "businessName" => $details ? $details['businessName'] : null,
            ],
        ];
    }

    public function disconnectAccount(int $userId): void
    {
        $row = $this->getPaymentAccount($userId);
        if (!$row) {
            return;
        }

        try {
            $stripe = $this->getStripeClient();
            $this->runStripe(fn() => $stripe->accounts->delete($row['accountId']));
        } catch (Throwable $th) {
            // Best effort: still unlink locally if Stripe deletion fails.
        }
        PaymentAccount::deleteRecord(['userId' => $userId]);
    }

    public function createCheckoutSession(int $userId, array $data): array
    {
        // Buying a ticket on this platform requires a logged-in account.
        // Guests are not allowed to purchase paid tickets.
        if ($userId <= 0) {
            throw new Exception("You must be logged in to purchase a ticket.");
        }

        $eventId = $data['eventId'] ?? '';
        $quantity = (int)($data['quantity'] ?? 1);
        $currency = strtolower($data['currency'] ?? 'lkr');
        $registerId = $data['registerId'] ?? null;

        if (empty($eventId) || $quantity < 1) {
            throw new Exception("Missing or invalid required fields");
        }

        $event = $this->eventService->getEvent($eventId);
        if (!$event) {
            throw new Exception("Event not found");
        }

        $ticketPrice = (float)($event['ticketPrice'] ?? 0);
        if ($ticketPrice <= 0) {
            throw new Exception("This event is free. No payment required.");
        }

        // Ensure a registration exists for this user+event so the buyer's
        // registration appears immediately (the frontend only sends
        // eventId + email). The real registration id is stored in the
        // Stripe session metadata so the payment can be linked on success.
        if (!$registerId) {
            $registrationService = new RegistrationService();
            if ($registrationService->isUserRegisteredForEvent($userId, $eventId)) {
                $existing = \Models\Registration::where(["userId" => $userId, "eventId" => $eventId]);
                $registerId = $existing[0]['id'];
            } else {
                $registration = new \Models\Registration($eventId, $userId);
                $registerId = $registrationService->registerUserForEvent($registration);
            }
            if (!$registerId) {
                throw new Exception("Could not create a registration for this event");
            }
        }

        // Prevent the same user from buying a ticket for an event they have
        // already paid for. A freshly created registration (no payment yet)
        // is left untouched so a cancelled checkout can be retried.
        $existingPayments = Payment::where([
            "userId" => (int)$userId,
            "registerId" => (int)$registerId,
        ]);
        if (count($existingPayments) > 0) {
            throw new Exception("You have already purchased a ticket for this event.");
        }

        $domain = rtrim($this->frontendHost(), '/');
        $stripe = $this->getStripeClient();

        // Always bill the logged-in user's own email. The frontend only sends
        // eventId + quantity, so relying on $data['email'] leaves customer_email
        // null and Stripe rejects it with "Invalid email address:". Look it up
        // from the user record instead; omit the param entirely when absent.
        $userService = new UserService();
        $buyer = $userService->getUser((string)$userId);
        $customerEmail = ($buyer['email'] ?? null) ?: null;

        $unitAmount = (int)round($ticketPrice * 100);
        $totalAmount = $unitAmount * $quantity;

        // 5% platform commission. Charged only when the event organizer has a
        // connected Stripe account so the remaining 95% can be transferred to
        // them at checkout. Without a connected account the full amount is
        // collected by the platform and no commission applies.
        $commissionRate = 0.05;
        $applicationFeeAmount = null;
        $transferDestination = null;
        $organizerAccount = $this->getOrganizerConnectedAccount((int) $event['organizerId']);
        if ($organizerAccount) {
            $applicationFeeAmount = (int)round($totalAmount * $commissionRate);
            $transferDestination = $organizerAccount;
        }

        $sessionParams = [
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $unitAmount,
                    'product_data' => [
                        'name' => $event['title'] ?? 'Event Ticket',
                        'description' => 'Ticket for ' . ($event['title'] ?? 'Event'),
                    ],
                ],
                'quantity' => $quantity,
            ]],
            'metadata' => array_filter([
                'eventId' => $eventId,
                'userId' => (string)$userId,
                'registerId' => $registerId ? (string)$registerId : null,
                'quantity' => (string)$quantity,
            ]),
            'success_url' => $domain . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $domain . '/payment/cancel',
        ];
        if ($customerEmail) {
            $sessionParams['customer_email'] = $customerEmail;
        }
        if ($applicationFeeAmount !== null && $transferDestination !== null) {
            $sessionParams['payment_intent_data'] = [
                'application_fee_amount' => $applicationFeeAmount,
                'transfer_data' => ['destination' => $transferDestination],
            ];
        }

        $session = $this->runStripe(function () use ($stripe, $sessionParams) {
            return $stripe->checkout->sessions->create($sessionParams);
        });

        return [
            'sessionId' => $session->id,
            'url' => $session->url,
            'amount' => $ticketPrice,
            'currency' => $currency,
            'quantity' => $quantity,
            'commission' => $applicationFeeAmount !== null ? (float)($applicationFeeAmount / 100) : 0.0,
            'organizerReceives' => $applicationFeeAmount !== null ? (float)(($totalAmount - $applicationFeeAmount) / 100) : null,
        ];
    }

    public function confirmPayment(int $userId, string $sessionId): void
    {
        $stripe = $this->getStripeClient();
        $session = $this->runStripe(function () use ($stripe, $sessionId) {
            return $stripe->checkout->sessions->retrieve($sessionId);
        });

        if (($session->payment_status ?? '') !== 'paid') {
            throw new Exception("Payment has not been completed.");
        }

        $metadata = (array)($session->metadata ?? []);
        $buyerId = (int) ($metadata['userId'] ?? 0);
        if ($buyerId !== $userId) {
            throw new Exception("This checkout session does not belong to your account.");
        }

        $this->recordCompletedPayment($session);
    }

    public function recordCompletedPayment($session): void
    {
        
        if (($session->payment_status ?? '') !== 'paid') {
            return;
        }

        $metadata = $session->metadata;
        $userId = $metadata->userId;
        $eventId = $metadata->eventId;
        $registerId = $metadata->registerId;

        if (!$userId || !$eventId) {
            return;
        }

        // A paid checkout may arrive without a pre-created registration
        // (the frontend sends only eventId + email). Ensure one exists so
        // the registration appears in the user's registrations.
        if (!$registerId) {
            $registrationService = new RegistrationService();
            if (!$registrationService->isUserRegisteredForEvent($userId, $eventId)) {
                $registration = new Registration($eventId, $userId);
                $registerId = $registrationService->registerUserForEvent($registration);
            } else {
                $existing = Registration::where(["userId" => $userId, "eventId" => $eventId]);
                $registerId = $existing[0]['id'];
            }
        }

        if (!$registerId) {
            return;
        }
        
        $exists = Payment::where([
            "userId" => (int)$userId,
            "registerId" => (int)$registerId,
        ]);
        if (count($exists) > 0) {
            return;
        }

        $amount = $session->amount_total ? (float)($session->amount_total / 100) : 0.0;
        $payment = new Payment((int)$userId, (int)$registerId, $amount);
        $payment->save();
    }

    public function getPaymentRecords(int $userId): array
    {
        // Money the user's events collected (as organizer)
        $salesRows = Payment::query(
            "SELECT p.id, p.amount, p.paymentAt,
                    e.id AS eventId, e.title AS eventTitle, e.startDate AS eventStartDate, e.coverImage AS coverImage,
                    u.firstName AS buyerFirstName, u.lastName AS buyerLastName, u.email AS buyerEmail
             FROM Payments p
             JOIN Registrations r ON r.id = p.registerId
             JOIN events e ON e.id = r.eventId
             JOIN users u ON u.id = p.userId
             WHERE e.organizerId = ?
             ORDER BY e.startDate DESC, p.paymentAt DESC",
            [(int) $userId]
        );

        // Money the user spent on tickets (as buyer)
        $purchaseRows = Payment::query(
            "SELECT p.id, p.amount, p.paymentAt,
                    e.id AS eventId, e.title AS eventTitle, e.startDate AS eventStartDate, e.coverImage AS coverImage
             FROM Payments p
             JOIN Registrations r ON r.id = p.registerId
             JOIN events e ON e.id = r.eventId
             WHERE p.userId = ?
             ORDER BY e.startDate DESC, p.paymentAt DESC",
            [(int) $userId]
        );

        return [
            "sales" => $this->groupPaymentsByEvent($salesRows),
            "purchases" => $this->groupPaymentsByEvent($purchaseRows),
        ];
    }

    private function groupPaymentsByEvent(array $rows): array
    {
        $events = [];
        $order = [];
        foreach ($rows as $row) {
            $eventId = (int) $row['eventId'];
            if (!isset($events[$eventId])) {
                $order[] = $eventId;
                $events[$eventId] = [
                    "event" => [
                        "id" => $eventId,
                        "title" => $row['eventTitle'] ?? '',
                        "startDate" => $row['eventStartDate'] ?? null,
                        "coverImage" => $row['coverImage'] ?? null,
                    ],
                    "paymentCount" => 0,
                    "revenue" => 0.0,
                    "commission" => 0.0,
                    "payout" => 0.0,
                    "payments" => [],
                ];
            }
            $amount = (float) ($row['amount'] ?? 0);
            $events[$eventId]['paymentCount']++;
            $events[$eventId]['revenue'] = round($events[$eventId]['revenue'] + $amount, 2);
            $events[$eventId]['payments'][] = [
                "id" => (int) $row['id'],
                "amount" => $amount,
                "paymentAt" => $row['paymentAt'] ?? null,
                "buyer" => isset($row['buyerFirstName'])
                    ? [
                        "firstName" => $row['buyerFirstName'],
                        "lastName" => $row['buyerLastName'],
                        "email" => $row['buyerEmail'],
                    ]
                    : null,
            ];
        }

        $result = [];
        foreach ($order as $eventId) {
            $event = $events[$eventId];
            if (isset($event['event'])) {
                $commission = round($event['revenue'] * 0.05, 2);
                $event['commission'] = $commission;
                $event['payout'] = round($event['revenue'] - $commission, 2);
            }
            $result[] = $event;
        }
        return $result;
    }

    public function recordAccountUpdate($account): void
    {
        $accountId = $account->id ?? null;
        if (!$accountId) {
            return;
        }

        $rows = PaymentAccount::where(['accountId' => $accountId]);
        if (count($rows) === 0) {
            return;
        }
        $row = $rows[0];

        $isConnected = (bool)($account->charges_enabled ?? false) && (bool)($account->payouts_enabled ?? false);
        $details = json_encode([
            'chargesEnabled' => (bool)($account->charges_enabled ?? false),
            'payoutsEnabled' => (bool)($account->payouts_enabled ?? false),
            'detailsSubmitted' => (bool)($account->details_submitted ?? false),
            'country' => $account->country ?? null,
            'defaultCurrency' => $account->default_currency ?? null,
            'email' => $account->email ?? null,
            'businessName' => !empty($account->business_profile) ? ($account->business_profile->name ?? null) : null,
        ]);

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $set = [
            'isConnected' => $isConnected ? '1' : '0',
            'accountDetails' => $details,
            'updatedAt' => $now,
        ];
        if ($isConnected && empty($row['connectedAt'])) {
            $set['connectedAt'] = $now;
        }
        PaymentAccount::updateRecord(['userId' => (int)$row['userId']], $set);
    }
}