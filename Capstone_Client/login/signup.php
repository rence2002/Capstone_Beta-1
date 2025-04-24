<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page with 3D Model</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
 <!-- Include the model-viewer script from Google CDN -->
 <script type="module" src="https://cdn.jsdelivr.net/npm/@google/model-viewer@2.0.0/dist/model-viewer.min.js"></script>
 <link rel="stylesheet" href="../static/css-files/Register.css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>

<body>
            <div class="buttons1">
                <!-- <button type="submit" class="signup" id="signupSubmitButton" disabled>Sign up</button> -->
                <a href="/Capstone_Beta/Capstone_Client/login/login.php" class="login">
                <button>Back To Login</button>
                </a>

                    </div>
    <div class="form-container">
        <!-- 3D Model Container -->
        <div class="model-container" id="modelContainer">
            <!-- Insert your 3D model path below in the src -->
            <model-viewer
            src="../static/images/school_furniture_pack.glb"
            alt="3D Model"
            auto-rotate
            camera-controls
            camera-orbit="0deg 60deg 2.5m"
            field-of-view="50deg"
            style="width: 100%; height: 100%;">
            </model-viewer>

        </div>

        <!-- Form Container -->
        <div class="form-wrapper">
            <h1>Create Your Account</h1>
            <form id="signupForm" method="POST" action="signup-rec.php" enctype="multipart/form-data">
                <!-- Step 1 -->
                <div class="form-step" id="step1">
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
                </div>

                <!-- Step 2 -->
                <div class="form-step hidden" id="step2">
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
                </div>

                <!-- Step 3 -->
                <div class="form-step hidden" id="step3">
                    <div class="form-group">
                        <label for="profilePic">Profile Picture</label>
                        <div class="upload-btn-wrapper">
                            <button class="btn">Upload a file</button>
                            <input type="file" name="profilePic" id="profilePic" accept="image/*" onchange="previewProfilePic(event)" />
                            <img id="profilePicPreview" src="#" alt="Profile Picture Preview" style="display: none;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="validID">Upload Valid ID</label>
                        <div class="upload-btn-wrapper">
                            <button class="btn">Upload a file</button>
                            <input type="file" name="validID" id="validID" accept="image/*" onchange="previewValidID(event)" required>
                            <img id="validIDPreview" src="#" alt="Valid ID Preview" style="display: none; max-width: 200px; max-height: 200px; margin-top: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="form-step hidden" id="step4">
                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="signupTerms" id="signupTermsCheckbox" disabled>
                            I agree to the <button type="button" id="signupTermsButton" class="termsbutton">Terms and Conditions & Privacy Policy</button>
                        </label>
                    </div>
                   
                </div>

                <!-- Navigation Buttons -->
                <div class="buttons">
                    <button type="button" id="prevButton" class="hidden">Prev</button>
                    <button type="button" id="nextButton">Next</button>
                </div>
            </form>

            <div id="signupTermsModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" id="signupModalClose">&times;</span>
                    <h1>Terms and Conditions</h1>
            <p>Welcome to RM Betis Furniture. By using our system and placing an order, you agree to the following terms and conditions:</p>
        
            <h2>1. Downpayment Policy</h2>
            <p>Before we process or accept your order, you are required to pay a 60% downpayment. The remaining balance must be settled upon completion of the order.</p>
  
            <h2>2. No Cancellation, Return, and Refund Policy</h2>
            <p>Once an order is placed, it is considered final. Cancellations, returns, and refunds are strictly not allowed under any circumstances. Please ensure all details are accurate and final before confirming your order.</p>
        
            <h2>3. Privacy Policy</h2>
            <p>All personal information you provide in this system will be kept private and secure. We are committed to protecting your data and will not share it with third parties without your consent.</p>
        
            <h2>4. Pickup Service Policy</h2>
            <p>Our standard pickup service does not include any freebies. However, for bulk orders, we occasionally offer complimentary small pieces of furniture as a gesture of appreciation. This policy helps us build positive relationships with our customers and reward larger orders with added value.</p>
        
            <p>If you have any questions or concerns regarding these terms, please contact us for clarification.</p>
                    <label>
                        <input type="checkbox" id="signupModalAgreeCheckbox"> I agree to the terms and conditions
                    </label>
                </div>
            </div>
    </div>

    

        </div>
    </div>

    <script type="module">
    // Form Navigation Logic
    let currentStep = 1;
    const totalSteps = 4;
    const steps = document.querySelectorAll('.form-step');
    const prevButton = document.getElementById('prevButton');
    const nextButton = document.getElementById('nextButton');

    // Function to show the correct step
    function showStep(step) {
        steps.forEach((stepElement, index) => {
            stepElement.classList.add('hidden');
            if (index === step - 1) {
                stepElement.classList.remove('hidden');
            }
        });

        // Toggle visibility of the "Prev" button based on the current step
        prevButton.classList.toggle('hidden', step === 1);
        
        // Change the "Next" button text to "Submit" if it's the last step
        nextButton.textContent = step === totalSteps ? 'Submit' : 'Next';
    }

    // Event listener for the "Prev" button
    prevButton.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // Event listener for the "Next" button
    nextButton.addEventListener('click', () => {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        } else {
            document.getElementById('signupForm').submit();
        }
    });

    // Initially show the first step
    showStep(currentStep);

    // Preview for Profile Picture
    function previewProfilePic(event) {
        var reader = new FileReader();
        reader.onload = function () {
            var output = document.getElementById('profilePicPreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Preview for Valid ID
    function previewValidID(event) {
        var reader = new FileReader();
        reader.onload = function () {
            var output = document.getElementById('validIDPreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

<script>
        // Terms and Conditions Modal Logic
        const signupTermsButton = document.getElementById('signupTermsButton');
        const signupTermsModal = document.getElementById('signupTermsModal');
        const signupModalClose = document.getElementById('signupModalClose');
        const signupModalAgreeCheckbox = document.getElementById('signupModalAgreeCheckbox');
        const signupTermsCheckbox = document.getElementById('signupTermsCheckbox');
        const signupSubmitButton = document.getElementById('signupSubmitButton');

        // Open the modal when the "Terms and Conditions" button is clicked
        signupTermsButton.addEventListener('click', () => {
            signupTermsModal.style.display = 'block';
        });

        // Close the modal when the "close" button is clicked
        signupModalClose.addEventListener('click', () => {
            signupTermsModal.style.display = 'none';
        });

        // Close the modal when clicking outside the modal content
        window.addEventListener('click', (event) => {
            if (event.target === signupTermsModal) {
                signupTermsModal.style.display = 'none';
            }
        });

        // Update the modal checkbox logic
        signupModalAgreeCheckbox.addEventListener('change', () => {
            if (signupModalAgreeCheckbox.checked) {
                signupTermsCheckbox.disabled = false; // Enable the checkbox
                signupTermsCheckbox.checked = true;  // Check the checkbox
                signupSubmitButton.disabled = false; // Enable the "Sign up" button
            } else {
                signupTermsCheckbox.disabled = true; // Disable the checkbox
                signupTermsCheckbox.checked = false; // Uncheck the checkbox
                signupSubmitButton.disabled = true;  // Disable the "Sign up" button
            }
        });

        // Ensure the checkbox is enabled before submission
        signupSubmitButton.addEventListener('click', () => {
            signupTermsCheckbox.disabled = false;
        });
    </script>

</body>

</html>
