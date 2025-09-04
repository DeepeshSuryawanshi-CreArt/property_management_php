// ✅ Function to get token from cookies
async function getToken() {
  try {
    const cookie = await cookieStore.get('token');
    return cookie ? cookie.value : null;
  } catch (err) {
    console.error("❌ Error reading cookie:", err);
    return null;
  }
}

let userLoggedIn = false
let token = null;

// ✅ Main check
(async function () {
  userLoggedIn = localStorage.getItem('loggedin');
  token = await getToken();
  // If not logged in OR no token → redirect
  if (!userLoggedIn || !token) {
    window.location.href = "./login.html";
  }
})();

// Handle form submission
$("#propertyForm").on("submit", function (e) {
  e.preventDefault();

  // Collect form data
  let formData = new FormData(this);

  $.ajax({
    url: "./api/add_property.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false, // ✅ Important for FormData
    headers: {
      "Authorization": `Bearer ${token}`,
    },
    success: function (response) {
      alert("✅ Property added successfully!");
      console.log(response);
    },
    error: function (xhr) {
      console.error("❌ Error:", xhr);

      // Check if Unauthorized (401 or 403)
      if (xhr.status === 401 || xhr.status === 403) {
        // 🔹 Clear localStorage
        localStorage.clear();

        // 🔹 Clear all cookies
        document.cookie.split(";").forEach(function (c) {
          document.cookie = c
            .replace(/^ +/, "")
            .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });

        // 🔹 Redirect to login page
        window.location.href = "./login.html";
      } else {
        alert("❌ Failed to add property.");
      }
    },
  });
});