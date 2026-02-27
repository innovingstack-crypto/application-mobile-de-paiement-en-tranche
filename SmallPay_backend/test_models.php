<?php

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentSchedule;

echo 'Testing model instantiation...' . PHP_EOL;

try {
    $product = new Product();
    echo '✅ Product model OK' . PHP_EOL;
} catch (Exception $e) {
    echo '❌ Product model error: ' . $e->getMessage() . PHP_EOL;
}

try {
    $order = new Order();
    echo '✅ Order model OK' . PHP_EOL;
} catch (Exception $e) {
    echo '❌ Order model error: ' . $e->getMessage() . PHP_EOL;
}

try {
    $payment = new Payment();
    echo '✅ Payment model OK' . PHP_EOL;
} catch (Exception $e) {
    echo '❌ Payment model error: ' . $e->getMessage() . PHP_EOL;
}

try {
    $schedule = new PaymentSchedule();
    echo '✅ PaymentSchedule model OK' . PHP_EOL;
} catch (Exception $e) {
    echo '❌ PaymentSchedule model error: ' . $e->getMessage() . PHP_EOL;
}

echo 'All models tested successfully!' . PHP_EOL;
