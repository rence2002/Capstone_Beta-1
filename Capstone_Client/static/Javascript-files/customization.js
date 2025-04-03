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

document.addEventListener('DOMContentLoaded', function () {
    // Ensure the modal is hidden on page load
    const printModal = document.getElementById('print-modal');
    if (printModal) {
        printModal.style.display = 'none';
    }
});
