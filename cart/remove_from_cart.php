<?php
    session_start();

    $prod_id = $_POST['prod_id'] ?? null;
    $price_type = $_POST['price_type'] ?? null;

    if ($prod_id !== null && $price_type !== null) {
        $prod_id = intval($prod_id);
        $cart_item_key = $prod_id . "_" . $price_type;

        if (isset($_SESSION['cart'][$cart_item_key])) {
            unset($_SESSION['cart'][$cart_item_key]);
        }
    }

    header("Location: cart.php");
    exit;