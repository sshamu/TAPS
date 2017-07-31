<?php

//Author:Shamu

session_start();
ob_start();
include "config.php";
include("const_link.php");
include_once('model/tapsprocesshandler.php');
$transaction_obj = new TapsProcessHandler();
//  To find the client ID 
$clientid = $_REQUEST['clientid'];
$metaid    = $_REQUEST['mid'];
$emp_id = $_SESSION['emp_id'];
$user_id = $_SESSION['user_id'];


if($_REQUEST['type']=='fileStatCheck'){
try{
sleep(3);
		$iteration_query = "SELECT meta_status from taps_metadata where article_name=:meta_id"; 
		$meta_query_fetch = $dbh->prepare($iteration_query);
		$meta_query_fetch->bindParam(":meta_id",$_REQUEST['metaid']);
		$meta_query_fetch->execute();
		$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);
		$meta_status = $meta_val['meta_status'];
		
		if($meta_status=="2"){
		echo "File Transfer success";
		}
		if($meta_status=="10"){
		echo "File Transfer failure";
		}else{
		echo "";
		}
}catch(Exception $e){
		echo $e;
}
}

if($clientid == 1 ) //elsevier
{
if($_REQUEST['type'] == 'word'){

$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.process_status = 1 AND tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1 and tpt.process_id=5"; 
									
									
									
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
					
					
					if($status_ar['process_id'] == "")
					{
					
					
					$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.process_status = 1 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id']; 
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);				
					$is_fileopen = 1;
								$insert = "update taps_process_transaction set is_fileopen=:is_fileopen where trans_id=:trans_id";
									$stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
									$stmt->bindParam(':is_fileopen', $is_fileopen);
									$stmt->bindParam(':trans_id', $status_ar['trans_id']);
									$stmt->execute();
            $process_time   = date('Y-m-d H:i:s');
            $insert = "delete from taps_subprocess_transaction where trans_id=:trans_id and process_endtime is null and process_id=5 and sub_process_name='Copy Editing'";
            $stmt = $dbh->prepare($insert);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id and process_endtime is null and process_id=5";
            $stmt = $dbh->prepare($insert);
                                    // Bind parameters to statement variables
            $stmt->bindParam(':process_endtime', $process_time);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where assign_id=:assign_id and process_id=5 order by trans_id desc";
            $stmt = $dbh->prepare($sub_proc_get);
             $stmt->bindParam(':assign_id', $_REQUEST['assign_id']);
            $stmt->execute();
            $status_ar_sp=$stmt->fetch(PDO::FETCH_ASSOC);
            $sub_proc_insert = "INSERT INTO taps_subprocess_transaction (trans_id,user_id, process_id, project_id, assign_id, process_starttime, process_status,created_on,sub_process_name,process_comments) values ( :trans_id,:user_id,:process_id,:project_id,:assign_id,:process_starttime, 1, :created_on,:sub_process_name,:process_comments)";
            $stmt = $dbh->prepare($sub_proc_insert);
            $sub_proc_name="Copy Editing";
            $proc_cmts="Document opened";
            // Bind parameters to statement variables
            $stmt->bindParam(':trans_id',$status_ar_sp['trans_id']);
            $stmt->bindParam(':user_id',$status_ar_sp['user_id']);
            $stmt->bindParam(':process_id',$status_ar_sp['process_id']);
            $stmt->bindParam(':project_id',$status_ar_sp['project_id']);
            $stmt->bindParam(':assign_id',$status_ar_sp['assign_id']);
            $stmt->bindParam(':process_starttime', $process_time);				
            $stmt->bindParam(':created_on', $process_time); 
            $stmt->bindParam(':sub_process_name', $sub_proc_name); 
            $stmt->bindParam(':process_comments', $proc_cmts);
            $stmt->execute();
$des_place   	  = TAPS.$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";
	$explorer = $_ENV["SYSTEMROOT"] . '\\explorer.exe';
	$folder_to_open = $des_place;
	$wordfile = $des_place."\\".$_REQUEST['metaid'].".docx";
	//shell_exec("start /min $explorer /n,/e,$folder_to_open");
	$output = shell_exec('start /min winword '.$wordfile);
	
	}
	else
	{
	
	$articlename = $status_ar['article_name']." already opened in SPiCE";
					echo $status_message =  "1|spice|".$articlename."|3";  
					
	}
		
		
}



if($_REQUEST['type'] == '3b2')
{
$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1 and tpt.process_id=2"; 
									
									
									
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
					
					
					$fpp_filename_3d = $status_ar['article_name'];
					
					if($status_ar['process_id'] == "")
					{
					
					
					$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.process_id = 2 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id']; 
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);				
					$is_fileopen = 1;
					
					$insert = "update taps_process_transaction set is_fileopen=:is_fileopen where trans_id=:trans_id";
					$stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
									$stmt->bindParam(':is_fileopen', $is_fileopen);
									$stmt->bindParam(':trans_id', $status_ar['trans_id']);
									$stmt->execute();		
									
            // track manual fixing in 3b2

            $process_time   = date('Y-m-d H:i:s');
            $insert = "delete from taps_subprocess_transaction where trans_id=:trans_id and process_endtime is null and process_id=2 and sub_process_name='Pagination'";
            $stmt = $dbh->prepare($insert);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id and process_endtime is null and process_id=2";
            $stmt = $dbh->prepare($insert);
            $stmt->bindParam(':process_endtime', $process_time);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where trans_id=:trans_id";
            $stmt = $dbh->prepare($sub_proc_get);
             $stmt->bindParam(':trans_id', $status_ar['trans_id']);
            $stmt->execute();
            $status_ar_sp=$stmt->fetch(PDO::FETCH_ASSOC);
            $sub_proc_insert = "INSERT INTO taps_subprocess_transaction (trans_id,user_id, process_id, project_id, assign_id, process_starttime, process_status,created_on,sub_process_name,process_comments) values ( :trans_id,:user_id,:process_id,:project_id,:assign_id,:process_starttime, 1, :created_on,:sub_process_name,:process_comments)";
            $stmt = $dbh->prepare($sub_proc_insert);
            $sub_proc_name="Pagination";
            $proc_cmts="Document opened";
            // Bind parameters to statement variables
            $stmt->bindParam(':trans_id',$status_ar_sp['trans_id']);
            $stmt->bindParam(':user_id',$status_ar_sp['user_id']);
            $stmt->bindParam(':process_id',$status_ar_sp['process_id']);
            $stmt->bindParam(':project_id',$status_ar_sp['project_id']);
            $stmt->bindParam(':assign_id',$status_ar_sp['assign_id']);
            $stmt->bindParam(':process_starttime', $process_time);				
            $stmt->bindParam(':created_on', $process_time); 
            $stmt->bindParam(':sub_process_name', $sub_proc_name); 
            $stmt->bindParam(':process_comments', $proc_cmts);
            $stmt->execute();

            // code ends
					$copyrightyear = $status_ar['copyrightyear'];
					
					$fpp_filename_3d = $_REQUEST['jid'].$_REQUEST['aid'];
									
									$des_place   	  = TAPS.$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\FPP";
					$pagfile = $des_place."\\".$fpp_filename_3d.".3d";

					//$output = shell_exec('start '.$pagfile);
					
					
					$WshShell = new COM("WScript.Shell");	
			
					
					$output = $WshShell->Run($pagfile, 0, false);
					//$output = shell_exec("D:\\Programs\\TAPSFPP\\Springer\\RecentTemplate\\Springer_TAPS_dipe.bat ". $pagfile);
					echo $status_message =  $_REQUEST['id']."|fpp| 3B2 Tool Triggered!|1";
					}
					else
					{
					$articlename = "Article ".$fpp_filename_3d. " is opened already";
					echo $status_message =  $_REQUEST['id']."|fpp|".$articlename."|9";  
					}
					
	


}




if($_REQUEST['type'] == 'itCheck'){

$iteration_query = "SELECT ts.iteration FROM taps_assignment ts
left outer join taps_metadata tm on tm.meta_id = ts.meta_id
where assign_id = ".$_REQUEST['assign_id']; 
					$iteration_query_fetch = $dbh->query($iteration_query);
					$iteration_val = $iteration_query_fetch->fetch(PDO::FETCH_ASSOC);

if($_REQUEST['clientid'] == 1)
{
$meta_query = "select meta_status from taps_metadata where meta_value = '".$_REQUEST['metaid']."'"; 
$meta_query_fetch = $dbh->query($meta_query);
$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

if ($meta_val['meta_status'] == 14)
{
$meta_query = "update taps_metadata set meta_status = 2  where meta_value = '".$_REQUEST['metaid']."'"; 
$meta_query_fetch = $dbh->query($meta_query);
	echo "dataset";
}
/*if ($meta_val['meta_status'] == 10)
{
	echo "File Upload Failure";
}if ($meta_val['meta_status'] == 2)
{

	echo "File Upload Success";
}*/

}
					
echo $iteration_val['iteration'];
}


}



// Springer


if($clientid == 2)
{
if($_REQUEST['type'] == 'word'){

$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1 and tpt.process_id=5"; 
									
									
									
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
					
					$spice_filename_doc = $_REQUEST['jid']."_".$_REQUEST['aid'];
					
					
					if($status_ar['process_id'] == "")
					{
					
					
					$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.process_id = 5 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id']; 
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);				
					$is_fileopen = 1;
								$insert = "update taps_process_transaction set is_fileopen=:is_fileopen where trans_id=:trans_id";
									$stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
									$stmt->bindParam(':is_fileopen', $is_fileopen);
									$stmt->bindParam(':trans_id', $status_ar['trans_id']);
									$stmt->execute();
            $process_time   = date('Y-m-d H:i:s');
            $insert = "delete from taps_subprocess_transaction where trans_id=:trans_id and process_endtime is null and process_id=5 and sub_process_name='Copy Editing'";
            $stmt = $dbh->prepare($insert);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id and process_endtime is null and process_id=5";
            $stmt = $dbh->prepare($insert);
                                    // Bind parameters to statement variables
            $stmt->bindParam(':process_endtime', $process_time);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where assign_id=:assign_id and process_id=5 order by trans_id desc";
            $stmt = $dbh->prepare($sub_proc_get);
             $stmt->bindParam(':assign_id', $_REQUEST['assign_id']);
            $stmt->execute();
            $status_ar_sp=$stmt->fetch(PDO::FETCH_ASSOC);
            $sub_proc_insert = "INSERT INTO taps_subprocess_transaction (trans_id,user_id, process_id, project_id, assign_id, process_starttime, process_status,created_on,sub_process_name,process_comments) values ( :trans_id,:user_id,:process_id,:project_id,:assign_id,:process_starttime, 1, :created_on,:sub_process_name,:process_comments)";
            $stmt = $dbh->prepare($sub_proc_insert);
            $sub_proc_name="Copy Editing";
            $proc_cmts="Document opened";
            // Bind parameters to statement variables
            $stmt->bindParam(':trans_id',$status_ar_sp['trans_id']);
            $stmt->bindParam(':user_id',$status_ar_sp['user_id']);
            $stmt->bindParam(':process_id',$status_ar_sp['process_id']);
            $stmt->bindParam(':project_id',$status_ar_sp['project_id']);
            $stmt->bindParam(':assign_id',$status_ar_sp['assign_id']);
            $stmt->bindParam(':process_starttime', $process_time);				
            $stmt->bindParam(':created_on', $process_time); 
            $stmt->bindParam(':sub_process_name', $sub_proc_name); 
            $stmt->bindParam(':process_comments', $proc_cmts);
            $stmt->execute();
									
									
									
									
     $des_place   	  = TAPS.$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";
	$explorer = $_ENV["SYSTEMROOT"] . '\\explorer.exe';
	$folder_to_open = $des_place;
	$wordfile = $des_place."\\".$spice_filename_doc.".docx";
	//shell_exec("start /min $explorer /n,/e,$folder_to_open");
	$output = shell_exec('start /min winword '.$wordfile);
	
	}
	else
	{
	echo $status_ar['article_name']." already opened in SPiCE";
	}
		
		
}



if($_REQUEST['type'] == '3b2')
{
$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1 and tpt.process_id=2"; 
									
									
									
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
					$copyrightyear = $status_ar['copyrightyear'];
					
					$fpp_filename_3d = $_REQUEST['jid']."_".$copyrightyear."_".$_REQUEST['aid'];
					
					if($status_ar['process_id'] == "")
					{
					
					
					$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id JOIN taps_metadata tm ON tm.meta_id = ta.meta_id WHERE tpt.process_id = 2 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id']; 
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);				
					$is_fileopen = 1;
					
					$insert = "update taps_process_transaction set is_fileopen=:is_fileopen where trans_id=:trans_id";
					$stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
									$stmt->bindParam(':is_fileopen', $is_fileopen);
									$stmt->bindParam(':trans_id', $status_ar['trans_id']);
									$stmt->execute();		
            // track manual fixing in 3b2

            $process_time   = date('Y-m-d H:i:s');
            $query = "delete from taps_subprocess_transaction where trans_id=:trans_id and process_endtime is null and process_id=2 and sub_process_name='Pagination'";
            $stmt = $dbh->prepare($query);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $query = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id and process_endtime is null and process_id=2";
            $stmt = $dbh->prepare($query);
            $stmt->bindParam(':process_endtime', $process_time);
            $stmt->bindParam(':trans_id',  $status_ar['trans_id']);
            $stmt->execute();
            $sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where trans_id=:trans_id";
            $stmt = $dbh->prepare($sub_proc_get);
             $stmt->bindParam(':trans_id', $status_ar['trans_id']);
            $stmt->execute();
            $status_ar_sp=$stmt->fetch(PDO::FETCH_ASSOC);
            $sub_proc_insert = "INSERT INTO taps_subprocess_transaction (trans_id,user_id, process_id, project_id, assign_id, process_starttime, process_status,created_on,sub_process_name,process_comments) values ( :trans_id,:user_id,:process_id,:project_id,:assign_id,:process_starttime, 1, :created_on,:sub_process_name,:process_comments)";
            $stmt = $dbh->prepare($sub_proc_insert);
            $sub_proc_name="Pagination";
            $proc_cmts="Document opened";
            // Bind parameters to statement variables
            $stmt->bindParam(':trans_id',$status_ar_sp['trans_id']);
            $stmt->bindParam(':user_id',$status_ar_sp['user_id']);
            $stmt->bindParam(':process_id',$status_ar_sp['process_id']);
            $stmt->bindParam(':project_id',$status_ar_sp['project_id']);
            $stmt->bindParam(':assign_id',$status_ar_sp['assign_id']);
            $stmt->bindParam(':process_starttime', $process_time);				
            $stmt->bindParam(':created_on', $process_time); 
            $stmt->bindParam(':sub_process_name', $sub_proc_name); 
            $stmt->bindParam(':process_comments', $proc_cmts);
            $stmt->execute();

            // code ends
									$copyrightyear = $status_ar['copyrightyear'];
					
					$fpp_filename_3d = $_REQUEST['jid']."_".$copyrightyear."_".$_REQUEST['aid'];
									
									$des_place   	  = TAPS.$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\FPP";
					$pagfile = $des_place."\\".$fpp_filename_3d."_Article.3d";
					//$output = shell_exec('start '.$pagfile);
					
					//$output = shell_exec("D:\\Programs\\TAPSFPP\\Springer\\RecentTemplate\\Springer_TAPS_dipe.bat ". $pagfile);
					
						$WshShell = new COM("WScript.Shell");	
			
					$output = $WshShell->Run('D:\\Programs\\TAPSFPP\\Springer\\RecentTemplate\\Springer_TAPS_dipe.bat '.$pagfile, 0, false);
					
					echo $status_message =  $_REQUEST['id']."|fpp| 3B2 Tool Triggered!|1";
					}
					else
					{
					$articlename = "Article ".$fpp_filename_3d. " is in progress";
					echo $status_message =  $_REQUEST['id']."|fpp|".$articlename."|9";  
					}
					
	


}


if($_REQUEST['type'] == 'itCheck'){

$iteration_query = "SELECT ts.iteration FROM taps_assignment ts
left outer join taps_metadata tm on tm.meta_id = ts.meta_id
where assign_id = ".$_REQUEST['assign_id']; 
					$iteration_query_fetch = $dbh->query($iteration_query);
					$iteration_val = $iteration_query_fetch->fetch(PDO::FETCH_ASSOC);
echo $iteration_val['iteration'];

					
}


}

if($_REQUEST['type'] == 'dist'){
	$meta_id=$_REQUEST['id'];
	$basefile_path      = TAPS.$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid'];
	$copyright_query = "SELECT tm.copyrightyear,ta.assign_id FROM taps_metadata tm join taps_assignment ta on ta.meta_id=tm.meta_id where tm.meta_id = :meta_id"; 
	$stmt = $dbh->prepare($copyright_query);
	$stmt->bindParam(":meta_id",$meta_id);
	$stmt->execute();
	$copyright_val = $stmt->fetch(PDO::FETCH_ASSOC);	
	
	$remove_invalid_trans_q = "delete from taps_process_transaction where assign_id=:assign_id and process_id!=11 and process_status=1"; 
	$stmt1 = $dbh->prepare($remove_invalid_trans_q);
	$stmt1->bindParam(":assign_id",$copyright_val['assign_id']);
	$stmt1->execute();	
	
	$files = glob('Manifest/*'); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file))
		unlink($file); // delete file
	}
	
	$pid_file="Log/";
	if($clientid==1){
		$des_place = $basefile_path."\\FPP\\".$_REQUEST['metaid'].".xml";
		if (file_exists($des_place)) {		  
			$input = @file_get_contents($des_place);
			preg_match_all('/<jid>(.*?)<\/jid>/s', $input, $matches_jid); 
			preg_match_all('/<aid>(.*?)<\/aid>/s', $input, $matches_aid);
			$ps_name = $matches_jid[1][0]."_".$matches_aid[1][0];	 
		}
		$manifest_filename = $ps_name.".json";
		$pid_file.=$ps_name."_distiller_pid.pid";
		if(file_exists($pid_file)){
			$pid = @file_get_contents($pid_file);
			shell_exec("taskkill /pid $pid /F");
		}
		$jidcaps  = strtoupper($_REQUEST['jid']);
		$fppsource_ps    = $basefile_path."\\FPP\\".$ps_name.".ps";								
		$fppdest_ps   =  FPP_DEST_PATH;
		$fppmanifest_path  =  ELSEVIER_DISTILLER_MANIFEST_PATH;
		$log = $ps_name.".log";
		$cc = shell_exec("fastcopy.exe /verify /auto_close /speed=full /filelog=$log $fppsource_ps /to=$fppdest_ps");
		$src_xmp    = $basefile_path."\\FPP\\".$ps_name.".xmp";
		$dest_xmp   =  ELSEVIER_DISTILLER_XMP_PATH;
		$cc = shell_exec("fastcopy.exe /verify /auto_close /speed=full /filelog=$log $src_xmp /to=$dest_xmp");
		$src_xfdf    = $basefile_path."\\FPP\\".$ps_name.".xfdf";
		$dest_xfdf  =  ELSEVIER_DISTILLER_XFDF_PATH;
		$cc = shell_exec("fastcopy.exe /verify /auto_close /speed=full /filelog=$log $src_xfdf /to=$dest_xfdf");
	}
	if($clientid==2){
		$fppdest_ps   =  SPRINGER_DISTILLER_IN_PATH;
		$fppmanifest_path  =  SPRINGER_DISTILLER_MANIFEST_PATH;
		$ps_name = strtoupper($_REQUEST['jid'])."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid'];
		$manifest_filename = strtoupper($_REQUEST['jid'])."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_onlinePDF.json";
		$pid_file.=$ps_name."_OnlinePDF_distiller_pid.pid";
		if(file_exists($pid_file)){
			$pid = @file_get_contents($pid_file);
			shell_exec("taskkill /pid $pid /F");
		}
		$jidcaps  = strtoupper($_REQUEST['jid']);
		$fppsrc_ps    = $basefile_path."\\FPP\\".$ps_name."_OnlinePDF.ps";
		$log = $ps_name.".log";
		$cc = shell_exec("fastcopy.exe /verify /auto_close /speed=full /filelog=$log $fppsrc_ps /to=$fppdest_ps");
	}
	
	$timestamp = time();
	$datetime = date('Y-m-d H:i:s');
	$cpdata = '{"distiller":{"timestamp":"'.$timestamp.'","process":"Distiller","article_id":"'.$_REQUEST['aid'].'","process_type":"AUTO","process_time":"'.$datetime.'","journal_id":"'.$jidcaps.'","process_status":"0","artfile_status":"","sub_process_name":""}}';								
	$myFile = $fppmanifest_path.$manifest_filename;
	$fh = fopen($myFile, 'w') or die("can't open file");
	fwrite($fh, $cpdata);
	$WshShell = new COM("WScript.Shell");
	$oExec = $WshShell->Run('cmd /C java -jar TAPSCommunicator.jar -mf_path="'.$myFile.'"', 0, false);
	echo $status_message =  "1|dist|Article reposted to distiller|3";  
}

if($_REQUEST['type'] == 'complete'){

	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$meta_query = "SELECT tm.meta_value,tm.metaxml_path,tm.metadownload_path,ta.assign_id,ta.iteration,ta.user_id,ta.project_id from taps_metadata tm join taps_assignment ta on ta.meta_id=tm.meta_id where tm.meta_id=:meta_id"; 
	$meta_query_fetch = $dbh->prepare($meta_query);
	$meta_query_fetch->bindParam(":meta_id",$meta_id);
	$meta_query_fetch->execute();
	$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

	//$dest_id = $meta_val['metaxml_path'];
	$article_name = $_REQUEST['jid']."_".$_REQUEST['aid'];
	$dest_id = PRODUCTION_FTP_PATH."PAGING-FILES/".$_REQUEST['jid']."/VOL000/".$article_name; 

	$dest_spice = PRODUCTION_FTP_PATH."COPYEDITING/4_S3G-OUT/".$_REQUEST['jid']."/".$article_name; 

	 //$final_dest_path_spice = PRODUCTION_FTP_PATH."COPYEDITING/2_PRE-EDITING/OUT/".$_REQUEST['jid']."/".$article_name; 

	//$dest_spice = $meta_val['metadownload_path'];
	$article_name = $meta_val['meta_value'];
	$time = date("Y-m-d H:i:s");
	$insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, downloaded_time, process_starttime, process_status, is_completed, iteration,is_fileopen) VALUES (:user_id, 10,  :project_id, :assign_id, :downloaded_time,:process_starttime, 1, 1, :iteration,0)";
	$stmt = $dbh->prepare($insert);

	// Bind parameters to statement variables
	$stmt->bindParam(':user_id', $meta_val['user_id']);
	$stmt->bindParam(':project_id', $meta_val['project_id']);
	$stmt->bindParam(':assign_id', $meta_val['assign_id']);
	$stmt->bindParam(':downloaded_time', $time);
	$stmt->bindParam(':process_starttime', $time);
	//$stmt->bindParam(':process_endtime', $time);
	$stmt->bindParam(':iteration',$meta_val['$iteration']);

	 $stmt->execute();
	 $trans_id = $dbh->lastInsertId();
	if($_REQUEST['clientid'] == 1)
	{
		

		$src = "ftp://172.20.145.95/TAPS/".$article_name."/pagination";
		if($nopdf == 0){ //PDF REQUIRED
			$filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\FPP";
		}
		else if($nopdf == 1) { //PDF NOT REQUIRED
			$filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";
		}
		
		$spice_filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";

		$cpdata = '<xml><article_name>'.$article_name.'</article_name></xml>';
		
		/*$myFile = $article_name."_taps_success.xml";
		$fh = fopen($filepath."\\".$myFile, 'w') or die("can't open file");
		fwrite($fh, $cpdata);*/
					
		$WshShell = new COM("WScript.Shell");	
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"  domain=""', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"  domain=""' .'\n'. $oExec . "\n----------------------", FILE_APPEND);
		sleep(10);
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain="" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain="" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"\n' . $oExec . "\n----------------------", FILE_APPEND);
		//$oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$filepath.'" dest_path="'.$src.'" username="tapsdev" password="temp@dev" domain=""', 0, false);
		


	}

	if($_REQUEST['clientid'] ==2)
	{

		$filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\FPP";
		$spice_filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";

		$cpdata = '<xml><article_name>'.$article_name.'</article_name></xml>';

		$myFile = $article_name."_taps_success.xml";
		$fh = fopen($filepath."\\".$myFile, 'w') or die("can't open file");
		fwrite($fh, $cpdata);
	 
		$WshShell = new COM("WScript.Shell");	
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"\n' . $oExec . "\n----------------------", FILE_APPEND);
		sleep(10);
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"\n' . $oExec . "\n----------------------", FILE_APPEND);

	}
 
	


/*$updateStatus = "update taps_metadata set meta_status = 2 where meta_id=:meta_id";
	  $stmt = $dbh->prepare($updateStatus);							 
	  $stmt->bindParam(':meta_id', $meta_id);				
	  $stmt->execute();	*/
}


if($_REQUEST['type'] == "addCard"){
	$updateStatus = "update taps_metadata set meta_status = 1 where meta_id=:meta_id";
	  $stmt = $dbh->prepare($updateStatus);							 
	  $stmt->bindParam(':meta_id', $_REQUEST['meta_id']);				
	  $stmt->execute();	
	echo "added";
}
if($_REQUEST['type'] == "checkQry"){
$articleName = $_REQUEST['article_name'];
	$updateStatus = "select COUNT(is_closed) as open from taps_chat where article_name = '".$articleName."' and is_closed=0 and ctype=0";
	  	$meta_query_fetch = $dbh->query($updateStatus);
		$result = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);
	echo $result['open'];
}

if($_REQUEST['type'] == "forPO"){
	$updateStatus = "update taps_metadata set meta_status = 4 where meta_id=:meta_id";
	  $stmt = $dbh->prepare($updateStatus);							 
	  $stmt->bindParam(':meta_id', $_REQUEST['mid']);				
	  $stmt->execute();	
	echo "forApproval";
}
if($_REQUEST['type'] == "poSkipped"){
	$updateStatus = "update taps_metadata set meta_status = 7 where meta_id=:meta_id";
	  $stmt = $dbh->prepare($updateStatus);							 
	  $stmt->bindParam(':meta_id', $_REQUEST['mid']);				
	  $stmt->execute();	
	echo "skipped";
}
if($_REQUEST['type'] == "poStatus"){
	$selStatus = "select meta_status from taps_metadata where meta_id=".$_REQUEST['mid'];
		$meta_query_fetch = $dbh->query($selStatus);
		$result = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);
		echo $result[meta_status];
}
if($_REQUEST['type'] == "checkPO"){
	$selStatus = "select meta_status,meta_id from taps_metadata where meta_status in(4,5,6,7)";
		$meta_query_fetch = $dbh->query($selStatus);
		$data = $meta_query_fetch->fetchAll(PDO::FETCH_ASSOC);
			$out = array();
		foreach ($data as $row) {
			$data = $row['meta_id']."~".$row['meta_status'];
			array_push($out, $data);	
		}
		echo $vals = implode('|', $out);
}
// Code - Ram start
if($_REQUEST['type']=="disown"){
	$meta_id = $_REQUEST['metaid'];

	$meta_query = "SELECT tm.meta_value,tm.metaxml_path,tm.metadownload_path,ta.assign_id,ta.iteration,ta.user_id,ta.project_id from taps_metadata tm join taps_assignment ta on ta.meta_id=tm.meta_id where tm.meta_id=:meta_id"; 
	$meta_query_fetch = $dbh->prepare($meta_query);
	$meta_query_fetch->bindParam(":meta_id",$meta_id);
	$meta_query_fetch->execute();
	$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

	$dest_id = $meta_val['metaxml_path'];
	$dest_spice = $meta_val['metadownload_path'];
	$article_name = $meta_val['meta_value'];
	$time = date("Y-m-d H:i:s");
	$insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, downloaded_time, process_starttime, process_status, is_completed, iteration,is_fileopen) VALUES (:user_id, 10,  :project_id, :assign_id, :downloaded_time,:process_starttime, 1, 1, :iteration,0)";
	$stmt = $dbh->prepare($insert);

	// Bind parameters to statement variables
	$stmt->bindParam(':user_id', $meta_val['user_id']);
	$stmt->bindParam(':project_id', $meta_val['project_id']);
	$stmt->bindParam(':assign_id', $meta_val['assign_id']);
	$stmt->bindParam(':downloaded_time', $time);
	$stmt->bindParam(':process_starttime', $time);
	//$stmt->bindParam(':process_endtime', $time);
	$stmt->bindParam(':iteration',$meta_val['$iteration']);

	 $stmt->execute();
	 $trans_id = $dbh->lastInsertId();
	 //$trans_id="";
	 
	$insert1 = "update taps_metadata set disown_comments='".trim($_REQUEST['disownremarks'])."',meta_status=13 where meta_id=:meta_id";
	$stmt = $dbh->prepare($insert1);
 
	// Bind parameters to statement variables- metadata table update.
 
	//$stmt1->bindParam(':disown_comments', trim($_REQUEST['disownremarks']));
	$stmt->bindParam(':meta_id', $_REQUEST['metaid']);
	$stmt->execute();
	file_put_contents('DisownLog.txt', '----------------------'."\n".'Disown remarks:'.trim($_REQUEST['disownremarks']).'<>Meta id:'.$_REQUEST['metaid'].'----------------------', FILE_APPEND);

	$insert2 = "update taps_assignment set user_active=:user_active where meta_id=:meta_id";
	$stmt = $dbh->prepare($insert2);
 
	// Bind parameters to statement variables - assignment table update.
	$reUserActive="0";
	$stmt->bindParam(':user_active', $reUserActive);
	$stmt->bindParam(':meta_id', $_REQUEST['metaid']);
	$stmt->execute();
	file_put_contents('DisownLog.txt', '----------------------'."\n".$reUserActive.'<>Meta id:'.$_REQUEST['metaid'].'----------------------', FILE_APPEND);
	
	if($_REQUEST['clientid_disown'] == 1)
	{


		$src = "ftp://172.20.145.95/TAPS/".$article_name."/pagination";

		$filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\FPP";
		$spice_filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";

		
					
		$WshShell = new COM("WScript.Shell");	
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"  domain=""', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"  domain=""\n' . $oExec . "\n----------------------", FILE_APPEND);
		sleep(10);
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain="" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain="" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"\n' . $oExec . "\n----------------------", FILE_APPEND);
		//$oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$filepath.'" dest_path="'.$src.'" username="tapsdev" password="temp@dev" domain=""', 0, false);

	}

	if($_REQUEST['clientid_disown'] ==2)
	{

		$filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\FPP";
		$spice_filepath = "D:\\TAPS\\".$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid']."\\SPICE";

		
		$WshShell = new COM("WScript.Shell");	
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$spice_filepath.'" dest_path="'.$dest_spice.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_spice" metaidval="'.$meta_id.'|'. $trans_id.'"\n' . $oExec . "\n----------------------", FILE_APPEND);
		sleep(10);
		$oExec = $WshShell->Run('cmd /C java -Xms200m -jar TAPSFileHandler.jar -mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"', 0, false);
		file_put_contents('TAPSFileHandler_Trigger.txt', '----------------------\nParams:-mode=packaging src_path="'.$filepath.'" dest_path="'.$dest_id.'" domain="'.NT_DOMAIN.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" article_name="'.$article_name.'_fpp" metaidval="'.$meta_id.'|'. $trans_id.'"\n' . $oExec . "\n----------------------", FILE_APPEND);
	}
	
	
	$insert1 = "update taps_metadata set meta_status=13 where meta_id=:meta_id";
	$stmt = $dbh->prepare($insert1);
	$stmt->bindParam(':meta_id', $_REQUEST['metaid']);
	$stmt->execute();
	
	echo "Article ".$_REQUEST['articlename']." disowned succesfully!!";

}
// Code - Ram end

//Code - Ajay start
if($_REQUEST['type']=="datasetcreation"){
	
	//print_r($_REQUEST);
	//$nopdf = $_REQUEST['nopdf'];
	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	
	$meta_query = "SELECT tm.meta_value,tm.metaxml_path,tm.metadownload_path,ta.assign_id,ta.iteration,ta.user_id,ta.project_id,tm.pdf_free from taps_metadata tm join taps_assignment ta on ta.meta_id=tm.meta_id where tm.meta_id=:meta_id"; 
	$meta_query_fetch = $dbh->prepare($meta_query);$meta_query_fetch->bindParam(":meta_id",$meta_id);
	$meta_query_fetch->execute();
	$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

	
	$job_query = "select job_name from taps_projects where job_id = :job_id"; 
	$job_query_fetch = $dbh->prepare($job_query);$job_query_fetch->bindParam(":job_id",$job_id);
	$job_query_fetch->execute();
	$job_val = $job_query_fetch->fetch(PDO::FETCH_ASSOC);
	
	$job_name = $_REQUEST['jid'];
	
	$dest_id = $meta_val['metaxml_path'];
	$dest_spice = $meta_val['metadownload_path'];
	$article_name = $meta_val['meta_value'];
	if($meta_val['pdf_free'] == 1)
	{
		$pdfless = 'yes';
	}
	else
	{
		$pdfless = 'no';
	}
	$time = date("Y-m-d H:i:s");
	$insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, downloaded_time, process_starttime, process_status, is_completed, iteration,is_fileopen) VALUES (:user_id, 10,  :project_id, :assign_id, :downloaded_time,:process_starttime, 1, 1, :iteration,0)";
	$stmt = $dbh->prepare($insert);

	// Bind parameters to statement variables
	$stmt->bindParam(':user_id', $meta_val['user_id']);
	$stmt->bindParam(':project_id', $meta_val['project_id']);
	$stmt->bindParam(':assign_id', $meta_val['assign_id']);
	$stmt->bindParam(':downloaded_time', $time);$stmt->bindParam(':process_starttime', $time);
	//$stmt->bindParam(':process_endtime', $time);
	$stmt->bindParam(':iteration',$meta_val['$iteration']);
	$stmt->execute();
	
	
	$sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where assign_id=:assign_id and process_id=10 order by trans_id desc";
	$stmt = $dbh->prepare($sub_proc_get);
	 $stmt->bindParam(':assign_id', $_REQUEST['assign_id']);
	$stmt->execute();
	$status_ar_sp=$stmt->fetch(PDO::FETCH_ASSOC);

	$process_time   = date('Y-m-d H:i:s');
	$sub_proc_insert = "INSERT INTO taps_subprocess_transaction (trans_id,user_id, process_id, project_id, assign_id, process_starttime, process_status,created_on,sub_process_name,process_comments) values ( :trans_id,:user_id,:process_id,:project_id,:assign_id,:process_starttime, 1, :created_on,:sub_process_name,:process_comments)";
	$stmt = $dbh->prepare($sub_proc_insert);
	$sub_proc_name="S100 Dataset tool triggered";
	$proc_cmts="S100 Dataset tool triggered...";
	// Bind parameters to statement variables
	$stmt->bindParam(':trans_id',$status_ar_sp['trans_id']);
	$stmt->bindParam(':user_id',$status_ar_sp['user_id']);
	$stmt->bindParam(':process_id',$status_ar_sp['process_id']);
	$stmt->bindParam(':project_id',$status_ar_sp['project_id']);
	$stmt->bindParam(':assign_id',$status_ar_sp['assign_id']);
	$stmt->bindParam(':process_starttime', $process_time);				
	$stmt->bindParam(':created_on', $process_time); 
	$stmt->bindParam(':sub_process_name', $sub_proc_name); 
	$stmt->bindParam(':process_comments', $proc_cmts);
	$stmt->execute();
	
	
	$updateStatus = "update taps_metadata set meta_status = 1 where meta_id=:meta_id";
	$stmt = $dbh->prepare($updateStatus);							 
	$stmt->bindParam(':meta_id', $meta_id);
	$stmt->execute();
	
	$data = array(    
		"journal_acronym"   => $job_name,
		"article_id"        => $metaval,
		"process"       	=> 'dataset'
	);

	$url = ELSEVIER_METADATA_INVOKER;
	echo $content = json_encode($data);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	 $metaxml_path =  DATASET_PATH.'\\metadataxml\\' . $metaval . '_metadata.xml';
	
	
	if (file_exists($metaxml_path)) {
		unlink($metaxml_path);
	}
	
	file_put_contents($metaxml_path, $json_response);	
	
	$WshShell = new COM("WScript.Shell");	
	//$oExec = $WshShell->Run('cmd /C  ' . DATASET_PATH . '"\\Elsevier Dataset Journals GUI.exe" "' . $metaxml_path . '"', 0, false);
	
	//for click once dataset tool changes 
	$responseCode = checOnceClickUpdateAvailability();
	
	if( $responseCode == 200 ){
		$prepare_shell_text 		= 		'"'.INTERNET_EXPLORE_EXE_PATH.'" "'.ONCE_CLICK_DATASET_TOOL_SERVER_PATH.'?fullfilepath='.$metaxml_path.'"';
		$oExec = $WshShell->Run( $prepare_shell_text  , 0 , false);
	}else{
		$oExec = $WshShell->Run('cmd /C  ' . DATASET_PATH . '"\\Elsevier Dataset Journals GUI.exe" "' . $metaxml_path . '"', 0, false);
	}
	

}

/** Ananth B
*/

if($_REQUEST['type']=="datasetcreation_s5_exe"){
	
	//print_r($_REQUEST);
	//$nopdf = $_REQUEST['nopdf'];
	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	
	$meta_query = "SELECT tm.meta_value,tm.metaxml_path,tm.metadownload_path,ta.assign_id,ta.iteration,ta.user_id,ta.project_id,tm.pdf_free from taps_metadata tm join taps_assignment ta on ta.meta_id=tm.meta_id where tm.meta_id=:meta_id"; 
	$meta_query_fetch = $dbh->prepare($meta_query);$meta_query_fetch->bindParam(":meta_id",$meta_id);
	$meta_query_fetch->execute();
	$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

	
	$job_query = "select job_name from taps_projects where job_id = :job_id"; 
	$job_query_fetch = $dbh->prepare($job_query);$job_query_fetch->bindParam(":job_id",$job_id);
	$job_query_fetch->execute();
	$job_val = $job_query_fetch->fetch(PDO::FETCH_ASSOC);
	
	$job_name = $_REQUEST['jid'];
	
	$dest_id = $meta_val['metaxml_path'];
	$dest_spice = $meta_val['metadownload_path'];
	$article_name = $meta_val['meta_value'];
	if($meta_val['pdf_free'] == 1)
	{
		$pdfless = 'yes';
	}
	else
	{
		$pdfless = 'no';
	}
	$time = date("Y-m-d H:i:s");
	$insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, downloaded_time, process_starttime, process_status, is_completed, iteration,is_fileopen) VALUES (:user_id, 13,  :project_id, :assign_id, :downloaded_time,:process_starttime, 1, 1, :iteration,0)";
	$stmt = $dbh->prepare($insert);

	// Bind parameters to statement variables
	$stmt->bindParam(':user_id', $meta_val['user_id']);
	$stmt->bindParam(':project_id', $meta_val['project_id']);
	$stmt->bindParam(':assign_id', $meta_val['assign_id']);
	$stmt->bindParam(':downloaded_time', $time);$stmt->bindParam(':process_starttime', $time);
	//$stmt->bindParam(':process_endtime', $time);
	$stmt->bindParam(':iteration',$meta_val['$iteration']);
	$stmt->execute();
	
	
	$sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where assign_id=:assign_id and process_id=13 order by trans_id desc";
	$stmt = $dbh->prepare($sub_proc_get);
	 $stmt->bindParam(':assign_id', $_REQUEST['assign_id']);
	$stmt->execute();
	$status_ar_sp=$stmt->fetch(PDO::FETCH_ASSOC);

	$process_time   = date('Y-m-d H:i:s');
	$sub_proc_insert = "INSERT INTO taps_subprocess_transaction (trans_id,user_id, process_id, project_id, assign_id, process_starttime, process_status,created_on,sub_process_name,process_comments) values ( :trans_id,:user_id,:process_id,:project_id,:assign_id,:process_starttime, 1, :created_on,:sub_process_name,:process_comments)";
	$stmt = $dbh->prepare($sub_proc_insert);
	$sub_proc_name="S5 Dataset tool triggered";
	$proc_cmts="S5 Dataset tool triggered...";
	// Bind parameters to statement variables
	$stmt->bindParam(':trans_id',$status_ar_sp['trans_id']);
	$stmt->bindParam(':user_id',$status_ar_sp['user_id']);
	$stmt->bindParam(':process_id',$status_ar_sp['process_id']);
	$stmt->bindParam(':project_id',$status_ar_sp['project_id']);
	$stmt->bindParam(':assign_id',$status_ar_sp['assign_id']);
	$stmt->bindParam(':process_starttime', $process_time);				
	$stmt->bindParam(':created_on', $process_time); 
	$stmt->bindParam(':sub_process_name', $sub_proc_name); 
	$stmt->bindParam(':process_comments', $proc_cmts);
	$stmt->execute();
	
	
	$updateStatus = "update taps_metadata set meta_status = 30 where meta_id=:meta_id";
	$stmt = $dbh->prepare($updateStatus);							 
	$stmt->bindParam(':meta_id', $meta_id);
	$stmt->execute();
	sleep(5);
	
	$data = array(    
		"journal_acronym"   => $job_name,
		"article_id"        => $metaval,
		"process"       	=> 's5_dataset'
	);

	$url = ELSEVIER_METADATA_INVOKER;
	echo $content = json_encode($data);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	 $metaxml_path =  DATASET_PATH.'\\metadataxml\\' . $metaval . '_metadata.xml';
	
	
	if (file_exists($metaxml_path)) {
		unlink($metaxml_path);
	}
	
	file_put_contents($metaxml_path, $json_response);	
	
	$WshShell = new COM("WScript.Shell");	
	//$oExec = $WshShell->Run('cmd /C  ' . DATASET_PATH . '"\\Elsevier Dataset Journals GUI.exe" "' . $metaxml_path . '"', 0, false);
	
	//for click once dataset tool changes 
	$responseCode = checOnceClickUpdateAvailability();
	
	if( $responseCode == 200 || $responseCode == 405 ){
		$prepare_shell_text 		= 		'"'.INTERNET_EXPLORE_EXE_PATH.'" "'.ONCE_CLICK_DATASET_TOOL_SERVER_PATH.'?fullfilepath='.$metaxml_path.'"';
		$oExec = $WshShell->Run( $prepare_shell_text  , 0 , false);
	}else{
		$oExec = $WshShell->Run('cmd /C  ' . DATASET_PATH . '"\\Elsevier Dataset Journals GUI.exe" "' . $metaxml_path . '"', 0, false);
	}
	
}


//for testing s5 dataset

if($_REQUEST['type'] == 'testing_s5'){
	
	$metaxml_path	=  $metaxml_path =  DATASET_PATH.'\\metadataxml\\'.'JGENE_109_metadata.xml';
	//for click once dataset tool changes 
	$responseCode = checOnceClickUpdateAvailability();	
	$WshShell = new COM("WScript.Shell");	
	//$oExec = $WshShell->Run('cmd /C  ' . DATASET_PATH . '"\\Elsevier Dataset Journals GUI.exe" "' . $metaxml_path . '"', 0, false);

	if( $responseCode == 200 || $responseCode == 405 ){
		$prepare_shell_text 		= 		'"'.INTERNET_EXPLORE_EXE_PATH.'" "'.ONCE_CLICK_DATASET_TOOL_SERVER_PATH.'?fullfilepath='.$metaxml_path.'"';
		$oExec = $WshShell->Run( $prepare_shell_text  , 0 , false);		
	}else{	
		$oExec = $WshShell->Run('cmd /C  ' . DATASET_PATH . '"\\Elsevier Dataset Journals GUI.exe" "' . $metaxml_path . '"', 0, false);
	}
	
}


function checOnceClickUpdateAvailability(){
   $url = ONCE_CLICK_DATASET_TOOL_SERVER_PATH;
   $curl1 = curl_init($url);
   $content = array();
   curl_setopt($curl1, CURLOPT_HEADER, false);
   curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
   curl_setopt($curl1, CURLOPT_POST, true);
   curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
   $json_responseStripins = curl_exec($curl1);
   $response = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
   curl_close($curl1);
   return $response;
}
 
//End testing s5 dataset


/* Ananth code end
*/
//Code - Ajay end
if($_REQUEST['type']=="updateEventNotification"){
$metaid = $_REQUEST['metaid'];
$updateQuery = "update taps_metadata set status=2 where meta_id=:meta_id";
$stmt = $dbh->prepare($updateQuery);							 
$stmt->bindParam(':meta_id', $meta_id);
$stmt->execute();

}
if($_REQUEST['type']=="pclogs"){
$meta_query = "select meta_value,pc_remarks from taps_metadata where meta_id = '".$_REQUEST['metaid']."'"; 
//echo $meta_query;
$meta_query_fetch = $dbh->query($meta_query);
$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);
echo $meta_val['meta_value']."-".$meta_val['pc_remarks'];
}
//Code - Ram End - Signal from Magnus ELS - Central Workflow system.

if($_REQUEST['type']=="s5_stripins"){

	//Author : Ananth B [ 006264 ]
	//Before Doing dataset creation consider stripins s5 dataset - ( Start )
	//New change request logic applied here 
	//step 1: Consider stripins
	//step 2: Consider s5 dataset
	/*
					journal_id'],
					"article_id"        => $input_arr['article_id'],
					"process"       	=> 'stripins',
					"datasetid"			=> $input_arr['datasetid'],
	*/	
	
							$insert_start = "update taps_metadata set meta_status = 2 where meta_value=:meta_value";
							$stmt = $dbh->prepare($insert_start);	
							$stmt->bindParam(':meta_value', $article_id);
							$stmt->execute();
							
							$data = array(    
								"journal_acronym"   	=> 	$_REQUEST['jid'],
								"article_id"        	=> 	$_REQUEST['metaval'],
								"process"       	 	=> 	'addtoolsthread'
							);
							
							//file_put_contents('back_tools.txt' , PHP_EOL.'GIVEN INPUT :'.json_encode($data) , FILE_APPEND );
							exit;
							$url = ELSEVIER_METADATA_INVOKER;
							$content = json_encode($data);
							$curl1 = curl_init($url);
							curl_setopt($curl1, CURLOPT_HEADER, false);
							curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
							curl_setopt($curl1, CURLOPT_POST, true);
							curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
							$json_response1 = curl_exec($curl1);
							$status1 = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
							curl_close($curl1);
								
							file_put_contents('back_tools.txt' , PHP_EOL.'GOT OUTPUT :'.$json_response1 , FILE_APPEND );
							
							
							if( $json_response1 == 1 ||  $json_response1 == '1'  ){
								
								$insert_start = "update taps_metadata set meta_status = 2 where meta_value=:meta_value";
								file_put_contents('back_tools.txt' , PHP_EOL.$insert_start.PHP_EOL , FILE_APPEND );
							
								//$insert_start = "update taps_metadata set meta_status = 2 where meta_value=:meta_value";
								$stmt = $dbh->prepare($insert_start);	
								$stmt->bindParam(':meta_value', $article_id);
								$stmt->execute();
								
							
							}else{						
							
								$insert_start = "update taps_metadata set meta_status = 47 where meta_value=:meta_value";
								$stmt = $dbh->prepare($insert_start);	
								$stmt->bindParam(':meta_value', $article_id);
								$stmt->execute();							
							
							}	 						
							
										
	exit;
							
	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	$returnParam	=	"jid=".$_REQUEST['jid']."&aid=".$_REQUEST['aid']."&clientid=".$_REQUEST['clientid']."&metaid=".$_REQUEST['metaid']."&cname=".$_REQUEST['cname']."&metaidval=".$_REQUEST['metaidval']."&assign_id=".$_REQUEST['assign_id']."&job_id=".$job_id."&nopdf=".$_REQUEST['nopdf']."&metaval=".$_REQUEST['metaval'];
	
	
	
	
		$taps_obj 		=  	new TapsProcessHandler();		
		$checkS5DatasetFlag	= 	$taps_obj->isS5DatasetTriggered( $metaval );		
		
		if( !$checkS5DatasetFlag ){
			
			$obj_s5		=		new beforeDataset();
			$input		=		array( 'journal_id' =>  $jidcaps , 'article_id' => $metaval, 'METAID' => $metaval );
			$status_of	=		$obj_s5->forstripins( $input , $dbh , $returnParam );
		
		}else{
		
			$ret	=	array( 'stripins stage skiped' , 's100_dataset' , $returnParam );
			echo json_encode( $ret );
			exit;
		}
		/*
		file_put_contents("s5_dataset_upload_trace.txt", 'S5 dataset over '.' final status is :'.$obj_s5->s5_upload.PHP_EOL ,FILE_APPEND);
				
		if( $obj_s5->s5_upload == 'fail' ){		
			file_put_contents("s5_dataset_upload_trace.txt", 'S5 dataset over '.' final status is : failure at upload '.PHP_EOL ,FILE_APPEND);		
		exit;
		}elseif( $obj_s5->s5_upload == 'success'  ){
			file_put_contents("s5_dataset_upload_trace.txt", PHP_EOL.'s100 dataset Creation started '.PHP_EOL ,FILE_APPEND);
		}
		*/
	//After Dataset creation consider stripins s5 dataset - ( End )
	
	//exit;
}	
	

if($_REQUEST['type']=="s5_xmp_pdf"){

	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	$returnParam	=	"jid=".$_REQUEST['jid']."&aid=".$_REQUEST['aid']."&clientid=".$_REQUEST['clientid']."&metaid=".$_REQUEST['metaid']."&cname=".$_REQUEST['cname']."&metaidval=".$_REQUEST['metaidval']."&assign_id=".$_REQUEST['assign_id']."&job_id=".$job_id."&nopdf=".$_REQUEST['nopdf']."&metaval=".$_REQUEST['metaval'];
	
		$obj_s5		=		new beforeDataset();
		$input_arr = $input		=		array( 'journal_id' =>  $jidcaps , 'article_id' => $metaval, 'METAID' => $metaval );
		$status_of	=		$obj_s5->for_s5_xmp_pdf( $input , $dbh , $returnParam );
		
		
		
}	
	

if($_REQUEST['type']=="s5_dataset"){

	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	$returnParam	=	"jid=".$_REQUEST['jid']."&aid=".$_REQUEST['aid']."&clientid=".$_REQUEST['clientid']."&metaid=".$_REQUEST['metaid']."&cname=".$_REQUEST['cname']."&metaidval=".$_REQUEST['metaidval']."&assign_id=".$_REQUEST['assign_id']."&job_id=".$job_id."&nopdf=".$_REQUEST['nopdf']."&metaval=".$_REQUEST['metaval'];
	
		$obj_s5		=		new beforeDataset();
		$input		=		array( 'journal_id' =>  $jidcaps , 'article_id' => $metaval, 'METAID' => $metaval );
		$status_of	=		$obj_s5->for_s5_dataset( $input , $dbh , $returnParam );
		
		
}	

if($_REQUEST['type']=="s5_upload"){

	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	$returnParam	=	"jid=".$_REQUEST['jid']."&aid=".$_REQUEST['aid']."&clientid=".$_REQUEST['clientid']."&metaid=".$_REQUEST['metaid']."&cname=".$_REQUEST['cname']."&metaidval=".$_REQUEST['metaidval']."&assign_id=".$_REQUEST['assign_id']."&job_id=".$job_id."&nopdf=".$_REQUEST['nopdf']."&metaval=".$_REQUEST['metaval'];
	
		$obj_s5		=		new beforeDataset();
		$input		=		array( 'journal_id' =>  $jidcaps , 'article_id' => $metaval, 'METAID' => $metaval );
		$status_of	=		$obj_s5->s5_dataset_upload( $input , $dbh , $returnParam );
		
		
}	

if($_REQUEST['type']=="s5_dataset_uploader_status"){
	
	$jidcaps = $_REQUEST['jid'];
	$meta_id = $_REQUEST['metaidval'];
	$nopdf = $_REQUEST['nopdf'];
	$job_id = $_REQUEST['job_id'];
	$metaval = $_REQUEST['metaval'];
	$returnParam	=	"jid=".$_REQUEST['jid']."&aid=".$_REQUEST['aid']."&clientid=".$_REQUEST['clientid']."&metaid=".$_REQUEST['metaid']."&cname=".$_REQUEST['cname']."&metaidval=".$_REQUEST['metaidval']."&assign_id=".$_REQUEST['assign_id']."&job_id=".$job_id."&nopdf=".$_REQUEST['nopdf']."&metaval=".$_REQUEST['metaval'];
	
		$obj_s5		=		new beforeDataset();
		$input		=		array( 'journal_id' =>  $jidcaps , 'article_id' => $metaval, 'METAID' => $metaval );
		$status_of	=		$obj_s5->s5_dataset_uploader_status( $input , $dbh , $returnParam );
		
	
}


if($_REQUEST['type']=="artRollback"){
	
	$jidcaps 		= 			$_REQUEST['jid'];
	$metaval 		= 			$_REQUEST['metaval'];
	
	//jid=GENE&metaidval=&nopdf=&job_id=&metaval=GENE_112
	
	//$returnParam	=	"jid=".$_REQUEST['jid']."&aid=".$_REQUEST['aid']."&clientid=".$_REQUEST['clientid']."&metaid=".$_REQUEST['metaid']."&cname=".$_REQUEST['cname']."&metaidval=".$_REQUEST['metaidval']."&assign_id=".$_REQUEST['assign_id']."&job_id=".$job_id."&nopdf=".$_REQUEST['nopdf']."&metaval=".$_REQUEST['metaval'];
	
	
		$obj_s5		=		new beforeDataset();
		$input		=		array( 'journal_id' =>  $jidcaps , 'article_id' => $metaval, 'METAID' => $metaval );
		$status_of	=		$obj_s5->artRollback( $input , $dbh , $returnParam );
		
	
}

	

class beforeDataset{

//step 1
	public function forstripins( $input_arr = array() , $dbh , $returnParam ){	
		
			file_put_contents("stripins_trace.txt", PHP_EOL.'Stripins Process initiated with the input of : '.PHP_EOL.json_encode($input_arr).PHP_EOL.str_repeat( '=' , 20 ).PHP_EOL , FILE_APPEND);			
		
			$insert_start = "update taps_metadata set meta_status = 27 where meta_value=:meta_value";
			//ECHO "update taps_metadata set meta_status = 27 where meta_value=".$input_arr['article_id'];				
			file_put_contents("stripins_trace.txt", $insert_start.PHP_EOL , FILE_APPEND);	
			
			$stmt = $dbh->prepare($insert_start);	
			$stmt->bindParam(':meta_value',  $input_arr['article_id'] );			
			$stmt->execute();		
			
			$transaction_obj = new TapsProcessHandler();
			$article_id	= $input_arr['article_id'];			
			$transaction_obj->insertProcessTransTable( $article_id , STRIPINS_PROCESS_ID );			
			$transaction_obj->insertSubProcessTransTable( $article_id  , STRIPINS_PROCESS_ID , 'S5 stripins' );			
		
			$data = array(    
					"journal_acronym"   => $input_arr['journal_id'],
					"article_id"        => $input_arr['article_id'],
					"process"       	=> 'stripins',
					"fromTaps"          => 'yes'
				);
				
				$url = ELSEVIER_METADATA_INVOKER;
				$content = json_encode($data);
				$curl1 = curl_init($url);
				curl_setopt($curl1, CURLOPT_HEADER, false);
				curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
				curl_setopt($curl1, CURLOPT_POST, true);
				curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
				$json_responseStripins = curl_exec($curl1);
				$status1 = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
				curl_close($curl1);
				$jsonResp = explode(":",$json_responseStripins);
				$json_responseStripins = $jsonResp[0];
				$jsonRespRemarks = $jsonResp[1];
				
				//echo  'Am here '.$jsonRespRemarks.' with status '.$json_responseStripins.' type '.gettype($json_responseStripins);
				if($json_responseStripins=="0" ){
				
				$insert_start = "update taps_metadata set meta_status = 28 where meta_value=:meta_value";
				file_put_contents("stripins_trace.txt", 'Success update : '.$insert_start.PHP_EOL ,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value',  $input_arr['article_id']);
				$stmt->execute();
				$this->s5_upload = '';
				//sleep(5);
				//call_user_func_array(	array( $this , 'for_s5_xmp_pdf' ) , array( $input_arr , $dbh ) );
				$ret	=	array( 'stripins creation completed' , 's5_xmp_pdf' , $returnParam );
				echo json_encode( $ret );
				
				$article_info = $transaction_obj->getArticleInfor( $article_id , STRIPINS_PROCESS_ID );
				$transac_id = $article_info['trans_id'];
				$time = date('Y-m-d H:i:s');
				$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 2 );
				$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
				$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $article_id ) ;
				$sub_trans_id = $records_sub['sub_trans_id'];
				$input_sub_trans = array(  'process_status' => 2 , 'process_endtime' => date('Y-m-d H:i:s') , 
				'process_comments' => 'S5 stripins Creation Completed', 'sub_trans_id' => $sub_trans_id );
				$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
				
				return 'success';
				}elseif( $json_responseStripins	== "1" ){
				
				$insert_start = "update taps_metadata set meta_status = 29 where meta_value=:meta_value";
				file_put_contents("stripins_trace.txt", 'failure update : '.$insert_start.PHP_EOL,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value',  $input_arr['article_id'] );
				$stmt->execute();
				$this->s5_upload = 'fail';
				$ret	=	array( 'stripins failed' , 'failed' , $returnParam );
				echo json_encode( $ret );
				
				$article_info = $transaction_obj->getArticleInfor( $article_id , STRIPINS_PROCESS_ID );
				$transac_id = $article_info['trans_id'];
				$time = date('Y-m-d H:i:s');
				$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 3 );
				$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
				$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $article_id ) ;
				$sub_trans_id = $records_sub['sub_trans_id'];
				$input_sub_trans = array(  'process_status' => 3 , 'process_endtime' => date('Y-m-d H:i:s') ,
				'process_comments' => 'S5 stripins creation failed', 'sub_trans_id' => $sub_trans_id );
				$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
								
				
				
				return 'fail';
				
				}else{	
						$this->s5_upload = 'fail';
						file_put_contents("stripins_trace.txt", 'stripins fail case didnot receive any signal update' , FILE_APPEND);	
				$ret	=	array( 'stripins didnot received signal ' , 'failed' , $returnParam );
				echo json_encode( $ret );	
					
				$insert_start = "update taps_metadata set meta_status = 39 where meta_value=:meta_value";
				file_put_contents("stripins_trace.txt", 'Success update : '.$insert_start.PHP_EOL ,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value',  $input_arr['article_id']);
				$stmt->execute();
				
				}
				// s5 dataset call.
				return '-1';
}


	public function for_s5_xmp_pdf( $input_arr = array() , $dbh , $returnParam ){
		
		// 36 init , 37 succ ,  38 failed		
			file_put_contents("s5_xmp_pdf_trace.txt", PHP_EOL.'s5 xmp pdf Process initiated with the input of : '.PHP_EOL.json_encode($input_arr).PHP_EOL.str_repeat( '=' , 20 ).PHP_EOL , FILE_APPEND);			
			$insert_start = "update taps_metadata set meta_status = 36 where meta_value=:meta_value";
			file_put_contents("s5_xmp_pdf_trace.txt", $insert_start.PHP_EOL , FILE_APPEND);				
			$stmt = $dbh->prepare($insert_start);	
			$stmt->bindParam(':meta_value',  $input_arr['article_id'] );			
			$stmt->execute();		
			
			$transaction_obj = new TapsProcessHandler();
			$article_id	= $input_arr['article_id'];			
			$transaction_obj->insertProcessTransTable( $article_id , XMP_PDF_PROCESS_ID );			
			$transaction_obj->insertSubProcessTransTable( $article_id  , XMP_PDF_PROCESS_ID , 'S5 xmp pdf' );			
		
		
			$data = array(    
					"journal_acronym"   => $input_arr['journal_id'],
					"article_id"        => $input_arr['article_id'],
					"process"       	=> 's5_xmp_pdf',
					"fromTaps"          => 'yes'
				);
				
				$url = ELSEVIER_METADATA_INVOKER;
				$content = json_encode($data);
				$curl1 = curl_init($url);
				curl_setopt($curl1, CURLOPT_HEADER, false);
				curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
				curl_setopt($curl1, CURLOPT_POST, true);
				curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
				$json_responseStripins = curl_exec($curl1);
				$status1 = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
				curl_close($curl1);
				$jsonResp = explode(":",$json_responseStripins);
				$json_responseStripins=$jsonResp[0];
				$jsonRespRemarks = $jsonResp[1];
				
				sleep(4);	
				
				if($json_responseStripins=="0" ){
				$this->s5_upload = '';
				$insert_start = "update taps_metadata set meta_status = 37 where meta_value=:meta_value";
				file_put_contents("s5_xmp_pdf_trace.txt", 'Success update :'.$insert_start,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value', $article_id);
				$stmt->execute();				
				$ret	=	array( 'xmp pdf completed' , 's5_dataset' , $returnParam );
				echo json_encode( $ret );
				
				$article_info = $transaction_obj->getArticleInfor( $article_id , XMP_PDF_PROCESS_ID );
				$transac_id = $article_info['trans_id'];
				$time = date('Y-m-d H:i:s');
				$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 2 );
				$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
				$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $article_id ) ;
				$sub_trans_id = $records_sub['sub_trans_id'];
				$input_sub_trans = array(  'process_status' => 2 , 'process_endtime' => date('Y-m-d H:i:s') , 
				'process_comments' => 'S5 xmp pdf Creation Completed', 'sub_trans_id' => $sub_trans_id );
				$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
				
				
				return 'success';
				}elseif( $json_responseStripins	== "1" ){
				$insert_start = "update taps_metadata set meta_status = 38 where meta_value=:meta_value";
				file_put_contents("s5_xmp_pdf_trace.txt", 'Failure update '.$insert_start,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value', $article_id);
				$stmt->execute();
				$this->s5_upload = 'fail';
				$ret	=	array( 's5 xmp pdf failed' , 'failed' , $returnParam );
				
				echo json_encode( $ret );
				
				$article_info = $transaction_obj->getArticleInfor( $article_id , XMP_PDF_PROCESS_ID );
				$transac_id = $article_info['trans_id'];
				$time = date('Y-m-d H:i:s');
				$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 3 );
				$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
				$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $article_id ) ;
				$sub_trans_id = $records_sub['sub_trans_id'];
				$input_sub_trans = array(  'process_status' => 3 , 'process_endtime' => date('Y-m-d H:i:s') , 
				'process_comments' => 'S5 xmp pdf Creation Failed.', 'sub_trans_id' => $sub_trans_id );
				$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
				
				
				return 'fail';
				}else{
				
				$this->s5_upload = 'fail';
				file_put_contents("s5_xmp_pdf_trace.txt", 's5_xmp_pdf fail case : didnot receive any signal' ,FILE_APPEND);	
				$ret	=	array( 'xmp pdf process didnot received signal ' , 'failed' , $returnParam );
				echo json_encode( $ret );
				$insert_start = "update taps_metadata set meta_status = 40 where meta_value=:meta_value";
				file_put_contents("stripins_trace.txt", 'Success update : '.$insert_start.PHP_EOL ,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value',  $input_arr['article_id']);
				$stmt->execute();
				
				
				}
			return '-1';
	
	
	}
	

//step 3

	public function for_s5_dataset( $input_arr = array() , $dbh , $returnParam ){

			// 30 init , 31 succ ,  32 failed		

			file_put_contents("s5_dataset_trace.txt", PHP_EOL.'s5 dataset Process initiated with the input of : '.PHP_EOL.json_encode($input_arr).PHP_EOL.str_repeat( '=' , 20 ).PHP_EOL , FILE_APPEND);			
			
			$article_id	=	$input_arr['article_id'];
			$insert_start = "update taps_metadata set meta_status = 30 where meta_value=:meta_value";
			file_put_contents("s5_dataset_trace.txt", $insert_start.PHP_EOL , FILE_APPEND);	
			$stmt = $dbh->prepare($insert_start);
			file_put_contents("s5_dataset_trace.txt", $insert_start.' article value :'.$input_arr['article_id'].PHP_EOL , FILE_APPEND);	
			$stmt->bindParam(':meta_value',  $input_arr['article_id']);
			$stmt->execute();
		
			$data = array(    
					"journal_acronym"   => $input_arr['journal_id'],
					"article_id"        => $input_arr['article_id'],
					"process"       	=> 's5_dataset',
					"fromTaps"          => 'yes'
				);
				
				$url = ELSEVIER_METADATA_INVOKER;
				$content = json_encode($data);
				$curl1 = curl_init($url);
				curl_setopt($curl1, CURLOPT_HEADER, false);
				curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
				curl_setopt($curl1, CURLOPT_POST, true);
				curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
				$json_responseStripins = curl_exec($curl1);
				$status1 = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
				curl_close($curl1);
				$jsonResp = explode(":",$json_responseStripins);
				$json_responseStripins=$jsonResp[0];
				$jsonRespRemarks = $jsonResp[1];
				$datasetid	=	$jsonResp[2];
				
				if($json_responseStripins=="0"){
				$this->s5_upload = '';
				$insert_start = "update taps_metadata set meta_status = 31 where meta_value=:meta_value";
				file_put_contents("s5_dataset_trace.txt", 'Success update :'.$insert_start,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value', $article_id);
				$stmt->execute();				
				$input_arr['datasetid'] = $datasetid;
				$ret	=	array( 's5 dataset creation completed successfully' , 's5_upload' , $returnParam );
				echo json_encode( $ret );
				return true;
				}elseif( $json_responseStripins	== "1" ){
				$insert_start = "update taps_metadata set meta_status = 32 where meta_value=:meta_value";
				file_put_contents("s5_dataset_trace.txt", 'Failure update '.$insert_start,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value', $article_id);
				$stmt->execute();
				$this->s5_upload = 'fail';
				
				$ret	=	array( 's5 dataset creation failed' , 'failed' , $returnParam );
				echo json_encode( $ret );
				
				
				}else{
				$this->s5_upload = 'fail';
				file_put_contents("s5_dataset_trace.txt", 's5_dataset fail case : didnot receive any signal' ,FILE_APPEND);	
				$ret	=	array( 's5 dataset creation didnot received signal ' , 'failed' , $returnParam );
				echo json_encode( $ret );	
				$insert_start = "update taps_metadata set meta_status = 41 where meta_value=:meta_value";
				file_put_contents("stripins_trace.txt", 'Success update : '.$insert_start.PHP_EOL ,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value',  $input_arr['article_id']);
				$stmt->execute();
				
				}
			return '-1';
	}
	
//step 4 
	
	public function s5_dataset_upload( $input_arr , $dbh , $returnParam ){
	
	// 33 init , 34 succ ,  35 failed
			file_put_contents("s5_dataset_upload_trace.txt", PHP_EOL.'s5 dataset upload Process initiated with the input of : '.PHP_EOL.json_encode($input_arr).PHP_EOL.str_repeat( '=' , 20 ).PHP_EOL , FILE_APPEND);			
			
	
			$article_id	=	$input_arr['article_id'];
			$insert_start = "update taps_metadata set meta_status = 33 where meta_value=:meta_value";
			file_put_contents("s5_dataset_upload_trace.txt", $insert_start."( $article_id)".PHP_EOL , FILE_APPEND);	
			$stmt = $dbh->prepare($insert_start);	
			$stmt->bindParam(':meta_value', $article_id);
			$stmt->execute();
			sleep(10);
			$data = array(    
					"journal_acronym"   => $input_arr['journal_id'],
					"article_id"        => $input_arr['article_id'],
					"process"       	=> 's5_dataset_upload',
					"datasetid"			=> $input_arr['datasetid'],
					"fromTaps"          => 'yes'
				);
				
				$url = ELSEVIER_METADATA_INVOKER;
				$content = json_encode($data);
				$curl1 = curl_init($url);
				curl_setopt($curl1, CURLOPT_HEADER, false);
				curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
				curl_setopt($curl1, CURLOPT_POST, true);
				curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
				$json_responseStripins = curl_exec($curl1);
				$status1 = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
				curl_close($curl1);
				$jsonResp = explode(":",$json_responseStripins);
				$json_responseStripins=$jsonResp[0];
				$jsonRespRemarks = $jsonResp[1];
				
				file_put_contents("s5_dataset_upload_trace.txt", 'Response Update '.$json_responseStripins.PHP_EOL ,FILE_APPEND);
				
				if($json_responseStripins =="2"){
				$this->s5_upload = 'succes';
				$insert_start = "update taps_metadata set meta_status = 34 where meta_value=:meta_value";
				file_put_contents("s5_dataset_upload_trace.txt", 'Succes Update '.$insert_start."( $article_id )".PHP_EOL ,FILE_APPEND);	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value', $article_id);
				$stmt->execute();
				sleep(5);
				$ret	=	array( 's5 upload completed successfully' , 's5_upload_completed' , $returnParam );
				echo json_encode( $ret );
				
				return 'success';
				}elseif( $json_responseStripins	== "3" ){
				$this->s5_upload = 'fail';
				$insert_start = "update taps_metadata set meta_status = 35 where meta_value=:meta_value";
					file_put_contents("s5_dataset_upload_trace.txt", 'Failure update :'.$insert_start."( $article_id)".PHP_EOL ,FILE_APPEND );	
				$stmt = $dbh->prepare($insert_start);	
				$stmt->bindParam(':meta_value', $article_id);
				$stmt->execute();
				sleep(5);
				$ret	=	array( 's5 upload failed' , 'failed' , $returnParam );
				echo json_encode( $ret );				
				return 'fail';
				}else{
					$this->s5_upload = 'fail';
					file_put_contents("s5_dataset_upload_trace.txt", 's5_dataset_upload fail case : didnot receive any signal' ,FILE_APPEND );	
					$ret	=	array( 's5 upload didnot received signal ' , 'failed' , $returnParam );
					echo json_encode( $ret );
					$insert_start = "update taps_metadata set meta_status = 42 where meta_value=:meta_value";
					file_put_contents("stripins_trace.txt", 'Success update : '.$insert_start.PHP_EOL ,FILE_APPEND);	
					$stmt = $dbh->prepare($insert_start);	
					$stmt->bindParam(':meta_value',  $input_arr['article_id']);
					$stmt->execute();
				sleep(2);
				}
			return '-1';
	}
	
	public function s5_dataset_uploader_status( $input_arr , $dbh , $returnParam ){
	
		$data = array(    
					"journal_acronym"   => $input_arr['journal_id'],
					"article_id"        => $input_arr['article_id'],
					"process"       	=> 's5_dataset_uploader_status',
					"datasetid"			=> $input_arr['datasetid'],
					"fromTaps"          => 'yes'
				);
					
				$url = ELSEVIER_METADATA_INVOKER;
				$content = json_encode($data);
				$curl1 = curl_init($url);
				curl_setopt($curl1, CURLOPT_HEADER, false);
				curl_setopt($curl1, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl1, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
				curl_setopt($curl1, CURLOPT_POST, true);
				curl_setopt($curl1, CURLOPT_POSTFIELDS, $content);
				$json_responseStripins = curl_exec($curl1);
				$status1 = curl_getinfo($curl1, CURLINFO_HTTP_CODE);
				curl_close($curl1);
				$jsonResp = explode(":",$json_responseStripins);
				$json_responseStripins=$jsonResp[0];
				$jsonRespRemarks = $jsonResp[1];
				
		$print_res =	'empty';		
		if(  $json_responseStripins == '2'){
		$print_res =	'success'; 
		sleep(10);	
		}
		if(  $json_responseStripins == '3'){ 
		$print_res =	'failed';
		sleep(10);
		}
		
	 $outres = array( true , $print_res );
	 $returns = json_encode(  $outres  );
	 echo  $returns;
	 
	}

	public function artRollback( $input_arr , $dbh , $returnParam ){
	
		//magnusEls/api/rollback.php?aid=94&jobarc=GENE&action=artrollback

		$aid	=	explode( 	$input_arr['journal_id'].'_' 	, $input_arr['article_id']  );		
		
		$data 	= 	array(    
						"aid"   		=> 		$aid[1]	,
						"jobarc"        => 		$input_arr['journal_id']	,
						"action"       	=> 		'artrollback'				,
				);
				
		$prep_meta  = $input_arr['journal_id'].'_'.$aid[1];
		
		file_put_contents("art_rollback_trace.txt", PHP_EOL.'Art rollback initialized with the Input of :'.json_encode($data).PHP_EOL ,FILE_APPEND);
				
							
		$url = MAGNUSELS_PRODUCTION_URL."api/rollback.php";
		$content = $data;
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		$json_response = curl_exec($curl);
		curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		$status = 	$json_response;
				
		$retString	=	'';
		$art_status	=	'';
		$status_r	=	'success';
		$pos1 = strpos( $status , 'success');
		$pos2 = strpos( $status , 'failed');
		
		$selecArtInfo = 'SELECT * FROM `taps_metadata` WHERE meta_value = "'.$prep_meta.'" limit 1';
		$rset		=	$dbh->query( $selecArtInfo );			
		$output_row	=	$rset->fetch( PDO::FETCH_ASSOC );
		$exit_status	=	$output_row['art_status'];
				
		
		$art_status	=	( intval($exit_status) % 2 == 0 ) ? intval($exit_status)+1 : intval($exit_status);
		
		if( $pos1 ){
			$retString	=	'Art  rollback completed successfully.';
			$art_status	=	$art_status;
			$status_r	=	'success';
		}
		if( $pos2 ){
			$retString	=	'Art rollback operation failed.';
			$art_status	=	$art_status;
			$status_r	=	'failed';
		}
		
		//after confirmation we need to enable this update
		
		$updateStatus = "update `taps_metadata` set meta_status = 1 , art_status = :art_status,art_reject_message=:art_reject_message where meta_value=:meta_value";
	    $stmt = $dbh->prepare($updateStatus);							 
	    $stmt->bindParam(':meta_value', $prep_meta );
		$stmt->bindParam(':art_reject_message', $retString);	  
		$stmt->bindParam(':art_status', $art_status);	  
		$stmt->execute();		
		
		echo $status_r;
		
		file_put_contents( "art_rollback_trace.txt" , PHP_EOL.' Art rollback magnus Response is : '.$status.PHP_EOL.$retString.PHP_EOL.str_repeat( '=' , 20 ).PHP_EOL  , FILE_APPEND );
				
	
	}
	
	
}


?>