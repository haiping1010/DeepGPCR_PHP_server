<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php
//error_reporting(E_ALL & ~E_NOTICE);
include("functions.php");
$errors = "";
$upload_failed = FALSE;
if (isset($_POST['submitted']) && $_POST["submitted"]) {
   $job_desc = $_POST["description"];
   
   /*-- Begin form validation --*/

   // peptide sequence
   if ($_FILES["protein_file"]["error"] == UPLOAD_ERR_NO_FILE ) {
         $upload_failed = TRUE;
   } else {
      $protein = file_get_contents($_FILES["protein_file"]["tmp_name"]);
   }
   
   if ($_FILES["ligand_file"]["error"] == UPLOAD_ERR_NO_FILE ) {
         $upload_failed = TRUE;
   } else {
      $ligand = file_get_contents($_FILES["ligand_file"]["tmp_name"]);
   }
   
   // email address
   $email = $_POST["email_address"];
   if ($email == "") {
      $errors .= "<li>\"Email address\" is a required field. </li>";
   } elseif (! checkEmail($email)) {
      $errors .= "<li>Please enter a valid email address. ".$email."</li>";
      $email = "";
   }

   /*-- End form validation --*/

   // if there are errors, create an error message to print (below);
   // otherwise create and execute the job
   if ($errors) {
      $errors = '<ul id="error-list">' . $errors . "</ul>";
   } elseif (! $upload_failed) {
      $ip_address = $_SERVER["REMOTE_ADDR"];
      $num_jobs = recentJobs($ip_address);
      if ($num_jobs < $job_limit) {
         // insert job into database
         $date = date ('Y-m-d H:i:s');
         $fields_list = "id, ipAddress, timestamp, emailAddress, description";
		 $job_desc = str_replace("'","\'",$job_desc);
		 $job_desc = str_replace("\"","\'",$job_desc);
         $query = "INSERT INTO jobs ($fields_list) VALUES (NULL, '$ip_address', '$date', '$email', '$job_desc');";
		 //echo $query;
		 //exit();
         $result = mysqli_query($conn, $query);
         
         // get job ID
         $query = "SELECT LAST_INSERT_ID();";
         $result = mysqli_query($conn, $query);
         $result = mysqli_fetch_array($result);
         $job_id = $result[0];
         
 
         // create new job directory
         $root_dir = getcwd();
         $job_dir = "/home/jobs/job{$job_id}";
         mkdir($job_dir);
         chmod($job_dir, 0777);
         //$cmd = "chmod -R 777 $job_dir";
         //exec($cmd);
         // make new job directory the working directory
         chdir($job_dir);

         // create protein and ligand file
		 
	
		$ret = move_uploaded_file($_FILES["protein_file"]["tmp_name"], "$job_dir/$job_id"."_protein.pdb");
		if(!$ret) {
			//$lastError = error_get_last();
			//$errorString = $lastError ? "Error: ".$lastError["message"]." on line ".$lastError["line"] : "";
			$errors .= "<li>Failed to save the uploaded file " . $_FILES["protein_file"]["tmp_name"]. "</li>";
		}
		$ret = move_uploaded_file($_FILES["ligand_file"]["tmp_name"], "$job_dir/$job_id"."_ligand.mol2");
		if(!$ret) {
			$errors .= "<li>Failed to save the uploaded file " . $_FILES["ligand_file"]["tmp_name"]. "</li>";
		}
		/*
         $cmd = "echo $protein >$job_id"."_protein.pdb";
		 exec($cmd);
		 $cmd = "echo $ligand >$job_id"."_ligand.pdb";
         exec($cmd);
         */

         $result_file = $job_id."_poc_result.zip";  //Huiling2017Mar18
         $PBS_script = "$job_id.cmd";
         $fh = fopen($PBS_script, "w+");
         $contents = "";
         //$contents .= "PBS -l nodes=1:ppn=4\n";    //HuilingHuiling2017Mar18
         //$contents .= "PBS -l walltime=07:00:00\n";//HuilingHuiling2017Mar18
         //$contents .= "PBS -W umask=0007\n";
         //$contents .= "cd \$PBS_O_WORKDIR\n";
		 $contents .= "cd $job_dir\n";
         $contents .= "{$root_dir}/test_extract.sh $job_id $job_dir \n";
         $contents .= "if [  -f \"$result_file\" ];then\n";//Huiling2017Mar18
         $contents .= "  php {$root_dir}/send_results_poc.php $job_id >send_results.log\n";
         $contents .= "fi\n";  //Huiling2017Mar18
         echo $PBS_script;
         fputs($fh, $contents);
         fclose($fh);
         $file_IPandEmail = "$job_id.ipEmail";
         $fh2 = fopen($file_IPandEmail, "w+");
         $ipemail = "";
         $ipemail = " IP: $ip_address \n";
         $ipemail .= " Email: $email \n";
         fputs($fh2, $ipemail);
         fclose($fh2);
         
         // run script
         //exec("/usr/bin/qsub $PBS_script");
		 exec("sh $PBS_script > run_sh_script.log &");

		 if ($errors) {
			$errors = '<ul id="error-list">' . $errors . "</ul>";
		 } else {
			 //redirect
			 header("HTTP/1.1 303 See Other");
			 header("Location: http://210.75.252.40/DeepBindBC/upload-status.php?job=$job_id");
		 }
      }
   }
}


if (isset($_POST['submitted1']) && $_POST["submitted1"]) {
   $job_desc1 = $_POST["description1"];
   
   /*-- Begin form validation --*/

   // peptide sequence
   if ($_FILES["zip_file1"]["error"] == UPLOAD_ERR_NO_FILE ) {
         $upload_failed = TRUE;
   } else {
      $zip_file1 = file_get_contents($_FILES["zip_file1"]["tmp_name"]);
   }
   
   
   // email address
   $email1 = $_POST["email_address1"];
   if ($email1 == "") {
      $errors .= "<li>\"Email address\" is a required field. </li>";
   } elseif (! checkEmail($email1)) {
      $errors .= "<li>Please enter a valid email address. ".$email1."</li>";
      $email1 = "";
   }

   /*-- End form validation --*/
   // if there are errors, create an error message to print (below);
   // otherwise create and execute the job
   if ($errors) {
      $errors = '<ul id="error-list">' . $errors . "</ul>";
   } elseif (! $upload_failed) {
      $ip_address = $_SERVER["REMOTE_ADDR"];
      $num_jobs = recentJobs($ip_address);
      if ($num_jobs < $job_limit) {
         // insert job into database
         $date = date ('Y-m-d H:i:s');
         $fields_list = "id, ipAddress, timestamp, emailAddress, description";
                 $job_desc1 = str_replace("'","\'",$job_desc1);
                 $job_desc1 = str_replace("\"","\'",$job_desc1);
         $query = "INSERT INTO jobs ($fields_list) VALUES (NULL, '$ip_address', '$date', '$email1', '$job_desc1');";
                 //echo $query;
                 //exit();
         $result = mysqli_query($conn, $query);
         
         // get job ID
         $query = "SELECT LAST_INSERT_ID();";
         $result = mysqli_query($conn, $query);
         $result = mysqli_fetch_array($result);
         $job_id = $result[0];
         
 
         // create new job directory
         $root_dir = getcwd();
         $job_dir = "/home/jobs/job{$job_id}";
         mkdir($job_dir);
         chmod($job_dir, 0777);
         //$cmd = "chmod -R 777 $job_dir";
         //exec($cmd);
         // make new job directory the working directory
         chdir($job_dir);

         // create protein and ligand file
                 
        
                $ret = move_uploaded_file($_FILES["zip_file1"]["tmp_name"], "$job_dir/$job_id".".zip");
                if(!$ret) {
                        //$lastError = error_get_last();
                        //$errorString = $lastError ? "Error: ".$lastError["message"]." on line ".$lastError["line"] : "";
                        $errors .= "<li>Failed to save the uploaded file " . $_FILES["zip_file1"]["tmp_name"]. "</li>";
                }
         $result_file = $job_id."_result.zip";  //Huiling2017Mar18
         $PBS_script = "$job_id.cmd";
         $fh = fopen($PBS_script, "w+");
         $contents = "";
                 $contents .= "cd $job_dir\n";
         $contents .= "{$root_dir}/test_xx.sh $job_id $job_dir \n";
         $contents .= "if [  -f \"$result_file\" ];then\n";//Huiling2017Mar18
         $contents .= "  php {$root_dir}/send_results.php $job_id >send_results.log\n";
         $contents .= "fi\n";  //Huiling2017Mar18
         echo $PBS_script;
         fputs($fh, $contents);
         fclose($fh);
         $file_IPandEmail = "$job_id.ipEmail";
         $fh2 = fopen($file_IPandEmail, "w+");
         $ipemail = "";
         $ipemail = " IP: $ip_address \n";
         $ipemail .= " Email: $email \n";
         fputs($fh2, $ipemail);
         fclose($fh2);
         // run script
         //exec("/usr/bin/qsub $PBS_script");
                 exec("sh $PBS_script > run_sh_script.log &");

                 if ($errors) {
                        $errors = '<ul id="error-list">' . $errors . "</ul>";
                 } else {
                         //redirect
                         header("HTTP/1.1 303 See Other");
                         header("Location: http://210.75.252.40/DeepBindBC/upload-status.php?job=$job_id");
                 }
      }
   }
}


if (isset($_POST['submitted2']) && $_POST["submitted2"]) {
   $job_desc1 = $_POST["description2"];

   /*-- Begin form validation --*/

   // peptide sequence
   if ($_FILES["zip_file2"]["error"] == UPLOAD_ERR_NO_FILE ) {
         $upload_failed = TRUE;
   } else {
      $zip_file1 = file_get_contents($_FILES["zip_file2"]["tmp_name"]);
   }


   // email address
   $email1 = $_POST["email_address2"];
   if ($email1 == "") {
      $errors .= "<li>\"Email address\" is a required field. </li>";
   } elseif (! checkEmail($email1)) {
      $errors .= "<li>Please enter a valid email address. ".$email1."</li>";
      $email1 = "";
   }
   /*-- End form validation --*/
   // if there are errors, create an error message to print (below);
   // otherwise create and execute the job
   if ($errors) {
      $errors = '<ul id="error-list">' . $errors . "</ul>";
   } elseif (! $upload_failed) {
      $ip_address = $_SERVER["REMOTE_ADDR"];
      $num_jobs = recentJobs($ip_address);
      if ($num_jobs < $job_limit) {
         // insert job into database
         $date = date ('Y-m-d H:i:s');
         $fields_list = "id, ipAddress, timestamp, emailAddress, description";
                 $job_desc1 = str_replace("'","\'",$job_desc1);
                 $job_desc1 = str_replace("\"","\'",$job_desc1);
         $query = "INSERT INTO jobs ($fields_list) VALUES (NULL, '$ip_address', '$date', '$email1', '$job_desc1');";
                 //echo $query;
                 //exit();
         $result = mysqli_query($conn, $query);

         // get job ID
         $query = "SELECT LAST_INSERT_ID();";
         $result = mysqli_query($conn, $query);
         $result = mysqli_fetch_array($result);
         $job_id = $result[0];


         // create new job directory
         $root_dir = getcwd();
         $job_dir = "/home/jobs/job{$job_id}";
         mkdir($job_dir);
         chmod($job_dir, 0777);
         //$cmd = "chmod -R 777 $job_dir";
         //exec($cmd);
         // make new job directory the working directory
         chdir($job_dir);
         // create protein and ligand file


                $ret = move_uploaded_file($_FILES["zip_file2"]["tmp_name"], "$job_dir/$job_id".".zip");
                if(!$ret) {
                        //$lastError = error_get_last();
                        //$errorString = $lastError ? "Error: ".$lastError["message"]." on line ".$lastError["line"] : "";
                        $errors .= "<li>Failed to save the uploaded file " . $_FILES["zip_file1"]["tmp_name"]. "</li>";
                }
         $result_file = $job_id."_result.zip";  //Huiling2017Mar18
         $PBS_script = "$job_id.cmd";
         $fh = fopen($PBS_script, "w+");
         $contents = "";
                 $contents .= "cd $job_dir\n";
         $contents .= "{$root_dir}/test_xx_repr.sh $job_id $job_dir \n";
         $contents .= "if [  -f \"$result_file\" ];then\n";//Huiling2017Mar18
         $contents .= "  php {$root_dir}/send_results.php $job_id >send_results.log\n";
         $contents .= "fi\n";  //Huiling2017Mar18
         echo $PBS_script;
         fputs($fh, $contents);
         fclose($fh);
         $file_IPandEmail = "$job_id.ipEmail";
         $fh2 = fopen($file_IPandEmail, "w+");
         $ipemail = "";
         $ipemail = " IP: $ip_address \n";
         $ipemail .= " Email: $email \n";
         fputs($fh2, $ipemail);
         fclose($fh2);
         // run script
         //exec("/usr/bin/qsub $PBS_script");
                 exec("sh $PBS_script > run_sh_script.log &");

                 if ($errors) {
                        $errors = '<ul id="error-list">' . $errors . "</ul>";
                 } else {
                         //redirect
                         header("HTTP/1.1 303 See Other");
                         header("Location: http://210.75.252.40/DeepBindBC/upload-status.php?job=$job_id");
                 }
      }
   }
}




















?>

<html>

<head>

<title>DeepBindBC: IDENTIFYING NATIVE LIKE PROTEIN-LIGAND COMPLEXES BY DEEP LEARNING OF INTERFACE CONTACT INFORMATION.</title>

<link rel="stylesheet" type="text/css" href="styles/ui-lightness/jquery-ui-1.8.4.custom.css" />
<link rel="stylesheet" type="text/css" href="styles/style.css" />
<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
<link rel="SHORTCUT ICON" href="favicon.ico" />

<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>


<script type="text/javascript">
function initializeForm() {
   var dialog = $('#error-dialog');
   dialog.dialog({ autoOpen: false, modal: true });
   <?php if ($errors || $upload_failed): ?>
   var form = document.getElementById('query_form');
   form.description.value = '<?php echo $job_desc; ?>';
   form.email_address.value = '<?php echo $email; ?>';
   form.protein_file.value = '<?php echo $protein; ?>';
   form.ligand_file.value = '<?php echo $ligand; ?>';
   
   var form1 = document.getElementById('query_form1');
   form1.description1.value = '<?php echo $job_desc1; ?>';
   form1.email_address1.value = '<?php echo $email1; ?>';
   form1.zip_file1.value = '<?php echo $zip_file1; ?>';

   var form2 = document.getElementById('query_form2');
   form2.description2.value = '<?php echo $job_desc2; ?>';
   form2.email_address2.value = '<?php echo $email2; ?>';
   form2.zip_file2.value = '<?php echo $zip_file2; ?>';


   <?php if ($upload_failed): ?>
   dialog.html('<p>Upload failed. If the problem persists, please <a href="mailto:jeff@titan.princeton.edu">contact us</a>.</p>');
   dialog.dialog('option', 'title', 'Upload Failed');
   <?php else: ?>
   dialog.html('<?php echo $errors; ?>');
   dialog.dialog('option', 'title', 'Invalid Input');
   <?php endif; ?>
   dialog.dialog('open');
   return;
   dialog.html('<?php printMessage(); ?>');
   dialog.dialog('option', 'title', 'Upload Failed');
   dialog.dialog('open');
   <?php endif; ?>
}
</script>   



</head>

<body onload="initializeForm();">

<div id="wrapper">

 <img src="images/DeepBindBC_5s_small.gif"/> 
<!-- <img src="images/dummy.png"/> -->
<!-- <img src="images/banner.v7.jpg"/>  -->

<div id="content">

<p> <b>DeepBindBC</b> is a deep learning based binary classifier for identifying native-like protein-ligand complexes <b></b>


<span class="error" style="background-color:silver;color: red"><?php echo $errors;?></span>
<h2>Extract pocket by known ligand(optional)</h2>
<span class="small">(Needed when user is not sure how to prepare protein pocket information)</span>
<form id="query_form" action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="submitted" id="submitted" value="1" />

<fieldset name="fs">
<!--<legend>Job submission form</legend>-->

<p><label for="protein_file">Protein PDB file</label>
<input type="file" name="protein_file" id="protein_file" /><br />
<label for="ligand_file"> Ligand file
<br /><span class="small">(PDB  format)</span></label>
<input type="file" name="ligand_file" id="ligand_file" /><br />
<a href="/DeepBindBC/input_for_pocket.zip" target="_blank">Input example</a><br />


<p><label for="email_address">Email address</label>
<input type="text" name="email_address" id="email_address" maxlength="100" /></p>

<p><label for="description">Brief description
   <br /><span class="small">(Optional, 100 characters max)</span></label>
<input type="text" name="description" id="description" maxlength="100" />
   </p>

<p style="clear: both"><input id="query_submit" type="submit" name="submit" value="Submit" /></p>

<a href="/DeepBindBC/extract_pocket.py" target="_blank">python script to extract pocket by known ligand in correct binding position</a><br />
<a href="/DeepBindBC/extract_pocket_cord.py" target="_blank">python script to extract pocket by given pocket position</a><br />


</fieldset>
</form>


<span class="error" style="background-color:silver;color: red"><?php echo $errors;?></span>
<h2>Submit a job </h2>
<span class="small">(One receptor against multiple ligand with known pocket, the protein name should be ????.pdb,the pocket name should be ????_pocket.pdb.)</span>

<form id="query_form1" action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="submitted1" id="submitted1" value="1" />

<fieldset name="fs">
<!--<legend>Job submission form</legend>-->

<p><label for="zip_file1">Proteins and corresponding ligands zip file</label>
<input type="file" name="zip_file1" id="zip_file1" /><br />
<a href="/var/www/html/DeepBindBC/yyy.zip" download>
<a href="/DeepBindBC/protein_ligands.zip" target="_blank">Input example</a>

<p><label for="email_address1">Email address</label>
<input type="text" name="email_address1" id="email_address1" maxlength="150" /></p>

<p><label for="description1">Brief description
   <br /><span class="small">(Optional, 100 characters max)</span></label>
<input type="text" name="description1" id="description1" maxlength="100" />
   </p>

<p style="clear: both"><input id="query_submit" type="submit" name="submit" value="Submit" /></p>
</fieldset>
</form>

<span class="small">(The output summary file is summary_all.txt, column 1 is ligand name with its conformation id, column 2 is DeepBindBC's predicted possiblity value, column 3 is vina docking score)</span>




<span class="error" style="background-color:silver;color: red"><?php echo $errors;?></span>
<h2>Submit a job</h2>
<span class="small"> Fast finding out the type of ligands that prefer to binding with the given protein with known pocket (One receptor against 100 representative ligands from cluster of a FDA approved drug dataset, the protein name should be ????.pdb,the pocket name should be ????_pocket.pdb, and they should put in a folder named receptor and zipped, exactly as the following example)</span>

<form id="query_form1" action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="submitted2" id="submitted2" value="1" />

<fieldset name="fs">
<!--<legend>Job submission form</legend>-->

<p><label for="zip_file2">Proteins and pocket zip file</label>
<input type="file" name="zip_file2" id="zip_file2" /><br />
<a href="/var/www/html/DeepBindBC/protein.zip" download>
<p style="text-align: center"><a href="/DeepBindBC/protein.zip" target="_blank">Input example</a> </p>

<p><label for="email_address2">Email address</label>
<input type="text" name="email_address2" id="email_address2" maxlength="150" /></p>

<p><label for="description2">Brief description
   <br /><span class="small">(Optional, 100 characters max)</span></label>
<input type="text" name="description2" id="description2" maxlength="100" />
   </p>

<p style="clear: both"><input id="query_submit" type="submit" name="submit" value="Submit" /></p>
</fieldset>
</form>

<span class="small">(The output summary file is summary_all.txt, column 1 is ligand name with its conformation id, column 2 is DeepBindBC's predicted possiblity value, column 3 is vina docking score)</span>


<!-- added Feb 16, 2011  -->


<!--<p><b>Note:</b> You may submit up to <?php echo $job_limit; ?> jobs per hour. </p> -->

<h2>References</h2>


<ol> DeepGPCR (submitted) </ol>

<ol>

</ol> 

<div id="error-dialog" style="display: none"></div>

</div>

<div id="footer" style="position: relative;">
   <?php printFooter(); ?>
</div> 
     
</div>

</body>
</html>
