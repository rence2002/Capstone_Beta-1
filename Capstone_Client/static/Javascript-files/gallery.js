document.addEventListener('DOMContentLoaded', function() {
  let previewContainer = document.querySelector('.products-preview');
  let previewBox = previewContainer.querySelectorAll('.preview');

  // Add event listener for all products
  document.querySelectorAll('.products-container .product').forEach(product => {
      product.addEventListener('click', (event) => {
          // Check if the clicked element is inside the model-viewer
          if (event.target.closest('model-viewer')) {
              // If the click is within the model-viewer, return early and don't trigger preview
              return;
          }

          // Show the preview container
          previewContainer.style.display = 'flex';
          let name = product.getAttribute('data-name');

          // Hide all previews before showing the active one
          previewBox.forEach(preview => {
              preview.classList.remove('active');
              if (preview.swiperInstance) {
                  preview.swiperInstance.destroy(true, true); // Destroy any existing Swiper instances
                  preview.swiperInstance = null;
              }
          });

          // Activate the correct preview based on the clicked product
          previewBox.forEach(preview => {
              let target = preview.getAttribute('data-target');
              if (name === target) {
                  preview.classList.add('active');

                  // Initialize a new Swiper for the active preview
                  preview.swiperInstance = new Swiper(preview.querySelector('.swiper-container'), {
                      slidesPerView: 1,
                      spaceBetween: 20,
                      pagination: {
                          el: preview.querySelector('.swiper-pagination'),
                          clickable: true,
                      },
                      navigation: {
                          nextEl: preview.querySelector('.swiper-button-next'),
                          prevEl: preview.querySelector('.swiper-button-prev'),
                      },
                  });
              }
          });
      });
  });

  // Close button logic for the preview box
  previewBox.forEach(close => {
      close.querySelector('.fa-times').onclick = () => {
          closePreview(close);
      };
  });

  // Function to close the preview
  function closePreview(close) {
      // Remove the active class from the preview and hide the container
      close.classList.remove('active');
      previewContainer.style.display = 'none';

      // Destroy the Swiper instance when the preview is closed to free up resources
      if (close.swiperInstance) {
          close.swiperInstance.destroy(true, true);
          close.swiperInstance = null;  // Clear reference to the Swiper instance
      }
  }

  // Event listener for the Escape key
  document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') { // Check if the pressed key is "Escape"
          // Find the active preview box
          const activePreview = document.querySelector('.preview.active');
          if (activePreview) {
              closePreview(activePreview); // Close the active preview
          }
      }
  });
});

//CUSTOMIZED

// Image Preview for Furniture
document.getElementById('file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Color
document.getElementById('color-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('color-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Sizes
document.getElementById('sizes-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('sizes-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Sizes
document.getElementById('texture-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('texture-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Wood
document.getElementById('wood-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('wood-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Foam
document.getElementById('foam-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('foam-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Cover
document.getElementById('cover-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('cover-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Design
document.getElementById('design-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('design-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Tiles
document.getElementById('tiles-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('tiles-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Image Preview for Metal
document.getElementById('metal-file-upload').addEventListener('change', function(event) {
  const file = event.target.files[0];
  const previewContainer = document.getElementById('metal-image-preview');
  previewContainer.innerHTML = ''; // Clear previous previews

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.maxWidth = '20%';
      img.style.borderRadius = '8px';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  }
});

// Print Button Click Handler
document.getElementById('print-button').addEventListener('click', function () {
  // Furniture section
  const furniture = document.getElementById('furniture') ? document.getElementById('furniture').value : '';
  const furnitureImage = document.getElementById('image-preview')?.querySelector('img')?.src || '';
  const furnitureInfo = document.getElementById('furniture-info') ? document.getElementById('furniture-info').value : '';

  // Color section
  const color = document.getElementById('color') ? document.getElementById('color').value : '';
  const colorImage = document.getElementById('color-image-preview')?.querySelector('img')?.src || '';
  const colorInfo = document.getElementById('color-info') ? document.getElementById('color-info').value : '';

  // Sizes section
  const sizes = document.getElementById('sizes') ? document.getElementById('sizes').value : '';
  const sizesImage = document.getElementById('sizes-image-preview')?.querySelector('img')?.src || '';
  const sizesInfo = document.getElementById('sizes-info') ? document.getElementById('sizes-info').value : '';

  // Texture section
  const texture = document.getElementById('texture') ? document.getElementById('texture').value : '';
  const textureImage = document.getElementById('texture-image-preview')?.querySelector('img')?.src || '';
  const textureInfo = document.getElementById('texture-info') ? document.getElementById('texture-info').value : '';

  // Foam section
  const foam = document.getElementById('foam') ? document.getElementById('foam').value : '';
  const foamImage = document.getElementById('foam-image-preview')?.querySelector('img')?.src || '';
  const foamInfo = document.getElementById('foam-info') ? document.getElementById('foam-info').value : '';

  // Wood section
  const wood = document.getElementById('wood') ? document.getElementById('wood').value : '';
  const woodImage = document.getElementById('wood-image-preview')?.querySelector('img')?.src || '';
  const woodInfo = document.getElementById('wood-info') ? document.getElementById('wood-info').value : '';

  // Cover section
  const cover = document.getElementById('cover') ? document.getElementById('cover').value : '';
  const coverImage = document.getElementById('cover-image-preview')?.querySelector('img')?.src || '';
  const coverInfo = document.getElementById('cover-info') ? document.getElementById('cover-info').value : '';

  // Design section
  const design = document.getElementById('design') ? document.getElementById('design').value : '';
  const designImage = document.getElementById('design-image-preview')?.querySelector('img')?.src || '';
  const designInfo = document.getElementById('design-info') ? document.getElementById('design-info').value : '';

  // Tiles section
  const tiles = document.getElementById('tiles') ? document.getElementById('tiles').value : '';
  const tilesImage = document.getElementById('tiles-image-preview')?.querySelector('img')?.src || '';
  const tilesInfo = document.getElementById('tiles-info') ? document.getElementById('tiles-info').value : '';

  // Metal section
  const metal = document.getElementById('metal') ? document.getElementById('metal').value : '';
  const metalImage = document.getElementById('metal-image-preview')?.querySelector('img')?.src || '';
  const metalInfo = document.getElementById('metal-info') ? document.getElementById('metal-info').value : '';

  // Display the summary
  const summaryDisplay = document.getElementById('summary-display');
  summaryDisplay.classList.add('show');
  summaryDisplay.style.transform = 'scale(1)';

  function formatValue(value, isImage = false) {
    if (isImage) {
        if (!value || value.trim() === '') {
            return '<span style="color: grey;">No Image</span>';
        }
        return `<img src="${value}" alt="Uploaded Image" style="max-width:100px; border-radius:8px; margin-top:10px;">`;
    } else {
        if (!value || value.trim() === '') {
            return '<span style="color: grey;">None</span>';
        }
        return value.trim();
    }
}

const summaryContent = document.getElementById('summary-content');
summaryContent.innerHTML = `

<div class="summary-item">
    <strong>Furniture:</strong><br> 
    <span>${formatValue(furniture)}</span><br>
    <strong>Furniture Information:</strong><br> 
    <span>${formatValue(furnitureInfo)}</span><br>
    <strong>Furniture Image:</strong><br>
    <span>${formatValue(furnitureImage, true)}</span>
</div>

<div class="summary-item">
    <strong>Color:</strong><br> 
    <span>${formatValue(color)}</span><br>
    <strong>Color Information:</strong><br> 
    <span>${formatValue(colorInfo)}</span><br>
    <strong>Color Image:</strong><br>
    <span>${formatValue(colorImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Sizes:</strong><br> 
    <span>${formatValue(sizes)}</span><br>
    <strong>Sizes Information:</strong><br> 
    <span>${formatValue(sizesInfo)}</span><br>
    <strong>Sizes Image:</strong><br>
    <span>${formatValue(sizesImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Texture:</strong><br> 
    <span>${formatValue(texture)}</span><br>
    <strong>Texture Information:</strong><br> 
    <span>${formatValue(textureInfo)}</span><br>
    <strong>Texture Image:</strong><br>
    <span>${formatValue(textureImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Foam:</strong><br> 
    <span>${formatValue(foam)}</span><br>
    <strong>Foam Information:</strong><br> 
    <span>${formatValue(foamInfo)}</span><br>
    <strong>Foam Image:</strong><br>
    <span>${formatValue(foamImage, true)}</span>
</div>

<div class="summary-item">
<br><br><br>
    <strong>Wood:</strong><br> 
    <span>${formatValue(wood)}</span><br>
    <strong>Wood Information:</strong><br> 
    <span>${formatValue(woodInfo)}</span><br>
    <strong>Wood Image:</strong><br>
    <span>${formatValue(woodImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Cover:</strong><br> 
    <span>${formatValue(cover)}</span><br>
    <strong>Cover Information:</strong><br> 
    <span >${formatValue(coverInfo)}</span><br>
    <strong>Cover Image:</strong><br>
    <span>${formatValue(coverImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Design:</strong><br> 
    <span>${formatValue(design)}</span><br>
    <strong>Design Information:</strong><br> 
    <span >${formatValue(designInfo)}</span><br>
    <strong>Design Image:</strong><br>
    <span>${formatValue(designImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Tiles:</strong><br> 
    <span>${formatValue(tiles)}</span><br>
    <strong>Tiles Information:</strong><br> 
    <span >${formatValue(tilesInfo)}</span><br>
    <strong>Tiles Image:</strong><br>
    <span>${formatValue(tilesImage, true)}</span>
</div>
<div class="summary-item">
    <strong>Metal:</strong><br> 
    <span>${formatValue(metal)}</span><br>
    <strong>Metal Information:</strong><br> 
    <span >${formatValue(metalInfo)}</span><br>
    <strong>Metal Image:</strong><br>
    <span>${formatValue(metalImage, true)}</span>
</div>


`;
  // Show the "Save" and "Cancel" buttons
  const saveButton = document.getElementById('save-button');
  saveButton.style.display = 'inline-block';

  const cancelButton = document.getElementById('cancel-button');
  cancelButton.style.display = 'inline-block';

  // Save button functionality
  saveButton.onclick = function () {
    setTimeout(function () {
      html2canvas(summaryDisplay).then(function (canvas) {
        const imageData = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = imageData;
        link.download = 'summary.png'; // Save as PNG
        link.click();
      });
    }, 500); // Delay for image render
  };

  // Cancel button functionality
  cancelButton.onclick = function () {
    resetSummaryDisplay();
  };
});

function resetSummaryDisplay() {
  const summaryDisplay = document.getElementById('summary-display');
  const summaryContent = document.getElementById('summary-content');
  const saveButton = document.getElementById('save-button');
  const cancelButton = document.getElementById('cancel-button');

  summaryDisplay.classList.remove('show');
  summaryDisplay.style.transform = 'scale(0)';
  summaryContent.innerHTML = '';
  saveButton.style.display = 'none';
  cancelButton.style.display = 'none';
}


  cancelButton.onclick = function () {
    console.log("Cancel button clicked"); // Debugging statement
    summaryDisplay.style.transform = 'scale(0)';
    summaryDisplay.classList.remove('show');
    
    setTimeout(function () {
        summaryDisplay.style.display = 'none'; // Completely hide popup
        summaryContent.innerHTML = ''; // Reset summary content

        // Hide save and cancel buttons
        console.log("Hiding buttons"); // Debugging statement
        if (saveButton) {
            saveButton.style.display = 'none'; // Hide save button
        } else {
            console.warn('Save button not found'); // Warning if save button is missing
        }
        if (cancelButton) {
            cancelButton.style.display = 'none'; // Hide cancel button
        } else {
            console.warn('Cancel button not found'); // Warning if cancel button is missing
        }
    }, 300); // Wait for animation to complete
};

function resetFields() {
  console.log("Reset/Delete button clicked"); // Debugging statement

  // Remove selected options from dropdowns and restore "Select One"
  const dropdowns = [
      'furniture', 'sizes', 'color', 'texture', 'wood', 'foam', 'cover', 'design', 'tiles', 'metal'
  ];

  dropdowns.forEach(dropdownId => {
      const dropdown = document.getElementById(dropdownId);
      if (dropdown) {
          dropdown.selectedIndex = 0; // Assuming the first option is "Select One"
          console.log(`Reset selection for: ${dropdownId}`); // Debugging statement
      } else {
          console.warn(`Dropdown not found: ${dropdownId}`); // Warning if dropdown is missing
      }
  });

  // Clear file inputs
  const fileInputs = [
      'file-upload', 'color-file-upload', 'sizes-file-upload', 'texture-file-upload', 
      'foam-file-upload', 'wood-file-upload', 'cover-file-upload', 'design-file-upload', 
      'tiles-file-upload', 'metal-file-upload'
  ];

  fileInputs.forEach(fileId => {
      const fileInput = document.getElementById(fileId);
      if (fileInput) {
          fileInput.value = ''; // Clear file input
          console.log(`Cleared file input: ${fileId}`); // Debugging statement
      } else {
          console.warn(`File input not found: ${fileId}`); // Warning if file input is missing
      }
  });

  // Clear image previews
  const imagePreviews = [
      'image-preview', 'color-image-preview', 'sizes-image-preview', 'texture-image-preview', 
      'foam-image-preview', 'wood-image-preview', 'cover-image-preview', 'design-image-preview', 
      'tiles-image-preview', 'metal-image-preview'
  ];

  imagePreviews.forEach(previewId => {
      const preview = document.getElementById(previewId);
      if (preview) {
          preview.innerHTML = ''; // Clear the entire preview content
          console.log(`Cleared image preview for: ${previewId}`); // Debugging statement
      } else {
          console.warn(`Preview not found: ${previewId}`); // Warning if preview is missing
      }
  });

  // Clear additional info fields
  const infoFields = [
      'furniture-info', 'sizes-info', 'color-info', 'texture-info', 
      'wood-info', 'foam-info', 'cover-info', 'design-info', 
      'tiles-info', 'metal-info'
  ];

  infoFields.forEach(infoId => {
      const infoField = document.getElementById(infoId);
      if (infoField) {
          infoField.value = ''; // Clear additional info field
          console.log(`Cleared info field: ${infoId}`); // Debugging statement
      } else {
          console.warn(`Info field not found: ${infoId}`); // Warning if info field is missing
      }
  });

  // Reset the summary display
  resetSummaryDisplay();
}

//image options


//for cart
// Function to add item to cart
function addItemToCart(item) {
  console.log("Adding item to cart:", item);
  let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
  console.log("Current cart data:", cartData);
  const existingItem = cartData.find(cartItem => cartItem.name === item.name);
  if (existingItem) {
    existingItem.quantity += 1;
    console.log("Increased quantity for existing item");
  } else {
    cartData.push(item);
    console.log("Added new item to cart");
  }
  localStorage.setItem('cartData', JSON.stringify(cartData));
  console.log("Updated cart data in localStorage");
}

// Event listener for "Add to Cart" buttons
document.querySelectorAll('add-to-cart').forEach(button => {
  button.addEventListener('click', function(event) {
    event.preventDefault();
    console.log("Add to Cart button clicked");
    const item = {
      name: this.getAttribute('data-name'),
      image: this.getAttribute('data-image'),
      price: parseFloat(this.getAttribute('data-price')),
      quantity: 1
    };
    console.log("Item data:", item);
    addItemToCart(item);
    alert('Item added to cart!');
  });
});

console.log("gallery.js loaded");