<!DOCTYPE html>
<html>

<head>
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    <link rel="stylesheet" href="../static/css-files/LogIn.css">
    <style>
        /* Add any additional styles here */
        .error {
            color: red;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .btn {
            border: 2px solid gray;
            color: gray;
            background-color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
        }

        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        #profilePicPreview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="right">
            <model-viewer src="../static/images/house.glb" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>
        </div>
        <div class="left">
            <h1>Create Your Account</h1>
            <form method="POST" action="signup-rec.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" placeholder="Enter your middle name">
                </div>
                <div class="form-group">
                    <label for="homeAddress">Home Address</label>
                    <input type="text" id="homeAddress" name="homeAddress" placeholder="Enter your home address">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="mobileNumber">Mobile Number</label>
                    <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="Enter your mobile number">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
                </div>
                <div class="form-group">
                    <label for="profilePic">Profile Picture</label>
                    <div class="upload-btn-wrapper">
                        <button class="btn">Upload a file</button>
                        <input type="file" name="profilePic" id="profilePic" accept="image/*" onchange="previewProfilePic(event)" />
                        <img id="profilePicPreview" src="#" alt="Profile Picture Preview" style="display: none;">
                    </div>
                </div>
                <div class="form-options">
                    <label><input type="checkbox" name="terms" id="terms" required> I agree to the <a href="#">Terms and Conditions</a></label>
                </div>
                <div class="buttons">
                    <button type="submit" class="signup">Sign up</button>
                    <a href="../index.php" class="login">Login</a>
                </div>
            </form>
            <div class="terms">
                By signing up, you agree to the <a href="#">Terms and Conditions & Privacy Policy</a>
            </div>
        </div>
    </div>
    <a class="theme-toggle">
        <span class="entypo--switch1"></span>
    </a>

    <script>
        const toggleButton = document.getElementsByClassName('theme-toggle')[0]; // Access the first element
        const body = document.body;

        toggleButton.addEventListener('click', () => {
            // Toggle the 'dark-mode' class on the body
            body.classList.toggle('dark-mode');

            // Change button icon based on the current theme
            if (body.classList.contains('dark-mode')) {
                toggleButton.innerHTML = '<span class="entypo--switch1"></span>'; // Switch to dark mode icon
            } else {
                toggleButton.innerHTML = '<span class="entypo--switch2"></span>'; // Switch to light mode icon
            }
        });

        function previewProfilePic(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var output = document.getElementById('profilePicPreview');
                output.src = reader.result;
                output.style.display = 'block'; // Show the image
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>

</html>
