<?php
namespace Heilmann\JhBasketballbundImport2\Hook;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Jonathan Heilmann <mail@jonathan-heilmann.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * ClearCacheHook
 */
class ClearCacheHook implements \TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface {

	/**
	 *
	 *
	 *
	 *
	 *
	 *
	 */
	public function manipulateCacheActions(&$cacheActions, &$optionValues) {
		if ($GLOBALS['BE_USER']->isAdmin()) {
			$cacheActions[] = array(
				'id'		=> 'jh_basketballbund_import_2',
				'title'	=> 'basketball-bund.net',
				'href'	=> $this->backPath . 'tce_db.php?vC=' . $GLOBALS['BE_USER']->veriCode() . '&cacheCmd=jh_basketballbund_import_2&ajaxCall=0' . \TYPO3\CMS\Backend\Utility\BackendUtility::getUrlToken('tceAction'),
				'icon'	=> \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-system-cache-clear-impact-low'),
			);
		}
	}


	/**
	 * This method is called by the CacheMenuItem in the Backend
	 *
	 * @param \array $_params
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
	 */
	public static function clear($_params, $dataHandler) {
		if (in_array($_params['cacheCmd'], array('pages', 'jh_basketballbund_import_2')) && $GLOBALS['BE_USER']->isAdmin()) {
			$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('tx_jhbasketballbundimport2_domain_model_cache');
		}
	}
}