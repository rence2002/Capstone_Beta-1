function updateFurnitureDetails() {
  const furnitureSelect = document.getElementById("furniture");
  const selectedValue = furnitureSelect.value;
  const furnitureImage = document.getElementById("furniture-image");
  const furnitureDescription = document.getElementById("furniture-description");

  // Define the image sources and descriptions based on the selected option
  const furnitureDetails = {
      chair: {
          image: '/images/chair.jpg', // Replace with the actual path to your image
          description: 'A comfortable chair perfect for relaxing.'
      },
      table: {
          image: 'path/to/table-image.jpg',
          description: 'A sturdy table suitable for dining or working.'
      },
      salaset: {
          image: 'path/to/salaset-image.jpg',
          description: 'A stylish sala set for your living room.'
      },
      bedframe: {
          image: 'path/to/bedframe-image.jpg',
          description: 'A durable bed frame that supports a good night’s sleep.'
      }
  };

  // Update the image source and description text
  if (furnitureDetails[selectedValue]) {
      furnitureImage.src = furnitureDetails[selectedValue].image;
      furnitureImage.style.display = 'block'; // Show the image
      furnitureDescription.innerText = furnitureDetails[selectedValue].description;
      furnitureDescription.style.display = 'block'; // Show the description
  } else {
      furnitureImage.src = '';
      furnitureImage.style.display = 'none'; // Hide the image if no valid selection
      furnitureDescription.innerText = '';
      furnitureDescription.style.display = 'none'; // Hide the description
  }
}