<?php
/**
 * Password rotation helper. Run from a terminal, not over the web:
 *
 *     php rotate-password.php
 *
 * It prints the two lines to paste into private/auth.php. The password itself is
 * never written to disk, never logged, and never leaves the machine you run it on.
 *
 * Upload this file only if you need it there; it is not required at runtime and
 * is safest kept off the server entirely.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Command line only.');
}

echo "New password (typed characters are hidden): ";

// Hide the input where the platform allows it.
$hidden = false;
if (DIRECTORY_SEPARATOR !== '\\' && function_exists('shell_exec')) {
    @shell_exec('stty -echo 2>/dev/null');
    $hidden = true;
}
$pass = rtrim((string)fgets(STDIN), "\r\n");
if ($hidden) {
    @shell_exec('stty echo 2>/dev/null');
    echo "\n";
}

if (strlen($pass) < 12) {
    exit("Too short. Use at least 12 characters.\n");
}

$salt = bin2hex(random_bytes(16));
$iter = 310000;
$hash = hash_pbkdf2('sha256', $pass, $salt, $iter, 32);

echo "\nReplace these three lines in private/auth.php:\n\n";
echo "    'salt' => '{$salt}',\n";
echo "    'iter' => {$iter},\n";
echo "    'hash' => '{$hash}',\n\n";
echo "Then delete private/.throttle if it exists, and sign in again.\n";
