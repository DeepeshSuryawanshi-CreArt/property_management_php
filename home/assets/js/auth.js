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

let token = null;

// ✅ Function to clear all cookies
function clearCookies() {
    document.cookie.split(";").forEach(c => {
        document.cookie = c
            .replace(/^ +/, "")
            .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
    });
}

$(document).ready(function () {
    (async function () {
        let userLoggedIn = localStorage.getItem('loggedin');
        token = await getToken();

        // Redirect if not logged in
        if (!userLoggedIn || !token) {
            window.location.href = "../login.html";
            return;
        }
    })();
})