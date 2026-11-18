<?php
include 'config.php';

$message = "";
$error = "";

if (isset($_POST['register_btn'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if ($username == "" || $password == "") {
        $error = "Please fill in all registration details.";
    } else {
        $check_sql = "SELECT * FROM USER WHERE Username = '$username'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO USER (Username, Password, AccountStatus) 
                           VALUES ('$username', '$hashed_password', 'Pending')";

            if (mysqli_query($conn, $insert_sql)) {
                $message = "Registration Confirmation: Your account has been saved with a <strong>Pending</strong> status. Please wait for Administrator approval.";
            } else {
                $error = "Error saving registration details.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Account | Automated Code Review & Quality Assurance Framework</title>
    <script>
        (function() {
            const saved = localStorage.getItem("cr_theme") || "light";
            document.documentElement.setAttribute("data-theme", saved);
        })();
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-top-bar">
        <button type="button" id="themeToggleBtn" class="theme-btn" onclick="toggleTheme()"></button>
    </div>

    <div class="auth-container">
        <div class="auth-info">
            <div>
                <span class="sys-tag">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    DEVELOPER REGISTRATION
                </span>
                <h1>Automated Code Review and Quality Assurance Framework</h1>
                <p>
                    Create a developer account to submit PHP web application code for static analysis and quality evaluation.
                </p>

                <div class="spec-list">
                    <div class="spec-item">
                        <div class="spec-icon blue">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </div>
                        <div>
                            <strong>Step 1: Submit Registration</strong>
                            <span>Your account is stored in the database with a Pending status.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon amber">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div>
                            <strong>Step 2: Administrator Verification</strong>
                            <span>Check your verification status on the sign-in page while awaiting approval.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon teal">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong>Step 3: Analyze &amp; Export Reports</strong>
                            <span>Approved users can evaluate PHP files and export review reports to PDF.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-footer-note">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                Pateros Technological College • BS Information Technology
            </div>
        </div>

        <div class="auth-form-side">
            <h2 style="margin: 0 0 4px; font-size: 19px;">Register Account</h2>
            <p style="margin: 0 0 18px; font-size: 13px; color: var(--muted);">
                Enter your desired username and password below.
            </p>

            <?php if ($error != "") { ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($message != "") { ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter a username" required>

                <label>Password</label>
                <div class="pass-field">
                    <input type="password" id="regPassword" name="password" placeholder="Create a password" required>
                    <button type="button" class="pass-toggle" onclick="togglePass('regPassword', this)">Show</button>
                </div>

                <button type="submit" name="register_btn" class="btn" style="width: 100%;">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Submit Registration Details
                </button>
            </form>

            <div class="divider-actions">
                <a href="index.php" class="btn btn-default" style="width: 100%;">
                    Sign In or Check Account Verification Status
                </a>
            </div>
        </div>
    </div>
</div>

<script>
const sunIcon  = '<svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg> Light Mode';
const moonIcon = '<svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg> Dark Mode';

function updateThemeButton() {
    const current = document.documentElement.getAttribute("data-theme") || "light";
    document.getElementById("themeToggleBtn").innerHTML = (current === "dark") ? sunIcon : moonIcon;
}

function toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme") || "light";
    const next = (current === "dark") ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", next);
    localStorage.setItem("cr_theme", next);
    updateThemeButton();
}

updateThemeButton();

function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "Hide";
    } else {
        input.type = "password";
        btn.textContent = "Show";
    }
}
</script>
</body>
</html>