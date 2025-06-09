<?php
    session_start();
    require_once '../assets/connection.php';

    if(!isset($_SESSION["loggedIn"]) || $_SESSION["role"] != 'admin'){
        header("location: ../auth/login.php");
        exit;
    }

    $user_sql = "SELECT user_id, username, role FROM users";
    $user_result = mysqli_query($conn, $user_sql);

    $product_sql = "SELECT prod_id, prod_name, regular_price, upsize_price FROM products";
    $product_result = mysqli_query($conn, $product_sql);

?>

    <!DOCTYPE html>
    <Html lang="en">
        <head>
            <title>Admin Dashboard</title>
            <link rel="stylesheet" type="text/css" href="../assets/css/dashboard.css">
            <script type="text/javascript" src="../assets/js/dashboard.js"></script>
        </head>
        <body>
            <h1>Admin Dashboard</h1>
            <?php
                if(isset($_SESSION["delete"])){
                    echo $_SESSION["delete"];
                    unset($_SESSION["delete"]);
                }
                if(isset($_SESSION["update"])){
                    echo $_SESSION["update"];
                    unset($_SESSION["update"]);
                }
            ?>

            <h2>Manage Users</h2>
            <table>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php $counter = 1; while($user = mysqli_fetch_assoc($user_result)) {?>
                    <tr>
                        <td><?php echo $counter; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <form action="update_user_role.php" method="post">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <select name="role">
                                    <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
                                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <button type="submit">Update Role</button>
                            </form>
                        </td>
                        <td>
                            <form action="delete_user.php?user_id=x" method="post" style="display: inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" class="delete-user-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php $counter++; }?>
            </table>

            <h2>Add New Product</h2>
            <form action="add_product.php" method="post">
                <label>Product Name:</label>
                <input type="text" name="prod_name" required> <br><br>
                <label>Regular Price (PHP):</label>
                <input type="number" step="0.01" name="regular_price" required> <br><br>
                <label>Upsize Price (PHP):</label>
                <input type="number" step="0.01" name="upsize_price" required> <br><br>
                <button type="submit">Add Product</button>
            </form>

            <h2>Manage Products</h2>
            <table>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Regular Price (PHP)</th>
                    <th>Upsize Price (PHP)</th>
                    <th colspan="2">Actions</th>
                </tr>
                <?php $counter = 1; while ($product = mysqli_fetch_assoc($product_result)) { ?>
                    <tr>
                        <form action="update_product.php" method="post">
                            <td><?php echo $counter; ?></td>
                            <td><input type="text" name="prod_name" value="<?php echo htmlspecialchars($product['prod_name']); ?>"></td>
                            <td><input type="number" step="0.01" name="regular_price" value="<?php echo $product['regular_price']; ?>"></td>
                            <td><input type="number" step="0.01" name="upsize_price" value="<?php echo $product['upsize_price']; ?>"></td>
                            <td>
                                <input type="hidden" name="prod_id" value="<?php echo $product['prod_id']; ?>">
                                <button type="submit">Update</button>
                            </td>
                        </form>

                        <td>
                            <form action="delete_product.php" method="post" style="display:inline;">
                                <input type="hidden" name="prod_id" value="<?php echo $product['prod_id']; ?>">
                                <button type="submit" class="delete-product-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php $counter++; } ?>
            </table>

            <!--
            <section>
                <h2>Welcome, <?php echo $_SESSION["username"]; ?>!</h2>
                <h3>Parque Cafe Website User List:</h3>

                <br>

                <?php
                if(isset($_SESSION['delete'])){
                    echo $_SESSION['delete'];
                    unset($_SESSION['delete']);
                }
                ?>

                <br><br>

                <table>
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $sql = "SELECT * FROM users" ;
                    $result = mysqli_query($conn,$sql);

                    if($result !== false){
                        $rows = mysqli_num_rows($result);
                        $sn = 1;
                        if($rows > 0){
                            while($row = mysqli_fetch_assoc($result)){
                                $id = $row["user_id"];
                                $username = $row["username"];
                                $email = $row["email"];
                                $role = $row["role"];
                                ?>

                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><?php echo $row["user_id"]; ?></td>
                                    <td><?php echo $row["username"]; ?></td>
                                    <td><?php echo $row["email"]; ?></td>
                                    <td><?php echo $row["role"]; ?></td>
                                    <td><a href="delete_user.php?id=<?php echo $id; ?>">Delete</a></td>
                                </tr>

                                <?php
                            }
                        }
                    }
                    ?>
                </table>
                <br>
                <a href="../auth/logout.php">Logout</a>
            </section> -->
        </body>
    </Html>
