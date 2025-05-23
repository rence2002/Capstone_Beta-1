<?php
session_start(); // Start the session

// Include the database connection
include '../config/database.php'; 

// Assuming the admin's ID is stored in session after login
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.php");
    exit();
}

// Fetch admin data from the database
$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT First_Name, PicPath FROM tbl_admin_info WHERE Admin_ID = :admin_id");
$stmt->bindParam(':admin_id', $adminId);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if admin data is fetched
if (!$admin) {
    echo "Admin not found.";
    exit();
}

$adminName = htmlspecialchars($admin['First_Name']);
$profilePicPath = htmlspecialchars($admin['PicPath']);

// Fetch all users from tbl_user_info
$userStmt = $pdo->prepare("SELECT User_ID, First_Name, Last_Name FROM tbl_user_info");
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <script src="../static/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../static/js/dashboard.js"></script>
    <link href="../static/css-files/dashboard.css" rel="stylesheet">
    <link href="../static/css-files/button.css" rel="stylesheet">
    <!-- <link href="../static/css-files/dashboard.css" rel="stylesheet"> -->
    <link href="../static/css-files/admin_homev2.css" rel="stylesheet">
    <link href="../static/js/admin_home.js" rel="">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

</head>

<body>
    <div class="sidebar">
      <div class="logo-details">
        <span class="logo_name">
            <img src="../static/images/rm raw png.png" alt="RM BETIS FURNITURE"  class="logo_name">
        </span>
    </div>
        <ul class="nav-links">
        
            <li>
                <a href="../dashboard/dashboard.php" class="">
                    <i class="bx bx-grid-alt"></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
         
            <li>
                <a href="../purchase-history/read-all-history-form.php" class="">
                    <i class="bx bx-comment-detail"></i>
                    <span class="links_name">All Purchase History</span>
                </a>
            </li>
            <li>
    <a href="../reviews/read-all-reviews-form.php">
        <i class="bx bx-message-dots"></i> <!-- Changed to a more appropriate message icon -->
        <span class="links_name">All Reviews</span>
    </a>
</li>
        </ul>

    </div>

    <section class="home-section">
    <nav>
            <div class="sidebar-button">
                <i class="bx bx-menu sidebarBtn"></i>
                <span class="dashboard">Dashboard</span>
            </div>
        

            <div class="profile-details" onclick="toggleDropdown()">
                <img src="http://localhost/Capstone_Beta/<?php echo $profilePicPath; ?>" alt="Profile Picture" />
                <span class="admin_name"><?php echo $adminName; ?></span>
                <i class="bx bx-chevron-down dropdown-button"></i>

                <div class="dropdown" id="profileDropdown">
                    <a href="../admin/read-one-admin-form.php?id=<?php echo urlencode($adminId); ?>">Settings</a>
                    <a href="../admin/logout.php">Logout</a>
                </div>
            </div>

<!-- Link to External JS -->
<script src="dashboard.js"></script>


 </nav>

    <br><br><br>
    <div class="container_boxes">
    <form name="frmCustomization" method="POST" enctype="multipart/form-data" action="create-custom-rec.php">
            <h4>Create Customization</h4>
            <table>
            <tr>
                <td>User:</td>
                    <td>
                    <select name="txtUserID">
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                <?php echo htmlspecialchars($user['First_Name']) . " " . htmlspecialchars($user['Last_Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    </td>
                </tr>
                <tr>
                <td>Furniture Type:</td>
                        <td>
                            <select name="txtCategory" id="categorySelect">
                                <option value="">Select Category</option>
                                <option value="Sofa">Sofa</option>
                                <option value="Table">Table</option>
                                <option value="Chair">Chair</option>
                                <option value="Bed">Bed</option>
                                <option value="Cabinet">Cabinet</option>
                                <option value="Shelf">Shelf</option>
                                <option value="dining">Dining Set</option>
                            </select>
                        </td>
                </tr>
                <tr>
                    <td>Furniture Type Additional Info:</td>
                    <td><textarea name="txtFurnitureTypeInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                    <td>Standard Sizes:</td>
                    <td>
                        <select id="sizes" name="txtStandardSize">
                            <option value="" disabled selected>Select one</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Desired Size:</td>
                    <td><input type="text" name="txtDesiredSize"></td>
                </tr>
                
                
                <tr>
                <td>Color:</td>
                    <td>
                        <select id="color" name="txtColor">
                            <option value="" disabled selected>Select one</option>
                            <option value="natural_oak">Natural Oak</option>
                            <option value="dark_walnut">Dark Walnut</option>
                            <option value="espresso">Espresso</option>
                            <option value="driftwood_gray">Driftwood Gray</option>
                            <option value="mahogany">Mahogany</option>
                            <option value="cherry_wood">Cherry Wood</option>
                            <option value="black">Black</option>
                            <option value="white">White</option>
                            <option value="charcoal_gray">Charcoal Gray</option>
                            <option value="antique_white">Antique White</option>
                            <option value="weathered_oak">Weathered Oak</option>
                            <option value="honey_pine">Honey Pine</option>
                            <option value="maple">Maple</option>
                            <option value="birch">Birch</option>
                            <option value="teak">Teak</option>
                            <option value="rosewood">Rosewood</option>
                            <option value="ebony">Ebony</option>
                            <option value="gunmetal">Gunmetal</option>
                            <option value="brushed_gold">Brushed Gold</option>
                            <option value="brushed_silver">Brushed Silver</option>
                        </select>
                    </td>

                </tr>
                <tr>
                    <td>Color Image:</td>
                    <td><input type="file" name="fileColorImage"></td>
                </tr>
                <tr>
                    <td>Color Additional Info:</td>
                    <td><textarea name="txtColorInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                <td>Texture:</td>
                    <td>
                        <select id="texture" name="txtTexture">
                            <option value="" disabled selected>Select one</option>
                            <option value="matte">Matte</option>
                            <option value="glossy">Glossy</option>
                            <option value="semi_glossy">Semi Glossy</option>
                            <option value="duco_finish">Duco Finish</option>
                            <option value="marble_finish">Marble Finish</option>
                            <option value="smooth">Smooth</option>
                            <option value="satin_finish">Satin Finish</option>
                            <option value="distressed">Distressed</option>
                            <option value="rustic">Rustic</option>
                            <option value="textured">Textured</option>
                            <option value="brushed">Brushed</option>
                            <option value="weathered">Weathered</option>
                            <option value="wood_grain">Wood Grain</option>
                            <option value="laminated">Laminated</option>
                            <option value="veneer">Veneer</option>
                            <option value="high_gloss">High Gloss</option>
                            <option value="powder_coated">Powder Coated</option>
                            <option value="patina">Patina</option>
                            <option value="raw_unfinished">Raw / Unfinished</option>
                            <option value="polished">Polished</option>
                        </select>
                    </td>

                </tr>
                <tr>
                    <td>Texture Image:</td>
                    <td><input type="file" name="fileTextureImage"></td>
                </tr>
                <tr>
                    <td>Texture Additional Info:</td>
                    <td><textarea name="txtTextureInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                    <td>Wood Type:</td>
                    <td>
                        <select id="woodType" name="txtWoodType">
                            <option value="" disabled selected>Select one</option>
                            <option value="mahogany">Mahogany</option>
                            <option value="tangile">Tangile</option>
                            <option value="nara">Nara</option>
                            <option value="plywood1">Plywood 1/8</option>
                            <option value="plywood2">Plywood 1/4</option>
                            <option value="plywood3">Plywood 1/2</option>
                            <option value="plywood4">Plywood 3/4</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Wood Image:</td>
                    <td><input type="file" name="fileWoodImage"></td>
                </tr>
                <tr>
                    <td>Wood Additional Info:</td>
                    <td><textarea name="txtWoodInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                <tr>
                        <td>Foam Type:</td>
                        <td>
                            <select id="foam" name="txtFoamType">
                                <option value="" disabled selected>Select one</option>
                                <option value="uratex">Uratex</option>
                            </select>
                        </td>

                    </tr>
                <tr>
                    <td>Foam Image:</td>
                    <td><input type="file" name="fileFoamImage"></td>
                </tr>
                <tr>
                    <td>Foam Additional Info:</td>
                    <td><textarea name="txtFoamInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                    <td>Cover Type:</td>
                    <td>
                        <select id="cover" name="txtCoverType">
                            <option value="" disabled selected>Select one</option>
                            <option value="velvet">Velvet</option>
                            <option value="linen">Linen Type</option>
                            <option value="suede">Suede</option>
                            <option value="frenchleather">French Leather</option>
                            <option value="germanleather">German Leather</option>
                            <option value="koreanleather">Korean Leather</option>
                            <option value="italianleather">Italian Leather</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Cover Image:</td>
                    <td><input type="file" name="fileCoverImage"></td>
                </tr>
                <tr>
                    <td>Cover Additional Info:</td>
                    <td><textarea name="txtCoverInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                    <td>Design:</td>
                    <td>
                        <select id="design" name="txtDesign">
                            <option value="" disabled selected>Select one</option>
                            <option value="modern">Modern</option>
                            <option value="contemporary">Contemporary</option>
                            <option value="rustic">Rustic</option>
                            <option value="industrial">Industrial</option>
                            <option value="scandinavian">Scandinavian</option>
                            <option value="midcentury">Mid-Century Modern</option>
                            <option value="minimalist">Minimalist</option>
                            <option value="traditional">Traditional</option>
                            <option value="bohemian">Bohemian</option>
                            <option value="artdeco">Art Deco</option>
                            <option value="farmhouse">Farmhouse</option>
                            <option value="vintage">Vintage</option>
                            <option value="eclectic">Eclectic</option>
                            <option value="shabbychic">Shabby Chic</option>
                            <option value="asianinspired">Asian Inspired</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Design Image:</td>
                    <td><input type="file" name="fileDesignImage"></td>
                </tr>
                <tr>
                    <td>Design Additional Info:</td>
                    <td><textarea name="txtDesignInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                    <td>Tile Type:</td>
                    <td>
                        <select id="tile" name="txtTileType">
                            <option value="" disabled selected>Select one</option>
                            <option value="marble">Marble</option>
                            <option value="porcelain">Porcelain</option>
                            <option value="quartz">Quartz</option>
                            <option value="granite">Granite</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Tile Image:</td>
                    <td><input type="file" name="fileTileImage"></td>
                </tr>
                <tr>
                    <td>Tile Additional Info:</td>
                    <td><textarea name="txtTileInfo" class="form-control"></textarea></td>
                </tr>
                <tr>
                    <td>Metal Type:</td>
                    <td>
                        <select id="metal" name="txtMetalType">
                            <option value="" disabled selected>Select one</option>
                            <option value="flat">Flat Bar</option>
                            <option value="tubular">Tubular</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Metal Image:</td>
                    <td><input type="file" name="fileMetalImage"></td>
                </tr>
                <tr>
                    <td>Metal Additional Info:</td>
                    <td><textarea name="txtMetalInfo" class="form-control"></textarea></td>
                </tr>
            </table>
            
            <div class="button-container">
            <a href="read-all-custom-form.php" target="_parent" class="buttonBack">Back to List</a>
                <input type="submit" value="Submit" class="buttonUpdate">
                <input type="reset" value="Reset" class="buttonDelete">
               
            </div>
        </form>
    </div>
</section>

<script>
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    sidebarBtn.onclick = function () {
        sidebar.classList.toggle("active");
        if (sidebar.classList.contains("active")) {
            sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        } else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    };

    document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
        toggle.addEventListener('click', function () {
            const parent = this.parentElement; // Get the parent <li> of the toggle
            const dropdownMenu = parent.querySelector('.dropdown-menu'); // Get the <ul> of the dropdown menu
            parent.classList.toggle('active'); // Toggle the 'active' class on the parent <li>

            // Toggle the chevron icon rotation
            const chevron = this.querySelector('i'); // Find the chevron icon inside the toggle
            if (parent.classList.contains('active')) {
                chevron.classList.remove('bx-chevron-down');
                chevron.classList.add('bx-chevron-up'); // Change to up when menu is open
            } else {
                chevron.classList.remove('bx-chevron-up');
                chevron.classList.add('bx-chevron-down'); // Change to down when menu is closed
            }
            
            // Toggle the display of the dropdown menu
            dropdownMenu.style.display = parent.classList.contains('active') ? 'block' : 'none';
        });
    });

    function updateSizes() {
            const furniture = document.getElementById('furniture').value;
            const sizes = document.getElementById('sizes');

            const categoryOptions = {
                sofa: [
                    { value: 'sofa1', text: 'Sofa 2 Seater' },
                    { value: 'sofa2', text: 'Sofa 3 Seater' },
                    { value: 'sofa3', text: 'Sofa 4 Seater' },
                    { value: 'sofa4', text: 'Sofa 5 Seater' }
                ],
                table: [
                    { value: 'table1', text: 'Table 2x4 ft.' },
                    { value: 'table2', text: 'Table 3x5 ft.' },
                    { value: 'table3', text: 'Table 4x6 ft.' },
                    { value: 'table4', text: 'Table 5x7 ft.' }
                ],
                chair: [
                    { value: 'chair1', text: 'Chair 1x1 ft.' },
                    { value: 'chair2', text: 'Chair 1.5x1.5 ft.' },
                    { value: 'chair3', text: 'Chair 2x2 ft.' }
                ],
                bed: [
                    { value: 'bed1', text: 'Bed 3x6 ft.' },
                    { value: 'bed2', text: 'Bed 4x6 ft.' },
                    { value: 'bed3', text: 'Bed 5x6 ft.' },
                    { value: 'bed4', text: 'Bed 6x6 ft.' }
                ],
                cabinet: [
                    { value: 'cabinet1', text: 'Cabinet 2x4 ft.' },
                    { value: 'cabinet2', text: 'Cabinet 3x5 ft.' },
                    { value: 'cabinet3', text: 'Cabinet 4x6 ft.' }
                ],
                shelf: [
                    { value: 'shelf1', text: 'Shelf 2x4 ft.' },
                    { value: 'shelf2', text: 'Shelf 3x5 ft.' },
                    { value: 'shelf3', text: 'Shelf 4x6 ft.' }
                ],
                dining: [
                    { value: 'dining1', text: 'Dining Set 8x8 ft.' },
                    { value: 'dining2', text: 'Dining Set 9x9 ft.' },
                    { value: 'dining3', text: 'Dining Set 10x10 ft.' },
                    { value: 'dining4', text: 'Dining Set 10x11 ft.' }
                ]
            };

            // Clear existing options
            sizes.innerHTML = '<option value="" disabled selected>Select one</option>';

            // Add new options based on selected furniture
            if (categoryOptions[furniture]) {
                categoryOptions[furniture].forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    sizes.appendChild(opt);
                });
            }
        }
</script>

</body>
</html>
