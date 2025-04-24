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
        // Handle file upload for a new valid ID if provided
        if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === 0) {
            $uploadDir = '../uploads/user/validid/';
            $fileExtension = pathinfo($_FILES['valid_id']['name'], PATHINFO_EXTENSION);
            $fileName = $userId . '_validid.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['valid_id']['tmp_name'], $uploadFile)) {
                $validIdPath = 'uploads/user/validid/' . $fileName;

                // Update the ID verification status and path in the database
                $stmt = $pdo->prepare("
                    UPDATE tbl_user_info 
                    SET Valid_ID_Path = :validIdPath, ID_Verification_Status = 'Unverified'
                    WHERE User_ID = :userId
                ");
                $stmt->execute([
                    'validIdPath' => $validIdPath,
                    'userId' => $userId
                ]);
            }
        }

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
    <link rel="stylesheet" href="../static/css-files/edit-profile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    <script src="../static/Javascript-files/script.js"></script>
</head>
<body>

<header>
  <nav class="navbar">
    <a href="../dashboard/home.php" class="logo">
      <img src="../static/images/rm raw png.png" alt="" class="logo">
    </a>
    <ul class="menu-links">
      <li class="dropdown">
        <a href="../dashboard/home.php">Home</a>
        <ul class="dropdown-menus">
          <li><a href="#about-section">About</a></li>
          <li><a href="#contact-section">Contacts</a></li>
          <li><a href="#offers-section">Offers</a></li>
        </ul>
      </li>
      <li><a href="../reviews/review.php">Reviews</a></li>
      <li><a href="../gallery/gallery.php" class="">Gallery</a></li>
      <li><a href="../cart/cart.php" class="cart" id="cart">Cart</a></li>
      <li class="dropdown">
        <a href="../profile/profile.php" class="profile activecon" id="sign_in">Profile</a>
        <ul class="dropdown-menus">
          <li><a href="../profile/profile.php">Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </li>
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
             <!-- Logout Button for Responsive Mode -->
        <a href="../logout/logout.php" class="logout-btn responsive-logout ">
    Logout <i class="fas fa-arrow-right"></i>
</a>
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
                    <label><strong>Valid ID Status:</strong></label>
                    <?php
                    $idStatus = htmlspecialchars($user['ID_Verification_Status']);
                    if ($idStatus === 'Valid') {
                        echo "<span class='status-valid'>Valid</span>";
                    } elseif ($idStatus === 'Invalid') {
                        echo "<span class='status-invalid'>Invalid</span>";
                        // Show the option to upload a new valid ID if the status is "Invalid"
                        echo '<div class="form-group">
                                <label for="valid_id">Upload New Valid ID</label>
                                <input type="file" id="valid_id" name="valid_id" accept="image/*">
                              </div>';
                    } else {
                        echo "<span class='status-unverified'>Unverified</span>";
                    }
                    ?>
                </div>


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
  <div class="footer-row">
    <div class="footer-col">
      <h4>Info</h4>
      <ul class="links">
        <li><a href="home.php">Home</a></li>
        <li><a href="#about-section">About Us</a></li>
        <li><a href="../gallery/gallery.php">Gallery</a></li>
        <li><a href="../reviews/review.php">Reviews</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Legal</h4>
      <ul class="links">
      <li><a href="../agreement/agreement.html">Customer Agreement & Privacy Policy</a></li>
      </ul>
    </div>

    <div class="footer-col">
    <h4>Contact</h4>
    <ul class="links">
      <li><a href="https://mail.google.com/mail/u/0/?fs=1&to=Rmbetisfurniture@yahoo.com&su=Your+Subject+Here&body=Your+message+here.&tf=cm" target="_blank">Email</a></li>
      <li><a href="https://www.facebook.com/BetisFurnitureExtension" target="_blank">Facebook</a></li>
      <li><a href="viber://chat?number=%2B6396596602006">Phone & Viber</a></li>
    </ul>
</div>

    </div>
  </div>
</footer>
   
</body>
<script src="../static/Javascript-files/script.js"></script>
</html>