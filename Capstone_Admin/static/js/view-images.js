document.addEventListener("DOMContentLoaded", function () {
  const images = document.querySelectorAll(".review-picture");
  
  images.forEach(image => {
      image.addEventListener("click", function () {
          // Create overlay
          const overlay = document.createElement("div");
          overlay.style.position = "fixed";
          overlay.style.top = "0";
          overlay.style.left = "0";
          overlay.style.width = "100vw";
          overlay.style.height = "100vh";
          overlay.style.backgroundColor = "rgba(0, 0, 0, 0.8)";
          overlay.style.display = "flex";
          overlay.style.justifyContent = "center";
          overlay.style.alignItems = "center";
          overlay.style.zIndex = "1000";
          overlay.style.cursor = "pointer";
          
          // Create enlarged image
          const enlargedImg = document.createElement("img");
          enlargedImg.src = image.src;
          enlargedImg.style.maxWidth = "90%";
          enlargedImg.style.maxHeight = "90%";
          enlargedImg.style.borderRadius = "10px";
          enlargedImg.style.boxShadow = "0 0 15px rgba(255, 255, 255, 0.5)";
          enlargedImg.style.transition = "transform 0.25s ease";

          // Append image to overlay
          overlay.appendChild(enlargedImg);
          document.body.appendChild(overlay);

          // Remove overlay on click
          overlay.addEventListener("click", function () {
              document.body.removeChild(overlay);
          });

          // Add right-click zoom functionality
          enlargedImg.addEventListener("contextmenu", function (event) {
              event.preventDefault();
              if (enlargedImg.style.transform === "scale(2)") {
                  enlargedImg.style.transform = "scale(1)";
              } else {
                  enlargedImg.style.transform = "scale(2)";
              }
          });
      });
  });
});
