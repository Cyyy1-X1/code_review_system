<?php
function analyzePhpCode($php_code) {
    $issues = array();

    $temp_file = __DIR__ . "/temp_scan_" . time() . "_" . rand(100, 999) . ".php";
    file_put_contents($temp_file, $php_code);

    // =========================================================================
    // PART 1: SOURCE CODE VALIDATION & SYNTAX CHECK (Major Issues)
    // =========================================================================
    if (strpos($php_code, "<?php") === false && strpos($php_code, "<?=") === false) {
        $issues[] = array(
            "severity" => "Major",
            "type"     => "Major - Syntax Error (PHPStan): Missing PHP opening tag (<?php).",
            "line"     => 1
        );
    }

    // Use PHP_BINARY so XAMPP always finds php.exe reliably on Windows
    $php_exe = defined('PHP_BINARY') && PHP_BINARY ? escapeshellarg(PHP_BINARY) : "php";
    $lint_output = array();
    $lint_status = 0;
    @exec($php_exe . " -l " . escapeshellarg($temp_file) . " 2>&1", $lint_output, $lint_status);

    if ($lint_status != 0) {
        $error_text = implode(" ", $lint_output);
        if (stripos($error_text, "not recognized") === false && stripos($error_text, "not found") === false) {
            $line_num = 1;
            if (preg_match('/on line (\d+)/', $error_text, $matches)) {
                $line_num = (int)$matches[1];
            }
            $clean_error = trim(preg_replace('/in .*? on line \d+/', '', $error_text));
            $issues[] = array(
                "severity" => "Major",
                "type"     => "Major - Syntax Error (PHPStan): " . $clean_error,
                "line"     => $line_num
            );
        }
    }

    // =========================================================================
    // PART 2: PHPSTAN CHECK - LOGIC ERRORS & BUGS (Major Issues)
    // =========================================================================
    $phpstan_path = __DIR__ . "/vendor/bin/phpstan";

    if (file_exists($phpstan_path)) {
        $stan_out = array();
        @exec(escapeshellarg($phpstan_path) . " analyse --no-progress --error-format=json " . escapeshellarg($temp_file), $stan_out);
        $stan_data = json_decode(implode("\n", $stan_out), true);

        if (isset($stan_data['files'][$temp_file]['messages'])) {
            foreach ($stan_data['files'][$temp_file]['messages'] as $msg) {
                $issues[] = array(
                    "severity" => "Major",
                    "type"     => "Major - Logic/Bug Error (PHPStan): " . $msg['message'],
                    "line"     => (int)$msg['line']
                );
            }
        }
    } else {
        $lines = explode("\n", $php_code);
        $defined_vars = array('$_GET', '$_POST', '$_SESSION', '$_SERVER', '$_REQUEST', '$_FILES', '$_COOKIE', '$this');

        foreach ($lines as $index => $line) {
            $line_number = $index + 1;
            $clean_line = trim($line);

            if ($clean_line == "" || strpos($clean_line, "//") === 0 || strpos($clean_line, "#") === 0) {
                continue;
            }

            // Register function parameters as defined variables
            if (preg_match('/function\s+[a-zA-Z0-9_]+\s*\(([^)]*)\)/', $line, $func_match)) {
                preg_match_all('/\$[a-zA-Z_][a-zA-Z0-9_]*/', $func_match[1], $param_vars);
                foreach ($param_vars[0] as $p_var) {
                    if (!in_array($p_var, $defined_vars)) {
                        $defined_vars[] = $p_var;
                    }
                }
                continue;
            }

            preg_match_all('/\$[a-zA-Z_][a-zA-Z0-9_]*/', $line, $var_matches);
            $vars_on_line = $var_matches[0];

            foreach ($vars_on_line as $var) {
                // Match single '=' assignment only (never '==' or '===')
                if (preg_match('/' . preg_quote($var, '/') . '\s*=(?!=)/', $line)) {
                    if (!in_array($var, $defined_vars)) {
                        $defined_vars[] = $var;
                    }
                } else {
                    if (!in_array($var, $defined_vars)) {
                        $issues[] = array(
                            "severity" => "Major",
                            "type"     => "Major - Logic Error (PHPStan): Variable $var might not be defined.",
                            "line"     => $line_number
                        );
                        $defined_vars[] = $var;
                    }
                }
            }

            // Detect assignment (=) inside if() condition
            if (preg_match('/\bif\s*\(\s*\$[a-zA-Z0-9_]+\s*=(?!=)/', $clean_line)) {
                $issues[] = array(
                    "severity" => "Major",
                    "type"     => "Major - Logic Bug (PHPStan): Assignment (=) used inside if() condition instead of comparison (== or ===).",
                    "line"     => $line_number
                );
            }
        }
    }

    // =========================================================================
    // PART 3: PHP_CODESNIFFER (PHPCS) - CODING STANDARDS (Minor Issues)
    // =========================================================================
    $phpcs_path = __DIR__ . "/vendor/bin/phpcs";

    if (file_exists($phpcs_path)) {
        $phpcs_out = array();
        @exec(escapeshellarg($phpcs_path) . " --standard=PSR12 --report=json " . escapeshellarg($temp_file), $phpcs_out);
        $phpcs_data = json_decode(implode("\n", $phpcs_out), true);

        if (isset($phpcs_data['files'][$temp_file]['messages'])) {
            foreach ($phpcs_data['files'][$temp_file]['messages'] as $msg) {
                $issues[] = array(
                    "severity" => "Minor",
                    "type"     => "Minor - Coding Standard Violation (PHPCS): " . $msg['message'],
                    "line"     => (int)$msg['line']
                );
            }
        }
    } else {
        $lines = explode("\n", $php_code);

        foreach ($lines as $index => $line) {
            $line_number = $index + 1;
            $raw_line = rtrim($line, "\r\n");

            if (strlen($raw_line) > 120) {
                $issues[] = array(
                    "severity" => "Minor",
                    "type"     => "Minor - Coding Standard Violation (PHPCS): Line exceeds 120 characters (PSR-12).",
                    "line"     => $line_number
                );
            }

            if (strpos($raw_line, "\t") !== false) {
                $issues[] = array(
                    "severity" => "Minor",
                    "type"     => "Minor - Coding Standard Violation (PHPCS): Tab indentation found; use 4 spaces (PSR-12).",
                    "line"     => $line_number
                );
            }

            if (strlen($raw_line) > 0 && substr($raw_line, -1) === " ") {
                $issues[] = array(
                    "severity" => "Minor",
                    "type"     => "Minor - Coding Standard Violation (PHPCS): Trailing whitespace at end of line.",
                    "line"     => $line_number
                );
            }
        }
    }

    if (file_exists($temp_file)) {
        unlink($temp_file);
    }

    // =========================================================================
    // PART 4: QUALITY SCORE CALCULATION (0 to 100 Scale)
    // =========================================================================
    $score = 100;
    foreach ($issues as $item) {
        if ($item['severity'] == "Major") {
            $score = $score - 10;
        } else {
            $score = $score - 2;
        }
    }

    if ($score < 0) {
        $score = 0;
    }

    return array(
        "issues" => $issues,
        "score"  => $score
    );
}
?>