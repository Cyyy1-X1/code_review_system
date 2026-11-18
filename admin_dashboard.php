<?php
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$feedback = "";

// Handle Approve or Reject Decision (DFD 1.8 & 1.9, Figure 5)
if (isset($_POST['decision_btn'])) {
    $user_id = (int)$_POST['user_id'];
    $decision = $_POST['decision_btn'] === 'Approved' ? 'Approved' : 'Rejected';
    $admin_name = mysqli_real_escape_string($conn, $_SESSION['admin']);

    $update_sql = "UPDATE USER 
                   SET AccountStatus = '$decision', VerifiedBy = '$admin_name', VerifiedDate = NOW() 
                   WHERE UserID = $user_id";

    if (mysqli_query($conn, $update_sql)) {
        $feedback = "Account status for User ID #$user_id updated to <strong>$decision</strong>.";
    }
}

$pending_result = mysqli_query($conn, "SELECT * FROM USER WHERE AccountStatus = 'Pending' ORDER BY UserID ASC");
$pending_count = mysqli_num_rows($pending_result);

$all_users_result = mysqli_query($conn, "SELECT * FROM USER ORDER BY UserID DESC");
$all_users = array();
$approved_count = 0;
$rejected_count = 0;

while ($row = mysqli_fetch_assoc($all_users_result)) {
    $all_users[] = $row;
    if ($row['AccountStatus'] == 'Approved') $approved_count++;
    if ($row['AccountStatus'] == 'Rejected') $rejected_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrator Console | Automated Code Review Framework</title>
    <script>
        (function() {
            const saved = localStorage.getItem("cr_theme") || "light";
            document.documentElement.setAttribute("data-theme", saved);
        })();
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-brand">
        <span class="brand-mark">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
        </span>
        <h1 class="topbar-title">Automated Code Review Framework — Admin Console</h1>
    </div>
    <div class="topbar-right">
        <button type="button" id="themeToggleBtn" class="theme-btn" onclick="toggleTheme()"></button>
        <span class="account-chip">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            Admin: <strong><?php echo htmlspecialchars($_SESSION['admin']); ?></strong>
        </span>
        <a href="logout.php" class="btn btn-sm btn-reject">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            Logout
        </a>
    </div>
</header>

<div class="workspace">
    <!-- Left Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-label">Administration</div>
        <button type="button" id="navAll" class="side-link active" onclick="switchAdminView('all', this)">
            <span class="side-link-left">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                All Modules
            </span>
        </button>
        <button type="button" id="navPending" class="side-link" onclick="switchAdminView('pending', this)">
            <span class="side-link-left">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Pending Requests
            </span>
            <span class="side-count"><?php echo $pending_count; ?></span>
        </button>
        <button type="button" id="navDirectory" class="side-link" onclick="switchAdminView('directory', this)">
            <span class="side-link-left">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Manage Accounts
            </span>
            <span class="side-count"><?php echo count($all_users); ?></span>
        </button>

        <div class="sidebar-card">
            <strong>
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Admin Responsibilities
            </strong>
            Verify developer registration requests and approve or reject system access per DFD Processes 1.5–1.9.
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-area">
        <div style="margin-bottom: 18px;">
            <h2 id="adminViewHeading" style="margin: 0; font-size: 20px; color: var(--text);">Administration Overview</h2>
            <p id="adminViewSubheading" style="margin: 2px 0 0; font-size: 13px; color: var(--muted);">
                Verification metrics, pending registration queue, and user account directory
            </p>
        </div>

        <?php if ($feedback != "") { ?>
            <div class="alert alert-success"><?php echo $feedback; ?></div>
        <?php } ?>

        <!-- Metrics Summary -->
        <div id="adminMetrics" class="metrics-row">
            <div class="metric-box">
                <div class="metric-icon amber">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div>
                    <div class="metric-title">Pending Verifications</div>
                    <div class="metric-num"><?php echo $pending_count; ?></div>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-icon green">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div>
                    <div class="metric-title">Approved Developers</div>
                    <div class="metric-num"><?php echo $approved_count; ?></div>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-icon red">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
                <div>
                    <div class="metric-title">Rejected Accounts</div>
                    <div class="metric-num"><?php echo $rejected_count; ?></div>
                </div>
            </div>
        </div>

        <!-- Section 1: Pending Registration Requests -->
        <section id="secPending" class="panel">
            <div class="panel-head">
                <div>
                    <h2>
                        <svg class="icon" style="color: #d97706;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Pending Registration Requests
                    </h2>
                    <p>Review developer registrations awaiting access approval</p>
                </div>
            </div>

            <?php if ($pending_count == 0) { ?>
                <p style="font-size: 13.5px; color: var(--muted); margin: 0;">No pending registration requests.</p>
            <?php } else { ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($pending_result)) { ?>
                            <tr>
                                <td>#<?php echo $row['UserID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['Username']); ?></strong></td>
                                <td><span class="badge badge-Pending"><?php echo $row['AccountStatus']; ?></span></td>
                                <td>
                                    <form method="POST" style="display:inline-flex; gap:6px;">
                                        <input type="hidden" name="user_id" value="<?php echo $row['UserID']; ?>">
                                        <button type="submit" name="decision_btn" value="Approved" class="btn btn-sm btn-approve">
                                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            Approve
                                        </button>
                                        <button type="submit" name="decision_btn" value="Rejected" class="btn btn-sm btn-reject">
                                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </section>

        <!-- Section 2: Manage All User Accounts -->
        <section id="secDirectory" class="panel">
            <div class="panel-head">
                <div>
                    <h2>
                        <svg class="icon" style="color: var(--navy);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Manage User Accounts
                    </h2>
                    <p>Audit user verification status and reviewer logs</p>
                </div>
                <input type="text" id="adminSearch" onkeyup="filterAdminTable()" placeholder="Filter by username or status..." style="max-width: 250px;">
            </div>

            <div class="table-wrap">
                <table id="adminUsersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Account Status</th>
                            <th>Verified By</th>
                            <th>Verified Date</th>
                            <th>Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($all_users as $u) { ?>
                        <tr>
                            <td>#<?php echo $u['UserID']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['Username']); ?></strong></td>
                            <td><span class="badge badge-<?php echo $u['AccountStatus']; ?>"><?php echo $u['AccountStatus']; ?></span></td>
                            <td><?php echo $u['VerifiedBy'] ? htmlspecialchars($u['VerifiedBy']) : "—"; ?></td>
                            <td><?php echo $u['VerifiedDate'] ? $u['VerifiedDate'] : "—"; ?></td>
                            <td>
                                <form method="POST" style="display:inline-flex; gap:6px;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['UserID']; ?>">
                                    <button type="submit" name="decision_btn" value="Approved" class="btn btn-sm btn-approve">Approve</button>
                                    <button type="submit" name="decision_btn" value="Rejected" class="btn btn-sm btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
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

function switchAdminView(view, btn) {
    document.querySelectorAll(".side-link").forEach(el => el.classList.remove("active"));
    btn.classList.add("active");

    const metrics      = document.getElementById("adminMetrics");
    const secPending   = document.getElementById("secPending");
    const secDirectory = document.getElementById("secDirectory");
    const heading      = document.getElementById("adminViewHeading");
    const subheading   = document.getElementById("adminViewSubheading");

    if (view === "pending") {
        metrics.style.display      = "none";
        secPending.style.display   = "block";
        secDirectory.style.display = "none";
        heading.textContent        = "Pending Registration Requests";
        subheading.textContent     = "Approve or reject newly registered developer accounts";
    } else if (view === "directory") {
        metrics.style.display      = "none";
        secPending.style.display   = "none";
        secDirectory.style.display = "block";
        heading.textContent        = "Manage User Accounts";
        subheading.textContent     = "Complete directory of registered accounts and verification timestamps";
    } else {
        metrics.style.display      = "grid";
        secPending.style.display   = "block";
        secDirectory.style.display = "block";
        heading.textContent        = "Administration Overview";
        subheading.textContent     = "Verification metrics, pending registration queue, and user account directory";
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
}

function filterAdminTable() {
    const q = document.getElementById("adminSearch").value.toLowerCase();
    document.querySelectorAll("#adminUsersTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none";
    });
}
</script>
</body>
</html>