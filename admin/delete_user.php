<?php
    session_start();
    require_once '../assets/connection.php';

    if (!isset($_SESSION["loggedIn"]) || $_SESSION["role"] != 'admin') {
        header("location: ../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        // to prevent self-delete
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['delete'] = "<div class='error'>You cannot delete your own account while logged in.</div>";
            header("location: dashboard.php");
            exit();
        }

        // if user exists
        $check_stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_id = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            mysqli_stmt_close($check_stmt);

            // if exist = delete
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            $result = mysqli_stmt_execute($stmt);

            if ($result && mysqli_stmt_affected_rows($stmt) > 0) {
                $_SESSION['delete'] = "<div class='success'>User deleted successfully.</div>";
            } else {
                $_SESSION['delete'] = "<div class='error'>Failed to delete user.</div>";
            }

            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($check_stmt);
            $_SESSION['delete'] = "<div class='error'>User not found.</div>";
        }

        mysqli_close($conn);
        header("location: dashboard.php");
        exit();
    } else {
        // Invalid access (GET, missing param, etc)
        $_SESSION['delete'] = "<div class='error'>Invalid request.</div>";
        header("location: dashboard.php");
        exit();
    }
