<?php

/*
 * This is part of Api Call FreePBX module. Make call using Rest API
 * Copyright (C) 2021  Stefano Fancello gentoo.stefano@gmail.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

include '/etc/freepbx_db.conf';
$sql = 'SELECT `val` FROM `kvstore_FreePBX_modules_Apicall` WHERE `key` = "token"';
$sth = $db->prepare($sql);
$sth->execute();
$token = $sth->fetchAll()[0][0];

if (empty($_SERVER['HTTP_TOKEN']) || $_SERVER['HTTP_TOKEN'] !== $token) {
	http_response_code(401);
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
$post = json_decode(file_get_contents('php://input'), true);
$tocall = $post['tocall'];
$message = !empty($post['message']) ? $post['message'] : '';
$language = !empty($post['language']) ? $post['language'] : 'it';
$maxretries = !empty($post['maxretries']) ? $post['maxretires'] : 0;
$retrytime = !empty($post['retrytime']) ? $post['retrytime'] : 60;
$waittime = !empty($post['waittime']) ? $post['waittime'] : 30;
$destination = !empty($post['destination']) ? $post['destination'] : 'app-blackhole,hangup,1';
$callerid = !empty($post['callerid']) ? $post['callerid'] : '999';

if (empty($tocall)) {
	error_log('ApiCall: missing tocall');
	http_response_code(400);
	exit(1);
}
$content = "Channel: Local/{$tocall}@from-internal
MaxRetries: {$maxretries}
RetryTime: {$retrytime}
WaitTime: {$waittime}
Callerid: {$callerid}
Context: apicall
Priority: 1
Extension: s
";

if (!empty($message) && function_exists('googletts_tts')) {
	$filename = googletts_tts($message,$language);
    	$tmpfilepath = '/tmp/'.$filename.'.mp3';
	$dstfilepath = '/var/lib/asterisk/sounds/' . $filename . '.wav';
    	$media = FreePBX::Media();
    	$media->load($tmpfilepath);
    	$media->convert($dstfilepath);
	$content .= "Setvar: message=$filename\n";
}

$content .= "Setvar: destination=$destination\n";

$tmp_name = tempnam("/tmp", 'apirecall');
$f = fopen($tmp_name,"w");
fwrite($f,$content);
fclose($f);
$filename = "apicall-".microtime(true).".call";
$res = rename($tmp_name,"/var/spool/asterisk/outgoing/$filename");

