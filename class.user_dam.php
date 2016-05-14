<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Peter Klein (pmk@io.dk)
*  (c) 2007 Stefan Galinski (stefan.galinski@frm2.tum.de)
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
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * tt_news modification to copy content from dam a file to the title, alt or caption field of the selected image
 *
 * @author Peter Klein <pmk@io.dk>
 * @subauthor Stefan Galinski <stefan.galinski@frm2.tum.de>
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */

/**
 * tt_news modification to copy content from dam a file to the title, alt or caption field of the selected image
 *
 * @author Peter Klein <pmk@io.dk>
 * @subauthor Stefan Galinski <stefan.galinski@frm2.tum.de>
 * @package Typo3
 */
class user_class
{
	function userTCAformDAM($PA, $fobj)
	{
		$this->tceforms = &$PA['pObj'];
		$PArow = $PA['row'];
		$PAconfig = $PA['fieldConf']['config'];

		if (intval($PArow['uid']) == 0)
			return '';

		// Add custom JavaScript functions
		$fobj->additionalJS_pre['copyDAMfield']='
			function copyDAMfield(field) {
				tmp = \'data[tt_news][' . $PArow['uid'] . '][\'+field+\']\';
				if (!document.' . $this->tceforms->formName . '[tmp]) {
					tmp = \'TSFE_EDIT[data][tt_news][' . $PArow['uid'] . '][\'+field+\']\';
					if (!document.' . $this->tceforms->formName . '[tmp])
					return false;
				}

				document.' . $this->tceforms->formName . '[tmp].value =
				document.' . $this->tceforms->formName . '[\'temp_' . $PA['itemFormElName'] . '\'].value;
			};
			function setDAMfield(el) {
				document.' . $this->tceforms->formName . '[\'temp_' . $PA['itemFormElName'] . '\'].value =
 					el[el.selectedIndex].value;
			}';

		// Collect DAM info
		$out = array();
  	$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_dam.description, tx_dam.alt_text, ' .
			'tx_dam.caption, tx_dam.abstract, tx_dam.title',
			'tx_dam', 'tx_dam_mm_ref', 'tt_news',
			'AND tx_dam_mm_ref.tablenames="tt_news" AND tx_dam_mm_ref.ident="tx_damnews_dam_images" ' .
			'AND tx_dam_mm_ref.uid_foreign="' . $PArow['uid'] . '"', '', 'tx_dam_mm_ref.sorting_foreign ASC');
		$tarows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if($tarows) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$out['description'][] = str_replace(array(chr(10),chr(13)), ' ', $row['description'].' ');
				$out['alt_text'][] = str_replace(array(chr(10),chr(13)), ' ', $row['alt_text'].' ');
				$out['caption'][] = str_replace(array(chr(10),chr(13)), ' ', $row['caption'].' ');
				$out['abstract'][] = str_replace(array(chr(10),chr(13)), ' ', $row['abstract'].' ');
				$out['title'][] = str_replace(array(chr(10),chr(13)), ' ', $row['title'].' ');
			}
    }

		$disabled = '';
		if ($this->tceforms->renderReadonly || $PAconfig['readOnly'])  {
			$disabled = ' disabled="disabled" readonly="readonly"';
		}

		// Create options
		if(is_array($out['caption']))
			$availableContent = '<option value="' . htmlspecialchars(implode($out['caption'], chr(10))) .
				'">DAM ' . $this->tceforms->sL('LLL:EXT:lang/locallang_general.xml:LGL.caption', true) .
				'</option>';

		if(is_array($out['description']))
			$availableContent .= '<option value="' . htmlspecialchars(implode($out['description'], chr(10))) .
				'">DAM ' . $this->tceforms->sL('LLL:EXT:lang/locallang_general.xml:LGL.description', true) .
				'</option>';

		if(is_array($out['alt_text']))
			$availableContent .= '<option value="' . htmlspecialchars(implode($out['alt_text'], chr(10))) .
				'">DAM ' . $this->tceforms->sL('LLL:EXT:dam/locallang_db.xml:tx_dam_item.alt_text', true) .
				'</option>';

		if(is_array($out['abstract']))
			$availableContent .= '<option value="' . htmlspecialchars(implode($out['abstract'], chr(10))) .
				'">DAM ' . $this->tceforms->sL('LLL:EXT:dam/locallang_db.xml:tx_dam_item.abstract', true) .
				'</option>';

		if(is_array($out['title']))
			$availableContent .= '<option value="' . htmlspecialchars(implode($out['title'], chr(10))) .
				'">DAM ' . $this->tceforms->sL('LLL:EXT:lang/locallang_general.xml:LGL.title', true) .
				'</option>';

		// Return custom field
		return '
			<div>
				<select onchange="setDAMfield(this)">
					<option value="">---</option>
					' . $availableContent . '
				</select>
			</div>
			<div>
				<textarea style="width: 460px;" cols="48" rows="' . $tarows . '" wrap="off" name="temp_' .
					$PA['itemFormElName'] . '"' . $disabled . '></textarea>
			</div>
			<div>
				<input type="button" name="c1" value="' . $this->tceforms->sL('LLL:EXT:dam_ttnews/locallang_db.xml:tt_news.copy1', true) . '" onclick="copyDAMfield(\'imagecaption\');return false;">
				<input type="button" name="c2" value="' . $this->tceforms->sL('LLL:EXT:dam_ttnews/locallang_db.xml:tt_news.copy2', true) . '" onclick="copyDAMfield(\'imagealttext\');return false;">
				<input type="button" name="c3" value="' . $this->tceforms->sL('LLL:EXT:dam_ttnews/locallang_db.xml:tt_news.copy3', true) . '" onclick="copyDAMfield(\'imagetitletext\');return false;">
			</div>';
	}
}

?>
