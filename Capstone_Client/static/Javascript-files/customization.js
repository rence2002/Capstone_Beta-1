$(document).ready(function() {
    // Function to toggle visibility of custom fields and image uploads
    function toggleCustomElements(selectElement, customFieldId, imageUploadId, imagePreviewId) {
        const customField = document.getElementById(customFieldId);
        const imageUpload = document.getElementById(imageUploadId);
        const imagePreview = document.getElementById(imagePreviewId);
        const infoField = document.getElementById(customFieldId + '-info'); // Get additional info field

        if (selectElement.value === 'custom') {
            if (customField) customField.style.display = 'block';
            if (imageUpload) imageUpload.style.display = 'block';
            if (imagePreview) imagePreview.style.display = 'block';
            if (infoField) infoField.style.display = 'block'; // Show additional info
        } else {
            if (customField) customField.style.display = 'none';
            if (imageUpload) imageUpload.style.display = 'none';
            if (imagePreview) {
                imagePreview.style.display = 'none';
                imagePreview.innerHTML = ''; // Clear the preview if hidden
            }
            if (infoField) infoField.style.display = 'none'; // Hide additional info
        }
    }

     // Handle furniture type change (unchanged)
     $('#furniture').change(function() {
        var furnitureType = $(this).val();
        var sizesOptions = '';

        if (furnitureType === 'chair') {
            sizesOptions = `
                <option value="chair-stan">Chair - 20x21 in. // B-T-F: 37 in. // S-F: 18 in.</option>
                <option value="custom">Custom</option>
            `;
        } else if (furnitureType === 'table') {
            sizesOptions = `
                <option value="table1">Table 10 seater - L: 9 ft. // W: 41 in. // H: 30 in.</option>
                <option value="table2">Table 8 seater - L: 8 ft. // W: 41 in. // H: 30 in.</option>
                <option value="table3">Table 6.5 seater - L: 6.5 ft. // W: 41 in. // H: 30 in.</option>
                <option value="custom">Custom</option>
            `;
        } else if (furnitureType === 'salaset') {
            sizesOptions = `
                <option value="salaset1">Sala Set 8x8 ft.</option>
                <option value="salaset2">Sala Set 9x9 ft.</option>
                <option value="salaset3">Sala Set 10x10 ft.</option>
                <option value="salaset4">Sala Set 10x11 ft.</option>
                <option value="custom">Custom</option>
            `;
        } else if (furnitureType === 'bedframe') {
            sizesOptions = `
                <option value="bedframe1">Bed Frame - California King  72x84 in.</option>
                <option value="bedframe2">Bed Frame -  King  76x80 in.</option>
                <option value="bedframe3">Bed Frame - Queen  60x80 in.</option>
                <option value="bedframe4">Bed Frame - Full XL  54x80 in.</option>
                <option value="bedframe5">Bed Frame - Full   54x75 in.</option>
                <option value="bedframe6">Bed Frame - Twin XL   38x80 in.</option>
                <option value="bedframe7">Bed Frame - Twin   38x75 in.</option>
                <option value="custom">Custom</option>
            `;
        } else if (furnitureType === 'sofa') {
            sizesOptions = `
                <option value="sofa1">Sofa - 3 Seater - L: 7 ft. // W: 35 in. // H: 34 in.</option>
                <option value="sofa2">Sofa - 2 Seater - L: 5 ft. // W: 35 in. // H: 34 in.</option>
                <option value="sofa3">Sofa - 1 Seater - L: 3 ft. // W: 35 in. // H: 34 in.</option>
                <option value="custom">Custom</option>
            `;
        }

        $('#sizes').html(sizesOptions);
        // Trigger the change event on the sizes dropdown to handle initial state
        $('#sizes').trigger('change');
    });

    // Show custom size input field when "Custom" is selected (unchanged)
    $('#sizes').change(function() {
        toggleCustomElements(this, 'sizes-custom-info', null, null);
    });
// Modified handlers for Color, Texture, etc.  These now also handle file uploads
handleCustomSelectChange('#color', 'color-custom-info', 'color-file-upload', 'color-image-preview');
handleCustomSelectChange('#texture', 'texture-custom-info', 'texture-file-upload', 'texture-image-preview');
handleCustomSelectChange('#woods', 'woods-custom-info', 'wood-file-upload', 'wood-image-preview');
handleCustomSelectChange('#foam', 'foam-custom-info', 'foam-file-upload', 'foam-image-preview');
handleCustomSelectChange('#cover', 'cover-custom-info', 'cover-file-upload', 'cover-image-preview');
handleCustomSelectChange('#design', 'design-custom-info', 'design-file-upload', 'design-image-preview');
handleCustomSelectChange('#tile', 'tile-custom-info', 'tile-file-upload', 'tile-image-preview');
handleCustomSelectChange('#metal', 'metal-custom-info', 'metal-file-upload', 'metal-image-preview');


function handleCustomSelectChange(selector, customFieldId, imageUploadId, imagePreviewId) {
    $(selector).change(function() {
        toggleCustomElements(this, customFieldId, imageUploadId, imagePreviewId);
        // Show/hide upload based on file selection
        $(`#${imageUploadId}`).change(function() {
            $(`#${imagePreviewId}`).show();
        });
    });
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
