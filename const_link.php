<?php
define("TAPS", "D:\\TAPS\\");   // TAPS DRIVE
define("TAPSPREVIEW", "D:/TAPS/");   // TAPS DRIVE
define("TAPSAPP","D:\\Programs\\SAMPLES");    // TAPSAPP DRIVE
define("MPATH","D:\\TAPS\\Meta\\FPP\\");    // TAPSAPP DRIVE
define("VTOOL","D:\\Programs\\XED\\Server\\Applications\\VTool\\vtool.jar");
//define("CONTENT_CHECKER","http://172.20.145.73/ContentCheckerService/");
define("CONTENT_CHECKER","D:\\Programs\\XED\\Server\\Applications\\ContentChecker\\ContentChecker-local.jar");
define('HTML_SPICE','\\\\PDGTS1033\\spice2\\TechUtilities\\Miscellaneous\\XMLViewer\\art520.bat '); 

define('FPP_DEST_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\IN'); // FPP Desintation path. It should distiller source path

define('ELSEVIER_DISTILLER_MANIFEST_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\MANIFEST\\'); // FPP Manifest path. 

define('ELSEVIER_DISTILLER_XMP_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\XMP'); // FPP Manifest path. 

define('ELSEVIER_DISTILLER_XFDF_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\XFDF'); // FPP Manifest path. 

//define('ELSEVIER_DISTILLER_MANIFEST_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\MANIFEST\\'); // FPP Manifest path. 

define('ELSEVIER_DISTILLER_AUTHOR_EDIT','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\AUTHOR_EDIT\\'); // Distiller Author edit PDF path

define('ELSEVIER_DISTILLER_SUCCESS_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\SUCCESS\\'); // Distiller Success PDF path

define('ELSEVIER_DISTILLER_FAILURE_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\FAILURE\\'); //  Distiller failure PDF path

//define('ELSEVIER_DISTILLER_FAILURE_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\ELSEVIER\\S100\\FAILURE\\'); //  Distiller failure PDF path

define('SPRINGER_HTMLTOOL','\\\\pdgts1033\\spice2\\TechUtilities\\Springer\\ViewAPP\\ViewXML_Spr.exe -x '); //  Distiller failure PDF path

define('SPRINGER_DISTILLER_IN_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\SPRINGER\\S200\\IN'); //  Distiller failure PDF path

define('SPRINGER_DISTILLER_MANIFEST_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\SPRINGER\\S200\\MANIFEST\\'); //  Distiller failure PDF path

define('SPRINGER_DISTILLER_SUCCESS_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\SPRINGER\\S200\\SUCCESS\\'); // Distiller Success PDF path

define('SPRINGER_DISTILLER_FAILURE_PATH','\\\\PDGTS1033\\spice2\\Production\\TAPS\Workflow\\SPRINGER\\S200\\FAILURE\\'); //  Distiller failure PDF path

define('FPP_SERVICE','D:\\TAPS\\Meta\\FPP\\templates\\TAPS_App.exe'); //FPP Service file
define("TAPS_WEB_PATH","http://172.20.145.94/taps/tapsweb/");	// TAPS WEB PATH for configuring sync (If this is updated please change it in js/config.js as well) - added by sanjeevi on 03/02/2015 02:07 PM
define("TAPS_WEB_PATH_PORT","http://172.20.145.94/taps/tapsweb/");
define("TAPS_WEB_PORT","");	// TAPS WEB App Port for configuring sync (If this is updated please change it in js/config.js as well) - added by sanjeevi on 03/02/2015 02:07 PM
define("SERVER_SIDE_SYNC_API","datasync_server.php");	// Server side sync handler for configuring sync (If this is updated please change it in js/config.js as well) - added by sanjeevi on 03/02/2015 02:07 PM


define("ARTICLE_ASSIGNMENT_API","Assigninfo.php");	// Server side handler for providing article allocation data to TAPS DT - added by sanjeevi on 03/13/2015 02:07 PM
define("NEW_USER_SYNC","NewUserSync.php");	 // Server side - new user sync. - added by ram on 04/09/2015
////// NOTE: MENTION THE DEFINING NAME DETAILS CLEARLY IN A COMMENT, FOLLOW THE ABOVE FORMAT//////

define("CLIENT_ELSEVIER","1");	// Client Name Elsevier
define("CLIENT_SPRINGER","2");	// Client Name Elsevier
define("DOWNLOAD","9");	// Download Process
define("SPICE","5");	// SPICE Process
define("FPP","2");	// FPP Process
define("DISTILLER","11");	// DISTILLER Process
/* Joel Added */
//define("NT_USERNAME","VNC User");// Changed to NT username from  FTP username
//define("NT_PASSWORD","Password01");//  Changed to NT password from FTP password
define("NT_USERNAME","magnususer");// Changed to NT username from  FTP username
define("NT_PASSWORD","M@gNu\$Us3r@6616");//  Changed to NT password from FTP password
/* Joel Added */
//define("FTP_USERNAME","tapsdev");//  Changed to FTP username from NT username
//define("FTP_PASSWORD","temp@dev");// Changed to FTP password from NT password//
define("FTP_USERNAME","ftp_magnususer");//  Changed to FTP username from NT username
define("FTP_PASSWORD","M@gNu\$Us3r@6616");// Changed to FTP password from NT password
define("FTP_USERNAME_ART","magnususer");//  Changed to FTP username from NT username - Added by Ram
define("FTP_PASSWORD_ART","M@gNu\$Us3r@6616");// Changed to FTP password from NT password - Addedd by Ram
define("NT_DOMAIN","spi-global");
define("ART_REJECT_PATH_SPRINGER","\\\\172.20.145.162\\art_signals\\test\\queries"); // Live path for posting art signals - Sanjeevi 5/15/2015
//define("ART_REJECT_PATH_ELSEVIER","ftp://172.20.145.95/art_signals/queries"); // Live path for posting art signals - Sanjeevi 5/15/2015
define("MANUSCRIPT_PATH_ELSEVIER","\\\\172.20.145.95\\ELS_Journals\\RAW");  // Live path for getting manuscript files - Sanjeevi 5/15/2015
define("ELSEVIER_METADATA_INVOKER","http://172.20.145.94/metaInvoker.php");
define("MAGNUSELS_PRODUCTION_URL","http://172.20.145.94/");
//define("ELSEVIER_METADATA_INVOKER_TEST","http://172.24.177.106:81/magnusEls/metaInvoker.php"); // Added by Ram - Test Method invoker page. Ram Local system.
define("DATASET_PATH","D:\\Programs\\dataset");
define("UPLOADMETA_OUT_PATH","\\\\172.20.145.95\\ELS_Journals\\WATCHFOLDER\\UPLOAD\\IN\\");
define("UPLOADMETA_IN_PATH","D:\\Programs\\dataset\\uploadxml\\");
define("PRODUCTION_NT_PATH","\\\\172.20.145.95\\ELS_Journals\\");
define("PRODUCTION_FTP_PATH","ftp://172.20.145.95/ELS_Journals/");
define("ART_SRCPATH_SP","\\\\pdgts1174\\springer\\jwf\\figures\\");// art file source path for springer
define("ART_SRCPATH_ES","\\\\172.20.145.95\\ELS_Journals\\ARTFILES\\PRE-PROCESS\\");// art file source path for elsevier
define("ART_SRCPATH_ES_FTP","ftp://172.20.145.95/ELS_Journals/ARTFILES/PRE-PROCESS/");// art file source path for elsevier
						
define("ACTIVE_MQ_URL","http://172.20.145.153:8161/api/message/magnus_taps?type=queue&clientId=curl");// activeMQ url to send messages to the Workflow systems
define("ACTIVE_MQ_UN","user:user"); // activeMQ username

//for click once dataset tool 
define("INTERNET_EXPLORE_EXE_PATH","C:\Program Files\Internet Explorer\iexplore.exe");
define("ONCE_CLICK_DATASET_TOOL_SERVER_PATH","http://172.20.145.120:100/Elsevier_Dataset_Journals_GUI.application");


//Start For Process id Constants 
//=========================================================

define( 'S5_DATASET_PROCESS_ID' , 13 ); 
define( 'STRIPINS_PROCESS_ID' , 14 ); 
define( 'XMP_PDF_PROCESS_ID' , 15 ); 
define( 'S5_UPLOADER_PROCESS_ID' , 16 ); 
define( 'UPDATE_ITEM_PROCESS_ID' , 17 ); 
define( 'S100_UPLOADER_PROCESS_ID' , 18 ); 
define( 'PROOF_CENTRAL_PROCESS_ID' , 19 ); 
define( 'PC_SIGNAL_UPLOAD_PROCESS_ID' , 20 ); 
define( 'EVENT_NOTIFIER_PROCESS_ID' , 21 ); 
define( 'RENAME_PROCESS_ID' , 22 ); 

//==========================================================
//End For Process id constants


define("SERVER_SIDE_SYNC_API_TEST","datasync_server_test.php");	

define( 'TAPS_LIVE_DB' , 'mysql:host=172.20.145.152;dbname=taps');
define( 'TAPS_LIVE_DB_USER_NAEM' , 'developer' );
define( 'TAPS_LIVE_DB_PASSWORD' , 'dev@123' );


define("ELSEVIER_TOOLS_RUN","http://172.20.145.94/runTools.php");

define("MAGNUS_BASE_URL" , "http://172.20.145.94/" );
define("MAGNUS_PROBLEM_HANDLER" ,"problemhandler/problemhandlerfortaps.php" );
define("MAGNUS_PTSOREDER_HANDLER" ,"problemhandler/ptsOrderfortaps.php" );

?>