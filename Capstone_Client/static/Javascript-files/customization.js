$(document).ready(function () {
    // Toggle custom options visibility
    function toggleCustomElements(selectElement, wrapperId) {
        const wrapper = $(`#${wrapperId}`);
        if (selectElement.value === 'custom') {
            wrapper.show();
        } else {
            wrapper.hide().find('input').val(''); // Clear inputs
            wrapper.find('input[type="file"]').val(''); // Clear file input
            wrapper.find('.image-preview').empty(); // Clear preview
        }
    }

    // Initialize all dropdown handlers
    function initHandlers() {
        // Furniture type handler
        $('#furniture').on('change', function () {
            toggleCustomElements(this, 'furniture-custom-options');
            const furnitureType = $(this).val();
            let sizesOptions = '<option value="" disabled selected>Select one</option>';

            switch (furnitureType) {
                case 'chair':
                    sizesOptions += `
                        <option value="custom">Custom</option>
                        <option value="Chair - 20x21 in.">Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.</option>
                    `;
                    break;
                case 'table':
                    sizesOptions += `
                        <option value="custom">Custom</option>
                        <option value="Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.">Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.</option>
                        <option value="Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.">Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.</option>
                        <option value="Table 6.5 seater - L: 6.5 ft. // W: 41 in. // H: 30 in.">Table 6.5 seater - L: 6.5 ft. // W: 41 in. // H: 30 in.</option>
                    `;
                    break;
                case 'salaset':
                    sizesOptions += `
                        <option value="custom">Custom</option>
                        <option value="Sala Set 8x8 ft.">Sala Set 8x8 ft.</option>
                        <option value="Sala Set 9x9 ft.">Sala Set 9x9 ft.</option>
                        <option value="Sala Set 10x10 ft.">Sala Set 10x10 ft.</option>
                        <option value="Sala Set 10x11 ft.">Sala Set 10x11 ft.</option>
                    `;
                    break;
                case 'bedframe':
                    sizesOptions += `
                        <option value="custom">Custom</option>
                        <option value="California King 72x84 in.">Bed Frame - California King 72x84 in.</option>
                        <option value="King 76x80 in.">Bed Frame - King 76x80 in.</option>
                        <option value="Queen 60x80 in.">Bed Frame - Queen 60x80 in.</option>
                        <option value="Full XL 54x80 in.">Bed Frame - Full XL 54x80 in.</option>
                        <option value="Full 54x75 in.">Bed Frame - Full 54x75 in.</option>
                        <option value="Twin XL 38x80 in.">Bed Frame - Twin XL 38x80 in.</option>
                        <option value="Twin 38x75 in.">Bed Frame - Twin 38x75 in.</option>
                    `;
                    break;
                case 'custom':
                    sizesOptions += `<option value="custom">Custom</option>`;
                    break;
                default:
                    sizesOptions = '<option value="" disabled selected>Select one</option>';
            }

            // Update sizes dropdown
            $('#sizes').html(sizesOptions).trigger('change');
        });
        // Sizes handler
        $('#sizes').on('change', function () {
            toggleCustomElements(this, 'sizes-custom-options');
        });
        // Color handler
        $('#color').on('change', function () {
            toggleCustomElements(this, 'color-custom-options');
        });

        // Texture handler
        $('#texture').on('change', function () {
            toggleCustomElements(this, 'texture-custom-options');
        });

        // Woods handler
        $('#woods').on('change', function () {
            toggleCustomElements(this, 'woods-custom-options');
        });

        // Foam handler
        $('#foam').on('change', function () {
            toggleCustomElements(this, 'foam-custom-options');
        });

        // Cover handler
        $('#cover').on('change', function () {
            toggleCustomElements(this, 'cover-custom-options');
        });

        // Design handler
        $('#design').on('change', function () {
            toggleCustomElements(this, 'design-custom-options');
        });

        // Tiles handler
        $('#tiles').on('change', function () {
            toggleCustomElements(this, 'tiles-custom-options');
        });

        // Metal handler
        $('#metal').on('change', function () {
            toggleCustomElements(this, 'metal-custom-options');
        });

        // Image preview setup
        $('input[type="file"]').on('change', function () {
            const previewId = $(this).attr('id').replace('-file-upload', '-image-preview');
            const file = this.files[0];
            const preview = $(`#${previewId}`);

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.html(`<img src="${e.target.result}" style="max-width: 100px; max-height: 100px;">`);
                    preview.show();
                };
                reader.readAsDataURL(file);
            } else {
                preview.empty().hide();
            }
        });
    }

    // Initialize handlers on page load
    initHandlers();
    $('#furniture').trigger('change');

    // Reset all fields
    $('#reset-button').on('click', function () {
        // Reset dropdowns
        $('.cus-boxed select').each(function () {
            $(this).val('').trigger('change'); // Trigger change to hide custom options
        });

        // Clear file inputs
        $('.cus-boxed input[type="file"]').val('');

        // Clear text inputs
        $('.cus-boxed input[type="text"]').val('');

        // Clear image previews
        $('.cus-boxed div[id$="-image-preview"]').empty().hide();

        // Show modal instead of alert
        showModal('Success', 'All fields have been reset!');
    });

    function showModal(title, message) {
        // Check if a modal already exists
        let existingModal = document.querySelector('.modal');
        if (!existingModal) {
            // Create a new modal only if it doesn't already exist
            const modal = document.createElement('div');
            modal.classList.add('modal');
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>${title}</h2>
                    <p>${message}</p>
                </div>
            `;
            document.body.appendChild(modal);

            // Add event listener to close the modal
            const closeModal = modal.querySelector('.close-modal');
            closeModal.addEventListener('click', () => {
                modal.remove();
            });

            // Close the modal when clicking outside the modal content
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        } else {
            // Update the content of the existing modal
            const modalContent = existingModal.querySelector('.modal-content');
            modalContent.querySelector('h2').textContent = title;
            modalContent.querySelector('p').textContent = message;
        }
    }
});



document.addEventListener('DOMContentLoaded', function () {
    const modalOkButton = document.getElementById('modal-ok-button');
    const printModal = document.getElementById('print-modal');

    if (modalOkButton) {
        modalOkButton.addEventListener('click', function () {
            // Generate PDF from the print modal content
            generatePDF();
        });
    }

    function generatePDF() {
        // Import jsPDF
        const { jsPDF } = window.jspdf;

        // Create a new jsPDF instance
        const doc = new jsPDF();

        // Get the modal content
        const modalContent = printModal.querySelector('.modal-content');

        // Add the modal content to the PDF
        doc.text("Customization Receipt", 10, 10); // Title
        doc.text("====================================", 10, 20); // Separator

        // Extract the modal text content
        const modalText = modalContent.innerText || modalContent.textContent;
        const lines = doc.splitTextToSize(modalText, 180); // Wrap text to fit the page width
        doc.text(lines, 10, 30);

        // Save the PDF
        doc.save('customization-receipt.pdf');

        // Close the modal
        printModal.style.display = 'none';
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const submitButton = document.getElementById("submit-button");
    const submitButtonWrapper = document.getElementById("submit-button-wrapper"); // Wrapper for the button
    const modal = document.getElementById("idVerificationModal");
    const closeModal = document.getElementById("closeModal");
    const closeModalButton = document.getElementById("closeModalButton");
    const modalContent = modal.querySelector(".modal-content p");

    console.log("ID Verification Status:", idVerificationStatus);

    // Enable the submit button only if the ID verification status is "Valid"
    if (idVerificationStatus === "Valid") {
        submitButton.disabled = false;
    } else {
        // Update the modal message dynamically
        modalContent.innerHTML = `
            Your ID verification status is either <strong>${idVerificationStatus}</strong>. 
            Please verify your ID to proceed with customization. 
            Go to your <a href="../profile/profile.php">Profile</a> to check your ID verification status.
        `;
    }

    console.log("Submit Button Disabled:", submitButton.disabled);

    // Listen for the click event on the wrapper instead of the button
    submitButtonWrapper.addEventListener("click", function (e) {
        if (submitButton.disabled) {
            e.preventDefault(); // Prevent any default action
            console.log("Showing Modal...");
            modal.style.display = "flex"; // Show the modal
        }
    });

    // Close the modal when the close button is clicked
    closeModal.addEventListener("click", function () {
        modal.style.display = "none"; // Hide the modal
    });

    closeModalButton.addEventListener("click", function () {
        modal.style.display = "none"; // Hide the modal
    });
});
