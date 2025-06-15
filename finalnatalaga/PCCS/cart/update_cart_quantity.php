<?php
    session_start();

    $prod_id = $_POST['prod_id'] ?? null;
    $price_type = $_POST['price_type'] ?? null;
    $new_quantity = $_POST['quantity'] ?? null;

    if ($prod_id !== null && $price_type !== null && $new_quantity !== null) {
        $prod_id = intval($prod_id);
        $new_quantity = intval($new_quantity);

        if ($new_quantity < 1) {
            $new_quantity = 1;
        }

        $cart_item_key = $prod_id . "_" . $price_type;

        if (isset($_SESSION['cart'][$cart_item_key])) {
            $_SESSION['cart'][$cart_item_key]['quantity'] = $new_quantity;
        }
    }
    header("Location: cart.php");
    exit;