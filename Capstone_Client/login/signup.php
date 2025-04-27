<?php
session_start(); // Start the session at the very top

// Retrieve errors and form data from session, then clear them
$signup_errors = $_SESSION['signup_errors'] ?? [];
$form_data = $_SESSION['signup_form_data'] ?? [];
unset($_SESSION['signup_errors']);
unset($_SESSION['signup_form_data']);

// Helper function to display errors for a specific field (from full submit)
function display_error($field, $errors) {
    if (isset($errors[$field])) {
        echo '<span class="error-text">' . htmlspecialchars($errors[$field]) . '</span>';
    }
}

// Helper function to get form value or default
function old_value($field, $data, $default = '') {
    return htmlspecialchars($data[$field] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RM Betis Furniture</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Include the model-viewer script from Google CDN -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/@google/model-viewer@2.0.0/dist/model-viewer.min.js" async></script>
    <link rel="stylesheet" href="../static/css-files/Register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Add some basic styling for the error messages */
        .error-messages-summary { /* Styles for general errors */
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error-messages-summary ul {
            margin: 0;
            padding-left: 20px;
            list-style: disc;
        }
        .error-text, .ajax-error-text { /* Style for field-specific errors */
            color: #dc3545; /* Bootstrap's danger color */
            font-size: 0.875em;
            display: block; /* Ensure it takes its own line */
            margin-top: 0.25rem;
          font-style: italic;

           
        }
        .ajax-error-text {
             /* Keep visible but empty by default, or use display: none; */
        }
        .form-group input.is-invalid,
        .form-group select.is-invalid,
        .form-group textarea.is-invalid {
             border-color: #dc3545 !important; /* Ensure override */
        }
        /* Style for upload button wrapper */
        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin-bottom: 10px;
        }
        .upload-btn-wrapper .btn {
            border: 1px solid #ccc;
            color: #333;
            background-color: white;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }
        .preview-image {
            display: none;
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            object-fit: cover;
        }
        /* Style for loading state on button */
        #nextButton.is-loading {
            cursor: wait;
            opacity: 0.7;
        }
        #nextButton.is-loading::after {
            content: '...';
            display: inline-block;
            animation: loadingDots 1s infinite steps(3, end);
        }
        @keyframes loadingDots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
    </style>
</head>

<body>
    <!-- Back Button -->
    <div class="buttons1">
        <a href="/Capstone_Beta/Capstone_Client/login/login.php" class="login">
            <button type="button"><i class="fas fa-arrow-left"></i> Back To Login</button>
        </a>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- 3D Model Container -->
        <div class="model-container" id="modelContainer">
           <model-viewer src="../static/images/school_furniture_pack.glb" shadow-intensity="1" camera-controls touch-action="pan-y"></model-viewer>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="form-wrapper">
                <h1>Create Your Account</h1>

                <!-- General Error Display Area -->
                <?php if (isset($signup_errors['general']) || isset($signup_errors['database'])) : ?>
                    <div class="error-messages-summary">
                        <strong>Please fix the following issues:</strong>
                        <ul>
                            <?php if (isset($signup_errors['general'])) echo '<li>' . htmlspecialchars($signup_errors['general']) . '</li>'; ?>
                            <?php if (isset($signup_errors['database'])) echo '<li>' . htmlspecialchars($signup_errors['database']) . '</li>'; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Signup Form -->
                <form id="signupForm" method="POST" action="signup-rec.php" enctype="multipart/form-data" novalidate>
                    <!-- Step 1: Personal Info -->
                    <div class="form-step" id="step1">
                        <!-- Fields: firstName, lastName, middleName, homeAddress -->
                        <h2>Step 1: Personal Information</h2>
                         <div class="form-group">
                            <label for="firstName">First Name <span class="required">*</span></label>
                            <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required
                                   value="<?php echo old_value('firstName', $form_data); ?>"
                                   class="<?php echo isset($signup_errors['firstName']) ? 'is-invalid' : ''; ?>">
                            <?php display_error('firstName', $signup_errors); ?>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name <span class="required">*</span></label>
                            <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required
                                   value="<?php echo old_value('lastName', $form_data); ?>"
                                   class="<?php echo isset($signup_errors['lastName']) ? 'is-invalid' : ''; ?>">
                            <?php display_error('lastName', $signup_errors); ?>
                        </div>
                        <div class="form-group">
                            <label for="middleName">Middle Name</label>
                            <input type="text" id="middleName" name="middleName" placeholder="Enter your middle name"
                                   value="<?php echo old_value('middleName', $form_data); ?>">
                        </div>
                        <div class="form-group">
                            <label for="homeAddress">Home Address</label>
                            <input type="text" id="homeAddress" name="homeAddress" placeholder="Enter your home address"
                                   value="<?php echo old_value('homeAddress', $form_data); ?>"
                                   class="<?php echo isset($signup_errors['homeAddress']) ? 'is-invalid' : ''; ?>">
                            <?php display_error('homeAddress', $signup_errors); ?>
                        </div>
                    </div>

                    <!-- Step 2: Account Info -->
                    <div class="form-step hidden" id="step2">
                        <!-- Fields: email, mobileNumber, password, confirm-password -->
                         <h2>Step 2: Account Details</h2>
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required
                                   value="<?php echo old_value('email', $form_data); ?>"
                                   class="<?php echo isset($signup_errors['email']) ? 'is-invalid' : ''; ?>"
                                   aria-describedby="email-ajax-error"> <!-- Link error message for accessibility -->
                            <?php display_error('email', $signup_errors); // Shows error from full submit ?>
                            <span id="email-ajax-error" class="ajax-error-text" role="alert" aria-live="polite"></span> <!-- Placeholder for AJAX error -->
                        </div>
                         <div class="form-group">
                            <label for="mobileNumber">Mobile Number (11 digits)</label>
                            <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="e.g., 09123456789" pattern="[0-9]{11}"
                                   value="<?php echo old_value('mobileNumber', $form_data); ?>"
                                   class="<?php echo isset($signup_errors['mobileNumber']) ? 'is-invalid' : ''; ?>">
                            <?php display_error('mobileNumber', $signup_errors); ?>
                        </div>
                        <div class="form-group">
                            <label for="password">Password (min 8 characters) <span class="required">*</span></label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required minlength="8"
                                   class="<?php echo isset($signup_errors['password']) ? 'is-invalid' : ''; ?>">
                            <?php display_error('password', $signup_errors); ?>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm Password <span class="required">*</span></label>
                            <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required
                                   class="<?php echo isset($signup_errors['confirm-password']) ? 'is-invalid' : ''; ?>">
                            <?php display_error('confirm-password', $signup_errors); ?>
                        </div>
                    </div>

                    <!-- Step 3: Uploads -->
                    <div class="form-step hidden" id="step3">
                        <!-- Fields: profilePic, validID -->
                         <h2>Step 3: Verification Documents</h2>
                        <div class="form-group">
                            <label for="profilePic">Profile Picture (Optional)</label>
                            <div class="upload-btn-wrapper">
                                <button type="button" class="btn">Choose Profile Picture</button>
                                <input type="file" name="profilePic" id="profilePic" accept="image/jpeg, image/png, image/gif" />
                            </div>
                            <img id="profilePicPreview" src="#" alt="Profile Picture Preview" class="preview-image">
                            <?php display_error('profilePic', $signup_errors); ?>
                        </div>
                        <div class="form-group">
                            <label for="validID">Upload Valid ID (Required) <span class="required">*</span></label>
                            <div class="upload-btn-wrapper">
                                <button type="button" class="btn">Choose Valid ID</button>
                                <input type="file" name="validID" id="validID" accept="image/jpeg, image/png, image/gif" required>
                            </div>
                             <img id="validIDPreview" src="#" alt="Valid ID Preview" class="preview-image">
                             <?php display_error('validID', $signup_errors); ?>
                        </div>
                    </div>

                    <!-- Step 4: Terms -->
                    <div class="form-step hidden" id="step4">
                        <!-- Field: signupTerms -->
                        <h2>Step 4: Terms and Conditions</h2>
                        <div class="form-options">
                            <label class="terms-label">
                                <input type="checkbox" name="signupTerms" id="signupTermsCheckbox" required disabled>
                                I agree to the <button type="button" id="signupTermsButton" class="termsbutton">Terms and Conditions & Privacy Policy</button>
                            </label>
                             <?php display_error('signupTerms', $signup_errors); ?>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="buttons">
                        <button type="button" id="prevButton" class="hidden">Prev</button>
                        <button type="button" id="nextButton">Next</button>
                    </div>
                </form>

                <!-- Terms Modal -->
                <div id="signupTermsModal" class="modal" style="display: none;">
                    <!-- Modal Content -->
                     <div class="modal-content">
                        <span class="close" id="signupModalClose">&times;</span>
                        <h1>Terms and Conditions & Privacy Policy</h1>
                        <!-- Terms text -->
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
                        <label class="modal-agree-label">
                            <input type="checkbox" id="signupModalAgreeCheckbox"> I have read and agree to the terms and conditions
                        </label>
                    </div>
                </div>

            </div><!-- /.form-wrapper -->
        </div><!-- /.form-container -->
    </div>

    <script type="module">
        // --- DOM Elements ---
        const steps = document.querySelectorAll('.form-step');
        const prevButton = document.getElementById('prevButton');
        const nextButton = document.getElementById('nextButton');
        const signupForm = document.getElementById('signupForm');
        const termsCheckbox = document.getElementById('signupTermsCheckbox');
        const emailInput = document.getElementById('email');
        const emailAjaxError = document.getElementById('email-ajax-error');
        const profilePicInput = document.getElementById('profilePic');
        const profilePicPreview = document.getElementById('profilePicPreview');
        const validIDInput = document.getElementById('validID');
        const validIDPreview = document.getElementById('validIDPreview');
        const termsButton = document.getElementById('signupTermsButton');
        const termsModal = document.getElementById('signupTermsModal');
        const modalClose = document.getElementById('signupModalClose');
        const modalAgreeCheckbox = document.getElementById('signupModalAgreeCheckbox');

        // --- State ---
        let currentStep = 1;
        const totalSteps = steps.length;

        // --- Functions ---

        // Function to show the correct step and update buttons
        function showStep(step) {
            steps.forEach((stepElement, index) => {
                stepElement.classList.toggle('hidden', index !== step - 1);
            });
            prevButton.classList.toggle('hidden', step === 1);
            nextButton.textContent = step === totalSteps ? 'Sign Up' : 'Next';
            nextButton.type = step === totalSteps ? 'submit' : 'button';
            nextButton.disabled = (step === totalSteps && !termsCheckbox.checked);
            window.scrollTo(0, 0);
        }

        // Function to display/clear AJAX email error
        function setEmailAjaxError(message = '') {
            if (emailAjaxError) {
                emailAjaxError.textContent = message;
            }
            if (emailInput) {
                emailInput.classList.toggle('is-invalid', !!message);
            }
        }

        // Function to validate step fields
        function validateStepFields(step) {
            const currentStepElement = document.getElementById(`step${step}`);
            if (!currentStepElement) return false;

            const inputs = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
            let isStepValid = true;

            inputs.forEach(input => {
                let isFieldValid = true;
                input.classList.remove('is-invalid');

                if (input.id === 'email') {
                    setEmailAjaxError('');
                    isFieldValid = input.value.trim() !== '' && /^\S+@\S+\.\S+$/.test(input.value);
                } else if (input.id === 'password') {
                    isFieldValid = input.value.length >= 8;
                } else if (input.id === 'confirm-password') {
                    isFieldValid = input.value === document.getElementById('password').value && input.value !== '';
                } else if (input.type === 'file') {
                    isFieldValid = input.files.length > 0;
                } else {
                    isFieldValid = input.value.trim() !== '';
                }

                if (!isFieldValid) {
                    isStepValid = false;
                    input.classList.add('is-invalid');
                }
            });

            return isStepValid;
        }

        // Function to check email existence
        async function checkEmailExists(email) {
            nextButton.disabled = true;
            nextButton.classList.add('is-loading');
            setEmailAjaxError('');

            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('check_email.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const result = await response.json();

                if (result.error || result.exists) {
                    setEmailAjaxError(result.message || 'This email is already registered.');
                    return false;
                }
                return true;

            } catch (error) {
                console.error('Error checking email:', error);
                setEmailAjaxError(`Could not check email. ${error.message || 'Please try again.'}`);
                return false;
            } finally {
                nextButton.disabled = false;
                nextButton.classList.remove('is-loading');
                nextButton.textContent = currentStep === totalSteps ? 'Sign Up' : 'Next';
                if (currentStep === totalSteps) {
                    nextButton.disabled = !termsCheckbox.checked;
                }
            }
        }

        // Function to handle file preview
        function previewImage(fileInput, previewElement) {
            const file = fileInput.files[0];
            if (file && previewElement) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewElement.src = e.target.result;
                    previewElement.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else if (previewElement) {
                previewElement.src = '#';
                previewElement.style.display = 'none';
            }
        }

        // --- Event Listeners ---

        // Previous Button
        prevButton.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                setEmailAjaxError('');
                showStep(currentStep);
            }
        });

        // Next / Submit Button
        nextButton.addEventListener('click', async (event) => {
            if (nextButton.type === 'button') {
                event.preventDefault();
                const isStepValid = validateStepFields(currentStep);

                if (!isStepValid) {
                    return;
                }

                if (currentStep === 2) {
                    const isEmailAvailable = await checkEmailExists(emailInput.value);
                    if (isEmailAvailable) {
                        currentStep++;
                        showStep(currentStep);
                    }
                } else if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            } else if (!termsCheckbox.checked) {
                event.preventDefault();
                alert("Please agree to the Terms and Conditions before signing up.");
            }
        });

        // File Previews
        if (profilePicInput && profilePicPreview) {
            profilePicInput.addEventListener('change', () => previewImage(profilePicInput, profilePicPreview));
        }
        if (validIDInput && validIDPreview) {
            validIDInput.addEventListener('change', () => previewImage(validIDInput, validIDPreview));
        }

        // Terms Modal
        if (termsButton && termsModal && modalClose && modalAgreeCheckbox) {
            termsButton.addEventListener('click', () => {
                termsModal.style.display = 'block';
            });

            modalClose.addEventListener('click', () => {
                termsModal.style.display = 'none';
            });

            window.addEventListener('click', (event) => {
                if (event.target === termsModal) {
                    termsModal.style.display = 'none';
                }
            });

            modalAgreeCheckbox.addEventListener('change', () => {
                termsCheckbox.checked = modalAgreeCheckbox.checked;
                termsCheckbox.disabled = !modalAgreeCheckbox.checked;
                if (nextButton.type === 'submit') {
                    nextButton.disabled = !modalAgreeCheckbox.checked;
                }
                if (modalAgreeCheckbox.checked) {
                    termsModal.style.display = 'none';
                }
            });
        }

        // --- Initialization ---
        <?php
            $error_fields_by_step = [
                1 => ['firstName', 'lastName', 'middleName', 'homeAddress'],
                2 => ['email', 'mobileNumber', 'password', 'confirm-password'],
                3 => ['profilePic', 'validID'],
                4 => ['signupTerms']
            ];
            $initial_step = 1;
            if (!empty($signup_errors)) {
                foreach ($error_fields_by_step as $step_num => $fields) {
                    foreach ($fields as $field) {
                        if (isset($signup_errors[$field])) {
                            $initial_step = $step_num;
                            break 2;
                        }
                    }
                }
            }
            echo "currentStep = " . $initial_step . ";";
        ?>

        showStep(currentStep);
    </script>

</body>
</html>