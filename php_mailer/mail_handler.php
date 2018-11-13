<?php
require_once('email_config.php');
require('phpmailer/PHPMailer/src/PHPMailer.php');
require('phpmailer/PHPMailer/src/SMTP.php');
       // Enable verbose debug output. Change to 0 to disable debugging output.

// Validate POST inputs
$message = [];
$output = [
    'success' => null,
    'message' => []
];

// Sanitize name field
$message['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
if(empty($message['name'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing name key';
};

$message['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if(empty($message['email'])) {
    $output['success'] = false;
    $output['messages'][] = 'invalid email key';  
};
$message['message'] = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
if(empty($message['message'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing message';
};

if ($output['success'] !== null) {
    http_response_code(422);
    echo json_encode($output);
    exit();
};
// Validate email field


$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->SMTPDebug = 0;    
$mail->isSMTP();                // Set mailer to use SMTP.
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
$mail->SMTPAuth = true;         // Enable SMTP authentication


$mail->Username = EMAIL_USER;   // SMTP username
$mail->Password = EMAIL_PASS;   // SMTP password
$mail->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
$mail->Port = 587;              // TCP port to connect to
$options = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->smtpConnect($options);
$mail->From = EMAIL_USER;  // sender's email address (shows in "From" field)
$mail->FromName = EMAIL_USERNAME;  // sender's name (shows in "From" field)
$mail->addAddress(EMAIL_TO_ADDRESS, EMAIL_USERNAME);  // Add a recipient
$mail->addReplyTo($message['email'], $message['name']);                          // Add a reply-to address
$message['subject'] = substr($message['message'], 0, 78);
$mail->Subject = $message['subject'];

$mail->isHTML(true);                     
$message['message'] = nl2br($message['message']);
$mail->Body      = $message['message'];
$mail->AltBody = htmlentities($message['message']);

// $mail->Subject = 'Here is the subject';
// $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->send()) {
    $output['success'] = false;
    $output['messages'][] = $mail->ErrorInfo;
} else {
    $output['success'] = true;
    $output['messages'] = 'message has been sent';
};
echo json_encode($output);
?>
