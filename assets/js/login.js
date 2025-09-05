
// code
const user_logedin = localStorage.getItem('loggedin');
if(user_logedin){ 
    window.location.href = "./dashboard.php"; // redirect if needed
};

$(document).ready(function () {
    $("#loginform").on("submit", function (e) {
        e.preventDefault(); // stop normal form submission

        // Create FormData object (needed for file upload)
        let formData = new FormData(this);
        $.ajax({
            url: "http://localhost/project/real_estate/api/login.php", // API endpoint
            type: "POST",
            data: formData,
            contentType: false, // tell jQuery not to set contentType
            processData: false, // tell jQuery not to process data
            success: function (response) {
                try {
                    let res = response
                    localStorage.setItem('user',JSON.stringify(res.user))
                    cookieStore.set('token',res.token)
                    localStorage.setItem('loggedin',true)
                    if (res.success === true) {
                        alert("✅ " + res.message);
                        window.location.href = "./dashboard.php"; // redirect if needed
                    } else {
                        alert("❌ " + res.message);
                        let errors = res.error;
                        for(let error of errors){
                            alert("Error",error);
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