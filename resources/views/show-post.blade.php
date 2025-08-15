<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Post - API App</title>
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

        #post-container {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .list-group-item {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .post-image {
            max-width: 400px;
            height: auto;
            border-radius: 8px;
            display: block;
        }
    </style>
</head>
<body>
@include('layouts.navbar')

<div class="container mt-4">
    <div id="toast-msg" class="alert"></div>

    <!-- Post -->
    <div id="post-container" class="card p-3 mb-4">
        
    </div>

    <!-- Comments -->
    <div>
        <h5>Comments</h5>
        <ul id="comments-list" class="list-group"></ul>
        <button id="load-more-comments" class="btn btn-primary mt-2" style="display:none;">Load More</button>

        <form id="comment-form" class="mt-3">
            <div class="mb-3">
                <textarea id="comment_body" class="form-control" placeholder="Write a comment..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Comment</button>
        </form>
    </div>
</div>

<!-- Likes Modal -->
<div class="modal fade" id="likesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">People who liked this</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:300px; overflow-y:auto;">
                <ul id="likes-list" class="list-group"></ul>
                <button id="load-more-likes" class="btn btn-primary w-100" style="display:none;">Load More</button>
            </div>
        </div>
    </div>
</div>

@include('layouts.footer')
<script src="{{ asset('js/jQuery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let token = localStorage.getItem("token");
let postId = window.location.pathname.split("/").pop();
let currentUserId = null;
let commentsPage = 1;
let commentsLastPage = 1;

$(document).ready(function () {
    if (!token) {
        window.location.href = "/login";
        return;
    }

    getCurrentUser().then(() => {
        loadPost();
        loadComments();
    });

    // Like/Unlike
    $(document).on("click", ".likeBtn", function() {
        $.ajax({
            url: `/api/posts/${postId}/like`,
            method: "POST",
            headers: { "Authorization": "Bearer " + token },
            success: function(res) {
                showToast(res.message, "success");
                $(".likeBtn").text(res.liked ? "Unlike" : "Like");
                $("#likes-count").text(res.total_likes + " Likes");
            }
        });
    });

    // Add comment
    $("#comment-form").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: `/api/posts/${postId}/comments`,
            method: "POST",
            headers: { "Authorization": "Bearer " + token },
            data: { comment_text: $("#comment_body").val() },
            success: function(res) {
                $("#comment_body").val("");
                showToast(res.message, "success");
                commentsPage = 1;
                loadComments();
            }
        });
    });

    // Edit comment
    $(document).on("click", ".edit-comment", function() {
        let commentId = $(this).data("id");
        let oldText = $(this).closest("li").find(".comment-text").text();
        let newText = prompt("Edit your comment:", oldText);
        if (newText && newText.trim() !== "") {
            $.ajax({
                url: `/api/comments/${commentId}`,
                method: "PUT",
                headers: { "Authorization": "Bearer " + token },
                data: { comment_text: newText },
                success: function(res) {
                    showToast(res.message, "success");
                    commentsPage = 1;
                    loadComments();
                }
            });
        }
    });

    // Delete comment
    $(document).on("click", ".delete-comment", function() {
        if (confirm("Are you sure you want to delete this comment?")) {
            let commentId = $(this).data("id");
            $.ajax({
                url: `/api/comments/${commentId}`,
                method: "DELETE",
                headers: { "Authorization": "Bearer " + token },
                success: function(res) {
                    showToast(res.message, "success");
                    commentsPage = 1;
                    loadComments();
                }
            });
        }
    });

    // Load more comments
    $("#load-more-comments").click(function() {
        if (commentsPage < commentsLastPage) {
            commentsPage++;
            loadComments(true);
        }
    });
});

function getCurrentUser() {
    return $.ajax({
        url: "/api/user",
        method: "GET",
        headers: { "Authorization": "Bearer " + token },
        success: function(res) {
            currentUserId = res.user.id;
        }
    });
}

function loadPost() {
    $.ajax({
        url: `/api/posts/${postId}`,
        method: "GET",
        headers: { "Authorization": "Bearer " + token },
        success: function(res) {
            let post = res.post;
            let likedByUser = post.likes.some(l => l.user_id === currentUserId);
            let likeBtnText = likedByUser ? "Unlike" : "Like";
            let imageHtml = "";
            if (post.post_image) {
                let imageUrl = post.post_image.startsWith("http") 
                    ? post.post_image 
                    : `/storage/${post.post_image}`;
                imageHtml = `<img src="${imageUrl}" class="post-image mb-3" alt="Post Image">`;
            }

            $("#post-container").html(`
                <h3>${post.title}</h3>
                ${imageHtml}
                <p>${post.content}</p>
                <small class="text-muted">By <a href="/profile/${post.user.id}">${post.user.name}</a> • ${post.created_at}</small>
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-primary likeBtn">${likeBtnText}</button>
                    <span id="likes-count">${post.likes.length} Likes</span>
                    <button id="show-likes-btn" class="btn btn-link btn-sm">View All</button>
                </div>
            `);
        }
    });
}

let likesPage = 1;
let likesLastPage = 1;

function loadLikes(append = false) {
    $.ajax({
        url: `/api/posts/${postId}/likes?page=${likesPage}&per_page=10`,
        method: "GET",
        headers: { "Authorization": "Bearer " + token },
        success: function(res) {
            likesLastPage = res.likes.last_page;
            let html = "";
            res.likes.data.forEach(like => {
                html += `<li class="list-group-item"><a href="/profile/${like.user.id}">${like.user.name}</a></li>`;
            });

            if (append) {
                $("#likes-list").append(html);
            } else {
                $("#likes-list").html(html);
            }

            $("#load-more-likes").toggle(likesPage < likesLastPage);
        }
    });
}

$(document).on("click", "#load-more-likes", function() {
    if (likesPage < likesLastPage) {
        likesPage++;
        loadLikes(true);
    }
});

// Show modal on click
$(document).on("click", "#show-likes-btn", function() {
    likesPage = 1;
    loadLikes(false);
    let modal = new bootstrap.Modal(document.getElementById('likesModal'));
    modal.show();
});

function loadComments(append = false) {
    $.ajax({
        url: `/api/posts/${postId}/comments?page=${commentsPage}&per_page=5`,
        method: "GET",
        headers: { "Authorization": "Bearer " + token },
        success: function(res) {
            commentsLastPage = res.last_page;
            let comments = res.data || [];
            let html = "";
            comments.forEach(c => {
                let actions = "";
                if (c.user_id === currentUserId) {
                    actions = `
                        <button class="btn btn-sm btn-warning edit-comment" data-id="${c.id}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-comment" data-id="${c.id}">Delete</button>
                    `;
                }
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><a href="/profile/${c.user.id}">${c.user.name}</a>:</strong>
                                <span class="comment-text">${c.comment_text}</span>
                            </div>
                            <div>${actions}</div>
                         </li>`;
            });
            if (append) {
                $("#comments-list").append(html);
            } else {
                $("#comments-list").html(html);
            }
            $("#load-more-comments").toggle(commentsPage < commentsLastPage);
        }
    });
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
