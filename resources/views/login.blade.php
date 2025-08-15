<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - API App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .login-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #007bff;
        }
        .social-btn {
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h3 class="login-title">Login</h3>
    <div id="alertBox"></div>

    <form id="loginForm">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" id="email" class="form-control" placeholder="Enter email" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <hr>
    <div class="text-center mb-2">Or Login with</div>
    <div class="d-grid gap-2">
        <a href="{{ url('/auth/google') }}" class="btn btn-danger social-btn">Login with Google</a>
    </div>

    <hr>
    <p class="text-center">
        Don't have an account? 
        <a href="{{ url('/register') }}">Register here</a>
    </p>
</div>

<script src="js/jQuery.js"></script>
<script>
    $(document).ready(function() {
        $("#loginForm").submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ url('/api/login') }}", // Laravel API login route
                method: "POST",
                data: {
                    email: $("#email").val(),
                    password: $("#password").val()
                },
                success: function(response) {
                    if (response.status) {
                        localStorage.setItem("token", response.token); // Save token
                        $("#alertBox").html(`<div class="alert alert-success">${response.message}</div>`);
                        setTimeout(() => {
                            window.location.href = "/dashboard"; // Redirect after login
                        }, 1500);
                    } else {
                        $("#alertBox").html(`<div class="alert alert-danger">${response.message}</div>`);
                    }
                },
                error: function(xhr) {
                    $("#alertBox").html(`<div class="alert alert-danger">Invalid email or password</div>`);
                }
            });
        });
    });
</script>

</body>
</html>
