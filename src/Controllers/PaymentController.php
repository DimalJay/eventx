<?php

namespace Controllers;

use Stripe\Webhook;
use Services\PaymentService;
use Throwable;

class PaymentController
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    private function userId(): ?int
    {
        $userId = $_SERVER["uid"] ?? null;
        return $userId ? (int)$userId : null;
    }

    private function requestData(): array
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);
        return is_array($data) ? $data : [];
    }

    public function connectAccount()
    {
        $userId = $this->userId();
        if (!$userId) {
            return [
                "success" => false,
                "message" => "User not authenticated",
                "data" => null
            ];
        }

        $email = $this->requestData()["email"] ?? "";
        if (empty($email)) {
            return [
                "success" => false,
                "message" => "email is required",
                "data" => null
            ];
        }

        try {
            $result = $this->paymentService->connectAccount($userId, $email);
            return [
                "success" => true,
                "message" => "Connect account created successfully",
                "data" => $result
            ];
        } catch (Throwable $th) {
            $msg = $th->getMessage();
            $hint = "";
            if (stripos($msg, "sign up for Connect") !== false || stripos($msg, "Unable to start Stripe connection") !== false) {
                $hint = " Stripe Connect is not activated on this account. Enable it at https://dashboard.stripe.com/connect (requires a Stripe account in a supported country; test with a US sandbox key).";
            }
            return [
                "success" => false,
                "message" => "Error creating connect account: " . $msg . $hint,
                "data" => null
            ];
        }
    }

    public function connectStatus()
    {
        $userId = $this->userId();
        if (!$userId) {
            return [
                "success" => false,
                "message" => "User not authenticated",
                "data" => null
            ];
        }

        $status = $this->paymentService->getConnectStatus($userId);

        return [
            "success" => true,
            "message" => $status['connected'] ? "Stripe account connected" : ($status['pending'] ? "Stripe account setup incomplete" : "No Stripe account connected"),
            "data" => $status
        ];
    }

    public function disconnectAccount()
    {
        $userId = $this->userId();
        if (!$userId) {
            return [
                "success" => false,
                "message" => "User not authenticated",
                "data" => null
            ];
        }

        try {
            $this->paymentService->disconnectAccount($userId);
            return [
                "success" => true,
                "message" => "Stripe account disconnected",
                "data" => null
            ];
        } catch (Throwable $th) {
            return [
                "success" => false,
                "message" => "Error disconnecting Stripe account: " . $th->getMessage(),
                "data" => null
            ];
        }
    }

    public function createCheckoutSession()
    {
        // Paid tickets require a logged-in account (enforced by AuthMiddleware
        // on the route and by the service's null-buyer guard).
        $userId = $this->userId();

        try {
            $result = $this->paymentService->createCheckoutSession($userId, $this->requestData());
            return [
                "success" => true,
                "message" => "Checkout session created successfully",
                "data" => $result
            ];
        } catch (Throwable $th) {
            return [
                "success" => false,
                "message" => "Error creating checkout session: " . $th->getMessage(),
                "data" => null
            ];
        }
    }

    public function handleWebhook()
    {
        $payload = @file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? getenv('STRIPE_WEBHOOK_SECRET') ?? '';

        if (empty($secret)) {
            http_response_code(500);
            echo json_encode(["error" => "Stripe webhook secret is not configured"]);
            return null;
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid payload"]);
            return null;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid signature"]);
            return null;
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->paymentService->recordCompletedPayment($event->data->object);
                break;
            case 'account.updated':
                $this->paymentService->recordAccountUpdate($event->data->object);
                break;
        }

        http_response_code(200);
        echo json_encode(["success" => true]);
        return null;
    }
}