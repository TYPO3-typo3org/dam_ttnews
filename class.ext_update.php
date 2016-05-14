<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Stefan Galinski <stefan.galinski@frm2.tum.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_site . t3lib_extMgm::siteRelPath('dam') . 'lib/class.tx_dam_guifunc.php');

/**
 * Class for updating the db
 *
 * @author Stefan Galinski <stefan.galinski@frm2.tum.de>
 * @package TYPO3-DAM
 * @subpackage tx_dam
 */
class ext_update  {

	/** @var array [tt_news.uid][media|pics][counter] => tx_dam.uid */
	var $fileCopies = array();

	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return string HTML
	 */
	function main()
	{
		$content = $GLOBALS['SOBE']->doc->section('File Copies', '', 0, 1, 2);
		if(t3lib_div::_GP('submitted')) {
			$data = t3lib_div::_GP('update');
			if($this->execFileReferences($data))
				$content .= 'File copies converted to references!';
			else
				$content .= 'Some or all file copies couldnt be converted!';
		}
		$content .= $this->outputFileReferences();
		
		return $content;
	}

	/**
	 * Checks how many rows are found and returns true if there are any
	 *
	 * @return boolean
	 */
	function access()
	{
		if ($this->checkFileReferences())
			return true;

		return false;
	}

	/**
	 * fast file copy check
	 *
	 * @return boolean true after first occurence
	 */
	function checkFileReferences()
	{
		$entries = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_dam', '1');
		while ($entry = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($entries)) {
			$damUploads = $this->getMediaUsageUploads($entry['uid']);
			$damMedia = $this->getMediaUsageUploads($entry['uid'], 'uploads/media/');
			$damUploads = array_merge($damUploads, $damMedia);
			if (is_array($damUploads) && count($damUploads))
				foreach($damUploads as $row)
					return true;
		}
	
		return false;
	}

	function getMediaUsageUploads($uid, $dir='uploads/pics/')
	{
		$fields = 'tx_dam_file_tracking.*';

		$where = array();
		$where[] = 'tx_dam.uid = "' . $uid . '"';
		$where[] = 'tx_dam_file_tracking.file_hash = tx_dam.file_hash';
		$where[] = 'tx_dam_file_tracking.file_path=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($dir, 'tx_dam_file_tracking');
		$where = implode(' AND ', $where);

		$rowsUploads = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$fields,
			'tx_dam_file_tracking, tx_dam',
			$where
		);

		$rows = array();

		if($rowsUploads) {
			$whereFilenames = array();
			foreach ($rowsUploads as $row) {
				$whereFilenames[] = 'image REGEXP BINARY ' .
					$GLOBALS['TYPO3_DB']->fullQuoteStr('[^, ]*'.$row['file_name'].'[^, ]*','tt_news');
				$whereFilenames[] = 'news_files REGEXP BINARY ' .
					$GLOBALS['TYPO3_DB']->fullQuoteStr('[^, ]*'.$row['file_name'].'[^, ]*','tt_news');
			}
			$where = implode(' OR ', $whereFilenames);
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('"tt_news" AS tablenames, uid AS uid_foreign, image, news_files AS type',
				'tt_news', $where.t3lib_BEfunc::deleteClause('tt_news'));
		}

		return $rows;
	}

	/**
	 * Outputs a table with all data rows which needs changes
	 *
	 * @return string html code
	 */
	function outputFileReferences()
	{
		$entries = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_dam', '1', '', 'uid');
		while ($entry = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($entries)) {
			$damUploads = $this->getMediaUsageUploads($entry['uid']);
			$damMedia = $this->getMediaUsageUploads($entry['uid'], 'uploads/media/');
			$damUploads = array_merge($damUploads, $damMedia);
			if (is_array($damUploads) && count($damUploads)) {
				foreach($damUploads as $row) {
					$type = !empty($row['image']) ? 'pics' : 'media';
					$this->fileCopies[$row['uid_foreign']][$type][] = $entry['uid'];
				}
			}
		}

		if(!count($this->fileCopies))
			return '';

		$content .= '</div></form><form action="' .
			htmlspecialchars(t3lib_div::linkThisScript()) . '" method="post"><div>';

		// hint
		$content .= '<strong>Please use the check index and check upload functionality in Media-&gt;Tools first! Its needed to find all file copies in the system. You can use a tool like kb_clean_files to remove the unnecessary files afterwards. To finalize the cleanup call the check upload function again and cleanup your table references(check database-&gt;Manage Reference Index)!</strong>';

		// TODO never inserted into the final html code (no doc object)
		$GLOBALS['SOBE']->doc->inDocStyles['dam_ttcontent'] = '
			#tx-dam_ttcontent-table {
				margin-bottom: 10px;
				width: 100%;
				clear: both;
			}

			#tx-dam_ttcontent-table thead {
				font-size: 0.7em;
				font-weight: bold;
			}

			#tx-dam_ttcontent-table tbody {
				text-align: center;
			}

			#tx-dam_ttcontent-table tbody td, #tx-dam_ttcontent-table thead th {
				padding: 2px;
				border: 1px solid #AAA;
			}';

		$content .= '<table id="tx-dam_ttcontent-table" style="margin-bottom: 10px; width: 100%; clear: both;">';
		$content .= '<thead style="font-weight: bold;"><tr>';
		$content .= '<th class="bgColor5" style="padding: 2px; border: 1px solid #AAA;">uid (dam entry)</th>';
		$content .= '<th class="bgColor5" style="padding: 2px; border: 1px solid #AAA;">uid (tt_news element)</th>';
		$content .= '<th class="bgColor5" style="padding: 2px; border: 1px solid #AAA;">UPDATE</th>';
		$content .= '</tr></thead>';
		$content .= '<tbody style="text-align: center;">';
		foreach($this->fileCopies as $uid_foreign => $fileCopy) {
			foreach($fileCopy as $type => $nFileCopy) {
				foreach($nFileCopy as $uid_local)
				{
					$newsRec = t3lib_BEfunc::getRecord ('tt_news', $uid_foreign, 'title');
					$damRec = t3lib_BEfunc::getRecord ('tx_dam', $uid_local, 'file_dl_name AS file');

					$content .= '<tr>';
					$content .= '<td class="bgColor4" style="padding: 2px; border: 1px solid #AAA;">' .
						$uid_local . ' (' . (!empty($damRec['file']) ? $damRec['file'] : 'Empty') . ')</td>';
					$content .= '<td class="bgColor4" style="padding: 2px; border: 1px solid #AAA;">' .
						$uid_foreign . ' (' . (!empty($newsRec['title']) ? $newsRec['title'] : 'Empty') . ')</td>';
					$content .= '<td class="bgColor4" style="padding: 2px; border: 1px solid #AAA;">' .
						'<input type="checkbox" checked="checked" name="update[' .
						$uid_foreign . '][' . $type . '][]" value="' . $uid_local . '" /></td>';
					$content .= '</tr>';
				}
			}
		}
		$content .= '</tbody></table>';
		$content .= '<p style="text-align: center;">';
		$content .= '<input type="hidden" name="submitted" value="1" />';
		$content .= '<input type="submit" value="UPDATE!" />';
		$content .= '</p>';
	
		return $content;
	}

	/**
	 * changes the reference to the dam way and removed the old ones
	 *
	 * @param array file copy (uid_dam => uid_ttcontent)
	 * @return boolean
	 */
	function execFileReferences($entries)
	{
		$error = 0;
		foreach($entries as $uid_foreign => $entry)
		{
			foreach($entry as $type => $nEntry)
			{
				$counter = 0;
				foreach($nEntry as $uid_local)
				{
					$insert = array(
						'uid_local' => $uid_local,
						'uid_foreign' => $uid_foreign,
						'ident' => ($type == 'pics' ? 'tx_damnews_dam_images' : 'tx_damnews_dam_media'),
						'tablenames' => 'tt_news',
						'sorting' => '0',
						'sorting_foreign' => ++$counter
					);

					if($type == 'pics') {
						if(!$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news', 'uid=' . $uid_foreign,
							array('image' => '', 'tx_damnews_dam_images' => count($entries[$uid_foreign][$type]))))
							++$error;
					}
					else {
						if(!$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news', 'uid=' . $uid_foreign,
							array('news_files' => '', 'tx_damnews_dam_media' => count($entries[$uid_foreign][$type]))))
							++$error;
					}

					if(!$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_dam_mm_ref', $insert))
						++$error;
				}
			}
		}

		if($error)
			return false;

		return true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_ttnews/class.ext_update.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_ttnews/class.ext_update.php']);
}

?>
