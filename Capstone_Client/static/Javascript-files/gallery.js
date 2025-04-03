document.addEventListener('DOMContentLoaded', function () {
    // Product preview functionality (unchanged)
    const previewContainer = document.querySelector('.products-preview');
    const previewBox = document.querySelectorAll('.preview');

    // Handle product click to show preview
    document.querySelectorAll('.products-container .product').forEach(product => {
        product.addEventListener('click', (event) => {
            if (!event.target.closest('model-viewer')) {
                previewContainer.style.display = 'flex';
                const name = product.getAttribute('data-name');
                previewBox.forEach(preview => {
                    const target = preview.getAttribute('data-target');
                    if (name === target) {
                        preview.classList.add('active');
                        if (!preview.swiper) {
                            preview.swiper = new Swiper(preview.querySelector('.swiper-container'), {
                                slidesPerView: 1,
                                spaceBetween: 20,
                                pagination: {
                                    el: '.swiper-pagination',
                                    clickable: true,
                                },
                                navigation: {
                                    nextEl: '.swiper-button-next',
                                    prevEl: '.swiper-button-prev',
                                },
                                on: {
                                    init: function () {
                                        this.slides.forEach(slide => slide.style.opacity = 0);
                                        this.slides[this.activeIndex].style.opacity = 1;
                                    },
                                    slideChange: function () {
                                        this.slides.forEach(slide => slide.style.opacity = 0);
                                        this.slides[this.activeIndex].style.opacity = 1;
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });
    });

    // Close preview
    document.querySelectorAll('.preview .fa-times').forEach(button => {
        button.addEventListener('click', () => {
            button.closest('.preview').classList.remove('active');
            previewContainer.style.display = 'none';
        });
    });

    // Submit Button Handler
    const submitButton = document.getElementById('submit-button');
    submitButton.addEventListener('click', async (e) => {
        e.preventDefault();

        // Validate required fields
        const requiredFields = ['furniture', 'sizes'];
        let isValid = true;
        requiredFields.forEach(field => {
            const value = document.querySelector(`[name="${field}"]`).value;
            if (!value) {
                isValid = false;
            }
        });
        const selectedSize = document.querySelector('[name="sizes"]').value;
        const customSizeInput = document.querySelector('[name="sizes-info"]');
        if (selectedSize === 'custom' && !customSizeInput.value.trim()) {
            isValid = false;
        }
        if (!isValid) {
            showModal('Error', 'Please fill in all required fields.');
            return;
        }

        // Show confirmation modal
        const confirmationModal = document.getElementById('confirmation-modal');
        confirmationModal.style.display = 'block';

        // Handle OK button in confirmation modal
        document.getElementById('confirm-ok-button').onclick = async () => {
            confirmationModal.style.display = 'none'; // Hide confirmation modal

            // Collect form data
            const formData = new FormData();
            const fields = [
                'furniture', 'furniture-info', 'sizes', 'sizes-info',
                'color', 'color-info', 'fileColorImage',
                'texture', 'texture-info', 'fileTextureImage',
                'wood', 'wood-info', 'fileWoodImage',
                'foam', 'foam-info', 'fileFoamImage',
                'cover', 'cover-info', 'fileCoverImage',
                'design', 'design-info', 'fileDesignImage',
                'tiles', 'tiles-info', 'fileTileImage',
                'metal', 'metal-info', 'fileMetalImage'
            ];
            fields.forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    if (input.type === 'file') {
                        if (input.files.length > 0) {
                            formData.append(field, input.files[0]);
                        }
                    } else {
                        formData.append(field, input.value || '');
                    }
                }
            });

            try {
                const response = await fetch('gallery-custom-rec.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    // Display the modal
                    const printModal = document.getElementById('print-modal');
                    printModal.style.display = 'block';

                    // Now that the modal is displayed, populate its content
                    const modalPreview = document.getElementById('modal-preview');
                    if (modalPreview) {
                        modalPreview.innerHTML = `
                            <p>Customization ID: ${data.data.customization_id}</p>
                            <p>Product ID: ${data.data.product_id}</p>
                            <p>User ID: ${data.data.user_id}</p>
                            <p>User Name: ${data.data.user_name}</p>
                            <p>Furniture: ${data.data.furniture}</p>
                            <p>Size: ${data.data.size}</p>
                            <p>Color: ${data.data.color}</p>
                            ${data.data.color_image ? `<img src="${data.data.color_image}" alt="Color Image" style="max-width: 100px;">` : ''}
                            <p>Color Info: ${data.data.color_info}</p>
                            <p>Texture: ${data.data.texture}</p>
                            ${data.data.texture_image ? `<img src="${data.data.texture_image}" alt="Texture Image" style="max-width: 100px;">` : ''}
                            <p>Texture Info: ${data.data.texture_info}</p>
                            <p>Wood: ${data.data.wood}</p>
                            ${data.data.wood_image ? `<img src="${data.data.wood_image}" alt="Wood Image" style="max-width: 100px;">` : ''}
                            <p>Wood Info: ${data.data.wood_info}</p>
                            <p>Foam: ${data.data.foam}</p>
                            ${data.data.foam_image ? `<img src="${data.data.foam_image}" alt="Foam Image" style="max-width: 100px;">` : ''}
                            <p>Foam Info: ${data.data.foam_info}</p>
                            <p>Cover: ${data.data.cover}</p>
                            ${data.data.cover_image ? `<img src="${data.data.cover_image}" alt="Cover Image" style="max-width: 100px;">` : ''}
                            <p>Cover Info: ${data.data.cover_info}</p>
                            <p>Design: ${data.data.design}</p>
                            ${data.data.design_image ? `<img src="${data.data.design_image}" alt="Design Image" style="max-width: 100px;">` : ''}
                            <p>Design Info: ${data.data.design_info}</p>
                            <p>Tiles: ${data.data.tiles}</p>
                            ${data.data.tiles_image ? `<img src="${data.data.tiles_image}" alt="Tiles Image" style="max-width: 100px;">` : ''}
                            <p>Tiles Info: ${data.data.tiles_info}</p>
                            <p>Metal: ${data.data.metal}</p>
                            ${data.data.metal_image ? `<img src="${data.data.metal_image}" alt="Metal Image" style="max-width: 100px;">` : ''}
                            <p>Metal Info: ${data.data.metal_info}</p>
                            <p>Timestamp: ${data.data.timestamp}</p>
                        `;
                    } else {
                        console.error("modal-preview element not found!");
                    }
                    if (!modalPreview){
                        throw new Error(data.message || 'Submission failed');
                    }

                    // Add event listener for the close button
                    const closeModal = document.querySelector('.close-modal');
                    closeModal.addEventListener('click', function () {
                        printModal.style.display = 'none';
                    });

                    // Add event listener for the OK button
                    const modalOkButton = document.getElementById('modal-ok-button');
                    modalOkButton.addEventListener('click', function () {
                        printModal.style.display = 'none';
                    });

                } else {
                    throw new Error(data.message || 'Submission failed');
                }
            } catch (error) {
                showModal('Error', error.message || 'An unexpected error occurred.');
            }
        };

        // Handle Cancel button in confirmation modal
        document.getElementById('confirm-cancel-button').onclick = () => {
            confirmationModal.style.display = 'none'; // Hide confirmation modal
        };
    });

    // Reset Button Handler (unchanged)
    document.getElementById('reset-button').addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelectorAll('.cus-boxed select').forEach(select => {
            select.value = '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        document.querySelectorAll('.cus-boxed input[type="file"]').forEach(input => {
            input.value = '';
        });
        document.querySelectorAll('.cus-boxed input[type="text"]').forEach(input => {
            input.value = '';
        });
        document.querySelectorAll('.cus-boxed div[id$="-image-preview"]').forEach(div => {
            div.innerHTML = '';
            div.style.display = 'none';
        });
        document.getElementById('sizes').innerHTML = '<option value="" disabled selected>Select one</option>';
        showModal('Success', 'All fields have been reset!');
    });

    // Image Preview Handler (unchanged)
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', (e) => {
            const previewId = input.id.replace('-file-upload', '-image-preview');
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 150px; max-height: 150px;">`;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
                preview.style.display = 'none';
            }
        });
    });

    // Modal Functionality (unchanged)
    function showModal(title, message) {
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
        const closeModal = modal.querySelector('.close-modal');
        closeModal.addEventListener('click', () => {
            modal.remove();
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
});

// Close Print Modal (unchanged)
document.getElementById('modal-ok-button').addEventListener('click', () => {
    document.getElementById('print-modal').style.display = 'none';
});

// Close modal when clicking the close button or outside the modal (unchanged)
document.querySelector('.close-modal').addEventListener('click', () => {
    document.getElementById('print-modal').style.display = 'none';
});

document.getElementById('print-modal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('print-modal')) {
        document.getElementById('print-modal').style.display = 'none';
    }
});
