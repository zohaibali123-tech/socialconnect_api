<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - API App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        #toast-msg {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            display: none;
        }

        .post-card {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .post-card img {
            max-height: 200px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .card-body {
            background: #ffffff;
            border-radius: 0 0 12px 12px;
        }

        .card-title {
            color: #0d6efd;
            font-weight: 600;
        }

        .card-text {
            color: #495057;
        }

        .likeBtn {
            border-radius: 50px;
            padding: 4px 12px;
        }
    </style>
</head>
<body>

@include('layouts.navbar')

<div class="d-flex">
    @include('layouts.sidebar')

    @if(session('auth_token'))
    <script>
        localStorage.setItem('token', "{{ session('auth_token') }}");
    </script>
    @endif

    <div class="container my-4">
        <div id="toast-msg" class="alert"></div>

        <h4 class="mb-4">Latest Posts</h4>
        <div class="row" id="postsContainer">
            <!-- Posts will load here via AJAX -->
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation example" class="mt-4">
            <ul class="pagination justify-content-center" id="paginationLinks">
                <!-- Pagination buttons will be generated dynamically -->
            </ul>
        </nav>
    </div>
</div>

@include('layouts.footer')

<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createPostForm" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPostModalLabel">Create Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="createPostAlert"></div>
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Content</label>
                    <textarea name="content" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Post Image</label>
                    <input type="file" name="post_image" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Create</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/jQuery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    let token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "/";
        return;
    }

    let currentPage = 1;
    loadPosts(currentPage);

    // Load posts with pagination
    function loadPosts(page) {
        $.ajax({
            url: `/api/posts?page=${page}`,
            method: "GET",
            headers: { "Authorization": "Bearer " + token },
            success: function(response) {
                $("#postsContainer").empty();
                let posts = response.post.data || response.post;
                posts.forEach(post => {
                    $("#postsContainer").append(`
                        <div class="col-md-4 mb-4">
                            <div class="card post-card">
                                ${post.post_image ? `<img src="/storage/${post.post_image}" class="card-img-top">` : ''}
                                <div class="card-body">
                                    <h5 class="card-title">${post.title}</h5>
                                    <p class="card-text">${post.content.substring(0, 100)}...</p>
                                    <small class="text-muted">By ${post.user.name}</small><br>
                                    <small class="text-muted likes-count" data-id="${post.id}">
                                        ${post.likes_count || 0} Likes • ${post.comments_count || 0} Comments
                                    </small>
                                    <div class="mt-2 d-flex justify-content-between">
                                        <button class="btn btn-sm btn-outline-primary likeBtn" data-id="${post.id}">
                                            ${post.liked_by_user ? 'Unlike' : 'Like'}
                                        </button>
                                        <a href="/posts/${post.id}" class="btn btn-sm btn-outline-secondary">
                                            Show Post
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });

                // Pagination buttons
                if (response.post.last_page) {
                    $("#paginationLinks").empty();
                    for (let i = 1; i <= response.post.last_page; i++) {
                        $("#paginationLinks").append(`
                            <li class="page-item ${i === response.post.current_page ? 'active' : ''}">
                                <a class="page-link" href="#">${i}</a>
                            </li>
                        `);
                    }
                }
            }
        });
    }

    // Handle pagination click
    $(document).on("click", "#paginationLinks a", function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).text());
        loadPosts(currentPage);
    });

    // Like/Unlike functionality
    $(document).on("click", ".likeBtn", function(){
        let postId = $(this).data("id");
        let btn = $(this);
        $.ajax({
            url: `/api/posts/${postId}/like`,
            method: "POST",
            headers: { "Authorization": "Bearer " + token },
            success: function(res) {
                showToast(res.message, "success");
                btn.text(res.liked ? "Unlike" : "Like");
                let likesCountEl = $(`.likes-count[data-id="${postId}"]`);
                let currentCount = parseInt(likesCountEl.text());
                likesCountEl.text(
                    (res.liked ? currentCount + 1 : currentCount - 1) + 
                    " Likes • " + likesCountEl.text().split("•")[1]
                );
            }
        });
    });

    // Create Post form
    $("#createPostForm").submit(function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: "/api/posts",
            method: "POST",
            headers: { "Authorization": "Bearer " + token },
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                $("#createPostAlert").html(`<div class="alert alert-success">${res.message}</div>`);
                setTimeout(() => {
                    $("#createPostModal").modal("hide");
                    loadPosts(1);
                }, 1000);
            },
            error: function(xhr) {
                $("#createPostAlert").html(`<div class="alert alert-danger">Error creating post</div>`);
            }
        });
    });
});

function showToast(message, type) {
    let toast = $("#toast-msg");
    toast.removeClass("alert-success alert-danger").addClass(`alert-${type}`);
    toast.text(message).fadeIn();
    setTimeout(() => toast.fadeOut(), 4000);
}
</script>

</body>
</html>
