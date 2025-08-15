<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(90deg, #4e54c8, #8f94fb);">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="{{ url('/dashboard') }}">SocialConnect</a>

        <!-- Search Bar -->
        <form class="d-flex mx-auto position-relative" id="searchForm">
            <input class="form-control me-2" id="searchInput" type="search" placeholder="Search posts..." aria-label="Search" autocomplete="off">
            <button class="btn btn-light" type="submit">Search</button>
        
            <!-- Dropdown -->
            <ul id="searchSuggestions" class="list-group position-absolute w-100" style="top: 100%; z-index: 1000; display: none;"></ul>
        </form>

        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
               id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img id="navProfileImage"  alt="profile"
                     width="32" height="32" class="rounded-circle me-2">
                <span id="navUserName">User</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" id="profileLink">Profile</a></li>
                <li><a class="dropdown-item" href="#" id="logoutBtn">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<script src="{{ asset('js/jQuery.js') }}"></script>
<script>
function checkAuth() {
    let token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "/";
    } else {
        $.ajax({
            url: "{{ url('/api/user') }}",
            method: "GET",
            headers: { "Authorization": "Bearer " + token },
            success: function(response) {
                if (response.status) {
                    let user = response.user;
                    $("#navUserName").text(user.name);
                    $("#navProfileImage").attr(
                        "src",
                        user.profile_image
                            ? (user.profile_image.startsWith("http") ? user.profile_image : "/storage/" + user.profile_image)
                            : "{{ asset('default.png') }}"
                    );
                    $("#profileLink").attr("href", "/profile/" + user.id);
                }
            },
            error: function() {
                localStorage.removeItem("token");
                window.location.href = "/";
            }
        });
    }
}

$(document).ready(function() {
    checkAuth();

    $("#logoutBtn").click(function(e) {
        e.preventDefault();
        let token = localStorage.getItem("token");
        $.ajax({
            url: "{{ url('/api/logout') }}",
            method: "POST",
            headers: { "Authorization": "Bearer " + token },
            success: function() {
                localStorage.removeItem("token");
                window.location.href = "/";
            }
        });
    });

    let token = localStorage.getItem("token");
    let selectedPostId = null;
    let searchTimeout;
    // Live search
    $("#searchInput").on("keyup", function() {
        clearTimeout(searchTimeout);
        let query = $(this).val();

        if (query.length < 2) {
            $("#searchSuggestions").hide();
            return;
        }
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: "/api/search/posts",
                method: "GET",
                data: { query: query },
                headers: { "Authorization": "Bearer " + token },
                success: function(res) {
                    if (res.status && res.posts.length > 0) {
                        let suggestions = "";
                        res.posts.forEach(function(post) {
                            suggestions += `<li class="list-group-item suggestion-item" data-id="${post.id}">
                                ${post.title} <small class="text-muted">by ${post.user.name}</small>
                            </li>`;
                        });
                        $("#searchSuggestions").html(suggestions).show();
                    } else {
                        $("#searchSuggestions").hide();
                    }
                }
            });
        }, 300);
    });

    // Click on suggestion
    $(document).on("click", ".suggestion-item", function(){
        let title = $(this).text().trim();
        selectedPostId = $(this).data("id");
        $("#searchInput").val(title);
        $("#searchSuggestions").hide();
    });

    // Search form submit
    $("#searchForm").on("submit", function(e){
        e.preventDefault();
        if(selectedPostId){
            window.location.href = "/posts/" + selectedPostId;
        } else {
            alert("Please select a post from suggestions.");
        }
    });
});
</script>
