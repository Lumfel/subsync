<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Portal</title>
<link rel="stylesheet" href="{{ asset('styles.css') }}">
</head>
<body>

<div class="container" style="justify-content: center; align-items: center;">
    <div class="login_box">
        <div class="login_logo">
            <img src="emblem_white_1.svg" alt="logo">
        </div>

        <div class="form-header">
            <h2 id="form-title">Login</h2>
            <p id="form-subtitle">Welcome back! Please enter your details.</p>
        </div>

        <form id="login-form">
            <input type="email" placeholder="Gmail Address" required>
            <input type="password" placeholder="Password" required>
            <button type="submit" class="main-btn">Login</button>
        </form>

        <form id="register-form" style="display: none;">
            <input type="text" placeholder="Household Name" required>
            <div class="input-row">
                <input type="text" placeholder="Block" style="width: 48%;">
                <input type="text" placeholder="Lot" style="width: 48%;">
            </div>
            <input type="email" placeholder="Gmail Address" required>
            <input type="tel" placeholder="Contact Number" required>
            <select required>
                <option value="" disabled selected>Member Type</option>
                <option value="family">Family Member</option>
                <option value="relative">Relative</option>
                <option value="tenant">Tenant</option>
            </select>
            <input type="password" placeholder="Create Password" required>
            <button type="submit" class="main-btn">Create Account</button>
        </form>

        <div class="toggle-container">
            <p id="toggle-text">Don't have an account? <a href="#" onclick="toggleForm()">Register</a></p>
        </div>
    </div>
</div>

<script>
    function toggleForm() {
        const loginForm = document.getElementById('login-form');
        const regForm = document.getElementById('register-form');
        const title = document.getElementById('form-title');
        const subtitle = document.getElementById('form-subtitle');
        const toggleText = document.getElementById('toggle-text');

        if (loginForm.style.display === 'none') {
            loginForm.style.display = 'block';
            regForm.style.display = 'none';
            title.innerText = 'Login';
            subtitle.innerText = 'Welcome back! Please enter your details.';
            toggleText.innerHTML = "Don't have an account? <a href='#' onclick='toggleForm()'>Register</a>";
        } else {
            loginForm.style.display = 'none';
            regForm.style.display = 'block';
            title.innerText = 'Register';
            subtitle.innerText = 'Join the community portal.';
            toggleText.innerHTML = "Already have an account? <a href='#' onclick='toggleForm()'>Login</a>";
        }
    }
</script>

</body>
</html>