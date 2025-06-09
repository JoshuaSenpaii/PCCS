<?php
    session_start();
    require_once '../assets/connection.php';

    if (!isset($_SESSION["loggedIn"]) || $_SESSION["role"] != 'admin') {
        header("location: ../auth/login.php");
        exit;
    }

    if (isset($_POST['user_id'], $_POST['role'])) {
        $user_id = intval($_POST['user_id']);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

        $sql = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $role, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['update'] = "<div class='success'>User role updated successfully!</div>";
        } else {
            $_SESSION['update'] = "<div class='error'>Failed to update user role.</div>";
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: dashboard.php");
exit();
