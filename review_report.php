<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$report_id = isset($_GET['report_id']) ? (int)$_GET['report_id'] : 0;

$sql = "SELECT rr.ReportID, rr.GeneratedDate,
               cs.SubmissionID, cs.ProjectName, cs.PhpCode, cs.SubmitDate,
               qs.OverallScore
        FROM ReviewReport rr
        JOIN CodeSubmission cs ON rr.SubmissionID = cs.SubmissionID
        JOIN QualityScore qs ON rr.ScoreID = qs.ScoreID
        WHERE rr.ReportID = $report_id AND cs.UserID = $user_id";

$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    die("Review report not found.");
}
$report = mysqli_fetch_assoc($result);

$submission_id = (int)$report['SubmissionID'];
$issues_sql = "SELECT * FROM CodeIssue WHERE SubmissionID = $submission_id ORDER BY LineNumber ASC, IssueID ASC";
$issues_result = mysqli_query($conn, $issues_sql);

$issues_list = array();
$major_count = 0;
$minor_count = 0;

while ($iss = mysqli_fetch_assoc($issues_result)) {
    $issues_list[] = $iss;
    if (strpos($iss['IssueType'], "Major") === 0) {
        $major_count++;
    } else {
        $minor_count++;
    }
}

$score = (int)$report['OverallScore'];
$score_class = $score >= 85 ? "score-high" : ($score >= 60 ? "score-mid" : "score-low");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Report #<?php echo $report['ReportID']; ?></title>
    <script>
        (function() {
            const saved = localStorage.getItem("cr_theme") || "light";
            document.documentElement.setAttribute("data-theme", saved);
        })();
    </script>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

<header class="topbar no-print">
    <div class="topbar-brand">
        <span class="brand-mark">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
        </span>
        <h1 class="topbar-title">Generated Code Review Report</h1>
    </div>
    <div class="topbar-right">
        <button type="button" id="themeToggleBtn" class="theme-btn" onclick="toggleTheme()"></button>
        <a href="user_dashboard.php" class="btn btn-sm btn-default">&larr; Back to Workspace</a>
        <a href="logout.php" class="btn btn-sm btn-reject">Logout</a>
    </div>
</header>

<div class="container-narrow">
    <div class="no-print" style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 14px;">
        <button type="button" id="pdfBtn" class="btn btn-sm" onclick="saveAsPDF()">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Download as PDF
        </button>
        <button type="button" class="btn btn-sm btn-default" onclick="window.print()">
            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <div class="panel" id="printableReport">
        <div class="panel-head">
            <div>
                <h2>
                    <svg class="icon" style="color: var(--navy);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    Code Quality Review Report #<?php echo $report['ReportID']; ?>
                </h2>
                <p>Automated Code Review and Quality Assurance Framework for Web Applications</p>
            </div>
            <span class="badge badge-Approved">
                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Completed
            </span>
        </div>

        <div class="report-summary">
            <div style="font-size: 13.5px;">
                <p style="margin: 3px 0;"><strong>Project Name:</strong> <?php echo htmlspecialchars($report['ProjectName']); ?></p>
                <p style="margin: 3px 0;"><strong>Developer:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p style="margin: 3px 0;"><strong>Submit Date:</strong> <?php echo $report['SubmitDate']; ?></p>
                <p style="margin: 3px 0;"><strong>Generated Date:</strong> <?php echo $report['GeneratedDate']; ?></p>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="text-align: right; font-size: 12.5px; color: var(--muted);">
                    <div><strong>Major Issues (PHPStan):</strong> <?php echo $major_count; ?> (-10 pts each)</div>
                    <div><strong>Minor Issues (PHPCS):</strong> <?php echo $minor_count; ?> (-2 pts each)</div>
                    <div>Base Score: 100 pts</div>
                </div>
                <div class="score-badge-box <?php echo $score_class; ?>">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Quality Score</div>
                    <div style="font-size: 28px; font-weight: 800; line-height: 1.1;"><?php echo $score; ?> / 100</div>
                </div>
            </div>
        </div>

        <div class="panel-head">
            <div>
                <h2 style="font-size: 15.5px;">Detected Findings (PHPStan &amp; PHP_CodeSniffer)</h2>
                <p>Classified issues mapped by source line number</p>
            </div>
            <?php if (count($issues_list) > 0) { ?>
                <div class="filter-bar no-print">
                    <button type="button" class="pill-btn active" onclick="filterIssues('all', this)">All (<?php echo count($issues_list); ?>)</button>
                    <button type="button" class="pill-btn" onclick="filterIssues('Major', this)">Major (<?php echo $major_count; ?>)</button>
                    <button type="button" class="pill-btn" onclick="filterIssues('Minor', this)">Minor (<?php echo $minor_count; ?>)</button>
                </div>
            <?php } ?>
        </div>

        <?php if (count($issues_list) == 0) { ?>
            <div class="alert alert-success">No predefined coding issues or PSR-12 standard violations detected.</div>
        <?php } else { ?>
            <div class="table-wrap">
                <table id="reportIssuesTable">
                    <thead>
                        <tr>
                            <th>Issue ID</th>
                            <th>Classification</th>
                            <th>Line</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($issues_list as $item) {
                        $sev = strpos($item['IssueType'], "Major") === 0 ? "Major" : "Minor";
                    ?>
                        <tr data-sev="<?php echo $sev; ?>">
                            <td>#<?php echo $item['IssueID']; ?></td>
                            <td><span class="badge badge-<?php echo $sev; ?>"><?php echo $sev; ?></span></td>
                            <td><strong>Line <?php echo $item['LineNumber']; ?></strong></td>
                            <td><?php echo htmlspecialchars($item['IssueType']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <h3 style="margin: 22px 0 10px; font-size: 15.5px;">Submitted PHP Source Code</h3>
        <pre class="code-viewer"><?php
            $code_lines = explode("\n", $report['PhpCode']);
            foreach ($code_lines as $num => $text) {
                echo "<span class='ln'>" . ($num + 1) . " |</span>" . htmlspecialchars($text) . "\n";
            }
        ?></pre>
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

function filterIssues(type, btn) {
    document.querySelectorAll(".pill-btn").forEach(p => p.classList.remove("active"));
    btn.classList.add("active");

    document.querySelectorAll("#reportIssuesTable tbody tr").forEach(row => {
        row.style.display = (type === "all" || row.getAttribute("data-sev") === type) ? "" : "none";
    });
}

function saveAsPDF() {
    const element = document.getElementById("printableReport");
    const btn = document.getElementById("pdfBtn");

    if (typeof html2pdf === "undefined") {
        window.print();
        return;
    }

    // Temporarily render in Light Mode so the exported PDF has a clean white background
    const activeTheme = document.documentElement.getAttribute("data-theme") || "light";
    document.documentElement.setAttribute("data-theme", "light");

    btn.textContent = "Exporting PDF...";
    btn.disabled = true;

    html2pdf().set({
        margin: 0.4,
        filename: "Code_Review_Report_<?php echo $report['ReportID']; ?>.pdf",
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: "in", format: "letter", orientation: "portrait" }
    }).from(element).save().then(() => {
        document.documentElement.setAttribute("data-theme", activeTheme);
        btn.innerHTML = '<svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Download as PDF';
        btn.disabled = false;
    });
}
</script>
</body>
</html>