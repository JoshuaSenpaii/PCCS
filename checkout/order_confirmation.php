<?php
    session_start();
    require_once '../assets/connection.php';

    $order_id = $_GET['order_id'] ?? 'N/A';
    $order_message = $_SESSION['order_message'] ?? "<div class='info'>Your order has been placed.</div>";

    if (isset($_SESSION['order_message'])) {
        unset($_SESSION['order_message']);
    }
    ?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Order Confirmation - Parque Cafe</title>
        <link rel="stylesheet" href="../assets/css/order_c.css" />
        <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css)"/>
    </head>
    <body>
    <div class="confirmation-container">
        <h1>Order Confirmed!</h1>
        <?php echo $order_message; ?>
        <p>Thank you for your purchase from Parque Cafe.</p>
        <p>Your order ID is: <span class="order-id"><?php echo htmlspecialchars($order_id); ?></span></p>
        <p>We'll send you an email with your order details shortly.</p>
        <a href="../index.php" class="btn">Continue Shopping</a>
    </div>
    </body>
</html>