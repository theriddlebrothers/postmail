<?php
/**
 * Postmail
 *
 * Receives any data via POST, formats it and emails the information to 
 * the specified email address(es). Does not store requests or handle any 
 * additional processing other than the emailing of results. It does keep 
 * a cache of data for failed emails.
 */
require("includes/class.phpmailer.php");
require("includes/class.smtp.php");
ini_set('display_errors', 'On');
ini_set('html_errors', 0);
error_reporting(E_ALL);

/***********************************************************
 * Configuration
 ***********************************************************/
$ignored = array();
$data = $_POST;
$subject = "New Form Submission: " . date('Y-m-d h:ia');
$failed_cache = "cache/";

if (!file_exists($failed_cache)) {
	mkdir($failed_cache) or die("Unable to create cache directory " . $failed_cache);
}

if (!is_writable($failed_cache)) {
	die("Cache is not writeable: " . $failed_cache);
}


/***********************************************************
 * Process Data
 ***********************************************************/
$html = "<table cellspacing='0' cellpadding='8' border='1'>";

foreach($data as $field=>$val) {
	if (in_array($field, $ignored)) continue;

	$html .= "<tr><th style='vertical-align:top'>" . $field . "</th><td style='vertical-align:top'>";

	if (is_array($val)) {
		foreach($val as $v) {
			$html .= $v . "<br />";
		}
	} else {
		$html .= $val;
	}

	$html .= "</td></tr>";
}

$html .= "</table>";


$mail = new PHPMailer;

$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.sendgrid.net';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'xxxx';                 // SMTP username
$mail->Password = 'xxxx';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->From = 'noreply@example.com';
$mail->FromName = "Acme, Inc";
$mail->addAddress('josh@example.com');     // Add a recipient
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $subject;
$mail->Body    = $html;

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
   	$f = fopen($failed_cache . date('m-d-Y_his') . ".log", "w") or die("Unable to open file!");
	fwrite($f, $html);
	fclose($f);
}