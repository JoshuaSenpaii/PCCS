<?php
    session_start();
    require_once '../assets/connection.php';

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['order_message'] = "<div class='error'>You must be logged in to place an order.</div>";
        header("Location: ../auth/login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'] ?? null;
    $cart_items_session = $_SESSION['cart'] ?? [];

    if (empty($cart_items_session) || empty($payment_method)) {
        $_SESSION['order_message'] = "<div class='error'>Cannot place an empty order or missing payment option.</div>";
        header("Location: checkout.php");
        exit();
    }

    $total_order_amount = 0;
    $db_product_prices = [];

    $distinct_product_ids_for_query = [];
    foreach ($cart_items_session as $item_key => $item_data) {
        $distinct_product_ids_for_query[] = $item_data['prod_id'];
    }
    $distinct_product_ids_for_query = array_unique($distinct_product_ids_for_query);

    if (!empty($distinct_product_ids_for_query)) {
        $clean_product_ids = array_map('intval', $distinct_product_ids_for_query);
        $placeholders = implode(',', array_fill(0, count($clean_product_ids), '?'));


        $sql_fetch_prices = "SELECT prod_id, regular_price, upsize_price FROM products WHERE prod_id IN ($placeholders)";
        $stmt_fetch_prices = mysqli_prepare($conn, $sql_fetch_prices);

        if ($stmt_fetch_prices === false) {
            $_SESSION['order_message'] = "<div class='error'>Failed to prepare product price fetch statement: " . mysqli_error($conn) . "</div>";
            header("Location: checkout.php");
            exit();
        }

        $types = str_repeat('i', count($clean_product_ids));
        mysqli_stmt_bind_param($stmt_fetch_prices, $types, ...$clean_product_ids);
        mysqli_stmt_execute($stmt_fetch_prices);
        $result_prices = mysqli_stmt_get_result($stmt_fetch_prices);

        if ($result_prices) {
            while ($row_price = mysqli_fetch_assoc($result_prices)) {
                $db_product_prices[$row_price['prod_id']] = [
                    'regular' => $row_price['regular_price'],
                    'upsize'  => $row_price['upsize_price']
                ];
            }
        } else {
            $_SESSION['order_message'] = "<div class='error'>Failed to fetch product prices. " . mysqli_error($conn) . "</div>";
            header("Location: checkout.php");
            exit();
        }
        mysqli_stmt_close($stmt_fetch_prices);
    } else {
        $_SESSION['order_message'] = "<div class='error'>No valid products in cart to process.</div>";
        header("Location: checkout.php");
        exit();
    }

    foreach ($cart_items_session as $item_key => $item_data) {
        $prod_id    = $item_data['prod_id'];
        $quantity   = $item_data['quantity'];
        $price_type = $item_data['price_type'];

        if (isset($db_product_prices[$prod_id])) {
            $product_db_prices = $db_product_prices[$prod_id];
            $current_item_price = 0;

            if ($price_type === 'upsize' && isset($product_db_prices['upsize'])) {
                $current_item_price = $product_db_prices['upsize'];
            } else if ($price_type === 'regular' && isset($product_db_prices['regular'])) {
                $current_item_price = $product_db_prices['regular'];
            } else {

                $_SESSION['order_message'] = "<div class='error'>Invalid price type or price for product ID: " . htmlspecialchars($prod_id) . "</div>";
                header("Location: checkout.php");
                exit();
            }
            $total_order_amount += ($current_item_price * $quantity);
        } else {
            $_SESSION['order_message'] = "<div class='error'>Product ID " . htmlspecialchars($prod_id) . " not found in database for price verification.</div>";
            header("Location: checkout.php");
            exit();
        }
    }

    $insert_order_sql = "INSERT INTO orders (user_id, total_amount, payment_method, order_status, order_date) VALUES (?, ?, ?, ?, NOW())";
    $stmt_order = mysqli_prepare($conn, $insert_order_sql);

    if ($stmt_order === false) {
        $_SESSION['order_message'] = "<div class='error'>Failed to prepare order insertion: " . mysqli_error($conn) . "</div>";
        header("Location: checkout.php");
        exit();
    }
    $order_status = 'pending';


    mysqli_stmt_bind_param($stmt_order, "idss", $user_id, $total_order_amount, $payment_method, $order_status);

    if (mysqli_stmt_execute($stmt_order)) {
        $order_id = mysqli_stmt_insert_id($stmt_order);
        mysqli_stmt_close($stmt_order);

        $insert_item_sql = "INSERT INTO order_items (order_id, prod_id, quantity, price, price_type) VALUES (?, ?, ?, ?, ?)";
        $stmt_item = mysqli_prepare($conn, $insert_item_sql);

        if ($stmt_item === false) {
            $_SESSION['order_message'] = "<div class='error'>Failed to prepare order items insertion. " . mysqli_error($conn) . "</div>";

            header("Location: checkout.php");
            exit();
        }

        foreach ($cart_items_session as $item_key => $item_data) {
            $prod_id    = $item_data['prod_id'];
            $quantity   = $item_data['quantity'];
            $price_type = $item_data['price_type'];

            $actual_price_for_item = 0;
            if (isset($db_product_prices[$prod_id])) {
                $product_db_prices = $db_product_prices[$prod_id];
                if ($price_type === 'upsize' && isset($product_db_prices['upsize'])) {
                    $actual_price_for_item = $product_db_prices['upsize'];
                } else if ($price_type === 'regular' && isset($product_db_prices['regular'])) {
                    $actual_price_for_item = $product_db_prices['regular'];
                }
            }

            mysqli_stmt_bind_param($stmt_item, "iiids", $order_id, $prod_id, $quantity, $actual_price_for_item, $price_type);
            mysqli_stmt_execute($stmt_item);
        }
        mysqli_stmt_close($stmt_item);

        unset($_SESSION['cart']);

        $_SESSION['order_message'] = "<div class='success'>Order placed successfully! Your order ID is: " . htmlspecialchars($order_id) . ".</div>";
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit();

    } else {
        $_SESSION['order_message'] = "<div class='error'>Error placing order: " . mysqli_error($conn) . "</div>";
        header("Location: checkout.php");
        exit();
    }