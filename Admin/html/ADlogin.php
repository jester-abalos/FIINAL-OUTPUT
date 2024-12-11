<?php
session_start();
require '../../connection/connection.php'; // Ensure MongoDB connection is set up here

// Create a new MongoDB client instance // Modify the connection string if necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL); // Use 'email' as form field
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // MongoDB collection
        $db = $client->GADGETHUB; // Access the GADGETHUB database
        $admincollection = $db->admin; // Access the 'admin' collection

        // Check for the user by email
        $filter = ['email' => $email];
        $user = $admincollection->findOne($filter);

        if ($user) {
            // Check if the password field exists and is not null
            if (!empty($user['password'])) { // Updated to match the correct field name
                // Verify the password
                if (password_verify($password, $user['password'])) { 
                    // Store the user's unique ID and other relevant details in the session
                    $_SESSION['user_id'] = (string) $user['_id']; // Store user ID as string
                    $_SESSION['email'] = $user['email']; // Store email

                    // Set the user status to 'online' in the database when they log in
                    $userId = $_SESSION['user_id'];
                    
                    // Update the user's status to 'online'
                    $admincollection->updateOne(
                        ['_id' => new MongoDB\BSON\ObjectId($userId)],
                        ['$set' => ['status' => 'online']]
                    );

                    // Redirect to the admin dashboard or homepage after successful login
                    header("Location: /admin/html/dashboard.php"); // Replace with your admin page path
                    exit();
                } else {
                    echo "<script>alert('Incorrect password.');</script>";
                }
            } else {
                echo "<script>alert('Password is not set for this user.');</script>";
            }
        } else {
            echo "<script>alert('Email does not exist.');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Gadget Hub</title>
    <link rel="stylesheet" href="../css/ADlogin.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <img src="../../assets/img/LOGO.png" alt="Gadget Hub Logo">
        <span>Admin Portal</span>
    </nav>
    
    <!-- Main Container -->
    <div class="Container">
        <form action="" method="POST">
            <h1>WELCOME TO GADGET HUB!</h1>
            <h3>Login as Admin</h3>
            
            <!-- Input Fields -->
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <!-- Login Button -->
            <button type="submit">LOGIN</button>
            
            <!-- Reset Password Link -->
            <a href="/admin/reset-password">Forgot Password?</a>
        </form>
    </div>
</body>
</html>
