#!/usr/bin/env php
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


include_once '/etc/freepbx.conf';
define("AGIBIN_DIR", "/var/lib/asterisk/agi-bin");
include(AGIBIN_DIR."/phpagi.php");

$agi = new AGI();

$message = $argv[1];
$destination = !empty($argv[2]) ? $argv[2] : 'app-blackhole,hangup,1';
$agi->verbose($message);

if (!empty($message)) {
	$agi->exec("wait","1");
	$agi->stream_file($message);
}

$agi->goto($destination);
