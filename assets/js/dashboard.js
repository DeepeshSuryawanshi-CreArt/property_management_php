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
let page = 1; // Track current page

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
      window.location.href = "./login.html";
      return;
    }

      // ✅ Load properties only after token is ready
      loadProperties(page);
  })();

    // ✅ Load properties with pagination
    function loadProperties(pageNum = 1) {
      $.ajax({
        url: `./api/get_property.php?page=${pageNum}`,
        type: "GET",
        headers: { Authorization: `Bearer ${token}` },
        success: function (res) {
          let response;
          try {
            response = typeof res === "string" ? JSON.parse(res) : res;
          } catch (e) {
            console.error("❌ Invalid JSON response:", res);
            alert("❌ Failed to parse server response.");
            return;
          }

          if (!response.success) {
            alert("Unauthorized or failed to fetch properties.");
            localStorage.clear();
            clearCookies();
            window.location.href = "login.html";
            return;
          }

          let rows = "";
          response.data.forEach(p => {
            rows += `
              <tr>
                <td>${p.id}</td>
                <td>${p.name}</td>
                <td>${p.category}</td>
                <td>${p.type}</td>
                <td>${p.city}</td>
                <td>${p.zip_code}</td>
                <td>${p.listed_by || "N/A"}</td>
                <td>
                  ${p.photos && p.photos.length > 0
                ? `<img src="./${p.photos}" width="50" height="50" style="border-radius:4px;">`
                : "No Photo"}
                </td>
                <td>
                  <button class="btn btn-sm btn-primary edit-btn" data-id="${p.id}">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-danger delete-btn" data-id="${p.id}">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </td>
              </tr>`;
          });
          $("#propertyTable tbody").html(rows);
          // ✅ Build pagination
          let paginationHtml = "";
          for (let i = 1; i <= response.pagination.total_pages; i++) {
            paginationHtml += `<button class="page-btn btn btn-sm ${i === pageNum ? "btn-primary" : "btn-light"}" data-page="${i}">${i}</button> `;
          }
          $("#pagination").html(paginationHtml);
        },
        error: function (xhr, status, error) {
          console.error("❌ Error:", xhr);

          // Check if Unauthorized (401 or 403)
          if (xhr.status === 401 || xhr.status === 403) {
            localStorage.clear();
            document.cookie.split(";").forEach(function (c) {
              document.cookie = c
                .replace(/^ +/, "")
                .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });
            window.location.href = "./login.html";
          } else {
            alert("❌ Failed to add property.");
          }
        }
      });
    }

    // ✅ Handle pagination button click
    $(document).on("click", ".page-btn", function () {
      const selectedPage = parseInt($(this).data("page"));
      if (selectedPage !== page) {
        page = selectedPage;
        loadProperties(page);
      }
    });

  // ✅ Delete property
  $(document).on("click", ".delete-btn", function () {
    const id = $(this).data("id");
    if (!confirm("Are you sure you want to delete this property?")) return;

    $.ajax({
      url: "./api/delete_property.php?id=" + id,
      type: "DELETE",
      headers: { Authorization: `Bearer ${token}` },
      success: function (res) {
        let response;
        try {
          response = typeof res === "string" ? JSON.parse(res) : res;
        } catch (e) {
          alert("❌ Failed to parse server response.");
          return;
        }

        if (response.success) {
          alert("✅ Property deleted.");
            loadProperties(page);
        } else {
          alert("❌ Failed: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("❌ AJAX Error:", status, error);
        alert("❌ Error deleting property.");
      }
    });
  });

  // ✅ Edit property
  $(document).on("click", ".edit-btn", function () {
    const id = $(this).data("id");
    window.location.href = "edit_property.php?id=" + id;
  });
});