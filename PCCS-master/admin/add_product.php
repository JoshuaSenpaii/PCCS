<?php
    session_start();
    require_once '../assets/connection.php';

    if (!isset($_SESSION["loggedIn"]) || $_SESSION["role"] != 'admin') {
        header("location: ../auth/login.php");
        exit;
    }

    if (isset($_POST['prod_name'], $_POST['regular_price'], $_POST['upsize_price'])) {
        $prod_name = $_POST['prod_name'];
        $regular_price = floatval($_POST['regular_price']);
        $upsize_price = floatval($_POST['upsize_price']);

        $sql = "INSERT INTO products (prod_name, regular_price, upsize_price) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdd", $prod_name, $regular_price, $upsize_price);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['update'] = "<div class='success'>New product added successfully!</div>";
        } else {
            $_SESSION['update'] = "<div class='error'>Failed to add product.</div>";
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: dashboard.php");
