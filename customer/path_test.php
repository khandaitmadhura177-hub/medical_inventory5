<?php
// Define the root path for easier access
$root = __DIR__ . '/../PHPMailer-master/src/';

require $root . 'Exception.php';
require $root . 'PHPMailer.php';
require $root . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true); // Argument 'true' enables exceptions
?>