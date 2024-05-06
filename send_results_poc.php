<?php

include("functions.php");

require 'PHPMailer-5.2-stable/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->SMTPDebug = 3; 

$job_id = $argv[1];
print "job_id is ".$job_id."\n";

// get job info from database
$query = "SELECT * FROM jobs WHERE id=$job_id;";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_array($result);
$ip_address = $row[1];
$time_date = date("g:i:s A \o\\n F jS, Y", strtotime($row[2]));
$email_to = $row[3];
$job_desc = $row[4];

print "ip_address is ".$ip_address."\n";
print "email_to is ".$email_to."\n";


// construct email
$subject = "DeepBindBC Results: Job $job_id";
$message = "";
/*
$boundary_mixed = "Boundary_" . md5(time());
$boundary_alt = "Boundary_" . md5(time() + 1000);
//$headers ="From: COMSAT Team <COMSAT@hpcc.siat.ac.cn>";
//$headers .= "\nMIME-version: 1.0\n". "Content-type: multipart/mixed; boundary=\"${boundary_mixed}\"";
$message = "This is a multi-part message in MIME format.\n\n";
$message .= "--${boundary_mixed}\n"
   . "Content-type: multipart/alternative; boundary=\"${boundary_alt}\"\n\n";
// text-only message
$message .= "--${boundary_alt}\n"
   . "Content-type: text/plain; charset=\"iso-8859-1\"\n"
   . "Content-transfer-encoding: 7BIT\n\n";
$message .= "Attached are the results of job $job_id, ";
if ($job_desc) {
   $message .= "\"$job_desc,\" ";
}
$message .= "submitted at $time_date.\n\n";
// html message
$message .= "--${boundary_alt}\n"
   . "Content-type: text/html; charset=\"iso-8859-1\"\n"
   . "Content-transfer-encoding: 7BIT\n\n";
   */
$message .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n"
   . "<html>\n"
   . "<head>\n"
   . "</head>\n"
   . "<body bgcolor='#ffffff' style='color: #222222; font-family: Verdana, sans-serif; font-size: 10pt;' >\n";
$message .= "<p>Attached are the results of job $job_id, ";
if ($job_desc) {
   $message .= "\"$job_desc,\" ";
}
$message .= "submitted at $time_date.</p>";
$message .= "</body>\n"
   . "</html>\n\n";

//$message .= "--${boundary_alt}--\n\n";

// attachments
$file_name = $job_id."_poc_result.zip";
$file_path = "/home/jobs/job$job_id/$file_name";
/*
$message .= "--${boundary_mixed}\n"
   . "Content-type: text/plain; name=" . $file_name . "\n"
   . "Content-transfer-encoding: 8BIT\n"
   . "Content-disposition: inline; filename=" . $file_name . "\n\n";
$message .= file_get_contents($file_path) . "\n";
$message .= "--${boundary_mixed}--\n\n";
*/


$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.office365.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'deepbindpoc@outlook.com';                 // SMTP username
$mail->Password = 'haiping8249618@#&';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->setFrom('deepbindpoc@outlook.com', 'deepbindpoc');
$mail->addAddress($email_to);               // Add a recipient
//$mail->addReplyTo('info@example.com', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

$mail->addAttachment($file_path, $file_name);    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = "DeepBindBC Results: Job $job_id";
$mail->Body    = $message;
//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

$res = $mail->send();
if(!$res){
	$errorMessage = $mail->ErrorInfo;
	print "mail failed: $errorMessage\n";
} else {
	print "mail successed.\n";
}

// send email
/*
$res=mail($email_to, $subject, $message, $headers);
if(!$res){
	$errorMessage = error_get_last()['message'];
	print "mail failed: $errorMessage\n";
} else {
	print "mail successed.\n";
}
*/
$message2 = "This email is for recording the IP and Email address for all the users of COMSAT. \n\n";
$message2 .= "IP: $ip_address \n";
$message2 .= "Email: $email_to \n";
$subject2 = "DeepBindBC Job $job_id Information Email: $email_to  from IP: $ip_address ";

?>
