<?php
    session_start();
    require_once '../assets/connection.php';

    $cart_items_session = $_SESSION['cart'] ?? [];
    $total_cart_amount = 0;
    $products_from_db = [];
    $cart_message = "";

    $distinct_product_ids = [];
    foreach ($cart_items_session as $item_key => $item_data) {
        $distinct_product_ids[] = $item_data['prod_id'];
    }

    if (!empty($distinct_product_ids)) {
        $clean_product_ids = array_map('intval', array_unique($distinct_product_ids));
        $placeholders = implode(',', array_fill(0, count($clean_product_ids), '?'));
        $sql = "SELECT prod_id, prod_name, regular_price, upsize_price FROM products WHERE prod_id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt === false) {
            $cart_message = "<div class='error'>Error preparing product fetch statement: " . mysqli_error($conn) . "</div>";
        } else {
            $types = str_repeat('i', count($clean_product_ids));
            mysqli_stmt_bind_param($stmt, $types, ...$clean_product_ids);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $products_from_db[$row['prod_id']] = $row;
                }
            } else {
                $cart_message = "<div class='error'>Error fetching product details: " . mysqli_error($conn) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $cart_message = "<div class='info'>Your cart is empty.</div>";
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Your Cart - Parque Cafe</title>
        <link rel="stylesheet" href="../assets/css/index.css" />
        <link rel="stylesheet" href="../assets/css/cart.css" />
        <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css)"/>
    </head>
    <body>
        <div class="cart-container">
            <h1>Your Shopping Cart</h1>
            <?php echo $cart_message; ?>

            <?php if (!empty($cart_items_session) && !empty($products_from_db)): ?>
                <table class="cart-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($cart_items_session as $item_key => $item_data) {
                        $prod_id = $item_data['prod_id'];
                        $quantity = $item_data['quantity'];
                        $price_type = $item_data['price_type'];


                        $product = $products_from_db[$prod_id] ?? null;

                        if ($product) {
                            $product_name = htmlspecialchars($product['prod_name']);

                            $product_img = htmlspecialchars($product['prod_img'] ?? '[https://placehold.co/80x80/cccccc/333333?text=No+Image](https://placehold.co/80x80/cccccc/333333?text=No+Image)');

                            $price_per_item = 0;
                            $display_price_type = "";

                            if ($price_type === 'upsize' && isset($product['upsize_price'])) {
                                $price_per_item = $product['upsize_price'];
                                $display_price_type = " (Upsize)";
                            } else if ($price_type === 'regular' && isset($product['regular_price'])) {
                                $price_per_item = $product['regular_price'];
                                $display_price_type = " (Regular)";
                            }

                            $subtotal = $price_per_item * $quantity;
                            $total_cart_amount += $subtotal;
                            ?>
                            <tr>
                                <td data-label="Product">
                                    <div class="product-info">
                                        <img src="<?php echo $product_img; ?>" alt="<?php echo $product_name; ?>" class="cart-item-img">
                                        <span><?php echo $product_name . $display_price_type; ?></span>
                                    </div>
                                </td>
                                <td data-label="Price">₱ <?php echo number_format($price_per_item, 2); ?></td>
                                <td data-label="Quantity">
                                    <form action="update_cart_quantity.php" method="post" class="quantity-form">
                                        <input type="hidden" name="prod_id" value="<?php echo htmlspecialchars($prod_id); ?>">
                                        <input type="hidden" name="price_type" value="<?php echo htmlspecialchars($price_type); ?>">
                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" min="1">
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                                <td data-label="Subtotal">₱ <?php echo number_format($subtotal, 2); ?></td>
                                <td data-label="Actions">
                                    <form action="remove_from_cart.php" method="post">
                                        <input type="hidden" name="prod_id" value="<?php echo htmlspecialchars($prod_id); ?>">
                                        <input type="hidden" name="price_type" value="<?php echo htmlspecialchars($price_type); ?>">
                                        <button type="submit" class="remove-item-btn">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>

                <div class="cart-actions">
                    <a href="../index.php" class="btn">Continue Shopping</a>
                    <div class="total-amount">
                        Total: ₱ <?php echo number_format($total_cart_amount, 2); ?>
                    </div>
                    <a href="../checkout/checkout.php" class="btn">Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <div class="empty-cart-message">
                    <p>Your cart is empty.</p>
                    <a href="../index.php" class="btn">Go to Menu</a>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>