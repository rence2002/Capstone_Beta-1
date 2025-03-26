// Get the select element and the tool-tip container
const selectElement = document.getElementById('furniture');
const toolTip = document.querySelector('.tool-tip');

// Add an event listener to the select element
selectElement.addEventListener('mouseover', (e) => {
    const selectedOption = e.target.options[e.target.selectedIndex];
    
    // Check if the option has a tooltip
    if (selectedOption && selectedOption.dataset.tooltip) {
        toolTip.textContent = selectedOption.dataset.tooltip; // Set tool-tip text
        toolTip.style.opacity = 1; // Show tool-tip
    }
});

selectElement.addEventListener('mouseout', () => {
    toolTip.style.opacity = 0; // Hide tool-tip
});