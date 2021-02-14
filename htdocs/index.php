<?php

/* TODO add authentication */
if ($_REQUEST['tok'] != '123412346346234332') {
	echo 'error';
	exit(1);
}

include '/etc/freepbx.conf';
session_start();
define('FREEPBX_IS_AUTH', 1);
// Include all installed modules class
if ($handle = opendir(__DIR__. '/../..')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            $moduleClass = __DIR__. '/../../'. $entry. '/'. ucfirst($entry). '.class.php';
            $funcFile = __DIR__. '/../../'. $entry. '/functions.inc.php';

            // include main module class
            if (is_file($moduleClass)) {
                include_once($moduleClass);
            }

            // include functions.inc.php (deprecated but neeeded for some modules)
            if (is_file($funcFile)) {
                include_once($funcFile);
            }
        }
    }
    closedir($handle);
}


$tocall = $_REQUEST['tocall'];
$message = $_REQUEST['message'];
$language = !empty($_REQUEST['language']) ? $_REQUEST['language'] : 'it';
$maxretries = !empty($_REQUEST['maxretries']) ? $_REQUEST['maxretires'] : 0;
$retrytime = !empty($_REQUEST['retrytime']) ? $_REQUEST['retrytime'] : 60;
$waittime = !empty($_REQUEST['waittime']) ? $_REQUEST['waittime'] : 30;
$destination = !empty($_REQUEST['destination']) ? $_REQUEST['destination'] : 'app-blackhole,hangup,1';

if (empty($tocall)) {
	error_log('ApiCall: missing tocall');
	exit(1);
}
$content = "Channel: Local/{$tocall}@from-internal
MaxRetries: {$maxretries}
RetryTime: {$retrytime}
WaitTime: {$waittime}
Callerid: 999
Context: apicall
Priority: 1
Extension: s
";

if (!empty($message)) {
	$filename = googletts_tts($message,$language);
    	$tmpfilepath = '/tmp/'.$filename.'.mp3';
	#$dstfilepath = '/var/lib/asterisk/sounds/'.$language.'/'. $filename . '.wav';
	$dstfilepath = '/var/lib/asterisk/sounds/' . $filename . '.wav';
    	$media = FreePBX::Media();
    	$media->load($tmpfilepath);
    	$media->convert($dstfilepath);
	$content .= "Setvar: message=$filename\n";
	$content .= "Setvar: destination=$destination\n";
}

$tmp_name = tempnam("/tmp", 'apirecall');
$f = fopen($tmp_name,"w");
fwrite($f,$content);
fclose($f);
$filename = "apicall-".microtime(true).".call";
$res = rename($tmp_name,"/var/spool/asterisk/outgoing/$filename");

