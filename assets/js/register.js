const user_logedin = localStorage.getItem('loggedin');
if(user_logedin){ 
    window.location.href = "./dashboard.php"; // redirect if needed
};
// code
$(document).ready(function () {
    $("#registerForm").on("submit", function (e) {
        e.preventDefault(); // stop normal form submission

        // Create FormData object (needed for file upload)
        let formData = new FormData(this);
        console.log("form data in the payload:", formData);
        $.ajax({
            url: "http://localhost/project/real_estate/api/register.php", // API endpoint
            type: "POST",
            data: formData,
            contentType: false, // tell jQuery not to set contentType
            processData: false, // tell jQuery not to process data
            success: function (response) {
                try {
                    let res = response
                    console.log(res.success)
                    if (res.success === true) {
                        alert("✅ " + res.message);
                        window.location.href = "login.html"; // redirect if needed
                    } else {
                        alert("❌ " + res.message);
                        let errors = res.error;
                        for (let error of errors) {
                            alert("Error", error);
                        }
                    }
                } catch (e) {
                    console.warn("catch get", e);
                    console.error("Invalid JSON response", response);
                    alert("Unexpected server response");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                alert("Request failed: " + error);
            }
        });
    });
});