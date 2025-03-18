const header = document.querySelector("header");
const hamburgerBtn = document.querySelector("#hamburger-btn");
const closeMenuBtn = document.querySelector("#close-menu-btn");

// Toggle mobile menu on hamburger button click
hamburgerBtn.addEventListener("click", () => header.classList.toggle("show-mobile-menu"));

// Close mobile menu on close button click
closeMenuBtn.addEventListener("click", () => hamburgerBtn.click());

//gallery
let body = document.querySelector("body"),
    lightBox = document.querySelector(".lightBox"),
    img = document.querySelectorAll(".gImg"),
    showImg = lightBox.querySelector(".showImg img"),
    close = lightBox .querySelector(".close");

   for (let image of img) {
     image.addEventListener("click", ()=>{
       showImg.src = image.src;
       lightBox.style.display = "block";
       body.style.overflow = "hidden";
       close.onclick = ()=>{
         lightBox.style.display = "none";
         body.style.overflow = "visible";
       };
     });
   }

// JavaScript to toggle the side nav and class
document.querySelector('.side-nav-toggle').addEventListener('click', function() {
  document.body.classList.toggle('side-nav-open');
});
