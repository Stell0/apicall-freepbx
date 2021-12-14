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

// vim: set ai ts=4 sw=4 ft=php:
namespace FreePBX\modules;
/*
* Class stub for BMO Module class
* In getActionbar change "modulename" to the display value for the page
* In getActionbar change extdisplay to align with whatever variable you use to decide if the page is in edit mode.
*
*/

class Apicall extends \FreePBX_Helpers implements \BMO
{

	// Note that the default Constructor comes from BMO/Self_Helper.
	// You may override it here if you wish. By default every BMO
	// object, when created, is handed the FreePBX Singleton object.

	// Do not use these functions to reference a function that may not
	// exist yet - for example, if you add 'testFunction', it may not
	// be visibile in here, as the PREVIOUS Class may already be loaded.
	//
	// Use install.php or uninstall.php instead, which guarantee a new
	// instance of this object.
	public function install()
	{
		if(!$this->getConfig('token')) {
			$this->setConfig('token',$token = bin2hex(openssl_random_pseudo_bytes(16)));
		}
      		$this->generateLink();
	}
	public function uninstall()
	{
		$this->delConfig('token');
    		$path = \FreePBX::Config()->get_conf_setting('AMPWEBROOT');
		$location = $path. '/apicall';
		unlink($location);
	}

	public function showPage()
	{
		$subhead = _('Make outbound and internal calls using REST APIs');
		$settings = array('token' => $this->getConfig('token'));
		$content = load_view(__DIR__.'/views/form.php', array('settings' => $settings));
		show_view(__DIR__.'/views/default.php', array('subhead' => $subhead, 'content' => $content));
	}
	// The following two stubs are planned for implementation in FreePBX 15.
	public function backup()
	{
	}
	public function restore($backup)
	{
	}

	public function doConfigPageInit($page)
	{
	}

  	public function generateLink()
  	{
    		$path = \FreePBX::Config()->get_conf_setting('AMPWEBROOT');
    		$location = $path. '/apicall';
    		if (!file_exists($location)) {
        		symlink(dirname(__FILE__). '/htdocs', $location);
    		}
  	}

	// We want to do dialplan stuff.
	public static function myDialplanHooks()
	{
		return 900; //at the very last instance
	}

	public function doDialplanHook(&$ext, $engine, $priority)
	{
		$contextname = 'apicall';
		$ext->add($contextname, 's','',new \ext_answer(''));
		$ext->add($contextname, 's','',new \ext_wait(''));
		$ext->add($contextname, 's','',new \ext_agi('apicall.php,${message},${destination}'));
		$contextname = 'aibot';
		$ext->add($contextname, 's','',new \ext_set('EAGI_AUDIO_FORMAT','slin48'));
		$ext->add($contextname, 's','',new \ext_answer(''));
		$ext->add($contextname, 's','',new \extension('EAGI(aibot.eagi)'));
	}
}

