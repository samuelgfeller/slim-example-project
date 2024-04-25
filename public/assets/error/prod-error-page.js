const statusCode = document.getElementById('error-status-code');

// Make the linear gradient direction of the status code follow the cursor
document.documentElement.addEventListener("mousemove", function (event) {
    // Retrieve the bounding rectangle of the "statusCode" element
    const {left, top, width, height} = statusCode.getBoundingClientRect();

    // Calculate the center coordinates of the "statusCode" element
    const centerX = left + width / 2;
    const centerY = top + height / 2;

    // Calculate the angle (in radians) between the cursor position and the center of the element
    const radians = Math.atan2(event.clientY - centerY, event.clientX - centerX);

    // Convert the angle from radians to degrees
    const degrees = radians * (180 / Math.PI);

    // Add 90 degrees to shift the range from [-180, 180] to [0, 360] degrees
    const gradientDirection = degrees + 90;

    // Apply the linear gradient background to the "statusCode" element
    const style = getComputedStyle(document.body);
    const color1 = style.getPropertyValue('--error-status-code-gradient-color-1');
    const color2 = style.getPropertyValue('--error-status-code-gradient-color-2');
    setTimeout(() => {
            statusCode.style.backgroundImage = `linear-gradient(${gradientDirection}deg, ${color1} 0%, ${color2} 100%)`;
        }, 300
    )
});