<?php
session_start();
require '../../connection/connection.php'; // Ensure MongoDB connection is set up here


// Google API client configuration
$client = new Google_Client();
$client->setClientId('93359055132-m0r7af88a441ilcf3bh4uqq4pi77hrt2.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:3000/Customer/html/Login.php');
$client->addScope('email');
$client->addScope('profile');

// Handle OAuth 2.0 flow
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Get user profile information
        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        // Save user data in session
        $_SESSION['user_id'] = $userInfo->id;
        $_SESSION['email'] = $userInfo->email;
        $_SESSION['name'] = $userInfo->name;
        $_SESSION['picture'] = $userInfo->picture;

        // Redirect to another page or display user info
        header('Location: dashboard.php');
        exit;
    }
}

// Generate the login URL
$loginUrl = $client->createAuthUrl();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST["username"], FILTER_SANITIZE_EMAIL); // Accept email as username input
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // MongoDB collection
        $db = $client->GADGETHUB;
        $userCollection = $db->users;

        // Check for the user by email
        $filter = ['email' => $email];
        $user = $userCollection->findOne($filter);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['passwordHash'])) { // Use `passwordHash` from the DB
                // Store the user's unique ID and other relevant details in the session
                $_SESSION['user_id'] = (string) $user['_id']; // Store user ID as string
                $_SESSION['email'] = $user['email']; // Store email

                // Set the user status to 'online' in the database when they log in
                $userId = $_SESSION['user_id'];
                
                // Update the user's status to 'online'
                $userCollection->updateOne(
                    ['_id' => new MongoDB\BSON\ObjectId($userId)],
                    ['$set' => ['status' => 'online']]
                );

                // Redirect to the Dashboard
                header("Location: Dashboard.php");
                exit;
            } else {
                echo "<script>alert('Incorrect password.');</script>";
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
    <title>Login Page</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <nav class="navbar">
        <div class="logo">
            <img src="../../assets/img/LOGO1.png" alt="">
        </div>
        <div class="LOGIN">
            <h1>LOG-IN</h1>
        </div>
    </nav>
    <div class="Login-bg">
        <div class="Login_Container">
            <form action="" method="POST" class="login-form">
                <img class="profile-login" src="../../assets/img/profilepic.png" alt="">
                <div class="form-group">
                    <input type="email" id="username" name="username" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class="fa fa-eye" id="show-pass" onclick="showPassword()"></i>
                </div>
               
                <button type="submit">
                    <p>LOGIN</p>
                </button>
                <div class="or-span">
                    <span></span>
                    <p>Or</p>
                    <span></span>
                </div>
                <div class="connect-with" class="google">
                    <img src="../../assets/img/google.png" alt="">
                </div>
                <div class="sign-up">
                    <p>New Customer?</p>
                    <a href="Register.php">
                        <p class="loginsignup">Sign-Up</p>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPassword() {
            const passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
</body>

</html>