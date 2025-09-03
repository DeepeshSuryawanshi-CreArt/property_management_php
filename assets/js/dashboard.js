const user_logedin = localStorage.getItem('loggedin');
console.log(user_logedin);
if(!user_logedin){ 
    window.location.href = "./login.html"; // redirect if needed
};

// Handle form submission
  $("#propertyForm").on("submit", function(e) {
    e.preventDefault();

    // Collect form data
    let formData = new FormData(this);

    // Example: AJAX request to PHP backend
    $.ajax({
      url: "./api/add_property.php", // backend PHP file
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
        alert("✅ Property added successfully!");
        console.log(response);
      },
      error: function() {
        alert("❌ Failed to add property.");
      }
    });
  });