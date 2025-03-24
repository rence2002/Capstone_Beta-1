$(document).ready(function() {
    // Toggle custom options visibility
    function toggleCustomElements(selectElement, wrapperId, imageUploadId, imagePreviewId) {
        const wrapper = $(`#${wrapperId}`);
        const imageUpload = $(`#${imageUploadId}`);
        const imagePreview = $(`#${imagePreviewId}`);

        if (selectElement.value === 'custom') {
            wrapper.show();
            imageUpload.show();
            imagePreview.show();
        } else {
            wrapper.hide().find('input').val(''); // Clear inputs
            imageUpload.hide().val(''); // Clear file input
            imagePreview.hide().empty(); // Clear preview
        }
    }

    // Initialize all dropdown handlers
    function initHandlers() {
        // Color handler
        $('#color').on('change', function() {
            toggleCustomElements(this, 'color-custom-options', 'color-file-upload', 'color-image-preview');
        });

        // Texture handler
        $('#texture').on('change', function() {
            toggleCustomElements(this, 'texture-custom-options', 'texture-file-upload', 'texture-image-preview');
        });

        // Woods handler
        $('#woods').on('change', function() {
            toggleCustomElements(this, 'woods-custom-options', 'wood-file-upload', 'wood-image-preview');
        });

        // Foam handler
        $('#foam').on('change', function() {
            toggleCustomElements(this, 'foam-custom-options', 'foam-file-upload', 'foam-image-preview');
        });

        // Cover handler
        $('#cover').on('change', function() {
            toggleCustomElements(this, 'cover-custom-options', 'cover-file-upload', 'cover-image-preview');
        });

        // Design handler
        $('#design').on('change', function() {
            toggleCustomElements(this, 'design-custom-options', 'design-file-upload', 'design-image-preview');
        });

        // Tiles handler
        $('#tiles').on('change', function() {
            toggleCustomElements(this, 'tiles-custom-options', 'tiles-file-upload', 'tiles-image-preview');
        });

        // Metal handler
        $('#metal').on('change', function() {
            toggleCustomElements(this, 'metal-custom-options', 'metal-file-upload', 'metal-image-preview');
        });

        // Sizes handler
        $('#sizes').on('change', function() {
            $('#sizes-custom-options').toggle(this.value === 'custom');
        });

        // Furniture type handler
        $('#furniture').on('change', function() {
            const furnitureType = $(this).val();
            let sizesOptions = '<option value="" disabled selected>Select one</option>';
            
            switch(furnitureType) {
                case 'chair':
                    sizesOptions += `
                        <option value="custom">Custom</option>
                        <option value="Chair - 20x21 in.">Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.</option>
                    `;
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
                        <option value="Sala Set 9x9 ft">Sala Set 9x9 ft.</option>
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
                default:
                    sizesOptions = '<option value="" disabled selected>Select one</option>';
            }

            // Update sizes dropdown
            $('#sizes').html(sizesOptions).trigger('change');
        });

        // Image preview setup
        $('input[type="file"]').on('change', function() {
            const previewId = $(this).attr('id').replace('-file-upload', '-image-preview');
            const file = this.files[0];
            const preview = $(`#${previewId}`);

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
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

    // Reset all fields
    $('#reset-button').on('click', function() {
        // Reset dropdowns
        $('.cus-boxed select').each(function() {
            $(this).val('').trigger('change'); // Trigger change to hide custom options
        });

        // Clear file inputs
        $('.cus-boxed input[type="file"]').val('');

        // Clear text inputs
        $('.cus-boxed input[type="text"]').val('');

        // Clear image previews
        $('.cus-boxed div[id$="-image-preview"]').empty().hide();

        alert('All fields have been reset!');
    });
});