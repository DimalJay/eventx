<?php

use Middlewares\AuthMiddleware;
use Controllers\PaymentController;

$paymentController = new PaymentController();

$router->post("/payment/connect", [$paymentController, "connectAccount"], [AuthMiddleware::class]);
$router->get("/payment/connect-status", [$paymentController, "connectStatus"], [AuthMiddleware::class]);
$router->post("/payment/disconnect", [$paymentController, "disconnectAccount"], [AuthMiddleware::class]);
// Paid tickets require a logged-in account. Guests cannot checkout here.
$router->post("/payment/checkout-session", [$paymentController, "createCheckoutSession"], [AuthMiddleware::class]);
$router->post("/payment/webhook", [$paymentController, "handleWebhook"]);
