// Add this script in your existing JavaScript file or create a new one

document.getElementById('review-form').addEventListener('submit', function (event) {
  event.preventDefault(); // Prevent the form from submitting normally

  const reviewText = document.getElementById('review-text').value; // Get the review text
  if (reviewText) {
    const reviewsList = document.getElementById('reviews-list');
    const reviewItem = document.createElement('div');
    reviewItem.classList.add('review-item');
    reviewItem.textContent = reviewText; // Set the review text
    reviewsList.appendChild(reviewItem); // Add the review to the list

    document.getElementById('review-text').value = ''; // Clear the text area
  }
});