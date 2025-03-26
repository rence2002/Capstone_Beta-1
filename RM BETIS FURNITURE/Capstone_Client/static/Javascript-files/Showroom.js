// Showroom.js

// Open and Close Nav
function openNav() {
  document.getElementById("mySidenav").style.bottom = "0";
  document.getElementById("open-btn").style.display = "none";
}

function closeNav() {
  document.getElementById("mySidenav").style.bottom = "-250px";
  document.getElementById("open-btn").style.display = "block";
}

// Show AR model function
function showModel(model, iosModel) {
  const modelViewer = document.getElementById('mainModelViewer');
  if (modelViewer) {
      modelViewer.src = model;
      modelViewer.setAttribute('ios-src', iosModel); // Set iOS model
      modelViewer.setAttribute('ar', ''); // Ensure AR is enabled
      modelViewer.setAttribute('ar-modes', 'scene-viewer webxr quick-look'); // Set AR modes

      modelViewer.requestUpdate(); // Request update to reinitialize the viewer
  }
}

function setBackground(imageUrl) {
  const mainElement = document.querySelector('main');
  mainElement.style.backgroundImage = `url(${imageUrl})`;
  mainElement.style.backgroundSize = 'cover'; // Adjust background size
  mainElement.style.backgroundPosition = 'center'; // Center the background
}

// Swipe to close navigation
let startY;

const sidenav = document.getElementById("mySidenav");
sidenav.addEventListener('touchstart', function(event) {
  startY = event.touches[0].clientY; // Store the starting touch position
});

sidenav.addEventListener('touchmove', function(event) {
  const currentY = event.touches[0].clientY;
  const distanceY = currentY - startY;

  // Check if the swipe is downwards and the sidenav is open
  if (distanceY > 50 && sidenav.style.bottom === "0px") {
      closeNav(); // Close the sidenav if swiped down more than 50px
  }
});
