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
 * TeamController
 */
class TeamController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * teamRepository
	 *
	 * @var \Heilmann\JhBasketballbundImport2\Domain\Repository\TeamRepository
	 * @inject
	 */
	protected $teamRepository = NULL;

	/**
	 * teamRepository
	 *
	 * @var \Heilmann\JhBasketballbundImport2\Domain\Repository\CacheRepository
	 * @inject
	 */
	protected $cacheRepository = NULL;

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
	protected $llPrefix = 'LLL:EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_team.xlf:';

	/**
	 * action display
	 *
	 * @return void
	 */
	public function displayAction() {
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
			//evaluate which team should be displayed
			$team = $this->teamRepository->findByUid($this->settings['displayteam']);
			//evaluate which content-type should be rendered
			switch ($this->settings['displaycontent']) {
				case 'table':
					$content = $this->getTable($cache, $team);
					break;
				case 'results':
					$content = $this->getResults($cache, $team);
					break;
				case 'playing_schedule':
					$content = $this->getPlayingSchedule($cache, $team);
					break;
				case 'statistic':
					$content = $this->getStatistic($cache, $team);
					break;
			}
		}

		$this->persistenceManager->persistAll();

		$this->view->assign('content', $content);
		$this->view->assign('layout', $layout);
	}

	/**
	 * Get the table data
	 *
	 * @param $cache object
	 * @param $team object
	 * @return array
	 */
	private function getTable($cache, $team) {
		//import table from basketball-bund.net
		$importpath = 'http://www.basketball-bund.net/public/tabelle.jsp?print=1&viewDescKey=sport.dbb.views.TabellePublicView/index.jsp_&liga_id=' . $team->getLeague();
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
		$result = array(

		);
		//add header-row
		$result['data'][0] = array(
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.rank', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.team', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.games', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.w_l', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.points', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.baskets', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'table.displayrows.difference', 'JhBasketballbundImport2')
		);
		$i = 0;
		foreach ($tablerows as $wert) {
			$i++;
			//the first nine rows should not be shown
			if ($i <= 9) {
				continue;
			}
			//with row 10 the table starts
			if ($i > 9) {
				//split to field-data
				$wert[0] = str_replace('<tr>', '', $wert[0]);
				$wert[0] = str_replace('</tr>', '', $wert[0]);
				$wert[0] = trim($wert[0]);
				if (stristr($wert[0], '<strike>')) {
					preg_match_all('#<[^td>]+>(.*)</[^td>]+>#Usm', $wert[0], $tabledata, PREG_SET_ORDER);
				} else {
					preg_match_all('#<[^>]+>(.*)</[^>]+>#Usm', $wert[0], $tabledata, PREG_SET_ORDER);
				}
				$j = 0;
				foreach ($tabledata as $tabledata_single) {
					if (stristr($tabledata_single[0], '<strike>')) {
						$result['data'][$i - 9][$j] = '<span class="strike">' .  trim($tabledata_single[1]) . '</span>';
					} else {
						$result['data'][$i - 9][$j] = trim($tabledata_single[1]);
					}
					$j++;
				}

			}
		}
		$result['lastupdate'] = time();
		$result['link'] = 'http://www.basketball-bund.net/index.jsp?Action=102&liga_id=' . $team->getLeague();
		//write new data to database
		$cache->setSettingsMd5($this->settings_md5());
		$cache->setData(serialize($result));
		$this->cacheRepository->update($cache);
		//return tabledata
		return $result;
	}

	/**
	 * Get the results data
	 *
	 * @param $cache object
	 * @param $team object
	 * @return array
	 */
	private function getResults($cache, $team) {
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
		$result = array(

		);
		// if necessary create checkbox to display all teams
		if ($this->settings['displayselection'] == 'otherteamsbyjq') {
			$result['preHeader'] = '<div class="checkbox_displayall">'.LocalizationUtility::translate($this->llPrefix.'displayAllMatches', 'JhBasketballbundImport2').' <input type="checkbox" name="" id="results_allteams" /></div>';
		}
		// evaluate if quater-results are available of if the match has been accelerated
		if ($tablerows[8][1] == '<td class="sportViewHeader">1. Viertel </td>') {
			$headerend_i = 12;
			// add header-row
			// changed in version 0.0.4: column "details" has been removed by basketball-bund.net
			$result['data'][0] = array(
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.number', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.matchday', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.date', 'JhBasketballbundImport2'),
				//LocalizationUtility::translate($this->llPrefix.'results.displayrows.details', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.home', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.guest', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.endresult', 'JhBasketballbundImport2'),
				'1. Viertel',
				'2. Viertel',
				'3. Viertel',
				'4. Viertel'
			);
		} elseif ($tablerows[8][1] == '<td class="sportViewHeader">Vor Verl. </td>') {
			$headerend_i = 9;
			// add header-row
			// changed in version 0.0.4: column "details" has been removed by basketball-bund.net
			$result['data'][0] = array(
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.number', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.matchday', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.date', 'JhBasketballbundImport2'),
				//LocalizationUtility::translate($this->llPrefix.'results.displayrows.details', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.home', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.guest', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.endresult', 'JhBasketballbundImport2'),
				'Vor Verl.'
			);
		} else {
			$headerend_i = 8;
			// add header-row
			// changed in version 0.0.4: column "details" has been removed by basketball-bund.net
			$result['data'][0] = array(
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.number', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.matchday', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.date', 'JhBasketballbundImport2'),
				//LocalizationUtility::translate($this->llPrefix.'results.displayrows.details', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.home', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.guest', 'JhBasketballbundImport2'),
				LocalizationUtility::translate($this->llPrefix.'results.displayrows.endresult', 'JhBasketballbundImport2'),
			);
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
				//split to field-data
				preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $wert[1], $tabledata, PREG_SET_ORDER);
				$j = 0;
				foreach ($tabledata as $tabledata_single) {
					preg_match_all('/<div[^>]+>(.*?)<\\/div>/', $tabledata_single[1], $tabledata_single_clear, PREG_SET_ORDER);
					if (empty($tabledata_single_clear)) {
						$result['data'][$i - $headerend_i][$j] = trim($tabledata_single[1]);
					} else {
						$result['data'][$i - $headerend_i][$j] = trim($tabledata_single_clear[0][1]);
					}
					$j++;
				}
			}
		}
		$result['lastupdate'] = time();
		$result['link'] = 'http://www.basketball-bund.net/index.jsp?Action=103&liga_id=' . $team->getLeague();
		//write new data to database
		$cache->setSettingsMd5($this->settings_md5());
		$cache->setData(serialize($result));
		$this->cacheRepository->update($cache);
		//return tabledata
		return $result;
	}

	/**
	 * Get the playing schedule data
	 *
	 * @param $cache object
	 * @param $team object
	 * @return array
	 */
	private function getPlayingSchedule($cache, $team) {
		//import table from basketball-bund.net
		$importpath = 'http://www.basketball-bund.net/public/spielplan_list.jsp?print=1&viewDescKey=sport.dbb.liga.SpielplanViewPublic/index.jsp_&liga_id=' . $team->getLeague();
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
		//split imported table to rows
		preg_match_all('#\\<tr\\>(.*)\\</tr\\>#Us', $import, $tablerows, PREG_SET_ORDER);
		$result = array(

		);
		//if necessary create checkbox to display all teams
		if ($this->settings['displayselection'] == 'otherteamsbyjq') {
			$result['preHeader'] = '<div class="checkbox_displayall">'.LocalizationUtility::translate($this->llPrefix.'displayAllMatches', 'JhBasketballbundImport2').' <input type="checkbox" name="" id="playingschedule_allteams" /></div>';
		}
		//add header row
		$result['data'][0] = array(
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.number', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.day', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.date', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.home', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.guest', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.hall', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'playing_schedule.displayrows.referee', 'JhBasketballbundImport2')
		);
		$i = 0;
		$j = 0;
		$first_j = 0;
		//$last_j = 0;
		$whole_tablerow = array(

		);
		//$sr2 = 0;
		foreach ($tablerows as $wert) {
			$used = 0;
			$i++;
			//the two three rows should not be shown
			if ($i <= 9) {
				continue;
			}
			//workaround due to problems with referee: write a new array with one table-row in one array-number instead of two or three
			if ($i > 9) {
				if (strstr($wert[0], '<b>COA</b>') and $used == 0) {
					$whole_tablerow[$j] .= $wert[0];
					$used = 1;
				}
				if (strstr($wert[0], '<b>SR2</b>') and !strstr($wert[0], '<span title="">') and $used == 0) {
					$whole_tablerow[$j] .= $wert[0];
					$used = 1;
				}
				if ($used == 0) {
					if (strstr($wert[0], '<b>SR1</b>')) {
						if ($j == 0 and $first_j == 0) {
							$whole_tablerow[$j] = $wert[0];
							$first_j = 1;
						} else {
							$whole_tablerow[$j] .= '</table><td></tr>';
							$j++;
							$whole_tablerow[$j] = $wert[0];
						}
					} elseif (strstr($wert[0], '<b>SR2</b>')) {
						if ($j == 0 and $first_j == 0) {
							$whole_tablerow[$j] = $wert[0];
							$first_j = 1;
						} else {
							$whole_tablerow[$j] .= '';
							$j++;
							$whole_tablerow[$j] = $wert[0];
						}
					} else {
						$j++;
						$whole_tablerow[$j] = $wert[1];
					}
				}
			}
		}
		//use rewritten array with referee
		$i = 1;
		foreach ($whole_tablerow as $row) {
			//split to field-data
			preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $row, $col, PREG_SET_ORDER);

			// handle two referees:
			if (count($col) == 8) {
				preg_match_all('/<table[^>]+>(.*?)<\\/table>/', $row, $referees, PREG_SET_ORDER);
				preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $referees[0][1], $referee, PREG_SET_ORDER);
				$col[6][0] = strip_tags(trim($referee[0][1]), '<b>') . '<br/>' . strip_tags(trim($referee[1][1]), '<b>');
				$col[6][1] = $col[6][0];
				unset($col[7]);
			}

			$j = 0;
			foreach ($col as $col_data) {
				//write field
				$col_data[1] = str_replace('../images/icons/verlegt.png', 'http://www.basketball-bund.net/images/icons/verlegt.png', $col_data[1]);
				$col_data[1] = str_replace('../images/icons/abgesagt.png', 'http://www.basketball-bund.net/images/icons/abgesagt.png', $col_data[1]);
				$col_data[1] = strip_tags($col_data[1], '<img><br><b>');
				if (stristr($col_data[0], '<strike>')) {
					$result['data'][$i][$j] = '<span class="strike">' .  trim($col_data[1]) . '</span>';
				} else {
					$result['data'][$i][$j] = trim($col_data[1]);
				}
				$j++;
			}
			$i++;
		}
		$result['lastupdate'] = time();
		$result['link'] = 'http://www.basketball-bund.net/index.jsp?Action=101&liga_id=' . $team->getLeague();
		//write new data to database
		$cache->setSettingsMd5($this->settings_md5());
		$cache->setData(serialize($result));
		$this->cacheRepository->update($cache);
		//return tabledata
		return $result;
	}

	/**
	 * Get the statistic data
	 *
	 * @param $cace object
	 * @param $team object
	 * @return array
	 */
	private function getStatistic($cache, $team) {
		//import table from basketball-bund.net
		$importpath = 'http://www.basketball-bund.net/liga/statistik_team.jsp?print=1&viewDescKey=sport.dbb.views.TeamStatView/templates/base_template.jsp_&liga_id=' . $team->getLeague();
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
		$result = array(

		);
		//add header row
		$result['data'][0] = array(
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.rank', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.team', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.points', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.baskets', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.difference', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.fe', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.percentage', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.double', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.triple', 'JhBasketballbundImport2'),
			LocalizationUtility::translate($this->llPrefix.'statistic.displayrows.fouls', 'JhBasketballbundImport2')
		);
		$i = 0;
		foreach ($tablerows as $wert) {
			$i++;
			//the first 13 rows should not be shown
			if ($i <= 13) {
				continue;
			}
			//write table-content
			if ($i > 13) {
				//split to field-data
				preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $wert[1], $row, PREG_SET_ORDER);
				$j = 0;
				foreach ($row as $field) {
					//write field
					$field[1] = str_replace('align="center"', '', $field[1]);
					$field[1] = str_replace('align="right"', '', $field[1]);
					$content_table .= $field[0];
					$result['data'][$i - 13][$j] = trim($field[1]);
					$j++;
				}
			}
		}
		$result['lastupdate'] = time();
		$result['link'] = 'http://www.basketball-bund.net/statistik.do?reqCode=statTeam&liga_id=' . $team->getLeague();
		//write new data to database
		$cache->setSettingsMd5($this->settings_md5());
		$cache->setData(serialize($result));
		$this->cacheRepository->update($cache);
		//return tabledata
		return $result;
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
		//evaluate which team should be stressed
		switch ($this->settings['flexform']['markteam']) {
			case 0:
				$layout['stressteam'] = '';
				break;
			case 1:
				$layout['stressteam'] = $this->settings['flexform']['markteamyes'];
				break;
			case 2:
				$layout['stressteam'] = $this->settings['stressteam'];
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

	/**
	 * @return string
	 */
	private function settings_md5() {
		return md5(serialize($this->settings));
	}

}