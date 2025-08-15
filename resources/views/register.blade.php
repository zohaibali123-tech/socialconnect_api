<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - API App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6fa;
        }
        .register-container {
            max-width: 450px;
            margin: 80px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .register-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h3 class="register-title">Register</h3>
    <div id="alertBox"></div>

    <form id="registerForm" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
        </div>
        <div class="mb-3">
            <label>Profile Image (optional)</label>
            <input type="file" id="profile_image" name="profile_image" class="form-control">
        </div>
        <button type="submit" class="btn btn-success w-100">Register</button>
    </form>

    <hr>
    <p class="text-center">
        Already have an account? 
        <a href="{{ url('/') }}">Login here</a>
    </p>
</div>

<script src="js/jQuery.js"></script>
<script>
    $(document).ready(function() {
        $("#registerForm").submit(function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: "{{ url('/api/register') }}", // Laravel API register route
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        localStorage.setItem("token", response.token); // Save token
                        $("#alertBox").html(`<div class="alert alert-success">${response.message}</div>`);
                        setTimeout(() => {
                            window.location.href = "/dashboard"; // Redirect after register
                        }, 1500);
                    } else {
                        $("#alertBox").html(`<div class="alert alert-danger">${response.message}</div>`);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorHtml = `<div class="alert alert-danger"><ul>`;
                        $.each(errors, function(key, value) {
                            errorHtml += `<li>${value[0]}</li>`;
                        });
                        errorHtml += `</ul></div>`;
                        $("#alertBox").html(errorHtml);
                    } else {
                        $("#alertBox").html(`<div class="alert alert-danger">Something went wrong</div>`);
                    }
                }
            });
        });
    });
</script>

</body>
</html>
