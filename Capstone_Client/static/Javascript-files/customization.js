$(document).ready(function() {
    // Function to toggle visibility of custom fields and image uploads
    function toggleCustomElements(selectElement, wrapperId, imageUploadId, imagePreviewId) {
        const wrapper = document.getElementById(wrapperId);
        const imageUpload = document.getElementById(imageUploadId);
        const imagePreview = document.getElementById(imagePreviewId);

        if (selectElement.value === 'custom') {
            if (wrapper) wrapper.style.display = 'block';
            if (imageUpload) imageUpload.style.display = 'block'; // Ensure upload is visible
            if (imagePreview) imagePreview.style.display = 'block'; // Ensure preview is visible
        } else {
            if (wrapper) wrapper.style.display = 'none';
            if (imagePreview) {
                imagePreview.style.display = 'none';
                imagePreview.innerHTML = ''; // Clear the preview if hidden
            }
        }
    }

     // Handle furniture type change (unchanged)
     $('#furniture').change(function() {
        var furnitureType = $(this).val();
        var sizesOptions = '';

        if (furnitureType === 'chair') {
            sizesOptions = `
                <option value="custom">Custom</option>
                <option value="chair-stan">Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.</option>
                <option value="custom">Custom</option>
            `;
        } else if (furnitureType === 'table') {
            sizesOptions = `
                <option value="custom">Custom</option>
                <option value="table1">Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.</option>
                <option value="table2">Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.</option>
                <option value="table3">Table 6.5 seater - L: 6.5 ft. // W: 41 in. // H: 30 in.</option>
            `;
        } else if (furnitureType === 'salaset') {
            sizesOptions = `
                <option value="custom">Custom</option>
                <option value="salaset1">Sala Set 8x8 ft.</option>
                <option value="salaset2">Sala Set 9x9 ft.</option>
                <option value="salaset3">Sala Set 10x10 ft.</option>
                <option value="salaset4">Sala Set 10x11 ft.</option>
            `;
        } else if (furnitureType === 'bedframe') {
            sizesOptions = `
                <option value="custom">Custom</option>
                <option value="bedframe1">Bed Frame - California King  72x84 in.</option>
                <option value="bedframe2">Bed Frame -  King  76x80 in.</option>
                <option value="bedframe3">Bed Frame - Queen  60x80 in.</option>
                <option value="bedframe4">Bed Frame - Full XL  54x80 in.</option>
                <option value="bedframe5">Bed Frame - Full   54x75 in.</option>
                <option value="bedframe6">Bed Frame - Twin XL   38x80 in.</option>
                <option value="bedframe7">Bed Frame - Twin   38x75 in.</option>
            `;
        } else if (furnitureType === 'sofa') {
            sizesOptions = `
                <option value="custom">Custom</option>
                <option value="sofa1">Sofa - 3 Seater - L: 7 ft. // W: 35 in. // H: 34 in.</option>
                <option value="sofa2">Sofa - 2 Seater - L: 5 ft. // W: 35 in. // H: 34 in.</option>
                <option value="sofa3">Sofa - 1 Seater - L: 3 ft. // W: 35 in. // H: 34 in.</option>
            `;
        }

        $('#sizes').html(sizesOptions);
        // Trigger the change event on the sizes dropdown to handle initial state
        $('#sizes').trigger('change');
    });

    // Show custom size input field when "Custom" is selected (unchanged)
    $('#sizes').change(function() {
        toggleCustomElements(this, 'sizes-custom-info', null, null);
    });// Show custom size input field when "Custom" is selected
    $('#sizes').change(function() {
        toggleCustomElements(this, 'sizes-custom-options', null, null); // Use wrapper ID
    });

    // Call toggleCustomElements for each section on page load
    handleCustomSelectChange('#color', 'color-custom-options', 'color-file-upload', 'color-image-preview');
    handleCustomSelectChange('#texture', 'texture-custom-options', 'texture-file-upload', 'texture-image-preview');
    handleCustomSelectChange('#woods', 'woods-custom-options', 'wood-file-upload', 'wood-image-preview');
    handleCustomSelectChange('#foam', 'foam-custom-options', 'foam-file-upload', 'foam-image-preview');
    handleCustomSelectChange('#cover', 'cover-custom-options', 'cover-file-upload', 'cover-image-preview');
    handleCustomSelectChange('#design', 'design-custom-options', 'design-file-upload', 'design-image-preview');
    handleCustomSelectChange('#tile', 'tile-custom-options', 'tile-file-upload', 'tile-image-preview');
    handleCustomSelectChange('#metal', 'metal-custom-options', 'metal-file-upload', 'metal-image-preview');

    function handleCustomSelectChange(selector, wrapperId, imageUploadId, imagePreviewId) {
        $(selector).change(function() {
            toggleCustomElements(this, wrapperId, imageUploadId, imagePreviewId);
            $(`#${imageUploadId}`).change(function() {
                $(`#${imagePreviewId}`).show();
            });
        }).trigger('change'); // Trigger change to initialize hidden state
    }
});

// Show additional information fields when "Custom" is selected
function toggleCustomField(selectElement, fieldId) {
    const customField = document.getElementById(fieldId);
    if (selectElement.value === 'custom') {
        customField.style.display = 'block';
    } else {
        customField.style.display = 'none';
    }
}
