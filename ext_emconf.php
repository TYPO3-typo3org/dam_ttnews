<?php

########################################################################
# Extension Manager/Repository config file for ext "dam_ttnews".
#
# Auto generated 13-04-2011 15:54
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM News',
	'description' => 'Adds DAM support to tt_news image and media fields',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.1.12',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => 'bottom',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Erich Bircher',
	'author_email' => 'typo3@internetgalerie.ch',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'dam' => '',
			'tt_news' => '',
			'php' => '3.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:9:"ChangeLog";s:4:"68b3";s:10:"README.txt";s:4:"ee2d";s:20:"class.ext_update.php";s:4:"f65d";s:18:"class.user_dam.php";s:4:"c236";s:20:"displayFileLinks.php";s:4:"7a57";s:21:"ext_conf_template.txt";s:4:"3003";s:12:"ext_icon.gif";s:4:"999b";s:17:"ext_localconf.php";s:4:"6a1d";s:14:"ext_tables.php";s:4:"03e2";s:14:"ext_tables.sql";s:4:"e14f";s:19:"imageMarkerFunc.php";s:4:"a388";s:16:"locallang_db.xml";s:4:"d9bb";s:14:"doc/manual.sxw";s:4:"29a1";s:20:"static/constants.txt";s:4:"085e";s:16:"static/setup.txt";s:4:"3e68";}',
);

?>