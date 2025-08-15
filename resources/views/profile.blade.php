<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - API App</title>
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
        }

        .card-body {
            background: #ffffff;
            border-radius: 0 0 12px 12px;
        }
    </style>
</head>
<body>

@include('layouts.navbar')

<div class="container mt-4">
    <div id="toast-msg" class="alert"></div>
    <div class="card">
        <div class="card-body text-center">
            <img id="profileImage" src="" class="rounded-circle mb-3" style="width:150px;height:150px;object-fit:cover;">
            <h3 id="profileName"></h3>
            <p id="profileEmail"></p>

            <div id="editButtons" style="display:none;">
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Personal Information</button>
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#changePictureModal">Change Profile Picture</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal">
  <div class="modal-dialog">
    <form id="editProfileForm" class="modal-content">
      <div class="modal-header"><h5>Edit Profile</h5></div>
      <div class="modal-body">
        <input type="text" name="name" id="editName" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Change Picture Modal -->
<div class="modal fade" id="changePictureModal">
  <div class="modal-dialog">
    <form id="changePictureForm" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header"><h5>Change Profile Picture</h5></div>
      <div class="modal-body">
        <input type="file" name="profile_image" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Upload</button>
      </div>
    </form>
  </div>
</div>

@include('layouts.footer')

<script src="{{ asset('js/jquery.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let token = localStorage.getItem("token");
let loggedInUserId = null;
let profileUserId = "{{ request()->route('id') ?? '' }}"; // agar route me id pass hui hai

$(document).ready(function(){
    if(!token) { window.location.href = "/"; return; }

    // Get logged in user data
    $.ajax({
        url: "/api/user",
        headers: { Authorization: "Bearer " + token },
        success: function(res){
            loggedInUserId = res.user.id;
            if(!profileUserId) profileUserId = loggedInUserId;
            loadProfile();
        }
    });
});

function loadProfile(){
    $.ajax({
        url: `/api/users/${profileUserId}`,
        headers: { Authorization: "Bearer " + token },
        success: function(res){
            let u = res.user;
            $("#profileName").text(u.name);
            $("#profileEmail").text(u.email);
            $("#profileImage").attr(
                "src",
                u.profile_image
                    ? (u.profile_image.startsWith("http") ? u.profile_image : `/storage/${u.profile_image}`)
                    : "https://via.placeholder.com/150"
            );
            if(profileUserId == loggedInUserId){
                $("#editButtons").show();
                $("#editName").val(u.name);
            }
        }
    });
}

// Edit Profile
$("#editProfileForm").submit(function(e){
    e.preventDefault();
    $.ajax({
        url: "/api/profile/update",
        method: "PUT",
        headers: { Authorization: "Bearer " + token },
        data: $(this).serialize(),
        success: function(res){
            $("#editProfileModal").modal('hide');
            showToast(res.message, "success");
            loadProfile();
        }
    });
});

// Change Picture
$("#changePictureForm").submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: "/api/profile/update-picture",
        method: "POST",
        headers: { Authorization: "Bearer " + token },
        data: formData,
        processData: false,
        contentType: false,
        success: function(res){
            $("#changePictureModal").modal('hide');
            showToast(res.message, "success");
            loadProfile();
        }
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
