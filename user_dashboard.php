<?php
include 'config.php';
include 'analyzer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$error = "";

// Handle PHP Source Code Submission (DFD 1.10 to 1.13)
if (isset($_POST['submit_code_btn'])) {
    $project_name = mysqli_real_escape_string($conn, trim($_POST['project_name']));
    $raw_php_code = $_POST['php_code'];
    $escaped_php_code = mysqli_real_escape_string($conn, $raw_php_code);

    if ($project_name == "" || trim($raw_php_code) == "") {
        $error = "Please provide both a Project Name and PHP Source Code.";
    } else {
        mysqli_query($conn, "INSERT INTO CodeSubmission (UserID, ProjectName, PhpCode, SubmitDate) VALUES ($user_id, '$project_name', '$escaped_php_code', NOW())");
        $submission_id = mysqli_insert_id($conn);

        $analysis = analyzePhpCode($raw_php_code);
        $detected_issues = $analysis['issues'];
        $overall_score = $analysis['score'];

        foreach ($detected_issues as $issue) {
            $issue_type = mysqli_real_escape_string($conn, $issue['type']);
            $line_num = (int)$issue['line'];
            mysqli_query($conn, "INSERT INTO CodeIssue (SubmissionID, IssueType, LineNumber) VALUES ($submission_id, '$issue_type', $line_num)");
        }

        mysqli_query($conn, "INSERT INTO QualityScore (SubmissionID, OverallScore, AnalyzeDate) VALUES ($submission_id, $overall_score, NOW())");
        $score_id = mysqli_insert_id($conn);

        mysqli_query($conn, "INSERT INTO ReviewReport (SubmissionID, ScoreID, GeneratedDate) VALUES ($submission_id, $score_id, NOW())");
        $report_id = mysqli_insert_id($conn);

        header("Location: review_report.php?report_id=" . $report_id);
        exit();
    }
}

$history_sql = "SELECT cs.SubmissionID, cs.ProjectName, cs.SubmitDate, qs.OverallScore, rr.ReportID,
                       (SELECT COUNT(*) FROM CodeIssue ci WHERE ci.SubmissionID = cs.SubmissionID) AS IssueCount
                FROM CodeSubmission cs
                JOIN QualityScore qs ON cs.SubmissionID = qs.SubmissionID
                JOIN ReviewReport rr ON cs.SubmissionID = rr.SubmissionID
                WHERE cs.UserID = $user_id
                ORDER BY cs.SubmissionID DESC";
$history_result = mysqli_query($conn, $history_sql);

$submissions = array();
$total_score = 0;
while ($r = mysqli_fetch_assoc($history_result)) {
    $submissions[] = $r;
    $total_score += (int)$r['OverallScore'];
}

$total_submissions = count($submissions);
$avg_score = $total_submissions > 0 ? round($total_score / $total_submissions, 1) : 0;
$latest_score = $total_submissions > 0 ? $submissions[0]['OverallScore'] . " / 100" : "—";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Developer Workspace | Automated Code Review Framework</title>
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
        <h1 class="topbar-title">Automated Code Review &amp; Quality Assurance Framework</h1>
    </div>
    <div class="topbar-right">
        <button type="button" id="themeToggleBtn" class="theme-btn" onclick="toggleTheme()"></button>
        <span class="account-chip">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Developer: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </span>
        <a href="logout.php" class="btn btn-sm btn-reject">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            Logout
        </a>
    </div>
</header>

<div class="workspace">
    <!-- Left Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-label">Developer Menu</div>
        <button type="button" id="menuOverview" class="side-link active" onclick="switchDevView('all', this)">
            <span class="side-link-left">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Overview &amp; Workspace
            </span>
        </button>
        <button type="button" id="menuSubmit" class="side-link" onclick="switchDevView('submit', this)">
            <span class="side-link-left">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                Submit PHP Code
            </span>
        </button>
        <button type="button" id="menuHistory" class="side-link" onclick="switchDevView('history', this)">
            <span class="side-link-left">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                Review History
            </span>
            <span class="side-count"><?php echo $total_submissions; ?></span>
        </button>

        <div class="sidebar-card">
            <strong>
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Quality Score Criteria
            </strong>
            Base Score: <strong>100 pts</strong><br>
            • Major (PHPStan): <strong>-10 pts</strong><br>
            • Minor (PHPCS): <strong>-2 pts</strong><br>
            Range: 0 to 100 points.
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-area">
        <div style="margin-bottom: 18px;">
            <h2 id="viewHeading" style="margin: 0; font-size: 20px; color: var(--text);">Overview &amp; Workspace</h2>
            <p id="viewSubheading" style="margin: 2px 0 0; font-size: 13px; color: var(--muted);">
                Summary metrics, PHP source code submission, and evaluation history
            </p>
        </div>

        <!-- Section 0: Metrics Summary -->
        <div id="secMetrics" class="metrics-row">
            <div class="metric-box">
                <div class="metric-icon blue">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div>
                    <div class="metric-title">Submitted Projects</div>
                    <div class="metric-num"><?php echo $total_submissions; ?></div>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-icon green">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </div>
                <div>
                    <div class="metric-title">Average Quality Score</div>
                    <div class="metric-num"><?php echo $total_submissions > 0 ? $avg_score . " / 100" : "—"; ?></div>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-icon teal">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div>
                    <div class="metric-title">Latest Review Score</div>
                    <div class="metric-num"><?php echo $latest_score; ?></div>
                </div>
            </div>
        </div>

        <!-- Section 1: Submit PHP Source Code -->
        <section id="secSubmit" class="panel">
            <div class="panel-head">
                <div>
                    <h2>
                        <svg class="icon" style="color: var(--navy);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        Submit PHP Source Code
                    </h2>
                    <p>Static code analysis using PHPStan and PHP_CodeSniffer (PSR-12)</p>
                </div>
                <button type="button" class="btn btn-sm btn-default" onclick="insertSampleCode()">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    Load Sample PHP Code
                </button>
            </div>

            <?php if ($error != "") { ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <label>Project Name</label>
                <input type="text" id="projName" name="project_name" placeholder="e.g., Registration Controller (register.php)" required>

                <label>PHP Source Code</label>
                <div class="code-bar">
                    <span>Language: PHP</span>
                    <span id="lineCountDisplay">Lines: 1</span>
                </div>
                <textarea id="phpEditor" name="php_code" class="code-area" oninput="updateLines()" placeholder="<?php echo htmlspecialchars("<?php\n// Paste your PHP source code here...\n"); ?>" required></textarea>

                <button type="submit" name="submit_code_btn" class="btn">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Analyze Code &amp; Generate Report
                </button>
            </form>
        </section>

        <!-- Section 2: Evaluation History -->
        <section id="secHistory" class="panel">
            <div class="panel-head">
                <div>
                    <h2>
                        <svg class="icon" style="color: var(--navy);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        Submitted Projects &amp; Analysis Results
                    </h2>
                    <p>Access your generated quality scores and detailed code review reports</p>
                </div>
                <?php if ($total_submissions > 0) { ?>
                    <input type="text" id="devSearch" onkeyup="filterDevHistory()" placeholder="Search project name..." style="max-width: 240px;">
                <?php } ?>
            </div>

            <?php if ($total_submissions == 0) { ?>
                <p style="font-size: 13.5px; color: var(--muted); margin: 0;">No PHP code submissions yet. Submit your first project above.</p>
            <?php } else { ?>
                <div class="table-wrap">
                    <table id="devHistoryTable">
                        <thead>
                            <tr>
                                <th>Submission ID</th>
                                <th>Project Name</th>
                                <th>Submit Date</th>
                                <th>Findings</th>
                                <th>Quality Score</th>
                                <th>Review Report</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($submissions as $row) { ?>
                            <tr>
                                <td>#<?php echo $row['SubmissionID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['ProjectName']); ?></strong></td>
                                <td><?php echo $row['SubmitDate']; ?></td>
                                <td><?php echo (int)$row['IssueCount']; ?> issue(s)</td>
                                <td><strong><?php echo $row['OverallScore']; ?> / 100</strong></td>
                                <td>
                                    <a class="btn btn-sm" href="review_report.php?report_id=<?php echo $row['ReportID']; ?>">
                                        View Report
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
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

function switchDevView(view, btn) {
    document.querySelectorAll(".side-link").forEach(el => el.classList.remove("active"));
    btn.classList.add("active");

    const secMetrics = document.getElementById("secMetrics");
    const secSubmit  = document.getElementById("secSubmit");
    const secHistory = document.getElementById("secHistory");
    const heading    = document.getElementById("viewHeading");
    const subheading = document.getElementById("viewSubheading");

    if (view === "submit") {
        secMetrics.style.display = "none";
        secSubmit.style.display  = "block";
        secHistory.style.display = "none";
        heading.textContent      = "Submit PHP Source Code";
        subheading.textContent   = "Paste or load PHP code to run static analysis and calculate a quality score";
        document.getElementById("projName").focus();
    } else if (view === "history") {
        secMetrics.style.display = "none";
        secSubmit.style.display  = "none";
        secHistory.style.display = "block";
        heading.textContent      = "Review History";
        subheading.textContent   = "All previously analyzed PHP submissions and generated review reports";
    } else {
        secMetrics.style.display = "grid";
        secSubmit.style.display  = "block";
        secHistory.style.display = "block";
        heading.textContent      = "Overview & Workspace";
        subheading.textContent   = "Summary metrics, PHP source code submission, and evaluation history";
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
}

function updateLines() {
    const val = document.getElementById("phpEditor").value;
    const count = val.length === 0 ? 1 : val.split("\n").length;
    document.getElementById("lineCountDisplay").textContent = "Lines: " + count;
}

function insertSampleCode() {
    document.getElementById("projName").value = "User Session Validation Module";
    document.getElementById("phpEditor").value = "<?php\n\t$status = \"Pending\";\n\techo $missingVariable;\n\tif ($status = \"Approved\") {\n\t\techo \"Access Granted\";\n\t}\n";
    updateLines();
}

function filterDevHistory() {
    const q = document.getElementById("devSearch").value.toLowerCase();
    document.querySelectorAll("#devHistoryTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none";
    });
}
</script>
</body>
</html>