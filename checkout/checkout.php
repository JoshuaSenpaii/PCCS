<?php
    session_start();

    require_once '../assets/connection.php';

    $cart_items_session = $_SESSION['cart'] ?? [];
    $total_cart_amount = 0;
    $products_from_db = [];
    $checkout_message = "";

    if (empty($cart_items_session)) {
        $_SESSION['checkout_message'] = "<div class='info'>Your cart is empty. Please add items before checking out.</div>";
        header("Location: ../cart/cart.php");
        exit();
    }

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
            $checkout_message = "<div class='error'>Error preparing product fetch statement: " . mysqli_error($conn) . "</div>";
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
                $checkout_message = "<div class='error'>Error fetching product details: " . mysqli_error($conn) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $checkout_message = "<div class='info'>No valid products to display for checkout.</div>";
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Checkout - Parque Cafe</title>
        <link rel="stylesheet" href="../assets/css/checkout.css" />
        <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css)"/>
    </head>
    <body>
        <div class="checkout-container">
            <h1>Checkout</h1>
            <?php
            if (isset($_SESSION['checkout_message'])) {
                echo $_SESSION['checkout_message'];
                unset($_SESSION['checkout_message']);
            }
            echo $checkout_message;
            ?>

            <div class="order-summary">
                <h2>Order Summary</h2>
                <?php
                if (!empty($cart_items_session) && !empty($products_from_db)) {
                    foreach ($cart_items_session as $item_key => $item_data) {
                        $prod_id = $item_data['prod_id'];
                        $quantity = $item_data['quantity'];
                        $price_type = $item_data['price_type'];

                        $product = $products_from_db[$prod_id] ?? null;

                        if ($product) {
                            $product_name = htmlspecialchars($product['prod_name']);
                            $price_per_item = 0;
                            $display_price_label = "";

                            if ($price_type === 'upsize' && isset($product['upsize_price'])) {
                                $price_per_item = $product['upsize_price'];
                                $display_price_label = " (Upsize)";
                            } else if ($price_type === 'regular' && isset($product['regular_price'])) {
                                $price_per_item = $product['regular_price'];
                                $display_price_label = " (Regular)";
                            } else {
                                $price_per_item = 0;
                                $display_price_label = " (Invalid Price)";
                            }

                            $subtotal = $price_per_item * $quantity;
                            $total_cart_amount += $subtotal;
                            ?>
                            <div class="summary-item">
                                <span>
                                    <?php echo $product_name . $display_price_label; ?> (x<?php echo htmlspecialchars($quantity); ?>)</span>
                                <span>₱ <?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo "<p class='message info'>No items in cart for checkout.</p>";
                }
                ?>
                <div class="summary-total">
                    <span>Total Amount:</span>
                    <span>₱ <?php echo number_format($total_cart_amount, 2); ?></span>
                </div>
            </div>

            <form action="place_order.php" method="post" class="payment-section">
                <label for="payment_method">Choose Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="gcash">Gcash</option>
                    <option value="cash_on_delivery">Cash on Delivery</option>
                </select>

                <div id="gcash_details" class="gcash-details-box" style="display: none;">
                    <h3>Pay via Gcash</h3>
                    <p>Please send the total amount (₱ <?php echo number_format($total_cart_amount, 2); ?>) to our Gcash number:</p>
                    <p style="font-size: 1.2em; font-weight: bold; color: #5d4037;">gcash number here</p>
                    <p>After sending, please enter the Gcash Transaction Reference Number:</p>
                    <input type="text" name="gcash_transaction_id" id="gcash_transaction_id" placeholder="Enter Gcash Transaction ID (e.g., 1234567890)">
                </div>

                <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars(sprintf("%.2f", $total_cart_amount)); ?>">
                <button type="submit">Place Order</button>
            </form>
        </div>
        <script>
            function toggleGcashDetails() {
                const paymentMethod = document.getElementById('payment_method').value;
                const gcashDetails = document.getElementById('gcash_details');
                if (paymentMethod === 'gcash') {
                    gcashDetails.style.display = 'block';
                } else {
                    gcashDetails.style.display = 'none';
                }
            }
            document.addEventListener('DOMContentLoaded', toggleGcashDetails);
        </script>
    </body>
</html>