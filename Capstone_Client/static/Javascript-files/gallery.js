document.addEventListener('DOMContentLoaded', function () {
    // Product preview functionality
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
    document.getElementById('submit-button').addEventListener('click', async (e) => {
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
                    if (input.tagName === 'SELECT') {
                        const selected = input.options[input.selectedIndex];
                        formData.append(field, selected ? selected.text : '');
                    } else if (input.type === 'file') {
                        if (input.files.length) formData.append(field, input.files[0]);
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
                const rawResponse = await response.text();
                console.log('Raw server response:', rawResponse);
                const result = JSON.parse(rawResponse);

                if (result.success) {
                    showReceipt(result.data); // Show receipt after successful upload
                } else {
                    throw new Error(result.message || 'Submission failed');
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

    // Reset Button Handler
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

    // Image Preview Handler
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

    // Print Receipt Modal
    function showReceipt(data) {
        const modalPreview = document.getElementById('modal-preview');
        modalPreview.innerHTML = '';

        // Convert uploaded images to base64 URLs
        const imagePromises = [];
        const imageData = {};
        Object.keys(data).forEach(key => {
            if (data[key] instanceof File) {
                const file = data[key];
                const reader = new FileReader();
                const promise = new Promise((resolve, reject) => {
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = () => reject(reader.error);
                    reader.readAsDataURL(file);
                });
                imagePromises.push(promise.then(result => {
                    imageData[key] = result; // Store base64 URL
                }));
            }
        });

        // Wait for all images to be processed
        Promise.all(imagePromises).then(() => {
            modalPreview.innerHTML = `
                <h2>Receipt</h2>
                <div class="receipt-grid">
                    <!-- First Column -->
                    <div class="receipt-section">
                        <h3>Furniture Details</h3>
                        <p><strong>Type:</strong> ${data['furniture'] || 'N/A'}</p>
                        <p><strong>Info:</strong> ${data['furniture-info'] || 'N/A'}</p>
                        <p><strong>Size:</strong> ${data['sizes'] === 'custom' ? data['sizes-info'] : data['sizes'] || 'N/A'}</p>
                        <p><strong>Color:</strong> ${data['color'] || 'N/A'}</p>
                        ${imageData['fileColorImage'] ? `<img src="${imageData['fileColorImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Color Info:</strong> ${data['color-info'] || 'N/A'}</p>
                        <p><strong>Texture:</strong> ${data['texture'] || 'N/A'}</p>
                        ${imageData['fileTextureImage'] ? `<img src="${imageData['fileTextureImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Texture Info:</strong> ${data['texture-info'] || 'N/A'}</p>
                        <p><strong>Wood:</strong> ${data['wood'] || 'N/A'}</p>
                        ${imageData['fileWoodImage'] ? `<img src="${imageData['fileWoodImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Wood Info:</strong> ${data['wood-info'] || 'N/A'}</p>
                        <p><strong>Foam:</strong> ${data['foam'] || 'N/A'}</p>
                        ${imageData['fileFoamImage'] ? `<img src="${imageData['fileFoamImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Foam Info:</strong> ${data['foam-info'] || 'N/A'}</p>
                        <p><strong>Cover:</strong> ${data['cover'] || 'N/A'}</p>
                        ${imageData['fileCoverImage'] ? `<img src="${imageData['fileCoverImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Cover Info:</strong> ${data['cover-info'] || 'N/A'}</p>
                        <p><strong>Design:</strong> ${data['design'] || 'N/A'}</p>
                        ${imageData['fileDesignImage'] ? `<img src="${imageData['fileDesignImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Design Info:</strong> ${data['design-info'] || 'N/A'}</p>
                        <p><strong>Tiles:</strong> ${data['tiles'] || 'N/A'}</p>
                        ${imageData['fileTileImage'] ? `<img src="${imageData['fileTileImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Tiles Info:</strong> ${data['tiles-info'] || 'N/A'}</p>
                        <p><strong>Metal:</strong> ${data['metal'] || 'N/A'}</p>
                        ${imageData['fileMetalImage'] ? `<img src="${imageData['fileMetalImage']}" style="max-width: 100px; margin: 10px 0;">` : ''}
                        <p><strong>Metal Info:</strong> ${data['metal-info'] || 'N/A'}</p>
                        <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                    </div>
                </div>
            `;
            // Show the print modal
            const printModal = document.getElementById('print-modal');
            printModal.style.display = 'block';
        }).catch(error => {
            showModal('Error', 'Failed to process images. Please try again.');
        });
    }

    // Close Print Modal
    document.getElementById('modal-ok-button').addEventListener('click', () => {
        document.getElementById('print-modal').style.display = 'none';
    });

    document.querySelector('.close-modal').addEventListener('click', () => {
        document.getElementById('print-modal').style.display = 'none';
    });

    document.getElementById('print-modal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('print-modal')) {
            document.getElementById('print-modal').style.display = 'none';
        }
    });

    // Handle Furniture Type Changes to Populate Sizes
    document.getElementById('furniture').addEventListener('change', function () {
        const furnitureType = this.value;
        const sizesDropdown = document.getElementById('sizes');
        const sizeOptions = {
            chair: [
                { value: 'custom', text: 'Custom' },
                { value: 'chair-stan', text: 'Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.' }
            ],
            table: [
                { value: 'custom', text: 'Custom' },
                { value: 'table1', text: 'Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.' },
                { value: 'table2', text: 'Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.' },
                { value: 'table3', text: 'Table 6.5 seater - L: 6.5 ft. // W: 41 in. // H: 30 in.' }
            ],
            salaset: [
                { value: 'custom', text: 'Custom' },
                { value: 'salaset1', text: 'Sala Set 8x8 ft.' },
                { value: 'salaset2', text: 'Sala Set 9x9 ft.' },
                { value: 'salaset3', text: 'Sala Set 10x10 ft.' },
                { value: 'salaset4', text: 'Sala Set 10x11 ft.' }
            ],
            bedframe: [
                { value: 'custom', text: 'Custom' },
                { value: 'bedframe1', text: 'Bed Frame - California King 72x84 in.' },
                { value: 'bedframe2', text: 'Bed Frame - King 76x80 in.' },
                { value: 'bedframe3', text: 'Bed Frame - Queen 60x80 in.' },
                { value: 'bedframe4', text: 'Bed Frame - Full XL 54x80 in.' },
                { value: 'bedframe5', text: 'Bed Frame - Full 54x75 in.' },
                { value: 'bedframe6', text: 'Bed Frame - Twin XL 38x80 in.' },
                { value: 'bedframe7', text: 'Bed Frame - Twin 38x75 in.' }
            ]
        };
        sizesDropdown.innerHTML = '<option value="" disabled selected>Select one</option>';
        if (sizeOptions[furnitureType]) {
            sizeOptions[furnitureType].forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.text;
                sizesDropdown.appendChild(opt);
            });
        }
    });

    // Modal Functionality
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

// Close Print Modal
document.getElementById('modal-ok-button').addEventListener('click', () => {
    document.getElementById('print-modal').style.display = 'none';
});

// Close modal when clicking the close button or outside the modal
document.querySelector('.close-modal').addEventListener('click', () => {
    document.getElementById('print-modal').style.display = 'none';
});

document.getElementById('print-modal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('print-modal')) {
        document.getElementById('print-modal').style.display = 'none';
    }
});