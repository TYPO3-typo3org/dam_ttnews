<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'DAM ttnews');

$tc = array(
	'tx_damnews_dam_images' => txdam_getMediaTCA('image_field', 'tx_damnews_dam_images'),
	'tx_damnews_dam_description' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:dam_ttnews/locallang_db.xml:tt_news.tx_damnews_dam_description',
		'config' => array (
			'type' => 'user',
			'userFunc' => 'EXT:dam_ttnews/class.user_dam.php:user_class->userTCAformDAM',
			'noTableWrapping' => false,
			'readOnly' => true
		)
	)
);

//use tt_news l10n_mode_imageExclude settings
$confArr_ttnews = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tt_news']);
$tc['tx_damnews_dam_images']['l10n_mode'] = ($confArr_ttnews['l10n_mode_imageExclude'] ? 'exclude' : 'mergeIfNotBlank');

$tc['tx_damnews_dam_images']['exclude'] = 1;
$tc['tx_damnews_dam_images']['label'] = 'LLL:EXT:dam_ttnews/locallang_db.xml:tt_news.tx_damnews_dam_images';

$tempSetup = $GLOBALS['T3_VAR']['ext']['dam_ttnews']['setup'];


t3lib_div::loadTCA('tt_news');
t3lib_extMgm::addTCAcolumns('tt_news', $tc, 1);
t3lib_extMgm::addToAllTCAtypes('tt_news', 'tx_damnews_dam_description;;;;1-1-1', '', 'after:imagecaption');


if ($tempSetup['media_add_ref']) {
  if ($tempSetup['media_add_orig_field']) {  
    t3lib_extMgm::addToAllTCAtypes('tt_news','tx_damnews_dam_images','0','after:image');
    t3lib_extMgm::addToAllTCAtypes('tt_news','tx_damnews_dam_images','1','after:image');
    t3lib_extMgm::addToAllTCAtypes('tt_news','tx_damnews_dam_images','2','after:image');
  }
  else
  {
    $TCA['tt_news']['types']['0']['showitem'] = str_replace('image;', ' tx_damnews_dam_images;', $TCA['tt_news']['types']['0']['showitem']);
    $TCA['tt_news']['types']['1']['showitem'] = str_replace('image;', ' tx_damnews_dam_images;', $TCA['tt_news']['types']['1']['showitem']);
    $TCA['tt_news']['types']['2']['showitem'] = str_replace('image;', ' tx_damnews_dam_images;', $TCA['tt_news']['types']['2']['showitem']);
  }
}


$tc_el = array(
	'tx_damnews_dam_media' => txdam_getMediaTCA('media_field', 'tx_damnews_dam_media')
);

$tc_el['tx_damnews_dam_media']['l10n_mode'] = 'mergeIfNotBlank';
$tc_el['tx_damnews_dam_media']['exclude']= 1;
$tc_el['tx_damnews_dam_media']['label'] = 'LLL:EXT:dam_ttnews/locallang_db.xml:tt_news.tx_damnews_dam_media';

t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tc_el,1);

if ($tempSetup['media_add_ref']) {
  if ($tempSetup['media_add_orig_field']) {
    t3lib_extMgm::addToAllTCAtypes('tt_news','tx_damnews_dam_media','0','after:news_files');
    t3lib_extMgm::addToAllTCAtypes('tt_news','tx_damnews_dam_media','1','after:news_files');
    t3lib_extMgm::addToAllTCAtypes('tt_news','tx_damnews_dam_media','2','after:news_files');
  }
  else
  {
    $TCA['tt_news']['types']['0']['showitem'] = str_replace('news_files;', ' tx_damnews_dam_media;', $TCA['tt_news']['types']['0']['showitem']);
    $TCA['tt_news']['types']['1']['showitem'] = str_replace('news_files;', ' tx_damnews_dam_media;', $TCA['tt_news']['types']['1']['showitem']);
    $TCA['tt_news']['types']['2']['showitem'] = str_replace('news_files;', ' tx_damnews_dam_media;', $TCA['tt_news']['types']['2']['showitem']);
  }
}




?>
