<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts - API App</title>
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
            height: 200px; object-fit: cover;
        }
    </style>
</head>
<body>

@include('layouts.navbar')

<div class="container mt-4">
    <div id="toast-msg" class="alert"></div>

    <h2>My Posts</h2>
    <div class="row" id="myPostsContainer"></div>
    <div class="text-center mt-4">
        <button id="loadMoreMyPosts" class="btn btn-outline-primary d-none">Load More</button>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editPostForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editPostId">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" id="editTitle" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Content</label>
                        <textarea id="editContent" name="content" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Image</label>
                        <input type="file" id="editImage" name="post_image" class="form-control">
                        <img id="currentImage" src="" class="mt-2" style="max-width:100%; display:none;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('layouts.footer')

<script src="{{ asset('js/jQuery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let token = localStorage.getItem("token");
let currentPage = 1, lastPage = 1;

$(document).ready(function(){
    if(!token) { window.location.href = "/"; return; }
    loadMyPosts();

    $("#loadMoreMyPosts").click(function(){
        if(currentPage < lastPage){
            currentPage++;
            loadMyPosts(currentPage);
        }
    });

    // Save changes in modal
    $("#editPostForm").submit(function(e){
        e.preventDefault();
        let postId = $("#editPostId").val();
        let formData = new FormData(this);
        formData.append('_method', 'PUT');

        $.ajax({
            url: `/api/posts/${postId}`,
            method: "POST",
            headers: { Authorization: "Bearer " + token },
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                showToast(res.message, "success");
                $("#editPostModal").modal('hide');
                $("#myPostsContainer").empty();
                currentPage = 1;
                loadMyPosts();
            }
        });
    });
});

// Load My Posts
function loadMyPosts(page = 1){
    $.ajax({
        url: `/api/my-posts?page=${page}`,
        headers: { Authorization: "Bearer " + token },
        success: function(res){
            lastPage = res.posts.last_page;

            res.posts.data.forEach(post => {
                let likeBtnText = post.liked_by_user ? "Unlike" : "Like";

                $("#myPostsContainer").append(`
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            ${post.post_image ? `<img src="${post.post_image}" class="card-img-top">` : ''}
                            <div class="card-body">
                                <h5>${post.title}</h5>
                                <p>${post.content.substring(0, 100)}...</p>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary likeBtn" data-id="${post.id}">${likeBtnText}</button>
                                    <span class="likes-count" data-id="${post.id}">${post.likes_count} Likes • ${post.comments_count} Comments</span>
                                </div>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-warning" onclick="openEditModal(${post.id})">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deletePost(${post.id})">Delete</button>
                                    <a href="/posts/${post.id}" class="btn btn-sm btn-outline-secondary">Show Post</a>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });

            $("#loadMoreMyPosts").toggleClass("d-none", currentPage >= lastPage);
        }
    });
}

// Like/Unlike
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
            let counts = likesCountEl.text().split("•");
            let currentLikes = parseInt(counts[0]);
            likesCountEl.text(
                (res.liked ? currentLikes + 1 : currentLikes - 1) + " Likes • " + counts[1].trim()
            );
        }
    });
});

// Open edit modal
function openEditModal(id){
    $.ajax({
        url: `/api/posts/${id}`,
        headers: { Authorization: "Bearer " + token },
        success: function(res){
            let post = res.post;
            $("#editPostId").val(post.id);
            $("#editTitle").val(post.title);
            $("#editContent").val(post.content);
            if(post.post_image){
                $("#currentImage").attr("src", post.post_image).show();
            } else {
                $("#currentImage").hide();
            }
            $("#editPostModal").modal('show');
        }
    });
}

// Delete Post
function deletePost(id){
    if(confirm("Are you sure?")){
        $.ajax({
            url: `/api/posts/${id}`,
            method: "POST",
            headers: { Authorization: "Bearer " + token },
            data: { _method: "DELETE" },
            success: function(res){
                showToast(res.message, "success");
                $("#myPostsContainer").empty();
                currentPage = 1;
                loadMyPosts();
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
</script>
</body>
</html>
