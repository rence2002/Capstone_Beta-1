<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>
    <link href="../static/css/bootstrap.min.css" rel="stylesheet">
    <link href="../static/css-files/LogIn.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function validateForm() {
            const email = document.getElementById('email_address').value;
            const address = document.getElementById('address').value;

            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            const addressPattern = /^[a-zA-Z0-9\s,.'-]{3,}$/;

            if (!emailPattern.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }

            if (!addressPattern.test(address)) {
                alert('Please enter a valid home address.');
                return false;
            }
            const phoneInput = document.getElementById('mobile_number').value;
            const phonePattern = /^[0-9]{11}$/;
            const phonePattern2 = /^\+?[0-9]{13}$/;

            if (!phonePattern.test(phoneInput) && !phonePattern2.test(phoneInput))
            {
                alert('Please enter a valid 11 or 13 digit mobile number.');
                return false;
            }
              // Copy the value from the visible field to the hidden field
            document.getElementById('full_phone_number').value = phoneInput;

            return true;
        }

        let currentStep = 1;

        function showStep(step) {
            document.querySelectorAll('.step').forEach(function(stepDiv) {
                stepDiv.style.display = 'none';
            });

            document.getElementById('step-' + step).style.display = 'block';
        }

        function nextStep() {
            currentStep++;
            showStep(currentStep);
        }

        function prevStep() {
            currentStep--;
            showStep(currentStep);
        }
    </script>
</head>
<body>
    <div class="wrapper">
        <!-- Left Container -->
        <div class="left-container">
            <img src="../static/images/rm raw png.png" alt="Register Admin" class="rmreg"><br>
            <p>Welcome to RM Betis Furniture Admin Panel! Please complete the registration form to gain access to the administrative dashboard. As an admin, you will have the ability to manage users, update product listings, and oversee key business operations. Ensure that all details entered are accurate and secure. If you encounter any issues, please contact the system administrator.</p>
        </div>

        <!-- Right Container -->
        <div class="right-container">
            <form name="frmAdmin" method="POST" enctype="multipart/form-data" action="../login/register-rec.php" onsubmit="return validateForm()">
                <h2>REGISTER ADMIN</h2>

                <div id="step-1" class="step">
                    <div class="input-field">
                        <input type="text" name="txtLName" id="last_name" required>
                        <label for="last_name">Last Name</label>
                    </div>

                    <div class="input-field">
                        <input type="text" name="txtFName" id="first_name" required>
                        <label for="first_name">First Name</label>
                    </div>

                    <div class="input-field">
                        <input type="text" name="txtMName" id="middle_name" required>
                        <label for="middle_name">Middle Name</label>
                    </div>

                    <div class="input-field">
                        <input type="text" name="txtAddress" id="address" required>
                        <label for="address">Home Address</label>
                    </div>

                    <div class="input-field">
                        <input type="email" name="txtEmail" id="email_address" required>
                        <label for="email_address">Email Address</label>
                    </div>

                    <div class="input-field">
                        <input type="tel" name="txtMobile" id="mobile_number" required>
                        <label for="mobile_number">Mobile Number</label>
                        <input type="hidden" name="full_phone_number" id="full_phone_number">
                    </div>

                    <button type="button" class="buttonnext" onclick="nextStep()">&#8594;</button>
                </div>

                <div id="step-2" class="step" style="display: none;">
                    <div class="input-field">
                        <input type="password" name="txtPass" id="password" required>
                        <label for="password">Password</label>
                    </div>

                    <div class="input-field">
                        <input type="password" name="txtConfirm" id="confirm_password" required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>

                    <div class="choose">
                        <input type="file" name="filePic" id="profile_picture">
                    </div>

                    <div class="g-recaptcha" data-sitekey="6LdGk9wqAAAAAPGLtqpTt5f2IdBdSFGjA806AF7X"></div>

                    <div class="button-container">
                        <button type="submit" class="submit">Submit</button>
                        <button type="reset" class="reset">Reset</button>
                        <a href="../index.php" class="btn btn-link">Back to Login</a>
                    </div>

                    <div class="button-container">
                        <button type="button" class="buttonnext" onclick="prevStep()">&#8592;</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
