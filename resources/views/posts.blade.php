<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts - API App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .card {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .card-body {
            background: #ffffff;
            border-radius: 0 0 12px 12px;
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

@include('layouts.navbar')

<div class="container mt-4">
    <div id="toast-msg" class="alert"></div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>All Posts</h2>
        <button class="btn btn-primary" id="createPostBtn">Create Post</button>
    </div>
    <div class="row" id="postsContainer"></div>
    <div class="text-center mt-4">
        <button id="loadMoreBtn" class="btn btn-outline-primary d-none">Load More</button>
    </div>
</div>

<!-- Create/Edit Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="postForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalTitle">Create Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="postId">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Content</label>
                        <textarea id="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Image</label>
                        <input type="file" id="post_image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>  

@include('layouts.footer')

<script src="{{ asset('js/jQuery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let token = localStorage.getItem("token");
let currentUser = null;
let currentPage = 1;
let lastPage = 1;

$(document).ready(function(){
    if(!token) {
        window.location.href = "/";
        return;
    }

    $.ajax({
        url: "/api/user",
        headers: { Authorization: "Bearer " + token },
        success: function(res) {
            currentUser = res.user;
            loadPosts();
        }
    });

    $("#createPostBtn").click(function(){
        $("#postId").val("");
        $("#title").val("");
        $("#content").val("");
        $("#post_image").val("");
        $("#postModalTitle").text("Create Post");
        $("#postModal").modal("show");
    });

    $("#postForm").submit(function(e){
        e.preventDefault();
        let id = $("#postId").val();
        let formData = new FormData();
        formData.append("title", $("#title").val());
        formData.append("content", $("#content").val());
        if($("#post_image")[0].files[0]) {
            formData.append("post_image", $("#post_image")[0].files[0]);
        }

        let method = "POST";
        let url = id ? `/api/posts/${id}?_method=PUT` : "/api/posts";

        $.ajax({
            url: url,
            method: method,
            processData: false,
            contentType: false,
            headers: { Authorization: "Bearer " + token },
            data: formData,
            success: function(res){
                $("#postModal").modal("hide");
                showToast(res.message, "success");
                currentPage = 1;
                $("#postsContainer").empty();
                loadPosts();
            }
        });
    });

    $("#loadMoreBtn").click(function(){
        if(currentPage < lastPage){
            currentPage++;
            loadPosts(currentPage);
        }
    });
});

// Load Posts with Like/Comment Count
function loadPosts(page = 1){
    $.ajax({
        url: `/api/posts?page=${page}`,
        headers: { Authorization: "Bearer " + token },
        success: function(res){
            lastPage = res.post.last_page;

            res.post.data.forEach(post => {
                let actionBtns = "";
                if(post.user_id === currentUser.id){
                    actionBtns = `
                        <button class="btn btn-sm btn-warning" onclick="editPost(${post.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deletePost(${post.id})">Delete</button>
                    `;
                }

                let likeBtnText = post.liked_by_user ? "Unlike" : "Like";

                $("#postsContainer").append(`
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            ${post.post_image ? `<img src="/storage/${post.post_image}" class="card-img-top">` : ''}
                            <div class="card-body">
                                <h5>${post.title}</h5>
                                <p>${post.content.substring(0, 100)}...</p>
                                <small>By <a href="/profile/${post.user.id}">${post.user.name}</a></small>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary likeBtn" data-id="${post.id}">${likeBtnText}</button>
                                    <span class="likes-count" data-id="${post.id}">${post.likes_count} Likes • ${post.comments_count} Comments</span>
                                </div>
                                <div class="mt-2">
                                    ${actionBtns}
                                    <a href="/posts/${post.id}" class="btn btn-sm btn-outline-secondary">Show Post</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });

            if(currentPage < lastPage){
                $("#loadMoreBtn").removeClass("d-none");
            } else {
                $("#loadMoreBtn").addClass("d-none");
            }
        }
    });
}

// Like/Unlike Toggle
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
            let currentLikes = parseInt(likesCountEl.text());
            likesCountEl.text(
                (res.liked ? currentLikes + 1 : currentLikes - 1) + 
                " Likes • " + likesCountEl.text().split("•")[1]
            );
        }
    });
});

function editPost(id){
    $.ajax({
        url: `/api/posts/${id}`,
        headers: { Authorization: "Bearer " + token },
        success: function(res){
            showToast(res.message, "success");
            $("#postId").val(res.post.id);
            $("#title").val(res.post.title);
            $("#content").val(res.post.content);
            $("#postModalTitle").text("Edit Post");
            $("#postModal").modal("show");
        }
    });
}

function deletePost(id){
    if(confirm("Are you sure?")){
        $.ajax({
            url: `/api/posts/${id}`,
            method: "POST",
            headers: { Authorization: "Bearer " + token },
            data: { _method: "DELETE" },
            success: function(res){
                showToast(res.message, "success");
                currentPage = 1;
                $("#postsContainer").empty();
                loadPosts();
            }
        });
    }
}

function showToast(message, type) {
    let toast = $("#toast-msg");
    toast.removeClass("alert-success alert-danger").addClass(`alert-${type}`);
    toast.text(message).fadeIn();
    setTimeout(() => toast.fadeOut(), 4000);
}

function viewPost(id){
    window.location.href = `/posts/${id}`;
}

</script>
</body>
</html>
