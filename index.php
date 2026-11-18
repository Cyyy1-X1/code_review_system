<?php
include 'config.php';

if (isset($_SESSION['admin'])) {
    header("Location: admin_dashboard.php");
    exit();
}
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$error = "";
$success_msg = "";
$status_message = "";
$active_tab = "login"; // "login", "status", or "forgot"

// 1. HANDLE LOGIN (DFD 1.3 & 1.4)
if (isset($_POST['login_btn'])) {
    $active_tab = "login";
    $role = $_POST['role'];
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if ($role == "admin") {
        if ($username == $admin_username && $password == $admin_password) {
            $_SESSION['admin'] = $admin_username;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid Administrator credentials.";
        }
    } else {
        $sql = "SELECT * FROM USER WHERE Username = '$username'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($password, $row['Password'])) {
                if ($row['AccountStatus'] == "Approved") {
                    $_SESSION['user_id'] = $row['UserID'];
                    $_SESSION['username'] = $row['Username'];
                    header("Location: user_dashboard.php");
                    exit();
                } elseif ($row['AccountStatus'] == "Pending") {
                    $error = "Access Blocked: Your account verification status is currently <strong>Pending</strong>. Wait for Administrator approval.";
                } else {
                    $error = "Access Blocked: Your registration request was <strong>Rejected</strong> by the Administrator.";
                }
            } else {
                $error = "Incorrect password entered.";
            }
        } else {
            $error = "Username not found in registered developer accounts.";
        }
    }
}

// 2. HANDLE VIEW ACCOUNT VERIFICATION STATUS (Figure 7 Use Case)
if (isset($_POST['check_status_btn'])) {
    $active_tab = "status";
    $check_user = mysqli_real_escape_string($conn, trim($_POST['check_username']));
    $sql = "SELECT Username, AccountStatus, VerifiedBy, VerifiedDate FROM USER WHERE Username = '$check_user'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $badge = "badge-" . $row['AccountStatus'];
        $status_message = "Account <strong>" . htmlspecialchars($row['Username']) . "</strong> — Status: <span class='badge $badge'>" . $row['AccountStatus'] . "</span>";

        if ($row['VerifiedDate'] != "") {
            $status_message .= "<br><small style='display:inline-block; margin-top:6px;'>Verified by <strong>" . htmlspecialchars($row['VerifiedBy']) . "</strong> on " . $row['VerifiedDate'] . "</small>";
        } else {
            $status_message .= "<br><small style='display:inline-block; margin-top:6px;'>Pending review by the System Administrator.</small>";
        }
    } else {
        $status_message = "No registration record found for username <strong>" . htmlspecialchars($check_user) . "</strong>.";
    }
}

// 3. HANDLE SELF-SERVICE PASSWORD RESET (User Privacy - Only User Can Change Their Password)
if (isset($_POST['reset_pass_btn'])) {
    $active_tab = "forgot";
    $reset_user = mysqli_real_escape_string($conn, trim($_POST['reset_username']));
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($reset_user == "" || $new_pass == "") {
        $error = "Please enter your username and new password.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New password and confirmation password do not match.";
    } else {
        $check = mysqli_query($conn, "SELECT UserID FROM USER WHERE Username = '$reset_user'");
        if (mysqli_num_rows($check) == 1) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE USER SET Password = '$new_hash' WHERE Username = '$reset_user'");
            $success_msg = "Password updated for <strong>" . htmlspecialchars($reset_user) . "</strong>. You may now sign in.";
            $active_tab = "login";
        } else {
            $error = "No user account found with username <strong>" . htmlspecialchars($reset_user) . "</strong>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In | Automated Code Review & Quality Assurance Framework</title>
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
    <!-- Top Theme Switcher -->
    <div class="auth-top-bar">
        <button type="button" id="themeToggleBtn" class="theme-btn" onclick="toggleTheme()"></button>
    </div>

    <div class="auth-container">
        <!-- Left Specification Column -->
        <div class="auth-info">
            <div>
                <span class="sys-tag">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                    PHP STATIC ANALYSIS SYSTEM
                </span>
                <h1>Automated Code Review and Quality Assurance Framework</h1>
                <p>
                    A centralized web platform for reviewing PHP source code using static analysis rules, severity classification, and quality scoring.
                </p>

                <div class="spec-list">
                    <div class="spec-item">
                        <div class="spec-icon blue">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
                        </div>
                        <div>
                            <strong>Account Verification Gatekeeping</strong>
                            <span>Developer registrations require Administrator approval prior to code submission.</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon teal">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </div>
                        <div>
                            <strong>PHPStan &amp; PHP_CodeSniffer Integration</strong>
                            <span>Detects syntax/logic errors (Major) and PSR-12 coding standard violations (Minor).</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-icon amber">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        </div>
                        <div>
                            <strong>0–100 Quality Score &amp; Audit Reports</strong>
                            <span>Computes severity-based deductions and generates downloadable PDF review reports.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-footer-note">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                Pateros Technological College • BS Information Technology
            </div>
        </div>

        <!-- Right Interactive Form Column -->
        <div class="auth-form-side">
            <!-- Top Tab Bar: Only Sign In & Verification Status -->
            <div class="tab-bar">
                <button type="button" id="btnTabLogin" class="tab-btn <?php echo $active_tab == 'login' ? 'active' : ''; ?>" onclick="openAuthTab('login')">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    Sign In
                </button>
                <button type="button" id="btnTabStatus" class="tab-btn <?php echo $active_tab == 'status' ? 'active' : ''; ?>" onclick="openAuthTab('status')">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Verification Status
                </button>
            </div>

            <!-- VIEW 1: SIGN IN -->
            <div id="viewLogin" style="display: <?php echo $active_tab == 'login' ? 'block' : 'none'; ?>;">
                <h2 style="margin: 0 0 4px; font-size: 19px;">Account Sign In</h2>
                <p style="margin: 0 0 16px; font-size: 13px; color: var(--muted);">Choose your system role and enter your credentials.</p>

                <?php if ($error != "" && $active_tab == "login") { ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php } ?>
                <?php if ($success_msg != "") { ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php } ?>

                <form method="POST">
                    <label>Role</label>
                    <div class="role-switch">
                        <label class="role-choice" style="margin:0;">
                            <input type="radio" name="role" value="user" checked>
                            <span class="role-box">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <strong>User / Developer</strong>
                                <small>Code Analysis &amp; Reports</small>
                            </span>
                        </label>
                        <label class="role-choice" style="margin:0;">
                            <input type="radio" name="role" value="admin">
                            <span class="role-box">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <strong>Administrator</strong>
                                <small>Account Verification</small>
                            </span>
                        </label>
                    </div>

                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter your username" required>

                    <div class="label-row">
                        <label>Password</label>
                        <button type="button" class="link-btn" onclick="openAuthTab('forgot')">Forgot password?</button>
                    </div>
                    <div class="pass-field">
                        <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('loginPassword', this)">Show</button>
                    </div>

                    <button type="submit" name="login_btn" class="btn" style="width: 100%;">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        Sign In
                    </button>
                </form>

                <div class="divider-actions">
                    <button type="button" class="btn btn-default" style="width: 100%;" onclick="openAuthTab('status')">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Check Account Verification Status
                    </button>
                    <div style="text-align: center; font-size: 13px; color: var(--muted);">
                        Need a developer account? <a href="register.php" style="color: var(--navy); font-weight: 600;">Register Account</a>
                    </div>
                </div>
            </div>

            <!-- VIEW 2: ACCOUNT VERIFICATION STATUS -->
            <div id="viewStatus" style="display: <?php echo $active_tab == 'status' ? 'block' : 'none'; ?>;">
                <h2 style="margin: 0 0 4px; font-size: 19px;">Account Verification Status</h2>
                <p style="margin: 0 0 16px; font-size: 13px; color: var(--muted);">
                    Check whether your registration request is Pending, Approved, or Rejected.
                </p>

                <?php if ($status_message != "") { ?>
                    <div class="alert alert-info"><?php echo $status_message; ?></div>
                <?php } ?>

                <form method="POST">
                    <label>Registered Username</label>
                    <input type="text" name="check_username" placeholder="Enter registered username" required>

                    <button type="submit" name="check_status_btn" class="btn" style="width: 100%;">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Check Status
                    </button>
                </form>

                <div class="divider-actions">
                    <button type="button" class="btn btn-default" style="width: 100%;" onclick="openAuthTab('login')">
                        &larr; Back to Sign In
                    </button>
                </div>
            </div>

            <!-- VIEW 3: FORGOT / RESET PASSWORD (Opened only via 'Forgot password?' link) -->
            <div id="viewForgot" style="display: <?php echo $active_tab == 'forgot' ? 'block' : 'none'; ?>;">
                <h2 style="margin: 0 0 4px; font-size: 19px;">Reset Password</h2>
                <p style="margin: 0 0 16px; font-size: 13px; color: var(--muted);">
                    Enter your registered username and set a new private password for your account.
                </p>

                <?php if ($error != "" && $active_tab == "forgot") { ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php } ?>

                <form method="POST">
                    <label>Registered Username</label>
                    <input type="text" name="reset_username" placeholder="Enter your username" required>

                    <label>New Password</label>
                    <div class="pass-field">
                        <input type="password" id="newResetPass" name="new_password" placeholder="Enter new password" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('newResetPass', this)">Show</button>
                    </div>

                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter new password" required>

                    <button type="submit" name="reset_pass_btn" class="btn" style="width: 100%;">Update Password</button>
                </form>

                <div class="divider-actions">
                    <button type="button" class="btn btn-default" style="width: 100%;" onclick="openAuthTab('login')">
                        &larr; Back to Sign In
                    </button>
                </div>
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

function openAuthTab(tabName) {
    document.getElementById("viewLogin").style.display  = (tabName === "login")  ? "block" : "none";
    document.getElementById("viewStatus").style.display = (tabName === "status") ? "block" : "none";
    document.getElementById("viewForgot").style.display = (tabName === "forgot") ? "block" : "none";

    document.getElementById("btnTabLogin").classList.toggle("active", tabName === "login");
    document.getElementById("btnTabStatus").classList.toggle("active", tabName === "status");
}

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