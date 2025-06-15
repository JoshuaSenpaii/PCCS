<?php
    session_start();
    require_once '../assets/connection.php';

    if (!isset($_SESSION["loggedIn"]) || $_SESSION["role"] != 'admin') {
        header("location: ../auth/login.php");
        exit;
    }

    if (isset($_POST['prod_id'], $_POST['prod_name'], $_POST['regular_price'], $_POST['upsize_price'])) {
        $prod_id = intval($_POST['prod_id']);
        $prod_name = $_POST['prod_name'];
        $regular_price = floatval($_POST['regular_price']);
        $upsize_price = floatval($_POST['upsize_price']);

        $sql = "UPDATE products SET prod_name = ?, regular_price = ?, upsize_price = ? WHERE prod_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sddi", $prod_name, $regular_price, $upsize_price, $prod_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['update'] = "<div class='success'>Product updated successfully!</div>";
        } else {
            $_SESSION['update'] = "<div class='error'>Failed to update product.</div>";
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: dashboard.php");
