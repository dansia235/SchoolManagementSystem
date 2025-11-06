<?php
/**
 * EduChad - Root Redirect
 *
 * This file redirects all requests to the public directory
 * Allows accessing the application via http://localhost/educhad instead of http://localhost/educhad/public
 */

// Redirect to public directory
header('Location: public/index.php');
exit;
