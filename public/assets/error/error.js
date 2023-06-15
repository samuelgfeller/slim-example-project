const statusCode = document.getElementById('error-status-code');

document.documentElement.addEventListener("mousemove", function(event) {
  // Retrieve the bounding rectangle of the "statusCode" element
  const { left, top, width, height } = statusCode.getBoundingClientRect();

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
  statusCode.style.backgroundImage = `linear-gradient(${gradientDirection}deg, #2acaff 0%, #ff86c0 100%)`;
});