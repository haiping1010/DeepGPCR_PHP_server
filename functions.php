<?php

include("config.php");
include("opendb.php");

date_default_timezone_set("America/New_York");
$last_updated = strtotime("2019-10-29");
$job_limit = 5;

// Checks to see whether $sequence contains a valid, ungapped 
// FASTA or RAW peptide sequence
// On success, returns the sequence as a string (with 
// whitespace/line breaks removed); otherwise returns false
function sequenceToString($sequence) {
   $patterns = array ('/^>.*\n/', '/\s+/', '/\*$/');
   $sequence = preg_replace($patterns, "", $sequence);
   if (preg_match('/^[ABCDEFGHIKLMNPQRSTVWYZX]+$/i', $sequence))
      return $sequence;
   else
      return false;
}

//Check topology information
function checkTopology($sequenceSS) {
   echo ($sequenceSS);
   $SSarray = explode(' ',$sequenceSS);
   print_r($SSarray);
   //$array = sort($SSarray);
   //print_r($array);
   $SS=implode('',$SSarray);
   echo ($SS);
   //$sequenceSS=implode(' ',$SSarray);
   $nSS = count($SSarray);
   //if ((preg_match('^[0-9]*$',$SS) && $nSS>2 && $nSS%2==0)
   //if ($nSS>2)
   if (preg_match('^[0-9]*$',$SS)){
       print "Huiling";
       return $sequenceSS;
   }
   else
       return false;        
}
// Validate email address
// from Marty Taylor's code for Protein WISDOM (wisdom-fns.php)
function checkEmail($email) {
   if (preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $email))
      return true;
   return false;
}

// Return the number of jobs submitted from $ip_address in
// the last hour
function recentJobs($ip_address) {
	global $conn;
   $query = "SELECT id FROM jobs WHERE ipAddress='$ip_address' AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(timestamp) < 3600);";
   $result = mysqli_query($conn, $query);
   return mysqli_num_rows($result);
}

// Return the Unix timestamp at which $ip_address may submit another job
function nextAllowedJobTime($ip_address) {
   global $job_limit;
   global $conn;
   if (recentJobs($ip_address) < $job_limit)
      return time();
   else {  
      $query = "SELECT MIN(UNIX_TIMESTAMP(timestamp)) FROM jobs WHERE ipAddress='$ip_address' AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(timestamp) < 3600);";
      $result = mysqli_query($conn, $query);
      $row = mysqli_fetch_array($result);
      return $row[0] + 3600;
   }
}

function printFooter() {
   global $last_updated;
   print '<p>Copyright &#169; 2019 High Performance Computing Center, Shenzhen Institutes of Advanced Technology, Chinese Academy of Sciences| <a href="http://english.siat.cas.cn/Research_2015/rd_2015/201508/t20150807_151153.html">siat.cas.cn</a><br />';
   print 'Questions or comments? Contact <a href="mailto:yj.wei@siat.ac.cn">Yanjie Wei</a>  or  Haiping Zhang(hp.zhang@siat.ac.cn)</a>    | Last updated ' . date("j F Y", $last_updated) . '</p>';
}

?>
