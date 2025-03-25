document.addEventListener('DOMContentLoaded', function () {
        // Product preview functionality
        const previewContainer = document.querySelector('.products-preview');
        const previewBox = document.querySelectorAll('.preview');
    

        // Upload to Database button handler
    document.getElementById('upload-order-button').addEventListener('click', async (e) => {
        e.preventDefault();

        // Validate required fields
        const requiredFields = ['furniture', 'sizes'];
        let isValid = true;

        requiredFields.forEach(field => {
            const value = document.querySelector(`[name="${field}"]`).value;
            if (!value) {
                alert(`Please select ${field.replace('_', ' ')}`);
                isValid = false;
            }
        });

        const selectedSize = document.querySelector('[name="sizes"]').value;
        const customSizeInput = document.querySelector('[name="sizes-info"]');
        if (selectedSize === 'custom' && !customSizeInput.value.trim()) {
            alert('Custom size is required when selecting "Custom"');
            isValid = false;
        }

        if (!isValid) return;

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
            // Send data to gallery-custom-rec.php
            const response = await fetch('gallery-custom-rec.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Order uploaded successfully!');
                showReceipt(result.data); // Show receipt after successful upload
            } else {
                throw new Error(result.message || 'Submission failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(`Failed to upload order: ${error.message}`);
        }
    });

    // Print Receipt button handler
    document.getElementById('print-receipt-button').addEventListener('click', async (e) => {
        e.preventDefault();

        // Validate required fields
        const requiredFields = ['furniture', 'sizes'];
        let isValid = true;

        requiredFields.forEach(field => {
            const value = document.querySelector(`[name="${field}"]`).value;
            if (!value) {
                alert(`Please select ${field.replace('_', ' ')}`);
                isValid = false;
            }
        });

        const selectedSize = document.querySelector('[name="sizes"]').value;
        const customSizeInput = document.querySelector('[name="sizes-info"]');
        if (selectedSize === 'custom' && !customSizeInput.value.trim()) {
            alert('Custom size is required when selecting "Custom"');
            isValid = false;
        }

        if (!isValid) return;

        // Collect form data
        const formData = {};
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
                    formData[field] = selected ? selected.text : '';
                } else if (input.type === 'file') {
                    if (input.files.length) {
                        formData[field] = input.files[0]; // Store file object
                    } else {
                        formData[field] = null;
                    }
                } else {
                    formData[field] = input.value || '';
                }
            }
        });

        try {
            // Generate receipt dynamically
            showReceipt(formData);
        } catch (error) {
            console.error('Error:', error);
            alert(`Failed to generate receipt: ${error.message}`);
        }
    });

    // Reset button handler
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
        alert('Form reset successfully!');
    });

    // Print receipt modal
    function showReceipt(data) {
        const modalPreview = document.getElementById('modal-preview');

        // Clear existing content
        modalPreview.innerHTML = '';

        // Append new receipt content
        modalPreview.innerHTML = `
            <h2>Customization Receipt - ${data.user_name || 'Guest'}</h2>
            <p><strong>ID:</strong> ${data.customization_id}</p>
            <p><strong>User:</strong> ${data.user_id}</p>
            <p><strong>Furniture:</strong> ${data.furniture}</p>
            <p><strong>Size:</strong> ${data.size}</p>
            <p><strong>Color:</strong> ${data.color || 'N/A'}</p>
            ${data.color_image ? `<img src="${data.color_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Texture:</strong> ${data.texture || 'N/A'}</p>
            ${data.texture_image ? `<img src="${data.texture_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Wood:</strong> ${data.wood || 'N/A'}</p>
            ${data.wood_image ? `<img src="${data.wood_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Foam:</strong> ${data.foam || 'N/A'}</p>
            ${data.foam_image ? `<img src="${data.foam_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Cover:</strong> ${data.cover || 'N/A'}</p>
            ${data.cover_image ? `<img src="${data.cover_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Design:</strong> ${data.design || 'N/A'}</p>
            ${data.design_image ? `<img src="${data.design_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Tiles:</strong> ${data.tiles || 'N/A'}</p>
            ${data.tiles_image ? `<img src="${data.tiles_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Metal:</strong> ${data.metal || 'N/A'}</p>
            ${data.metal_image ? `<img src="${data.metal_image}" style="max-width: 100px; margin: 10px 0;">` : ''}
            <p><strong>Date:</strong> ${data.timestamp}</p>
        `;

        // Show the print modal
        const printModal = document.getElementById('print-modal');
        printModal.style.display = 'block';

        setTimeout(() => {
            window.print();
            printModal.style.display = 'none';
        }, 500);
    }

    // Close print modal
    document.querySelector('.close-modal').addEventListener('click', () => {
        document.getElementById('print-modal').style.display = 'none';
    });

    // Handle furniture type changes to populate sizes
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
});