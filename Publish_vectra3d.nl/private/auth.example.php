<?php
/**
 * Template for private/auth.php, which is gitignored on purpose — it holds the
 * real credential derivation and must never enter git history.
 *
 * To set up:
 *   1. Copy this file to auth.php (same folder).
 *   2. Run `php rotate-password.php` from a terminal and paste the three lines
 *      it prints over the salt/iter/hash below.
 *   3. Upload auth.php to the server by FTP/SFTP — never by committing it.
 *
 * See README_DEPLOY.md for the full walkthrough.
 */

return [
    'user' => 'Vectra2D',
    'salt' => 'REPLACE_ME',
    'iter' => 310000,
    'hash' => 'REPLACE_ME',

    // Session lifetime in seconds. 8 hours: long enough for a working day,
    // short enough that a forgotten browser on someone else's machine expires.
    'session_seconds' => 28800,

    // Lockout after this many consecutive failures from one IP, for this long.
    'max_attempts' => 8,
    'lockout_seconds' => 900,
];
