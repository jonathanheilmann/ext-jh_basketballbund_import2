<?php
namespace Heilmann\JhBasketballbundImport2\Controller;

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

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TeamCollectionController
 */
class TeamCollectionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * teamRepository
	 *
	 * @var \Heilmann\JhBasketballbundImport2\Domain\Repository\CacheRepository
	 * @inject
	 */
	protected $cacheRepository = NULL;

	/**
	 * teamCollectionRepository
	 *
	 * @var \Heilmann\JhBasketballbundImport2\Domain\Repository\TeamCollectionRepository
	 * @inject
	 */
	protected $teamCollectionRepository = NULL;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var string
	 */
	protected $llPrefix = 'LLL:EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_teamcollection.xlf:';

	/**
	 * action display
	 *
	 * @return void
	 */
	public function displayAction() {
		//$start=microtime(true);
		$this->cObj = $this->configurationManager->getContentObject();
		$contentuid = $this->cObj->data['uid'];

		$cache = $this->cacheRepository->findByCeUid($contentuid)->getFirst();
		if (empty($cache)) {
			$cache = $this->objectManager->get('Heilmann\JhBasketballbundImport2\Domain\Model\Cache');
			$cache->setPid($this->settings['storagefolder']);
			$cache->setCeUid($contentuid);
			$this->cacheRepository->add($cache);
			$this->persistenceManager->persistAll();
		}

		//get layout of table
		$layout = $this->getLayout();
		$content = unserialize($cache->getData());
		if ($content['lastupdate'] >= time() - 60 * 60 * $this->settings['cachelifetime'] && $cache->getSettingsMd5() == $this->settings_md5()) {
			//get data from cache
			$date = new \DateTime();
			$content['lastupdate'] = $date->setTimestamp($content['lastupdate']);
		} else {
			//evaluate which teamcollection should be displayed
			$teams = $this->teamCollectionRepository->findByUid($this->settings['displayteamcollection']);
			$leagues = array();
			$tableRows = array();
			$content = array();
			$content['data'][0] = array(
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.team', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.number', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.matchday', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.date', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.home', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.guest', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.team', 'JhBasketballbundImport2')
			);
			foreach ($teams->getTeams() as $key => $team) {
				$merged = array_merge($content['data'], $this->getTableRow($team, $layout['displayteam']));
				$content['data'] = $merged;
			}
			$content['lastupdate'] = time();
			$content['link'] = 'http://www.basketball-bund.net/';

			//write new data to database
			$cache->setSettingsMd5($this->settings_md5());
			$cache->setData(serialize($content));
			$this->cacheRepository->update($cache);
		}

		$this->persistenceManager->persistAll();

		$this->view->assign('content', $content);
		$this->view->assign('layout', $layout);
	}

	/**
	 *
	 *
	 * @param $team object
	 * @param $displayTeam string
	 */
	private function getTableRow($team, $displayTeam) {
		//import table from basketball-bund.net
		$importpath = 'http://www.basketball-bund.net/public/ergebnisse.jsp?print=1&viewDescKey=sport.dbb.liga.ErgebnisseViewPublic/index.jsp_&liga_id=' . $team->getLeague();
		$import = $this->import($importpath);
		if ($import['success'] === FALSE) {
			return array(
				'data' => array(
					'Fehler'
				)
			);
		} else {
			$import = $import['import'];
		}
		//replace some characters
		$import = preg_replace('/
|
/s', '', $import
		);
		$import = str_replace('&nbsp;', ' ', $import);
		//if linebreaks are allowed remove nobr-tag
		$import = $this->allowbr($import);
		//split importet table to rows
		preg_match_all('#\\<tr\\>(.*)\\</tr\\>#Us', $import, $tablerows, PREG_SET_ORDER);
		$result = array();

		//evaluate if quater-results are available of if the match has been accelerated
		if ($tablerows[8][1] == '<td class="sportViewHeader">1. Viertel </td>') {
			$headerend_i = 12;
		} elseif ($tablerows[8][1] == '<td class="sportViewHeader">Vor Verl. </td>') {
			$headerend_i = 9;
		} else {
			$headerend_i = 8;
		}
		//start table
		$i = 0;
		foreach ($tablerows as $wert) {
			$i++;
			//the two three rows should not be shown
			if ($i <= $headerend_i) {
				continue;
			}
			//write table-content
			if ($i > $headerend_i) {
				$res = array_filter($wert, function($var) use ($displayTeam) { return preg_match("/\b$displayTeam\b/i", $var); });
				if (!empty($res)) {
					//split to field-data
					preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $wert[1], $tabledata, PREG_SET_ORDER);

					$j = 1;
					$result[$i][0] = $team->getTeam();
					foreach ($tabledata as $tabledata_single) {
						preg_match_all('/<div[^>]+>(.*?)<\\/div>/', $tabledata_single[1], $tabledata_single_clear, PREG_SET_ORDER);
						if (empty($tabledata_single_clear)) {
							$result[$i][$j] = trim($tabledata_single[1]);
						} else {
							$result[$i][$j] = trim($tabledata_single_clear[0][1]);
						}
						$j++;
					}
					return $result;
				}
			}
		}
		//return tabledata
		return $result;
	}

	/**
	 * Get the details of the table
	 *
	 * @return array
	 */
	private function getDetails() {
		$details = array();
		return $details;
	}

	/**
	 * Get the layout of the table
	 *
	 * @return array
	 */
	private function getLayout() {
		$layout = array();
		//evaluate the bordersize for tables
		switch ($this->settings['flexform']['displayborder']) {
			case 0:
				$layout['bordersize'] = '0';
				break;
			case 1:
				$layout['bordersize'] = '1';
				break;
			case 2:
				$layout['bordersize'] = $this->settings['displayborder'];
				break;
		}
		//evaluate which team should be displayed
		switch ($this->settings['flexform']['displayteam']) {
			case 1:
				$layout['displayteam'] = $this->settings['flexform']['displayteamlocal'];
				break;
			case 2:
				$layout['displayteam'] = $this->settings['stressteam'];
				break;
		}
		return $layout;
	}

	/**
	 * Import data from given path
	 *
	 * @param $path string
	 * @return array
	 */
	private function import($path) {
		$success = TRUE;
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] == TRUE) {
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $path
			));
			$import = curl_exec($curl);
			if ($import === FALSE) {
				$success = FALSE;
				$import  = 'error - could not import path "' . $path . '"';
				$import .= "\n";
				$import .= 'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
			}
			curl_close($curl);
		} else {
			if ($stream = fopen($path, 'r')) {
				$import = stream_get_contents($stream,
					-1,
					0);
				fclose($stream);
			} else {
				$success = FALSE;
				$import = 'error - could not import path "' . $path . '"';
			}
		}
		return array('success' => $success, 'import' => $import);
	}

	/**
	 * Remove line-breaks if required
	 *
	 * @param $string string
	 * @return string
	 */
	private function allowbr($string) {
		switch ($this->settings['flexform']['nobr']) {
			case 0:
				$allowbr = '0';
				break;
			case 1:
				$allowbr = '1';
				break;
			case 2:
				$allowbr = $this->settings['allowbr'];
				break;
		}
		//if linebreaks are allowed remove nobr-tag
		if ($allowbr == 1) {
			$string = str_replace('<NOBR>', '', $string);
			$string = str_replace('</NOBR>', '', $string);
			$string = str_replace('<nobr>', '', $string);
			$string = str_replace('</nobr>', '', $string);
		}
		return $string;
	}

	function settings_md5() {
        return md5(serialize($this->settings));
    }
}