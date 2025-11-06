#!/usr/bin/env php
<?php
/**
 * EduChad License Generator
 *
 * This script generates offline license keys for schools using HMAC-SHA256
 * The key is bound to the school name and year for security
 *
 * Usage: php scripts/generate_license.php "School Name" 2025 "SECRET_KEY"
 */

// CLI only
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line');
}

// Parse arguments
$args = $argv;
array_shift($args); // Remove script name

if (count($args) < 3) {
    echo "\n";
    echo "=================================================\n";
    echo "   EduChad License Generator\n";
    echo "=================================================\n\n";
    echo "Usage:\n";
    echo "  php scripts/generate_license.php \"School Name\" YEAR SECRET\n\n";
    echo "Parameters:\n";
    echo "  School Name  : The exact name of the school (in quotes)\n";
    echo "  YEAR         : The license year (e.g., 2025)\n";
    echo "  SECRET       : Your APP_SECRET from .env.php\n\n";
    echo "Example:\n";
    echo "  php scripts/generate_license.php \"Lycée Privé EduChad\" 2026 \"my_secret_key_123\"\n\n";
    exit(1);
}

[$school, $year, $secret] = $args;

// Validate year
if (!is_numeric($year) || $year < 2024 || $year > 2100) {
    echo "Error: Year must be a valid number between 2024 and 2100\n";
    exit(1);
}

// Validate inputs
if (empty($school) || empty($secret)) {
    echo "Error: School name and secret cannot be empty\n";
    exit(1);
}

/**
 * Generate license key
 *
 * Format: base64url(year + '.' + hex(hmac(school|year, secret)))
 */
function generateLicense($school, $year, $secret) {
    $message = $school . '|' . $year;
    $mac = hash_hmac('sha256', $message, $secret, true);
    $key = rtrim(strtr(base64_encode($year . '.' . bin2hex($mac)), '+/', '-_'), '=');
    return $key;
}

/**
 * Calculate expiration date (end of academic year)
 */
function getExpirationDate($year) {
    // Academic year typically ends August 31st
    return $year . '-08-31';
}

// Generate the license
$licenseKey = generateLicense($school, $year, $secret);
$expirationDate = getExpirationDate($year);

// Display results
echo "\n";
echo "=================================================\n";
echo "   License Generated Successfully\n";
echo "=================================================\n\n";
echo "School Name    : " . $school . "\n";
echo "License Year   : " . $year . "\n";
echo "Valid Until    : " . $expirationDate . "\n";
echo "\n";
echo "License Key:\n";
echo "─────────────────────────────────────────────────\n";
echo $licenseKey . "\n";
echo "─────────────────────────────────────────────────\n\n";
echo "Instructions:\n";
echo "1. Copy the license key above\n";
echo "2. Log into EduChad as administrator\n";
echo "3. Go to Settings → License\n";
echo "4. Paste the license key\n";
echo "5. Enter the expiration date: " . $expirationDate . "\n";
echo "6. Click 'Update License'\n\n";
echo "Note: This license is bound to the school name\n";
echo "      '" . $school . "'\n";
echo "      Make sure this matches EXACTLY in the database.\n\n";

// Optional: Save to file
$filename = 'license_' . preg_replace('/[^a-z0-9]+/', '_', strtolower($school)) . '_' . $year . '.txt';
$content = "EduChad License Key\n";
$content .= "===================\n\n";
$content .= "School: " . $school . "\n";
$content .= "Year: " . $year . "\n";
$content .= "Valid Until: " . $expirationDate . "\n\n";
$content .= "License Key:\n";
$content .= $licenseKey . "\n\n";
$content .= "Generated: " . date('Y-m-d H:i:s') . "\n";

if (file_put_contents(__DIR__ . '/' . $filename, $content)) {
    echo "License also saved to: scripts/" . $filename . "\n\n";
}
