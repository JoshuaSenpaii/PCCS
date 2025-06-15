<?php
    session_start();
    require_once '../assets/connection.php';

    if (!isset($_SESSION["loggedIn"]) || $_SESSION["role"] != 'admin') {
        header("location: ../auth/login.php");
        exit;
    }

    if (isset($_POST['prod_id'])) {
        $prod_id = intval($_POST['prod_id']);

        $sql = "DELETE FROM products WHERE prod_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $prod_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['update'] = "<div class='success'>Product deleted successfully!</div>";
        } else {
            $_SESSION['update'] = "<div class='error'>Failed to delete product.</div>";
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: dashboard.php");
