<?php
// Security Improvement: move DB credentials so they are not accidently exposed
// Store this file OUTSIDE your public web root! Suggest /var/www folder
define('DB_HOST', '34.23.202.55');
define('DB_USER', 'webapi');
define('DB_PASS', 'cis4004!webapi');
define('DB_NAME', 'CIS4004');
define('DB_PORT', 3306);
?>