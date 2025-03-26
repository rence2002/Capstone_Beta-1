<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
    header("location: ../index.php");
    exit;
}

$userId = $_SESSION["user_id"];
include("../config/database.php");

// Fetch user information
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_user_info WHERE User_ID = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle file upload if a new profile picture is selected
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $uploadDir = '../uploads/user/';
            $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $fileName = $userId . '_profile.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
                $picPath = 'uploads/user/' . $fileName;
            }
        }

        // Handle password update if requested
        if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
            // Verify all password fields are filled
            if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
                throw new Exception('All password fields are required to change password');
            }

            // Verify current password
            $stmt = $pdo->prepare("SELECT Password FROM tbl_user_info WHERE User_ID = :userId");
            $stmt->execute(['userId' => $userId]);
            $currentHash = $stmt->fetchColumn();

            if (!password_verify($_POST['current_password'], $currentHash)) {
                throw new Exception('Current password is incorrect');
            }

            // Verify new passwords match
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('New passwords do not match');
            }

            // Update password
            $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $params['password'] = $newHash;
            
            // Add password to the UPDATE query
            $stmt = $pdo->prepare("
                UPDATE tbl_user_info SET 
                First_Name = :firstName,
                Last_Name = :lastName,
                Middle_Name = :middleName,
                Home_Address = :homeAddress,
                Email_Address = :email,
                Mobile_Number = :mobileNumber,
                Password = :password" .
                (isset($picPath) ? ", PicPath = :picPath" : "") .
                " WHERE User_ID = :userId"
            );
        } else {
            // Update user information
            $stmt = $pdo->prepare("
                UPDATE tbl_user_info SET 
                First_Name = :firstName,
                Last_Name = :lastName,
                Middle_Name = :middleName,
                Home_Address = :homeAddress,
                Email_Address = :email,
                Mobile_Number = :mobileNumber" .
                (isset($picPath) ? ", PicPath = :picPath" : "") .
                " WHERE User_ID = :userId"
            );
        }

        $params = [
            'firstName' => $_POST['firstName'],
            'lastName' => $_POST['lastName'],
            'middleName' => $_POST['middleName'],
            'homeAddress' => $_POST['homeAddress'],
            'email' => $_POST['email'],
            'mobileNumber' => $_POST['mobileNumber'],
            'userId' => $userId
        ];

        if (isset($picPath)) {
            $params['picPath'] = $picPath;
        }

        $stmt->execute($params);
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../static/css-files/profile.css">
    <link rel="stylesheet" href="../static/css-files/Home.css">
    <link rel="stylesheet" href="../static/css-files/profile.css">
    <link rel="stylesheet" href="../static/css-files/edit-profile.css">
</head>
<body>
<header>
<nav class="navbar">
      <a href="home.php" class="logo">
        <img src="../static/images/rm raw png.png" alt=""  class="logo">
      </a>
        <ul class="menu-links">
            <li class="dropdown">
                <a href="home.php" class="active dropdown-toggle">Home</a>
                <ul class="dropdown-menus">
                    <li><a href="#about-section">About</a></li>
                    <li><a href="#contact-section">Contacts</a></li>
                    <li><a href="#offers-section">Offers</a></li>
                </ul>
            </li>
            <li><a href="../review/review.php">Reviews</a></li>
            <li><a href="../gallery/gallery.php">Gallery</a></li>
            <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
            <ul class="menu-links">
            <li class="dropdown">
            <a href="profile.php" class="profile" id="sign_in">Profile</a>
                <ul class="dropdown-menus">
                    <li><a href="../profile/profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            <span id="close-menu-btn" class="material-symbols-outlined">close</span>
        </ul>
        <span id="hamburger-btn" class="material-symbols-outlined">menu</span>
    </nav>
</header>
    <main>
    <div class="container-profile">
            <div class="profile-icon-con">
                <img class="profile-icon" src="<?php echo ($user['PicPath']) ? '../uploads/user/' . basename($user['PicPath']) : '../static/profile-icon.png'; ?>" alt="Profile Icon">
                <p class="nameofuser"><?= $user['First_Name'] . " " . $user['Last_Name'] ?></p>
                <!-- <a class="ep--edit" href="edit-profile.php"></a> -->
            </div>
            <!-- <div class="logout-con">
                <a href="logout.php" class="logout-btn">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
            </div> -->
        </div>

        <div class="edit-form">
            <h2>Edit Profile</h2>
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($user['First_Name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($user['Last_Name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" value="<?= htmlspecialchars($user['Middle_Name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="homeAddress">Home Address</label>
                    <input type="text" id="homeAddress" name="homeAddress" value="<?= htmlspecialchars($user['Home_Address'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['Email_Address']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="mobileNumber">Mobile Number</label>
                    <input type="tel" id="mobileNumber" name="mobileNumber" value="<?= htmlspecialchars($user['Mobile_Number'] ?? '') ?>">
                </div>
                <div class="form-group password-section">
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" class="submit-btn">Save Changes</button>
                    <button href="profile.php" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Same footer as profile.php -->
    <footer class="footer">
        <!-- Footer content -->
    </footer>

    <script src="../static/Javascript-files/script.js"></script>
</body>
</html>