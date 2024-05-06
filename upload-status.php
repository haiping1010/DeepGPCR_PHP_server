<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
session_start();
include("functions.php");
$ip_address = $_SERVER["REMOTE_ADDR"];
$job_id = $_GET["job"];
//$job_dir = $_GET["jobdir"];
//$SH_script = $_GET["shscript"];
global $conn;
$query = "SELECT emailAddress FROM jobs WHERE id=$job_id;";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_array($result);
$email = $row[0];
?>

<html>
<head>
<title>DeepBindBC - Upload Status</title>
<link rel="stylesheet" type="text/css" href="styles/style.css" />
   <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
   <link rel="SHORTCUT ICON" href="favicon.ico" />
</head>

<body>

<div id="wrapper">

<div id="content">

<BR>

<h2>DeepBindBC - Identify native like protein-ligand binding</h2>

<BR>
<BR>

<h2>Upload status</h2>

<p>
Upload successful! Your job ID is <?php echo $job_id; ?>. Results will be
sent to <?php echo $email; ?>. If you do not recieve the result within 24 hours, please email yj.wei@siat.ac.cn with your job ID information.
</p>


<div style="text-align: center;">
<!--<p><a href="index.php">Submit another job</a></p>-->
<p><a href="/DeepBindBC/">Return home</a></p>
</div>

</div>

<div id="footer" style="position: relative;">
   <?php printFooter(); ?>
</div> 

</div>

</body>
</html>

//<?php
// run script
//exec("chmod -R 777 $job_dir");
//exec("sh $job_dir/$SH_script 2>$job_dir/stderr.txt");
//pclose(popen("sh $job_dir/$SH_script 2>$job_dir/stderr.txt &", 'r'));
//?>
