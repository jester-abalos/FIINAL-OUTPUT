<?php
session_start();
require '../../connection/connection.php'; // Ensure MongoDB connection is set up here

// Google API client configuration
$googleClient = new Google_Client();
$googleClient->setClientId('93359055132-m0r7af88a441ilcf3bh4uqq4pi77hrt2.apps.googleusercontent.com');
$googleClient->setClientSecret('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');
$googleClient->setRedirectUri('http://localhost:3000/Customer/html/Login.php');
$googleClient->addScope('email');
$googleClient->addScope('profile');

// MongoDB connection setup
$mongoClient = new MongoDB\Client();
$db = $mongoClient->GADGETHUB;
$userCollection = $db->users;

// Handle OAuth 2.0 flow
if (isset($_GET['code'])) {
    $token = $googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $googleClient->setAccessToken($token['access_token']);

        // Get user profile information
        $oauth2 = new Google_Service_Oauth2($googleClient);
        $userInfo = $oauth2->userinfo->get();

        // Save user data in session
        $_SESSION['user_id'] = $userInfo->id;
        $_SESSION['email'] = $userInfo->email;
        $_SESSION['name'] = $userInfo->name;
        $_SESSION['picture'] = $userInfo->picture;

        // Check if the user exists in MongoDB
        $user = $userCollection->findOne(['email' => $userInfo->email]);

        if ($existingUser) {
            // User exists: Update their name and status
            $userCollection->updateOne([
                '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])],
                ['$set' => ['status' => 'online']]
            );
        } else {
            // New user: Insert into MongoDB
            $userCollection->insertOne([
                'username' => $userInfo->email,
                'email' => $userInfo->email,
                'username' => $userInfo->name,
                'status' => 'online',
                'address' => '',
                'phone' => '',
                'gender' => '',
                'termsAgreed' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
            ]);
        }

        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        echo "Error: Unable to fetch access token.";
    }
}

// Generate the Google login URL
$loginUrl = $googleClient->createAuthUrl();



// Handle manual login form submission (if needed)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST["username"], FILTER_SANITIZE_EMAIL); // Accept email as username input
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // Check for the user by email
        $filter = ['email' => $email];
        $user = $userCollection->findOne($filter);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['passwordHash'])) { // Use `passwordHash` from the DB
                // Store the user's unique ID and other relevant details in the session
                $_SESSION['user_id'] = (string) $user['_id']; // Store user ID as string
                $_SESSION['email'] = $user['email']; // Store email

                // Update the user's status to 'online'
                $userCollection->updateOne([
                    '_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])],
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
                <!-- Google Login Button -->
                <div class="connect-with">
                    <a href="<?php echo htmlspecialchars($loginUrl); ?>" class="google-login">
                        <img src="../../assets/img/google.png" alt="Google Login">
                    </a>
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

