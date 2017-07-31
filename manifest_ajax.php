<?php 
/**
* Project Name : TAPS
* PHP Version 5.4
* @author Dipen Baskaran <dipen.baskaran@spi-global.com>
* @author Shamu Shihabudeen <shamu.shihabudeen@spi-global.com>
*/
session_start();
ob_start();
include "config.php";
include("const_link.php");  // All Constants variables are included in this file
//Ram Code-Start
include ("articleinfoUtils.php");
include ("nusoap.php");
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
include_once('model/tapsprocesshandler.php');
//Ram Code-End
/**
* Initialize the variables .
* @param strings
*/

global $dbh;


$clientid 		    = $_REQUEST['clientid'];
$metaid   	        = $_REQUEST['mid'];
$emp_id             = $_SESSION['emp_id'];
$_SESSION['opened'] = "0";
$user_id            = $_SESSION['user_id'];
$journal_name       = $_REQUEST['jid'];
$article_name       = $_REQUEST['aid'];
$basefile_path      = TAPS.$_REQUEST['cname']."\\".$emp_id."\\".$_REQUEST['jid']."\\".$_REQUEST['aid'];
$explorer 			= $_ENV["SYSTEMROOT"] . '\\explorer.exe';

	$controller_query = "select * from taps_process_transaction where user_id=:user_id and process_id=2 and is_fileopen=1"; 
	$controller_stmt=$dbh->prepare($controller_query);
	//$controller_stmt->bindParam(":assign_id",$_REQUEST['assign_id']);
	$controller_stmt->bindParam(":user_id",$user_id);
	//$result1 = $dbh->query($sql1);
	$controller_stmt->execute();
	$controller = $controller_stmt->fetch(PDO::FETCH_ASSOC);
	
	if($controller['is_fileopen']==1){
		$is_3b2_running=0;
		exec('tasklist /fi "Imagename eq APP.exe"', $is_3b2_running);
		$count = count($is_3b2_running);
		if($count == 1){
			$sql1 = "update taps_process_transaction  set is_fileopen=0 where trans_id ='".$controller['trans_id']."'";
			$result1 = $dbh->query($sql1);
		}
	}
/**
Posting realtime messages of each process to activeMQ
**/
				
function forActiveMQ($jobName,$processId,$processStart,$processEnd,$processStatus,$processIteration){
return false;
if($_REQUEST['clientid'] == 1){
	$cname ="Elsevier";
}
else if($_REQUEST['clientid']== 2){
	$cname ="Springer";
}

$articleName = $_REQUEST['metaid_name'];
$uname = $_SESSION['uname'];

  
if($processId == 5){
	$process ="Spice";
}
else if ($processId == 2){
	$process ="FPP";
}
else if ($processId == 10){
	$process ="Dataset Creation";
}
else if ($processId == 11){
	$process ="Distiller/Pitstop";
}
		
				
	$msg ='{
						"article_id": "'.$_REQUEST['aid'].'", 
						"article_name": "'.$articleName.'", 
						"client_name": "'.$cname.'", 
						"jcode": "'.$_REQUEST['jid'].'", 
						 "jid": "'.$jobName.'",
						"process_name":"'.$process.'",
						"process_status":"'.$processStatus.'",
						"process_start_time":"'.$processStart.'",
						"process_end_time":"'.$processEnd.'",
						"username":"'.$uname.'",
						"user_emp_id":"'.$_SESSION['emp_id'].'",
						"iteration":"'.$processIteration.'"
			}';
		
		if($_REQUEST['aid'] !="" || $_REQUEST['jid'] != "" || $jobName != "" || $process !=""){
		 $url = ACTIVE_MQ_URL;
		 $ch = curl_init();
		 //set the url, number of POST vars, POST data
		 curl_setopt($ch,CURLOPT_URL,$url);
		 curl_setopt($ch, CURLOPT_USERPWD, ACTIVE_MQ_UN);
		 curl_setopt($ch,CURLOPT_POST,1);
		 curl_setopt($ch,CURLOPT_POSTFIELDS,$msg);
		 //return the transfer as a string 
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		 curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
		 $result = curl_exec($ch);
		/* if(!curl_exec($ch)){
			$result = die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		 }*/
		 }
		// $fileName = 'Log/activeMQ.log';
		// $myfile = file_put_contents($fileName, $msg.PHP_EOL , FILE_APPEND);
		//$fh = fopen($myFile, 'a') or die("can't open file");
		//fwrite($fh, $msg);
		// echo $msg;
		//print_r($result);
}

/**
* Condition for ELSEVIER CLIENT
* All stages file movement, database entries, File name formation are included in this condition 
*/


if($clientid == CLIENT_ELSEVIER) {
		
/**
* File Name Formations
* All Required files names are formed here. Example XML file, order file
*/
$fpp_htmlname         = $_REQUEST['metaid_name']."_FPPlog.html";
$fpp_metaxml          = $_REQUEST['metaid_name']."_meta.xml";
$validatelog_name     = $_REQUEST['metaid_name']."_validation.log";
$download_name        = $_REQUEST['metaid_name']."_Filehandler.log";
//$validatelog_name     = strtoupper($journal_name).$article_name."_validation.log";
//$download_name        = $journal_name.$article_name."_Filehandler.log";

$folder_to_create 	  = $basefile_path."\\FPP";
$file_to_copy    	   = $basefile_path."\\SPICE\\".$_REQUEST['metaid_name'].".xml";
$des_place  	  	  = $basefile_path."\\FPP\\".$_REQUEST['metaid_name'].".xml";
$file_to_copy_order   = $basefile_path."\\SPICE\\".$_REQUEST['metaid_name'].".order";
$des_place_order  	  = $basefile_path."\\FPP\\".$_REQUEST['metaid_name'].".order";
$des_place_open_spice = $basefile_path."\\SPICE";
$file_to_copy_log     = $basefile_path."\\SPICE\\".$fpp_htmlname;
$des_place_log  	  = $basefile_path."\\FPP\\".$fpp_htmlname;
$wordfile             = $des_place_open_spice."\\".$_REQUEST['metaid_name'].".docx";
		  
				$iteration_query = "SELECT ts.iteration FROM taps_assignment ts
				left outer join taps_metadata tm on tm.meta_id = ts.meta_id
				where assign_id = ".$_REQUEST['assign_id']; 
				$iteration_query_fetch = $dbh->query($iteration_query);
				$iteration_val = $iteration_query_fetch->fetch(PDO::FETCH_ASSOC);					
				
				
				/**
				* To Find the Current process.
				* To find the current process going on TAPS for each article based on the Assign ID. 
				* If the process status = 1 then its consider are current process
				*/
					
		       $stat = "SELECT * FROM taps_process_transaction tpt
				JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id
				JOIN taps_metadata tm ON tm.meta_id = ta.meta_id
				WHERE tpt.process_status = 1 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id']; 
				//file_put_contents("updateItemLog.txt",$stat."\n",FILE_APPEND);
				$status_result = $dbh->query($stat);
				$status = $status_result->fetch(PDO::FETCH_ASSOC);
				$status_message = "";
				
				
				if(!$status){
					
				$stat = "SELECT * FROM taps_process_transaction tpt
				JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id
				JOIN taps_metadata tm ON tm.meta_id = ta.meta_id
				WHERE tpt.process_status = 2 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id'].' order by trans_id desc limit 1'; 
				$status_result = $dbh->query($stat);
				$for_art_roll = $status_result->fetch(PDO::FETCH_ASSOC);
				
				
				$trans_id = $for_art_roll['trans_id'];
				
				$queryMq = "SELECT tp.job_name,t.process_id,min(ts.process_starttime) as starttime ,max(ts.process_endtime)as endtime ,t.process_status,t.iteration from taps_subprocess_transaction ts join taps_process_transaction t on ts.trans_id = t.trans_id  join taps_projects tp on tp.project_id = t.project_id where t.trans_id=".$trans_id; 
				$queryMq_query_fetch = $dbh->query($queryMq);
				$mqData = $queryMq_query_fetch->fetch(PDO::FETCH_ASSOC);	
				
				$jobName =$mqData['job_name'];
				$processId =$mqData['process_id'];
				$processStart = $mqData['starttime'];
				$processEnd = $mqData['endtime'];
				$processStatus = $mqData['process_status'];
				$processIteration = $mqData['iteration'];
				if( intval( $for_art_roll['process_id'] ) == SPICE && intval( $processStatus ) == 2 ){
					
					$returns		=	 $metaid."|spice|Art is rejected.|11";	
					$selecArtInfo 	= 	'select * from `taps_metadata` where meta_id	=	'.$metaid.' limit 1';
					$rset			=	$dbh->query( $selecArtInfo );			
					$output_row		=	$rset->fetch( PDO::FETCH_ASSOC );
					 $exit_status	=	$output_row['art_status'];					
					
					if( !(intval( $exit_status ) % 2 == 0 ) ){
						
						if( $exit_status == "1"){
							echo $metaid."|spice|Art is not yet completed.|11";
						}else{
							echo $returns;
						}
							
					}
		
					
				}
					die();	 
				}
				
				$trans_id = $status['trans_id'];
				$meta_id_check = $status['meta_status'];
				$art_check = $status['art_status'];
				$final_dest_path = $status['metaxml_path'];
				$final_dest_path_spice = $status['metadownload_path'];
				$nopdf = $status['pdf_free'];
				$articleNameArt = $status['article_name'];

					
				/**
				* File Download status Montior.
				* Status -->  0  =  Downloading the files from FTP 
				* Status -->  1  =  Download failed 
				* Status -->  2  =  Download success 
				*/
			 $queryMq = "SELECT tp.job_name,t.process_id,min(ts.process_starttime) as starttime ,max(ts.process_endtime)as endtime ,t.process_status,t.iteration from taps_subprocess_transaction ts join taps_process_transaction t on ts.trans_id = t.trans_id  join taps_projects tp on tp.project_id = t.project_id where t.trans_id=".$trans_id; 
				$queryMq_query_fetch = $dbh->query($queryMq);
				$mqData = $queryMq_query_fetch->fetch(PDO::FETCH_ASSOC);	
				
				$jobName =$mqData['job_name'];
				$processId =$mqData['process_id'];
				$processStart = $mqData['starttime'];
				$processEnd = $mqData['endtime'];
				$processStatus = $mqData['process_status'];
				$processIteration = $mqData['iteration'];
				//file_put_contents('update_item.txt', PHP_EOL.''.$processStatus.PHP_EOL,FILE_APPEND);
				if( $status['process_id'] == UPDATE_ITEM_PROCESS_ID && intval( $processStatus ) == 1 ){
					file_put_contents('update_item.txt', PHP_EOL.'Am failure'.PHP_EOL,FILE_APPEND);
					goto fromUpdateOnly;
					
				}
				if( $status['process_id'] == UPDATE_ITEM_PROCESS_ID && intval( $processStatus ) == 2 ){
					//goto fromUpdateOnly;
					file_put_contents('update_item.txt', PHP_EOL.'Am success :'.$_REQUEST['metaid_name'].PHP_EOL,FILE_APPEND);
				
						$updateItemquery = "update taps_metadata set meta_status=1 where article_name=:article_name";
						$updateItemfetch = $dbh->prepare($updateItemquery);
						$updateItemfetch->bindParam(":article_name", $_REQUEST['metaid_name']);
						$updateItemfetch->execute();
						
				}
				
				if ($status['process_id'] == DOWNLOAD )
				{
						if ($status['isfile_downloaded'] == "0")
						{
						$status_message = $metaid . "|dnload|Downloading files..|1";
						
						 $parser_read = "Log/".$download_name;
							  
							  if (file_exists($parser_read)) {					  
							   
						      $input = @file_get_contents($parser_read);
				     		  preg_match_all('/<status>(.*?)<\/status>/s', $input, $matches);              
										
										if($matches[1][0] == "true")
										{
										$isfile_downloaded = "2";
										}
										else if($matches[1][0] == "false")
										{
										 $isfile_downloaded = "1";
										}
							   preg_match_all('/<error_message>(.*?)<\/error_message>/s', $input, $matches_completion);  
                                preg_match_all('/<end_time>(.*?)<\/end_time>/s', $input, $matches_endtime); 							   
                                
							   $process_completion = $matches_completion[1][0];						   
							   
							   
										$downloadend_time = $matches_endtime[1][0];
										
									$insert = "update taps_process_transaction set isfile_downloaded=:isfile_downloaded,process_endtime = :process_endtime,process_completion =:process_completion where trans_id=:trans_id";
					    $stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
					$stmt->bindParam(':isfile_downloaded', $isfile_downloaded);
					$stmt->bindParam(':process_completion', $process_completion);
					$stmt->bindParam(':process_endtime', $downloadend_time);
					$stmt->bindParam(':trans_id', $trans_id);
					$stmt->execute();
					
					 
			         
					 $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id";
					    $stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
					$stmt->bindParam(':process_endtime', $downloadend_time);
					$stmt->bindParam(':trans_id', $trans_id);
					$stmt->execute();
					
					
						
						
						
						
						}
						}
						  else
						{
								if ($status['isfile_downloaded'] == "1")
								{
								$status_message = $metaid . "|dnload|Download failed (" . $status['process_completion'] . ")|2";
								}
								
								if ($status['isfile_downloaded'] == "2")
								{
								$process_status = 2;
								$insert = "update taps_process_transaction set process_status=:process_status where trans_id='" . $status['trans_id'] . "'";
								$stmt = $dbh->prepare($insert);
								
								// Bind parameters to statement variables
								
								$stmt->bindParam(':process_status', $process_status);
								$stmt->execute();
								$status_message = $metaid . "|dnload|Download successful|3";
								}
						}
				
				echo $status_message;
				}
				
				
	
				/**
				* SPCIE status Montior.
				* XML Validate status -->  0  =  XML validation in progress
				* XML Validate status -->  1  =  XML validation failed
				* XML Validate status -->  2  =  XML validation success
				* XML Validate status -->  4  =  XML validation success
				* Metadata status     -->  3  =  Article Rejected from SPiCE and return to WMS
				* Art status          -->  0  =  Art Signal is not yet received
				* Art status          -->  1  =  Waiting for Art confirmation whether its completed or not.
				* Art status          -->  2  =  Art is completed
				*/						
					
				if($status['process_id'] == SPICE)
					  {
						//file_put_contents("updateItemLog.txt","XML Validate:".$status['xml_validate']."\n",FILE_APPEND);
						//file_put_contents("updateItemLog.txt","Art status:".$status['art_status']."\n",FILE_APPEND);
					   $download_log = "Log/".$download_name;  // Added by sanjeevi
					    if (file_exists($download_log)) {	
					   unlink($download_log); // Added by sanjeevi
					   }
						  //exit;
						   $vtool = 0;
						   if($meta_id_check == "3")
						   {
									echo $status_message = $metaid."|reject|reject from spice|1";	
									  $is_fileopen = 0;
									  $insert = "update taps_process_transaction set is_fileopen=:is_fileopen where trans_id=:trans_id";
									  $stmt = $dbh->prepare($insert);
									  $stmt->bindParam(':is_fileopen', $is_fileopen);
									  $stmt->bindParam(':trans_id', $status['trans_id']);
									  $stmt->execute();
						   exit;
						   }
						   if ($art_check % 2 != 0 && $art_check>2) {
						   echo $status_message = $metaid."|artreject|Art is rejected.|1";	
						   exit;
							}
							 if($status['isrejected'] == 1 ){
								echo $metaid."|spice|Figure Mismatch|1";
								exit;
							  }
							  if($status['xml_validate'] == 0 )  //  XML validation in progress
							  {
							  //Sending message to activeMQ - spice start
								forActiveMQ($jobName,$processId,$processStart,$processEnd,"In-progress",$processIteration);  
							  $status_message = $metaid."|spice|Validating XML!|1";
							  $parser_read = "Log/".$validatelog_name;
							  if (file_exists($parser_read)) {					  
									  $input = @file_get_contents($parser_read);
									  preg_match_all('/<XML_Validity>(.*?)<\/XML_Validity>/s', $input, $matches);              
										if($matches[1][0] == "true")
										{
										$xmlvalue = "2";
										}
										else if($matches[1][0] == "false")
										{
										$xmlvalue = "1";
										}
										$insert = "update taps_process_transaction set xml_validate=:xml_validate where trans_id=:trans_id";
										$stmt = $dbh->prepare($insert);
													// Bind parameters to statement variables
									$stmt->bindParam(':xml_validate', $xmlvalue);
									$stmt->bindParam(':trans_id', $trans_id);
									$stmt->execute();
									 $downloadend_time = date('Y-m-d H:i:s');
									 $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id";
										$stmt = $dbh->prepare($insert);
													// Bind parameters to statement variables
									$stmt->bindParam(':process_endtime', $downloadend_time);
									$stmt->bindParam(':trans_id', $trans_id);
									$stmt->execute();
									//unlink($parser_read);
												}
											  }else{ //Added by sanjeevi 
									$parser_read = "Log/".$validatelog_name;
									  if (file_exists($parser_read)) {		
									  unlink($parser_read);
									  }
							  }
						  if($status['xml_validate'] == 4 && $status['is_completed'] == 1)  //  SPiCE tool triggered
							  {
								  $status_message = $metaid."|spice|SPiCE tool triggered!|1";						
							  }
						  if($status['xml_validate'] == 1 && $status['is_completed'] == 2 && ( $status['sub_process_name'] == "PAUSE" || $status['sub_process_name'] == "ONHOLD"  ))  
							  {
								  if($status['sub_process_name'] == "PAUSE")
								  {
								$status_message = $metaid."|spice|XML validation error. Article has PAUSED|3";
								 }
								 if($status['sub_process_name'] == "ONHOLD")
								  {
								$status_message = $metaid."|spice|XML validation error. Article is on HOLD|3";
								 }
							  }
							  if($status['xml_validate'] == 4 && $status['is_completed'] == 2 && ( $status['sub_process_name'] == "PAUSE" || $status['sub_process_name'] == "ONHOLD"  ))  // process_status -- 2 is completed
							  {
								  if($status['sub_process_name'] == "PAUSE")
								  {
								$status_message = $metaid."|spice|Article has PAUSED|13";
								 }
								 if($status['sub_process_name'] == "ONHOLD")
								  {
								$status_message = $metaid."|spice|Article is on HOLD|13";
								 }
							  }
						  if($status['xml_validate'] == 1 && $status['is_completed'] == 1)  // XML Validate is failed
							  {
						  //sleep(5);
							  $stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id WHERE tpt.process_status = 1 AND tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1"; 								
						  $status_result_ar = $dbh->query($stat_ar);
						  $status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
						  if($status_ar['process_id'] == "")
						  {
								  $status_message = $metaid."|spice|XML validation error...|3";
								      $process_time   = date('Y-m-d H:i:s');
                                                                        $sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where trans_id=:trans_id";
                                                                            $stmt = $dbh->prepare($sub_proc_get);
                                                                             $stmt->bindParam(':trans_id', $status['trans_id']);
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
									  //shell_exec("start /min $explorer /n,/e,$des_place_open_spice");
									  $output = shell_exec('start /min winword '.$wordfile);
									  $status_2 = 2;
									  $is_fileopen = 1;
									  $insert = "update taps_process_transaction set is_completed=:is_completed,is_fileopen=:is_fileopen where trans_id=:trans_id";
									  $stmt = $dbh->prepare($insert);
										  // Bind parameters to statement variables
										  $stmt->bindParam(':is_completed', $status_2);
										  $stmt->bindParam(':is_fileopen', $is_fileopen);
										  $stmt->bindParam(':trans_id', $status['trans_id']);
										  $stmt->execute();
							  }
							  else
							  {
								  $status_message = $metaid."|spice|XML validation error, Queued for SPiCE process...|3";
							  }
							  }
							  if($status['xml_validate'] == 2 )  // process_status -- 2 is completed
							  {
								if($status['art_status'] == "0")
								  {
									  $status_message = $metaid."|spice|Waiting for Art Status|10";
								  }
								  if($status['art_status'] == "1")
								  {
									 $status_message = $metaid."|spice|Art is not yet completed|11";
									 $artsignal_file=$des_place_open_spice."\\".$_REQUEST['metaid_name']."_images.xml";
									if (file_exists($artsignal_file)) {					  
									  $input = @file_get_contents($artsignal_file);
									  preg_match_all('/<count>(.*?)<\/count>/s', $input, $matches);              
										  if($matches[1][0] == "0"){
											$insert = "update taps_metadata set art_status=2 where meta_id=:meta_id";
											$stmt = $dbh->prepare($insert);
											$stmt->bindParam(':meta_id', $metaid);
											$stmt->execute();
											unlink($artsignal_file);
										}
										
									}
									
								  }
								  /**
								  * $status['art_status'] is modified to continue the process on event of art got rejected earlier
								  * Bug closed - Sanjeevi 5/15/2015
								  */
								 
								  if($status['art_status'] %2 == 0 && $nopdf == 0)
								  {
									  usleep(4000000);	
									//  $html_preview = shell_exec(HTML_SPICE.$file_to_copy);
									  
									  
									
										  
									  if (!file_exists($folder_to_create)) {
										  mkdir($folder_to_create, 0777, true);
										  }
									  function copy_file($src,$dst) {
										 copy($src, $dst);
									  }  
									  copy_file($file_to_copy,$des_place);
									  copy_file($file_to_copy_order,$des_place_order);
									  copy_file($file_to_copy_log,$des_place_log);
									  
									  
									  $cpdata = '<?xml version="1.0"?>
									  <Metadata>
									  <account>'.$_REQUEST['cname'].'</account>
									  <ArticleID>'.$_REQUEST['jid'].'</ArticleID>
									  <JournalName>'.$_REQUEST['aid'].'</JournalName>
									  <Current_User>'.$_SESSION['emp_id'].'</Current_User>
									  <Actual_Name>AIO</Actual_Name>
									  <ArtLocation>\\xxxx\xxxx\xxx\</ArtLocation>
									  <Stage>100</Stage>
									  <model>Gulliver5</model>
									  </Metadata>';
									  
									  
										  $filepath = MPATH;
										   
										  $fh = fopen($filepath.$fpp_metaxml, 'w') or die("can't open file");
										  fwrite($fh, $cpdata);
									  $process_status = 2;
					 				  $insert = "update taps_process_transaction set process_status=:process_status where trans_id='".$status['trans_id']."'";
									  $stmt = $dbh->prepare($insert);
		
									  // Bind parameters to statement variables
									
									  $stmt->bindParam(':process_status', $process_status);
									  $stmt->execute();
									
		  	//Sending message to activeMQ - spice end
								
								forActiveMQ($jobName,$processId,$processStart,$processEnd,"completed",$processIteration);  
		  
		  
		  //Insert next process FPP 
		  
			// Prepare INSERT statement to SQLite3 file db
								  
								  $process_id_new = "2";
								  $status = "1";
								  $status_2 = "1";	
								  $job_id = $_REQUEST['job_id'];
								  $assign_id = $_REQUEST['assign_id'];
								  $time = date("Y-m-d H:i:s");
								  $iteration =  $iteration_val['iteration'];
								  
								  $sql1 = "select * from taps_process_transaction where process_id='".$process_id."' and assign_id ='".$assign_id."' and user_id ='".$user_id."' ";
								  $result1 = $dbh->query($sql1);
								  $t1 = $result1->fetch(PDO::FETCH_ASSOC);
								  if($t1['process_id'] == "")
								  {
								  $insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, isfile_downloaded, downloaded_time, process_starttime, process_endtime, process_status, is_completed, completed_time,iteration) VALUES (:user_id, :process_id,  :project_id, :assign_id, :isfile_downloaded, :downloaded_time, :process_starttime, :process_endtime, :process_status, :is_completed, :completed_time,:iteration)";
								  $stmt = $dbh->prepare($insert);
		
								  // Bind parameters to statement variables
								  $stmt->bindParam(':user_id', $user_id);
								  $stmt->bindParam(':process_id', $process_id_new);
								  $stmt->bindParam(':project_id', $job_id);
								  $stmt->bindParam(':assign_id', $assign_id);
								  $stmt->bindParam(':downloaded_time', $time);
								  $stmt->bindParam(':process_starttime', $time);
								  $stmt->bindParam(':process_endtime', $time);
								  $stmt->bindParam(':process_status', $status);
								  $stmt->bindParam(':is_completed', $status_2);
								  $stmt->bindParam(':completed_time', $time); 
								   $stmt->bindParam(':iteration', $iteration); 
								
								
								   $stmt->execute();
		   
		   
		   // Files are moving to WMS for file backup;
		   
							   $article_name = $_REQUEST['jid']."_".$_REQUEST['aid'];
							  
		
		
						 $final_dest_path_spice = PRODUCTION_FTP_PATH."COPYEDITING/2_PRE-EDITING/OUT/".$_REQUEST['jid']."/".$article_name; 
						  
					  $WshShell = new COM("WScript.Shell");	
					  $oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$des_place_open_spice.'" dest_path="'.$final_dest_path_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain=""', 0, false);
					  
		
		   
						exec('tasklist /fi "Imagename eq TAPS_App.exe"', $fpprunning);
		
						  $count = count($fpprunning);
                    if ($count == 1) {
							   $WshShell = new COM("WScript.Shell");
							  $oExec = $WshShell->Run('cmd /C '.FPP_SERVICE, 0, false);	  
						  }
		   
						if($stmt->errorCode() == 0) {
							$processid= $dbh->lastInsertId();
							
							 
						}
						 else {
							  $errors = $stmt->errorInfo();
							 // echo "failed|".$errors[2];
						}
								  }
									  
									  
									  exec('tasklist /fi "Imagename eq TAPS_App.exe"', $fpprunning);
		
									  $count = count($fpprunning);
                if ($count == 1) {
										   $WshShell = new COM("WScript.Shell");
										  $oExec = $WshShell->Run('cmd /C '.FPP_SERVICE, 0, false);
                }

            /*    //Update Item Code start - Ram
				$emp_id = $_SESSION['emp_id'];
                $iteration_query = "SELECT * from taps_metadata where article_name=:article_name";
                $meta_query_fetch = $dbh->prepare($iteration_query);
                $meta_query_fetch->bindParam(":article_name", $articleNameArt);
                $meta_query_fetch->execute();
                $meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

                $clientid = $meta_val['meta_client_id'];
                $project_id = $meta_val['project_id'];
                $articleValue = $meta_val['article_id'];
                $article_name = $articleNameArt . ".pdf";
                $journal_name = $meta_val['meta_id'];
//    echo "Project id:" . $project_id;
                $iterationCount = $meta_val['iteration'];

                $iteration_query1 = "SELECT * from taps_clients where client_id=:clientid";
                $meta_query_fetch1 = $dbh->prepare($iteration_query1);
                $meta_query_fetch1->bindParam(":clientid", $clientid);
                $meta_query_fetch1->execute();
                $meta_val1 = $meta_query_fetch1->fetch(PDO::FETCH_ASSOC);
                $clientName = $meta_val1['client_name'];

                $iteration_query2 = "SELECT * from taps_projects where project_id=:project_id";
                $meta_query_fetch2 = $dbh->prepare($iteration_query2);
                $meta_query_fetch2->bindParam(":project_id", $project_id);
                $meta_query_fetch2->execute();
                $meta_val2 = $meta_query_fetch2->fetch(PDO::FETCH_ASSOC);
                //$projectName = $meta_val2['job_name'];
                $projectName = $meta_val2['journal_id'];
                if ($iterationCount == 1) {
                    $tempArticleName = $projectName ."_". $articleValue . ".order";
                    $getfilename = "D:\\TAPS\\" . $clientName . "\\" . $emp_id . "\\" . $projectName . "\\" . $articleValue . "\\SPICE\\" . $tempArticleName;
                    $metaFile = file_get_contents($getfilename); // TEST METAFILE 
                    file_put_contents("updateItemLog.txt", $getfilename . "\n", FILE_APPEND);
                    file_put_contents("updateItemLog.txt", $metaFile . "\n", FILE_APPEND);
                    file_put_contents("updateItemLog.txt", "-------------------\n", FILE_APPEND);


                    $wsdl = PSTVTWINOVER_SOAP_SERVER_PATH; // WEB SERVICE PATH

                    $options = array('exceptions' => true, 'trace' => true);
                    $client = new SoapClient($wsdl);
					file_put_contents("updateItemLog.txt", $client."\n----------------------", FILE_APPEND);
                    $result = $client->performUpdateItemCall(array('articleMetadata' => $metaFile));
					file_put_contents("updateItemLog.txt", $result."\n-----------------------", FILE_APPEND);
                    $resdata = $result->performUpdateItemCallResult;
					file_put_contents("updateItemLog.txt", $resdata."\n----------------------", FILE_APPEND);
                    if (!empty($resdata)) {
                        $fwirtePath = "D:\\TAPS\\" . $clientName . "\\" . $emp_id . "\\" . $projectName . "\\" . $articleValue . "\\SPICE\\" . $projectName . $articleValue . "_updateitem.xml";
//                    $fwirtePath = FILE_SERVER_DIR . 'UPDATE_ITEM/' . $details['jidcode'] . '/' . $jid_aid . '/';
//                    $updatItemfile = $jid_aid . '_updateitem.xml';
                       // $fastCopy = new fastCopy();
                       // $fastCopy->ftp_make_dir($fwirtePath);


                        $fastCopy = new fastCopy();
                        $res = $fastCopy->ftp_file_put($fwirtePath, $resdata);
                    }
                }
                //Update Item Code end - Ram*/
								  $status_message = $metaid."|fpp|Queued for Pagination Process...|1";	
									  
								  
							  }
								  if($status['art_status'] %2 == 0 && $nopdf == 1)
								  {
								  	//spice file movement for pdfless ///samu///
										$article_name = $_REQUEST['jid']."_".$_REQUEST['aid'];
									  $final_dest_path_spice = PRODUCTION_FTP_PATH."COPYEDITING/2_PRE-EDITING/OUT/".$_REQUEST['jid']."/".$article_name;  
									   
									  $WshShell = new COM("WScript.Shell");	
									  $oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$des_place_open_spice.'" dest_path="'.$final_dest_path_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain=""', 0, false);
									  
									  sleep(5);
									  
									  $article_name = $_REQUEST['jid']."_".$_REQUEST['aid'];
									  $final_dest_path_spice = PRODUCTION_FTP_PATH."COPYEDITING/2_PRE-EDITING/IN/".$_REQUEST['jid']."/".$article_name;  
										  
									  $WshShell = new COM("WScript.Shell");	
									  $oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$des_place_open_spice.'" dest_path="'.$final_dest_path_spice.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain=""', 0, false);
									 sleep(10);
								/////samu codes ends here////
								  
								  
								  $process_status = 2;
					 				  $insert = "update taps_process_transaction set process_status=:process_status where trans_id='".$status['trans_id']."'";
									  $stmt = $dbh->prepare($insert);
		
									  // Bind parameters to statement variables
									
									  $stmt->bindParam(':process_status', $process_status);
									  $stmt->execute();
                //Update Item Code start - Ram
				$emp_id = $_SESSION['emp_id'];
                $iteration_query = "SELECT * from taps_metadata where article_name=:article_name";
                $meta_query_fetch = $dbh->prepare($iteration_query);
                $meta_query_fetch->bindParam(":article_name", $articleNameArt);
                $meta_query_fetch->execute();
                $meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);

                $clientid = $meta_val['meta_client_id'];
                $project_id = $meta_val['project_id'];
                $articleValue = $meta_val['article_id'];
                $article_name = $articleNameArt . ".pdf";
                $journal_name = $meta_val['meta_id'];
//    echo "Project id:" . $project_id;
                $iterationCount = $meta_val['iteration'];

                $iteration_query1 = "SELECT * from taps_clients where client_id=:clientid";
                $meta_query_fetch1 = $dbh->prepare($iteration_query1);
                $meta_query_fetch1->bindParam(":clientid", $clientid);
                $meta_query_fetch1->execute();
                $meta_val1 = $meta_query_fetch1->fetch(PDO::FETCH_ASSOC);
                $clientName = $meta_val1['client_name'];

                $iteration_query2 = "SELECT * from taps_projects where project_id=:project_id";
                $meta_query_fetch2 = $dbh->prepare($iteration_query2);
                $meta_query_fetch2->bindParam(":project_id", $project_id);
                $meta_query_fetch2->execute();
                $meta_val2 = $meta_query_fetch2->fetch(PDO::FETCH_ASSOC);
                //$projectName = $meta_val2['job_name'];
				$projectName = $meta_val2['journal_id'];
				//Code Ram - GCNS Update - Start
				$journalAcr = $projectName;
				$articleId = $projectName ."_". $articleValue;
					$data = array(    
						"journal_acronym"   => $journalAcr,
						"article_id"        => $articleId,
						"process"       	=> 'gcnsupdate',
						"pdfless" 			=> '1'
					);
					file_put_contents('gcns_update.txt',"\n".$data."\n",FILE_APPEND);	
					$url = ELSEVIER_METADATA_INVOKER;
					//$url = ELSEVIER_METADATA_INVOKER_TEST;
					$content = json_encode($data);
					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
					$json_response = curl_exec($curl);
					$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);
					file_put_contents('gcns_update.txt',"\nResponse:".$json_response."\n",FILE_APPEND);	
					if(strpos($json_response,'Failure')!==false){
					file_put_contents('gcns_update.txt',"\nIn Failed\n",FILE_APPEND);	
					$gcnsUpdate = "update taps_metadata set meta_status=26 where article_name=:article_name";
					$gcnsItemfetch = $dbh->prepare($gcnsUpdate);
                    $gcnsItemfetch->bindParam(":article_name", $articleNameArt);
                    $gcnsItemfetch->execute();
					break;
					}	
		//Code Ram - GCNS Update - End
		
		if( false ){
			fromUpdateOnly:
			$projectName	=	$jAcronym	=	$_REQUEST['jid'];			
		}
			$articleId	=	$_REQUEST['metaid_name'];
			$article_id	=	$articleId;
			
			//file_put_contents('update_item.txt', 'INPUT ARTICLEID: '.$articleId,FILE_APPEND);	
			//file_put_contents('update_item.txt', 'INPUT ARTICLE_ID: '.$article_id,FILE_APPEND);	
			
		//Ananth Code for checking failure and make update item call Start
			$openforUpdateItemFailure	=	false;
			$getupdateItemInfo_query = "SELECT * from taps_metadata where article_name=:article_name";
			$meta_query_fetch = $dbh->prepare($getupdateItemInfo_query);
			$meta_query_fetch->bindParam(":article_name", $articleId);
			$meta_query_fetch->execute();
			$meta_val = $meta_query_fetch->fetch(PDO::FETCH_ASSOC);
			//if( $meta_val['pc_remarks'] == '44' || $meta_val['pc_remarks'] == 44 ){
			//	 $openforUpdateItemFailure = true;
			//}			
		//Ananth Code for checking failure and make update item call End
		
			
				$updateItemquery = "update taps_metadata set meta_status=45 where article_name=:article_name";
				$updateItemfetch = $dbh->prepare($updateItemquery);
				$updateItemfetch->bindParam(":article_name", $articleId);
				//$updateItemfetch->execute();				
			
			
			
			

				//ANANTH B ->  TRANSACTION HANDLING - UPDATE ITEM
				//INSERT TRANSACTION TABLE
				//*************************************************************
			
				$transaction_obj = new TapsProcessHandler();
				$getRow	=	$transaction_obj->getArticleInfor( $articleId , UPDATE_ITEM_PROCESS_ID  ); 
				if( empty( $getRow ) ){
					//$transaction_obj->insertProcessTransTable( $articleId , UPDATE_ITEM_PROCESS_ID );			
					//$transaction_obj->insertSubProcessTransTable( $articleId  , UPDATE_ITEM_PROCESS_ID , 'Update Item' );	
				}
				
				//*************************************************************
				//END TRANSACTION INSERT
					
			
			//sleep(10);
			
			if( false ){
			
				//Trigger live checking wheather this is article`s Update Item completed
				$jAcronym = $projectName;				
				$data = array(    
					"journal_acronym"   => $jAcronym,
					"article_id"        => $articleId,
					"process"           => 'updateitemstatus'
				);				
				file_put_contents( 'UpdateItem_live_check.txt'  , PHP_EOL.' before_requst :  '.json_encode($data ).PHP_EOL , FILE_APPEND );
					
				
					$url = ELSEVIER_METADATA_INVOKER;
					$content = json_encode($data);
					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
					$json_response = curl_exec($curl);
					$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);
					file_put_contents( 'UpdateItem_live_check.txt'  , PHP_EOL.json_encode($data ).' : '.$json_response.PHP_EOL , FILE_APPEND );
					if( $json_response == 1 || $json_response == '1' ){
						$openforUpdateItemFailure = false;
					}
					else if( $json_response == '' ||  $json_response == 0 ||  $json_response == '0' ){
						$openforUpdateItemFailure = true;					
					}
					else{ 
						$openforUpdateItemFailure = true;
					}				
			
			}
			
			file_put_contents( 'UpdateItem_live_check.txt'  , PHP_EOL.' PrintFLAG : '.$json_response.PHP_EOL , FILE_APPEND );
					
			if ( $openforUpdateItemFailure ) {	
			
				ini_set('max_execution_time', 0);
				$article_id=$articleId;
				$jAcronym = $projectName;
				
				// Update item CURL execute
				
				$updateItemquery = "update taps_metadata set meta_status=24 where article_name=:article_name";
				$updateItemfetch = $dbh->prepare($updateItemquery);
				$updateItemfetch->bindParam(":article_name", $articleId );
				//$updateItemfetch->execute();				
				
				sleep(3);
				$data = array(    
					"journal_acronym"   => $jAcronym,
					"article_id"        => $articleId,
					"process"           => 'update_item'
				);
					
					file_put_contents('update_item.txt',$data,FILE_APPEND);	

					$url = ELSEVIER_METADATA_INVOKER;
					//$url = ELSEVIER_METADATA_INVOKER_TEST;
					$content = json_encode($data);
					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type=> application/json"));
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
					$json_response = curl_exec($curl);
					$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
					curl_close($curl);
					
					file_put_contents('update_item.txt', PHP_EOL.'Resp:'.$json_response.PHP_EOL.json_encode( $data ).PHP_EOL,FILE_APPEND);	
				
				//Ananth Code change for update item failure
				
				
				
				if( $json_response == 1 || $json_response == '1' || $json_response == true ){
				
					$updateItemquery = "update taps_metadata set meta_status=25,pc_remarks='' where article_name=:article_name";
					$updateItemfetch = $dbh->prepare($updateItemquery);
					$updateItemfetch->bindParam(":article_name", $articleId);
					$updateItemfetch->execute();
					
					sleep(2);	
					
					$updateItemquery = "update taps_metadata set meta_status=1 where article_name=:article_name";
					$updateItemfetch = $dbh->prepare($updateItemquery);
					$updateItemfetch->bindParam(":article_name", $articleId);
					$updateItemfetch->execute();
					
					//ANANTH B ->  TRANSACTION HANDLING - UPDATE ITEM
					//UPDATE ON TRANSACTION TABLE
					//*************************************************************
				
					$article_info = $transaction_obj->getArticleInfor( $articleId , UPDATE_ITEM_PROCESS_ID );
					$transac_id = $article_info['trans_id'];
					$time = date('Y-m-d H:i:s');
					$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 2 );
					$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
					$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $articleId ) ;
					$sub_trans_id = $records_sub['sub_trans_id'];
					$input_sub_trans = array(  'process_status' => 2 , 'process_endtime' => date('Y-m-d H:i:s') , 
					'process_comments' => 'Update Item Completed', 'sub_trans_id' => $sub_trans_id );
					$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
					
					//*************************************************************
					//END TRANSACTION UPDATE
					
					
				}else{
				
				
					$updateItemquery = "update taps_metadata set meta_status=44,pc_remarks=44 where article_name=:article_name";
					$updateItemfetch = $dbh->prepare($updateItemquery);
					$updateItemfetch->bindParam(":article_name", $articleId);
					$updateItemfetch->execute();
					
					//ANANTH B ->  TRANSACTION HANDLING -  UPDATE ITEM
					//UPDATE ON TRANSACTION TABLE
					//*************************************************************
				
					$article_info = $transaction_obj->getArticleInfor( $article_id , UPDATE_ITEM_PROCESS_ID );
					$transac_id = $article_info['trans_id'];
					$time = date('Y-m-d H:i:s');
					$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 3 );
					$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
					$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $article_id ) ;
					$sub_trans_id = $records_sub['sub_trans_id'];
					$input_sub_trans = array(  'process_status' => 3 , 'process_endtime' => date('Y-m-d H:i:s') , 
					'process_comments' => 'Update Item Failed', 'sub_trans_id' => $sub_trans_id );
					$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
					
					//*************************************************************
					//END TRANSACTION UPDATE
					
				break;
					
				}
				//Ananth Code change end for update item failure
				
				
				}else{
			
				//file_put_contents('update_item.txt', 'DDASDASEFOR : '.$articleId,FILE_APPEND);	
				//file_put_contents('update_item.txt', 'INPUT ARTICLE_ID: '.$article_id,FILE_APPEND);	
					
				    $updateItemquery = "update taps_metadata set meta_status=1 where article_name=:article_name";
					$updateItemfetch = $dbh->prepare($updateItemquery);
					$updateItemfetch->bindParam(":article_name", $articleId);
					//$updateItemfetch->execute();
					
					
					$transaction_obj = new TapsProcessHandler();
					$article_info = $transaction_obj->getArticleInfor( $articleId , UPDATE_ITEM_PROCESS_ID );
					$process_id = $article_info['process_id'];
					
					if( $process_id == UPDATE_ITEM_PROCESS_ID ){
					
						//Already done Update Item
						$updateItemquery = "update taps_metadata set meta_status=1 where article_name=:article_name";
						$updateItemfetch = $dbh->prepare($updateItemquery);
						$updateItemfetch->bindParam(":article_name", $articleId);
						$updateItemfetch->execute();
						
						
					//ANANTH B ->  TRANSACTION HANDLING - UPDATE ITEM
					//UPDATE ON TRANSACTION TABLE
					//*************************************************************
				
					$article_info = $transaction_obj->getArticleInfor( $article_id , UPDATE_ITEM_PROCESS_ID );
					$transac_id = $article_info['trans_id'];
					$time = date('Y-m-d H:i:s');
					$update_array	=	array( 'process_endtime'=> $time  , 'process_status' => 2 );
					$transaction_obj->updateProcessTransactionTable( $update_array  ,  $transac_id );
					$records_sub	= $transaction_obj->getArticleSubProcessTransaction( $article_id ) ;
					$sub_trans_id = $records_sub['sub_trans_id'];
					$input_sub_trans = array(  'process_status' => 2 , 'process_endtime' => date('Y-m-d H:i:s') , 
					'process_comments' => 'Update Item Completed', 'sub_trans_id' => $sub_trans_id );
					$transaction_obj->updateSubProcessTransactionTable( $input_sub_trans );
					
					//*************************************************************
					}
					
				}
				//Update Item Code end - Ram
				$status_message = $metaid."|nopdf|Process completed successfully|1";
				}
			}
			echo $status_message;
		}
				
					
					
						    /**
							* 3B2 status Montior.
							* Metadata status     -->  3  =  Article Rejected from SPiCE and return to WMS
							* Art status          -->  0  =  Art Signal is not yet received
							* Art status          -->  1  =  Waiting for Art confirmation whether its completed or not.
							* Art status          -->  2  =  Art is completed
							*/	
							
				if($nopdf == 0) {	
						if($status['process_id'] == FPP)   // FPP process status showing
						{
											
							 if($meta_id_check == "3")
							 {
							 
							 echo $status_message = $metaid."|reject|reject from spice|1";	
							 
							 exit;
							 
							 }
							 
							  if ($art_check % 2 != 0 && $art_check>2) {
							
							 echo $status_message = $metaid."|artreject|Art is rejected.|1";	
							 
							 exit;
							
								}
							
						$sql1 = "select * from taps_process_transaction where trans_id ='".$status['trans_id']."'";
									$result1 = $dbh->query($sql1);
									$t1 = $result1->fetch(PDO::FETCH_ASSOC);
									$result1->execute();
									
									$sub_process_name = $t1['sub_process_name'];
									$process_completion = $t1['process_completion'];
									/*if($t1['is_fileopen']==1){
									$is_3b2_running=0;
										exec('tasklist /fi "Imagename eq APP.exe"', $is_3b2_running);
										$count = count($is_3b2_running);
										if($count == 1){
											$sql1 = "update taps_process_transaction  set is_fileopen=0 where trans_id ='".$status['trans_id']."'";
											$result1 = $dbh->query($sql1);
										}
									}*/
									
									if($sub_process_name == "")
									{
									exec('tasklist /fi "Imagename eq TAPS_App.exe"', $fpprunning);
									  $count = count($fpprunning);
									  if($count == 1)
									  {
										   $WshShell = new COM("WScript.Shell");
										  $oExec = $WshShell->Run('cmd /C '.FPP_SERVICE, 0, false);
			
									  }
		  
									$status_message = $metaid."|fpp|Queued for Pagination Process...|3";	
									
									}
									else
									{

									if($sub_process_name == "Changes" || $sub_process_name == "NoChanges")
									{
									$status_message = $metaid."|fpp|".$sub_process_name." are made in 3B2! |3";
									}
									
									if($sub_process_name == "PAUSE")
									{
									$status_message = $metaid."|fpp|Article is ".$sub_process_name."|3";
									}
									
									if($sub_process_name == "RESUME")
									{
									$status_message = $metaid."|fpp|Article is ".$sub_process_name."|3";
									}
									if($sub_process_name != "Changes")
									{
									$status_message = $metaid."|fpp|".$sub_process_name."... (".$process_completion."% completed)|3";
									}
									}
									if($process_completion == "15")
									{
										$processEnd = "";
									  //Sending message to activeMQ - fpp start
										forActiveMQ($jobName,$processId,$processStart,$processEnd,"In-progress",$processIteration);
									}
									if($process_completion == "100")
									{
								
										 // $ps_name = strtoupper($_REQUEST['jid'])."_".$_REQUEST['aid'];
										  
										  if (file_exists($des_place)) {		  
									   
									  $input = @file_get_contents($des_place);
									  preg_match_all('/<jid>(.*?)<\/jid>/s', $input, $matches_jid); 
									  preg_match_all('/<aid>(.*?)<\/aid>/s', $input, $matches_aid);
									  
									  $ps_name = $matches_jid[1][0]."_".$matches_aid[1][0];	 
									  
									  
									  }
									 
										  $manifest_filename = $ps_name.".json";
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
										
										
					
							
										$timestamp = time();
											$datetime = date('Y-m-d H:i:s');
						
										$cpdata = '{"distiller":{"timestamp":"'.$timestamp.'","process":"Distiller","article_id":"'.$_REQUEST['aid'].'","process_type":"AUTO","process_time":"'.$datetime.'","journal_id":"'.$jidcaps.'","process_status":"0","artfile_status":"","sub_process_name":""}}';								
										
											
											$myFile = $fppmanifest_path.$manifest_filename;
											$fh = fopen($myFile, 'w') or die("can't open file");
											fwrite($fh, $cpdata);
											
											
							
								$WshShell = new COM("WScript.Shell");
								$oExec = $WshShell->Run('cmd /C java -jar TAPSCommunicator.jar -mf_path="'.$myFile.'"', 0, false);
							
							
										
										$process_status = 2;
										
										//echo $insert;
										
										 $insert = "update taps_process_transaction set process_status=:process_status where trans_id='".$status['trans_id']."'";
											$stmt = $dbh->prepare($insert);
										 
											// Bind parameters to statement variables
										 
											$stmt->bindParam(':process_status', $process_status);
											$stmt->execute();
																	
										$errors = $stmt->errorInfo();							
		//Sending message to activeMQ - fpp end
									forActiveMQ($jobName,$processId,$processStart,$processEnd,"completed",$processIteration);
									
									
										 // Insert the Next process Distiller 
										 
									$user_id = $user_id;
									$process_id_new = "11";
									$status = "1";
									$status_2 = "1";
									
									$job_id = $_REQUEST['job_id'];
									$assign_id = $_REQUEST['assign_id'];
									 $time = date("Y-m-d H:i:s");
										$iteration =  $iteration_val['iteration'];
									
									$sql1 = "select * from taps_process_transaction where process_id='".$process_id."' and assign_id ='".$assign_id."' and user_id ='".$user_id."' ";
									$result1 = $dbh->query($sql1);
									$t1 = $result1->fetch(PDO::FETCH_ASSOC);
									if($t1['process_id'] == "")
									{
										$insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, isfile_downloaded, downloaded_time, process_starttime, process_endtime, process_status, is_completed, completed_time,iteration) VALUES (:user_id, :process_id,  :project_id, :assign_id, :isfile_downloaded, :downloaded_time, :process_starttime, :process_endtime, :process_status, :is_completed, :completed_time,:iteration)";
										$stmt = $dbh->prepare($insert);
									 
										// Bind parameters to statement variables
										$stmt->bindParam(':user_id', $user_id);
										$stmt->bindParam(':process_id', $process_id_new);
										$stmt->bindParam(':project_id', $job_id);
										$stmt->bindParam(':assign_id', $assign_id);
										$stmt->bindParam(':downloaded_time', $time);
										$stmt->bindParam(':process_starttime', $time);
										$stmt->bindParam(':process_endtime', $time);
										$stmt->bindParam(':process_status', $status);
										$stmt->bindParam(':is_completed', $status_2);
										$stmt->bindParam(':completed_time', $time);
										$stmt->bindParam(':iteration', $iteration); 
										
										 $stmt->execute();
										 
										 $status_message = $metaid."|dp|".$sub_process_name."... (".$process_completion."% completed)|1";
										 
										  if($stmt->errorCode() == 0) {
											$status_message = $metaid."|dp|".$sub_process_name."... (".$process_completion."% completed)|1";	  
										  }
										   else {
													$errors = $stmt->errorInfo();
													echo "failed|".$errors[2];
											}
										//Sending message to activeMQ - distiller start
											$pEnd = "";
											forActiveMQ($jobName,$process_id_new,$time,$pEnd,"In-progress",$iteration);
									
										}						
									
									
									
									}
									
									
									
									/**
									* Distller status Montior.
									*/
								
						   
							 if($status['process_id'] == DISTILLER)   // Distller process status showing
							{
							
							$status_message = $metaid."|dp|PDF creation...|1";
			
							}
							echo $status_message ;
							}
							
						if($status['process_id'] == "11" && $status['is_completed'] == 1)   // Distller process status showing
						{
							
							$status_message = $metaid."|dp|PDF creation..|1";
						
							//if($status['process_id'])
							
									$sql1 = "select * from taps_process_transaction where trans_id ='".$status['trans_id']."'";
									$result1 = $dbh->query($sql1);
									$t1 = $result1->fetch(PDO::FETCH_ASSOC);
									$result1->execute();
									
									$sub_process_name = $t1['sub_process_name'];
									$process_completion = $t1['process_completion'];
									$process_status = $t1['process_status'];
									
									if($sub_process_name == "")
									{
									
									$status_message = $metaid."|dp|PDF creation...|3";	
								
									}
									else
									{
									
										
										
									
									$sub_process_name = preg_replace("/started+/", " ", $sub_process_name);
									$status_message = $metaid."|dp|".$sub_process_name."...|3";	
									}
									
									if($process_completion == "SUCCESS" || $process_completion == "FAILURE" )
									{
								
								//$author_pdf_name = $journal_name."_".$article_name.".pdf";
								
								 if (file_exists($des_place)) {		  
									   
									  $input = @file_get_contents($des_place);
									  preg_match_all('/<jid>(.*?)<\/jid>/s', $input, $matches_jid); 
									  preg_match_all('/<aid>(.*?)<\/aid>/s', $input, $matches_aid);
									  
									  $author_pdf_name = $matches_jid[1][0]."_".$matches_aid[1][0].".pdf";	 

									  }
									  
									  
								$pdf_name = strtolower($journal_name.$article_name.".pdf");
								$logpdf_name = strtolower($journal_name.$article_name."_log.pdf");
								
								$author_src_pdf   =  ELSEVIER_DISTILLER_AUTHOR_EDIT;
								
									if($process_completion == "SUCCESS")
									{
		//Sending message to activeMQ - distiller end
								
									forActiveMQ($jobName,$processId,$processStart,$processEnd,"completed",$processIteration);		
								
									$src_pdf   =  ELSEVIER_DISTILLER_SUCCESS_PATH;
									$des_pdf = $folder_to_create;
									$status_message = $metaid."|dp|Process completed successfully|4";	
									}
									
									if($process_completion == "FAILURE")
									{
									$src_pdf   =  ELSEVIER_DISTILLER_FAILURE_PATH;
									$des_pdf = $folder_to_create;
									$status_message = $metaid."|dp|PDF creation failed|5";	
									}
									
									
									
										 $article_name = strtolower($_REQUEST['jid']).$_REQUEST['aid'];
							

							

							
						//	echo 'cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$folder_to_create.'" dest_path="'.$final_dest_path.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain=""';
							
							
						$WshShell = new COM("WScript.Shell");	
					$oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$folder_to_create.'" dest_path="'.$final_dest_path.'" username="'.FTP_USERNAME.'" password="'.FTP_PASSWORD.'" domain=""', 0, false);
									
									

						$parameter = '"'.$src_pdf.'|'.$des_pdf.'|'.$pdf_name.'|'.$article_name.'|'.$journal_name.'" "'.$src_pdf.'|'.$des_pdf.'|'.$logpdf_name.'|'.$article_name.'|'.$journal_name.'" "'.$author_src_pdf.'|'.$des_pdf.'|'.$author_pdf_name.'|'.$article_name.'|'.$journal_name.'"';

						/**
						*Added the below function to force close the PDF prior copying from distiller path.
						*This solves the PDF not getting updated at sometimes issue.
						* - Sanjeevi 5/15/2015
						*/
						kill_pdf($pdf_name);
						kill_pdf($logpdf_name);
						kill_pdf($author_pdf_name);
						
						$WshShell = new COM("WScript.Shell");		
						
						$oExec = $WshShell->Run('cmd /C java -jar TAPSFileCopier.jar '.$parameter, 0, false);
						
						$process_status = 2;
						
								$insert = "update taps_process_transaction set process_status=:process_status,is_completed=:is_completed where trans_id='".$status['trans_id']."'";
			$stmt = $dbh->prepare($insert);
		 
			// Bind parameters to statement variables
		 
			$stmt->bindParam(':process_status', $process_status);
			$stmt->bindParam(':is_completed', $process_status);
			$stmt->execute();

								  
								  }
								  
								  echo $status_message;
							
							}
								
					
				}
				
}



/**
* Condition for SPRINGER CLIENT
* All stages file movement, database entries, File name formation are included in this condition 
*/



if($clientid == CLIENT_SPRINGER) {



/**
* File Name Formations
* All Required files names are formed here. Example XML file, order file
*/
	$copyright_query = "SELECT copyrightyear FROM taps_metadata where article_name = ".$_REQUEST['metaid_name']; 
					$copyright_query_fetch = $dbh->query($copyright_query);
					$copyright_val = $copyright_query_fetch->fetch(PDO::FETCH_ASSOC);				
					
$xml_filename        = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_Article.xml";
$art_count_filename  = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_Article_images.xml";
$jobsheet_filename   = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_JobSheet_200.xml";
$pdf_name            = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_OnlinePDF.pdf";
$logpdf_name         = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_OnlinePDF_log.pdf";
$failurelogpdf_name  = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_OnlinePDF.log";
$fpp_htmlname        = $_REQUEST['jid']."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_FPPlog.html";
$spice_filename_doc  = $_REQUEST['jid']."_".$_REQUEST['aid'].".docx";
$validatelog_name     = $_REQUEST['jid'].$_REQUEST['aid']."_validation.log";
$download_name     = $_REQUEST['jid'].$_REQUEST['aid']."_Filehandler.log";

$folder_to_create    = $basefile_path."\\FPP";
$file_to_copy        = $basefile_path."\\SPICE\\".$xml_filename;
$des_place  	     = $basefile_path."\\FPP\\".$xml_filename;
$file_to_copy_order  = $basefile_path."\\SPICE\\".$jobsheet_filename;
$des_place_order  	 = $basefile_path."\\FPP\\".$jobsheet_filename;
$file_to_copy_log    = $basefile_path."\\SPICE\\".$fpp_htmlname;
$file_to_copy_ref    = $basefile_path."\\SPICE\\Reference.pdf";
$des_place_ref  	 = $basefile_path."\\FPP\\Reference.pdf";
$des_place_log  	 = $basefile_path."\\FPP\\".$fpp_htmlname;
$spice_filepath      = $basefile_path."\\SPICE";

	               global $dbh;
		  
					$iteration_query = "SELECT ts.iteration FROM taps_assignment ts
					left outer join taps_metadata tm on tm.meta_id = ts.meta_id
					where assign_id = ".$_REQUEST['assign_id']; 
					$iteration_query_fetch = $dbh->query($iteration_query);
					$iteration_val = $iteration_query_fetch->fetch(PDO::FETCH_ASSOC);
					
					
					
				
					
		            $stat = "SELECT * FROM taps_process_transaction tpt
					JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id
					JOIN taps_metadata tm ON tm.meta_id = ta.meta_id
					WHERE tpt.process_status = 1 AND tpt.user_id =".$user_id." and tpt.assign_id = ".$_REQUEST['assign_id']; 
					$status_result = $dbh->query($stat);
					$status = $status_result->fetch(PDO::FETCH_ASSOC);
					
					
					$status_message = "";
					//$final_dest_path = $status['metaxml_path'];					
					$meta_id_check = $status['meta_status'];
					$art_check = $status['art_status'];
					$final_dest_path = $status['metaxml_path'];
					$trans_id= $status['trans_id'];
					$final_dest_path_spice = $status['metadownload_path'];
					/**
					* File Download status Montior.
					* Status -->  0  =  Downloading the files from FTP 
					* Status -->  1  =  Download failed 
					* Status -->  2  =  Download success 
					*/
					
					 
				
					 if($status['isrejected'] == 1 ){
								echo $metaid."|spice|Figure Mismatch|1";
								exit;
					}
					if($status['process_id'] == DOWNLOAD)
					{
					  if($status['isfile_downloaded'] == "0")
					  {
					      $status_message = $metaid."|dnload|Downloading files..|1";	
						  
						  
						   $parser_read = "Log/".$download_name;
							  
							  if (file_exists($parser_read)) {					  
							   
						      $input = @file_get_contents($parser_read);
				     		  preg_match_all('/<status>(.*?)<\/status>/s', $input, $matches);              
										
										if($matches[1][0] == "true")
										{
										$isfile_downloaded = "2";
										}
										else if($matches[1][0] == "false")
										{
										 $isfile_downloaded = "1";
										}
							   preg_match_all('/<error_message>(.*?)<\/error_message>/s', $input, $matches_completion);  
                                preg_match_all('/<end_time>(.*?)<\/end_time>/s', $input, $matches_endtime); 							   
                                
							   $process_completion = $matches_completion[1][0];						   
							   
							   
										$downloadend_time = $matches_endtime[1][0];
										
									$insert = "update taps_process_transaction set isfile_downloaded=:isfile_downloaded,process_endtime = :process_endtime,process_completion =:process_completion where trans_id=:trans_id";
					    $stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
					$stmt->bindParam(':isfile_downloaded', $isfile_downloaded);
					$stmt->bindParam(':process_completion', $process_completion);
					$stmt->bindParam(':process_endtime', $downloadend_time);
					$stmt->bindParam(':trans_id', $trans_id);
					$stmt->execute();
					
					 
			         
					 $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id";
					    $stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
					$stmt->bindParam(':process_endtime', $downloadend_time);
					$stmt->bindParam(':trans_id', $trans_id);
					$stmt->execute();
					
					//unlink($parser_read);
						
						}
					   }
					   else
					   {
							$download_log = "Log/".$download_name;  // Added by sanjeevi
							if (file_exists($download_log)) {	
							  unlink($download_log); // Added by sanjeevi
							}
							  if($status['isfile_downloaded'] == "1")
							  {
								$status_message = $metaid."|dnload|Download failed (".$status['process_completion'].")|2";	
						     
						      }
						   
						   if($status['isfile_downloaded'] == "2")
						   {
						   
								   $process_status = 2;
								   $insert = "update taps_process_transaction set process_status=:process_status where trans_id='".$status['trans_id']."'";
								   $stmt = $dbh->prepare($insert);
			 
									 // Bind parameters to statement variables
			 
								   $stmt->bindParam(':process_status', $process_status);
								   $stmt->execute();
								   $status_message = $metaid."|dnload|Download successful|3";	
						   
						   }
					   }
					   
					   echo $status_message;
					}
					
					/**
				* SPCIE status Montior.
				* XML Validate status -->  0  =  XML validation in progress
				* XML Validate status -->  1  =  XML validation failed
				* XML Validate status -->  2  =  XML validation success
				* XML Validate status -->  4  =  XML validation success
				* Metadata status     -->  3  =  Article Rejected from SPiCE and return to WMS
				* Art status          -->  0  =  Art Signal is not yet received
				* Art status          -->  1  =  Waiting for Art confirmation whether its completed or not.
				* Art status          -->  2  =  Art is completed
				*/		
					
					if($status['process_id'] == SPICE)
					{
						
					
					  $vtool = 0;
					  
					  
					   
						   
						   if($meta_id_check == "3")
						   {
						   
						   echo $status_message = $metaid."|reject|reject from spice|1";	
						   
						   exit;
						   
						   }
						   if ($art_check % 2 != 0 && $art_check>2) {
						  
						   echo $status_message = $metaid."|artreject|Art is rejected.|1";	
						   
						   exit;
						  
							}
							  
							  if($status['xml_validate'] == 0 )  //  XML validation in progress
							  {
							
							   $status_message = $metaid."|spice|Validating XML!|1";
							 
							 $parser_read = "Log/".$validatelog_name;
							  
							  if (file_exists($parser_read)) {					  
							   
						      $input = @file_get_contents($parser_read);
				     		  preg_match_all('/<XML_Validity>(.*?)<\/XML_Validity>/s', $input, $matches);              
										
										if($matches[1][0] == "true")
										{
									$xmlvalue = "2";
										}
										else if($matches[1][0] == "false")
										{
										 $xmlvalue = "1";
										}
										
									$insert = "update taps_process_transaction set xml_validate=:xml_validate where trans_id=:trans_id";
					    $stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
					$stmt->bindParam(':xml_validate', $xmlvalue);
					$stmt->bindParam(':trans_id', $trans_id);
					$stmt->execute();
					
					 $downloadend_time = date('Y-m-d H:i:s');
			         
					 $insert = "update taps_subprocess_transaction set process_endtime=:process_endtime where trans_id=:trans_id";
					    $stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
					$stmt->bindParam(':process_endtime', $downloadend_time);
					$stmt->bindParam(':trans_id', $trans_id);
					$stmt->execute();
					
					//unlink($parser_read);
  
								}
							  
							 
							  }else{
							   $validatelog_name = "Log/".$validatelog_name;  // Added by sanjeevi
								if (file_exists($validatelog_name)) {	
									unlink($validatelog_name); // Added by sanjeevi
								}
							  }
					
					
					
					
					
					
						//echo $status['is_completed'];
						
						
						
				    if($status['xml_validate'] == 4 && $status['is_completed'] == 1)  // process_status -- 2 is completed
						{
						
							$status_message = $metaid."|spice|SPiCE tool triggered!|1";						
						
						}
						
					if($status['xml_validate'] == 1 && $status['is_completed'] == 2 && ( $status['sub_process_name'] == "PAUSE" || $status['sub_process_name'] == "ONHOLD"  ))  // process_status -- 2 is completed
						{
						  
							if($status['sub_process_name'] == "PAUSE")
							{
						  $status_message = $metaid."|spice|XML validation error!. Article has PAUSED|3";
						   }
						   if($status['sub_process_name'] == "ONHOLD")
							{
						  $status_message = $metaid."|spice|XML validation error!. Article is on HOLD|3";
						   }
						}
						
						if($status['xml_validate'] == 4 && $status['is_completed'] == 2 && ( $status['sub_process_name'] == "PAUSE" || $status['sub_process_name'] == "ONHOLD"  ))  // process_status -- 2 is completed
						{
						  
							if($status['sub_process_name'] == "PAUSE")
							{
						  $status_message = $metaid."|spice|Article has PAUSED|13";
						   }
						   if($status['sub_process_name'] == "ONHOLD")
							{
						  $status_message = $metaid."|spice|Article is on HOLD|13";
						   }
						}
					
					if($status['xml_validate'] == 1 && $status['is_completed'] == 1)  // process_status -- 2 is completed
						{
						
						
						$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id WHERE tpt.process_status = 1 AND tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1"; 
						
					
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
					if($status_ar['process_id'] == "")
					{
					
							$status_message = $metaid."|spice|XML validation error!|3";
							
							$process_time   = date('Y-m-d H:i:s');
                                                        $sub_proc_get = "select trans_id,user_id,process_id,project_id,assign_id from taps_process_transaction where trans_id=:trans_id";
                                                        $stmt = $dbh->prepare($sub_proc_get);
                                                        $stmt->bindParam(':trans_id', $status['trans_id']);
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

						     
								
								$wordfile = $spice_filepath."\\".$spice_filename_doc;
								//shell_exec("start /min $explorer /n,/e,$spice_filepath");
								$output = shell_exec('start /min winword '.$wordfile);
								$status_2 = 2;
								$is_fileopen = 1;
									$insert = "update taps_process_transaction set is_completed=:is_completed,is_fileopen=:is_fileopen where trans_id=:trans_id";
									$stmt = $dbh->prepare($insert);
								 
									// Bind parameters to statement variables
								 
									$stmt->bindParam(':is_completed', $status_2);
									$stmt->bindParam(':is_fileopen', $is_fileopen);
									$stmt->bindParam(':trans_id', $status['trans_id']);
									$stmt->execute();
									
						}
						else
						{
						
							$status_message = $metaid."|spice|XML validation error, Queued for SPiCE process!|3";
						
						
						}

							
						}
						
						if($status['xml_validate'] == 2 )  // process_status -- 2 is completed
						{
							
							
							if($status['art_status'] == "0")
							{
							    $status_message = $metaid."|spice|Waiting for Art Status|10";
							}
							
							
							if($status['art_status'] == "1")
							{
							   $status_message = $metaid."|spice|Art is not yet completed|11";
							    $artsignal_file=$spice_filepath."\\".$art_count_filename;
									if (file_exists($artsignal_file)) {					  
									  $input = @file_get_contents($artsignal_file);
									  preg_match_all('/<count>(.*?)<\/count>/s', $input, $matches);              
										  if($matches[1][0] == "0"){
											$insert = "update taps_metadata set art_status=2 where meta_id=:meta_id";
											$stmt = $dbh->prepare($insert);
											$stmt->bindParam(':meta_id', $metaid);
											$stmt->execute();
											unlink($artsignal_file);
										}
									}
							}
							/**
							* $status['art_status'] is modified to continue the process on event of art got rejected earlier
							* Bug closed - Sanjeevi 5/15/2015
							*/
							if($status['art_status'] %2 == 0)
							{
							
							$stat_ar = "SELECT * FROM taps_process_transaction tpt JOIN taps_assignment ta ON ta.assign_id = tpt.assign_id WHERE tpt.process_id = 2  AND tpt.user_id =".$user_id." and tpt.assign_id <> ".$_REQUEST['assign_id']." and tpt.is_fileopen=1"; 
						
					
					$status_result_ar = $dbh->query($stat_ar);
					$status_ar = $status_result_ar->fetch(PDO::FETCH_ASSOC);
					if($status_ar['process_id'] == "")
					{
							
			     	//$html_preview = shell_exec(SPRINGER_HTMLTOOL.$file_to_copy);
								
							
									
								if (!file_exists($folder_to_create)) {
									mkdir($folder_to_create, 0777, true);
									}
								function copy_file($src,$dst) {
								   copy($src, $dst);
								}  
								copy_file($file_to_copy,$des_place);
								copy_file($file_to_copy_order,$des_place_order);
								copy_file($file_to_copy_log,$des_place_log);
								copy_file($file_to_copy_ref,$des_place_ref); //Sanjeevi 7/11/2015
								
								
								$cpdata = '<?xml version="1.0"?>
								<Metadata>
								<account>Springer</account>
								<ArticleID>'.$_REQUEST['aid'].'</ArticleID>
								<JournalName>'.$_REQUEST['jid'].'</JournalName>
								<Current_User>'.$_SESSION['emp_id'].'</Current_User>
								<Actual_Name>AIO</Actual_Name>
								<ArtLocation>\\xxxx\xxxx\xxx\</ArtLocation>
								<Stage>200</Stage>
								<model>Large</model>
								</Metadata>';
								
								
									$filepath = MPATH;
									$myFile = $_REQUEST['jid']."_".$_REQUEST['aid']."_meta.xml";
									$fh = fopen($filepath.$myFile, 'w') or die("can't open file");
									fwrite($fh, $cpdata);
								$process_status = 2;
								$insert = "update taps_process_transaction set process_status=:process_status where trans_id='".$status['trans_id']."'";
    $stmt = $dbh->prepare($insert);
 
    // Bind parameters to statement variables
 
    $stmt->bindParam(':process_status', $process_status);
    $stmt->execute();
	
	
	
	
	//Insert next process FPP 
	
	  // Prepare INSERT statement to SQLite3 file db
							$user_id = $user_id;
							$process_id_new = "2";
							$status = "1";
							$status_2 = "1";
							
							
							$job_id = $_REQUEST['job_id'];
							$assign_id = $_REQUEST['assign_id'];
							 $time = date("Y-m-d H:i:s");
							 $is_fileopen = 1;
							 
							$iteration =  $iteration_val['iteration'];
							
							$sql1 = "select * from taps_process_transaction where process_id='".$process_id."' and assign_id ='".$assign_id."' and user_id ='".$user_id."' ";
							$result1 = $dbh->query($sql1);
							$t1 = $result1->fetch(PDO::FETCH_ASSOC);
							if($t1['process_id'] == "")
							{
    $insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, isfile_downloaded, downloaded_time, process_starttime, process_endtime, process_status, is_completed, completed_time,iteration,is_fileopen) VALUES (:user_id, :process_id,  :project_id, :assign_id, :isfile_downloaded, :downloaded_time, :process_starttime, :process_endtime, :process_status, :is_completed, :completed_time,:iteration,:is_fileopen)";
    $stmt = $dbh->prepare($insert);
 
    // Bind parameters to statement variables
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':process_id', $process_id_new);
    $stmt->bindParam(':project_id', $job_id);
    $stmt->bindParam(':assign_id', $assign_id);
    $stmt->bindParam(':downloaded_time', $time);
    $stmt->bindParam(':process_starttime', $time);
    $stmt->bindParam(':process_endtime', $time);
    $stmt->bindParam(':process_status', $status);
    $stmt->bindParam(':is_completed', $status_2);
    $stmt->bindParam(':completed_time', $time); 
	 $stmt->bindParam(':iteration', $iteration); 
	  $stmt->bindParam(':is_fileopen', $is_fileopen); 

 
     $stmt->execute();
	 
	 
	 
	 
	  // Files are moving to iWorks for file backup;
	 
	


					
				
				$WshShell = new COM("WScript.Shell");	
			//	echo 'cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$spice_filepath.'" dest_path="'.$final_dest_path_spice.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" domain="'.NT_DOMAIN;
				$oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$spice_filepath.'" dest_path="'.$final_dest_path_spice.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" domain="'.NT_DOMAIN.'"', 0, false);
	 
	 
	 
	 
	  if($stmt->errorCode() == 0) {
		  $processid= $dbh->lastInsertId();
		  
		  exec('tasklist /fi "Imagename eq TAPS_App.exe"', $fpprunning);
 
			$count = count($fpprunning);
			if($count == 1)
			{
                 $WshShell = new COM("WScript.Shell");
				$oExec = $WshShell->Run('cmd /C '.FPP_SERVICE, 0, false);
				
			}
	  }
	   else {
    $errors = $stmt->errorInfo();
    echo "failed|".$errors[2];
}
							}
								
							
						}
						$status_message = $metaid."|fpp|Queued for Pagination Process!|1";
						
						}
					
						
							
							
							}echo $status_message ;
					}
					
					
				/**
				* 3B2 status Montior.
				* Metadata status     -->  3  =  Article Rejected from SPiCE and return to WMS
				* Art status          -->  0  =  Art Signal is not yet received
				* Art status          -->  1  =  Waiting for Art confirmation whether its completed or not.
				* Art status          -->  2  =  Art is completed
				*/		
					
			  if($status['process_id'] == FPP )   // FPP process status showing and art status is 
		  {
		  
	  $sql1 = "select * from taps_process_transaction where trans_id ='".$status['trans_id']."'";
				  $result1 = $dbh->query($sql1);
				  $t1 = $result1->fetch(PDO::FETCH_ASSOC);
				  $result1->execute();
				  
				  $sub_process_name = $t1['sub_process_name'];
				  $process_completion = $t1['process_completion'];
				  
				  if($sub_process_name == "")
				  {
				  
				  $status_message = $metaid."|fpp|Queued for Pagination Process!|3";	
				  }
				  else
				  {
				  $status_message = $metaid."|fpp|".$sub_process_name."... (".$process_completion."% completed)|3";	
				  }
				 
				  if($process_completion == "100")
				  {
				  
				  $ps_name = strtoupper($_REQUEST['jid'])."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid'];
				  $manifest_filename = strtoupper($_REQUEST['jid'])."_".$copyright_val['copyrightyear']."_".$_REQUEST['aid']."_onlinePDF.json";
				  
					 $jidcaps  = strtoupper($_REQUEST['jid']);
				  
					  $fppsrc_ps    = $basefile_path."\\FPP\\".$ps_name."_OnlinePDF.ps";
					  
					  $fppdest_ps   =  SPRINGER_DISTILLER_IN_PATH;
					  $fppmanifest_path  =  SPRINGER_DISTILLER_MANIFEST_PATH;
					  
					  $log = $ps_name.".log";
					  $cc = shell_exec("fastcopy.exe /verify /auto_close /speed=full /filelog=$log $fppsrc_ps /to=$fppdest_ps");
					  
								  
  
		  
					  $timestamp = time();
						  $datetime = date('Y-m-d H:i:s');
	  
					  $cpdata = '{"distiller":{"timestamp":"'.$timestamp.'","process":"Distiller","article_id":"'.$_REQUEST['aid'].'","process_type":"AUTO","process_time":"'.$datetime.'","journal_id":"'.$jidcaps.'","process_status":"0","artfile_status":"","sub_process_name":""}}';								
					  
						  
						  $myFile = $fppmanifest_path.$manifest_filename;
						  $fh = fopen($myFile, 'w') or die("can't open file");
						  fwrite($fh, $cpdata);
						  
						  
		  
			  $WshShell = new COM("WScript.Shell");
			  $oExec = $WshShell->Run('cmd /C java -jar TAPSCommunicator.jar -mf_path="'.$myFile.'"', 0, false);
		  
		  
					  
					  $process_status = 2;
					  
					  //echo $insert;
					  
					   $insert = "update taps_process_transaction set process_status=:process_status where trans_id='".$status['trans_id']."'";
$stmt = $dbh->prepare($insert);

// Bind parameters to statement variables

$stmt->bindParam(':process_status', $process_status);
$stmt->execute();
				  
$errors = $stmt->errorInfo();							
					
					   // Prepare INSERT statement to SQLite3 file db
				  $user_id = $user_id;
				  $process_id_new = "11";
				  $status = "1";
				  $status_2 = "1";
				  
				  $job_id = $_REQUEST['job_id'];
				  $assign_id = $_REQUEST['assign_id'];
				   $time = date("Y-m-d H:i:s");
					  $iteration =  $iteration_val['iteration'];
				  
				  $sql1 = "select * from taps_process_transaction where process_id='".$process_id."' and assign_id ='".$assign_id."' and user_id ='".$user_id."' ";
				  $result1 = $dbh->query($sql1);
				  $t1 = $result1->fetch(PDO::FETCH_ASSOC);
				  if($t1['process_id'] == "")
				  {
$insert = "INSERT INTO taps_process_transaction (user_id, process_id, project_id, assign_id, isfile_downloaded, downloaded_time, process_starttime, process_endtime, process_status, is_completed, completed_time,iteration) VALUES (:user_id, :process_id,  :project_id, :assign_id, :isfile_downloaded, :downloaded_time, :process_starttime, :process_endtime, :process_status, :is_completed, :completed_time,:iteration)";
$stmt = $dbh->prepare($insert);

// Bind parameters to statement variables
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':process_id', $process_id_new);
$stmt->bindParam(':project_id', $job_id);
$stmt->bindParam(':assign_id', $assign_id);
$stmt->bindParam(':downloaded_time', $time);
$stmt->bindParam(':process_starttime', $time);
$stmt->bindParam(':process_endtime', $time);
$stmt->bindParam(':process_status', $status);
$stmt->bindParam(':is_completed', $status_2);
$stmt->bindParam(':completed_time', $time);
$stmt->bindParam(':iteration', $iteration); 




$stmt->execute();


// Files are moving to iWorks for file backup;




	  $file_transfer_path  = $basefile_path."\\FPP";

		  
	  
	  $WshShell = new COM("WScript.Shell");	
	  $oExec = $WshShell->Run('cmd /C java -jar TAPSFileHandler.jar -mode=upload src_path="'.$file_transfer_path.'" dest_path="'.$final_dest_path.'" username="'.NT_USERNAME.'" password="'.NT_PASSWORD.'" domain="'.NT_DOMAIN.'"', 0, false);
	  
	  
$status_message = $metaid."|dp|".$sub_process_name."... (".$process_completion."% completed)|1";

if($stmt->errorCode() == 0) {
$status_message = $metaid."|dp|".$sub_process_name."... (".$process_completion."% completed)|1";	  
}
else {
$errors = $stmt->errorInfo();
echo "failed|".$errors[2];
}

}						
				  
				  
				  
				  }
				  
			  
		 
				   if($status['process_id'] == "11")   // FPP process status showing
				  {
				  $status_message = $metaid."|dp|PDF creation...|1";
				  }
				  echo $status_message ;
		  }
		  
		  
		      /**
			  * Distller status Montior.
			  */
					
			 if($status['process_id'] ==  DISTILLER && $status['is_completed'] == 1)   // Distller process status showing
			{
			
			$status_message = $metaid."|dp|PDF creation...|1";
			
			//if($status['process_id'])
			
					$sql1 = "select * from taps_process_transaction where trans_id ='".$status['trans_id']."'";
					$result1 = $dbh->query($sql1);
					$t1 = $result1->fetch(PDO::FETCH_ASSOC);
					$result1->execute();
					
					$sub_process_name = $t1['sub_process_name'];
					$process_completion = $t1['process_completion'];
					$process_status = $t1['process_status'];
					
					if($sub_process_name == "")
					{
					
					$status_message = $metaid."|dp|PDF creation...|3";	
					}
					else
					{
					
					$sub_process_name = preg_replace("/started+/", " ", $sub_process_name);
					$status_message = $metaid."|dp|".$sub_process_name."...|3";	
					}
					
					
					
					if($process_completion == "SUCCESS" || $process_completion == "FAILURE" )
					{
				
										
					if($process_completion == "SUCCESS")
					{
					
						
					$src_pdf   =  SPRINGER_DISTILLER_SUCCESS_PATH;
					$des_pdf = $folder_to_create;
					$status_message = $metaid."|dp|Process completed Successfully!|4";	
					
					$parameter = '"'.$src_pdf.'|'.$des_pdf.'|'.$pdf_name.'|'.$article_name.'|'.$journal_name.'" "'.$src_pdf.'|'.$des_pdf.'|'.$logpdf_name.'|'.$article_name.'|'.$journal_name.'"';
					 }
					
					if($process_completion == "FAILURE")
					{
					$src_pdf   =  SPRINGER_DISTILLER_FAILURE_PATH;
					$des_pdf = $folder_to_create;
					$status_message = $metaid."|dp|PDF creation failed!|5";	
					
					$parameter = '"'.$src_pdf.'|'.$des_pdf.'|'.$pdf_name.'|'.$article_name.'|'.$journal_name.'" "'.$src_pdf.'|'.$des_pdf.'|'.$logpdf_name.'|'.$article_name.'|'.$journal_name.'" "'.$src_pdf.'|'.$des_pdf.'|'.$failurelogpdf_name.'|'.$article_name.'|'.$journal_name.'"';
					}
					
				/**
				*Added the below function to force close the PDF prior copying from distiller path.
				*This solves the PDF not getting updated at sometimes issue.
				* - Sanjeevi 5/15/2015
				*/
				kill_pdf($pdf_name);
				kill_pdf($logpdf_name);
				kill_pdf($failurelogpdf_name);
					
		//	echo $status_message;
					
				//	exit;

		$WshShell = new COM("WScript.Shell");		
		$oExec = $WshShell->Run('cmd /C java -jar TAPSFileCopier.jar '.$parameter, 0, false);
		
		$process_status = 2;
						
						//echo $insert;
				$insert = "update taps_process_transaction set process_status=:process_status,is_completed=:is_completed where trans_id='".$status['trans_id']."'";
$stmt = $dbh->prepare($insert);

// Bind parameters to statement variables

$stmt->bindParam(':process_status', $process_status);
$stmt->bindParam(':is_completed', $process_status);
$stmt->execute();


				  
				  }
				  
				  echo $status_message;
			
			}
						
					
					
}
/**
*Added the below function to force close the PDF prior copying from distiller path.
*This solves the PDF not getting updated at sometimes issue.
* - Sanjeevi 5/15/2015
*/
function kill_pdf($pdf_name){
	echo 'taskkill /f /fi "Windowtitle eq '.$pdf_name.'*"';
	exec('taskkill /f /fi "Windowtitle eq '.$pdf_name.'*"');
	
}
?>


