<?php
session_start();
require '../../connection/connection.php'; // MongoDB connection

use MongoDB\Client;

$client = new Client("mongodb://localhost:27017");
$collection = $client->GADGETHUB->users;

$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !preg_match('/^[a-f\d]{24}$/i', $userId)) {
    // Invalid userId; redirect to login
    header("Location: Login.php");
    exit;
}

// Fetch the user from the database by their user ID
$user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);

// Check if the user is online
if ($user['status'] !== 'online') {
    echo "<script>alert('You must be logged in as online to manage your profile.'); window.location.href='Login.php';</script>";
    exit;
}

$gender = $user['gender'] ?? ''; // Default to an empty string if 'gender' doesn't exist

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? ''); // Email is not editable
    $phoneNumber = trim($_POST['phonenumber'] ?? '');
    $gender = trim($_POST['gender'] ?? ''); // Update gender from form submission
    $dateOfBirth = trim($_POST['dateofbirth'] ?? '');
    $newPassword = trim($_POST['newpassword'] ?? '');

    $updateData = [
        'name' => $username,
        'address' => $address,
        'phone_number' => $phoneNumber,
        'gender' => $gender,
        'date_of_birth' => $dateOfBirth,
    ];

    // Check if the user wants to update the password
    if (!empty($newPassword)) {
        $updateData['passwordHash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Ensure google_id and email are not overwritten
    if (!empty($user['google_id'])) {
        $updateData['google_id'] = $user['google_id'];
    }
    if (!empty($user['email'])) {
        $updateData['email'] = $user['email'];
    }

    // Update the user profile
    $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($userId)],
        ['$set' => $updateData]
    );

    header("Location: ManageProfile.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link rel="stylesheet" href="../css/ManageProfile.css" />
    <link rel="stylesheet" href="../css/navbar.css" />
</head>
<body>
<?php include '../html/navbar.php'; ?>
<div class="container">
    <div class="menu">
        <div class="useraccount">
            <div id="profilepic"><img src="../img/profilepic.png" alt="" /></div>
            <div class="profilename"><?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <button id="myaccount" onclick="location.href='manageprofile.php'">My Account</button>
        <span></span>
        <button id="myorders" onclick="location.href='orderspage.php'">My Orders</button>
        <span></span>
        <button id="notifications" onclick="location.href='notificationpage.php'">Notifications</button>
        <span></span>
        <button id="logout" onclick="location.href='Logout.php'">Log Out</button>
    </div>

    <div class="settings">
        <div class="settingsheader">
            <h2>My Profile</h2>
            <p>Manage and protect your account</p>
        </div>
        <form id="profileForm" method="POST">
            <!-- Read-Only Google ID and Email Fields -->
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
            <br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
            <br>

            <!-- Editable Profile Fields -->
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>
            <label for="phonenumber">Phone Number:</label>
            <input type="text" id="phonenumber" name="phonenumber" value="<?php echo htmlspecialchars($user['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>
            <label>Gender:</label>
            <input type="radio" id="male" name="gender" value="male" <?php echo ($user['gender'] === 'male') ? 'checked' : ''; ?>> Male
            <input type="radio" id="female" name="gender" value="female" <?php echo ($user['gender'] === 'female') ? 'checked' : ''; ?>> Female
            <br>
            <label for="dateofbirth">Date of Birth:</label>
            <input type="date" id="dateofbirth" name="dateofbirth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <br>

            <label for="newpassword">New Password</label>
            <input type="password" id="newpassword" name="newpassword" />

            <label for="confirmpassword">Confirm Password</label>
            <input type="password" id="confirmpassword" name="confirmpassword" />
            <br>

            <!-- Save Button -->
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>
