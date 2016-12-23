<?php
namespace Heilmann\JhBasketballbundImport2\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014-2016 Jonathan Heilmann <mail@jonathan-heilmann.de>
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

use Heilmann\JhBasketballbundImport2\Domain\Model\Team;
use Heilmann\JhBasketballbundImport2\Service\ImportService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TeamController
 */
class TeamController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var string
     */
    protected $extensionKey = 'jh_basketballbund_import2';

    /**
     * teamRepository
     *
     * @var \Heilmann\JhBasketballbundImport2\Domain\Repository\TeamRepository
     * @inject
     */
    protected $teamRepository = null;

    /**
     * @var \Heilmann\JhBasketballbundImport2\Service\ImportService
     */
    protected $importService = null;

    /**
     * initializeObject
     */
    public function initializeObject()
    {
        $this->importService = $this->objectManager->get(ImportService::class, $this->settings);
    }

    /**
     * action display
     *
     * @throws \Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function displayAction()
    {
        $cObj = $this->configurationManager->getContentObject();

        $tags = array('pageId_' . $cObj->data['pid'], 'ceUid_' . $cObj->data['uid']);
        $cacheIdentifier = $this->importService->getCacheIdentifier($tags);
        if (($entry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache($this->extensionKey)->get($cacheIdentifier)) === false)
        {
            //evaluate which team should be displayed
            /** @var Team $team */
            $team = $this->teamRepository->findByUid($this->settings['displayteam']);
            //evaluate which content-type should be rendered
            $getContentFunctionName = 'get' . GeneralUtility::underscoredToUpperCamelCase($this->settings['displaycontent']);
            if (is_callable(array(self::class, $getContentFunctionName)))
            {
                $entry = $this->$getContentFunctionName($team);
                $entry['lastupdate'] = new \DateTime();
                GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')
                    ->getCache($this->extensionKey)
                    ->set($cacheIdentifier, $entry, $tags, $this->settings['cachelifetimeSeconds']);
            } else
            {
                throw new \Exception('Could not find callable function ' . $getContentFunctionName . '() in ' . self::class);
            }
        }

        $this->view->assign('content', $entry);
        //get layout of table
        $layout = $this->importService->getLayout();
        $this->view->assign('layout', $layout);
    }

    /**
     * Get the table data
     *
     * @param Team $team
     * @return array
     */
    private function getTable($team)
    {
        //import table from basketball-bund.net
        $importPath = 'http://www.basketball-bund.net/public/tabelle.jsp?print=1&viewDescKey=sport.dbb.views.TabellePublicView/index.jsp_&liga_id=' . $team->getLeague();
        $import = $this->importService->import($importPath);
        if ($import['success'] === false) {
            return array(
                'data' => array(
                    'Fehler'
                )
            );
        }

        $tableRows = $this->importService->getTableRowsFromImport($import['import']);
        $result = array();
        //add header-row
        $result['data'][0] = array(
            LocalizationUtility::translate('table.displayrows.rank', $this->extensionName),
            LocalizationUtility::translate('table.displayrows.team', $this->extensionName),
            LocalizationUtility::translate('table.displayrows.games', $this->extensionName),
            LocalizationUtility::translate('table.displayrows.w_l', $this->extensionName),
            LocalizationUtility::translate('table.displayrows.points', $this->extensionName),
            LocalizationUtility::translate('table.displayrows.baskets', $this->extensionName),
            LocalizationUtility::translate('table.displayrows.difference', $this->extensionName)
        );
        $i = 0;
        foreach ($tableRows as $wert)
        {
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
                if (stristr($wert[0], '<strike>'))
                {
                    preg_match_all('#<[^td>]+>(.*)</[^td>]+>#Usm', $wert[0], $tabledata, PREG_SET_ORDER);
                } else
                {
                    preg_match_all('#<[^>]+>(.*)</[^>]+>#Usm', $wert[0], $tabledata, PREG_SET_ORDER);
                }
                $j = 0;
                foreach ($tabledata as $tabledata_single) {
                    $result['data'][$i - 9][$j] = stristr($tabledata_single[0], '<strike>') ? '<span class="strike">' . trim($tabledata_single[1]) . '</span>' : trim($tabledata_single[1]);
                    $j++;
                }

            }
        }
        $result['link'] = 'http://www.basketball-bund.net/index.jsp?Action=102&liga_id=' . $team->getLeague();
        //return tabledata
        return $result;
    }

    /**
     * Get the results data
     *
     * @param Team $team
     * @return array
     */
    private function getResults(Team $team)
    {
        //import table from basketball-bund.net
        $importPath = 'http://www.basketball-bund.net/public/ergebnisse.jsp?print=1&viewDescKey=sport.dbb.liga.ErgebnisseViewPublic/index.jsp_&liga_id=' . $team->getLeague();
        $import = $this->importService->import($importPath);
        if ($import['success'] === false) {
            return array(
                'data' => array(
                    'Fehler'
                )
            );
        }

        $tableRows = $this->importService->getTableRowsFromImport($import['import']);
        $result = array();
        // if necessary create checkbox to display all teams
        if ($this->settings['displayselection'] == 'otherteamsbyjq')
            $result['preHeader'] = '<div class="checkbox_displayall">' . LocalizationUtility::translate('displayAllMatches',
                    $this->extensionName) . ' <input type="checkbox" name="" id="results_allteams" /></div>';

        // evaluate if quater-results are available of if the match has been accelerated
        if ($tableRows[8][1] == '<td class="sportViewHeader">1. Viertel </td>')
        {
            $headerend_i = 12;
            // add header-row
            // changed in version 0.0.4: column "details" has been removed by basketball-bund.net
            $result['data'][0] = array(
                LocalizationUtility::translate('results.displayrows.number', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.matchday', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.date', $this->extensionName),
                //LocalizationUtility::translate('results.displayrows.details', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.home', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.guest', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.endresult', $this->extensionName),
                '1. Viertel',
                '2. Viertel',
                '3. Viertel',
                '4. Viertel'
            );
        } elseif ($tableRows[8][1] == '<td class="sportViewHeader">Vor Verl. </td>')
        {
            $headerend_i = 9;
            // add header-row
            // changed in version 0.0.4: column "details" has been removed by basketball-bund.net
            $result['data'][0] = array(
                LocalizationUtility::translate('results.displayrows.number', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.matchday', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.date', $this->extensionName),
                //LocalizationUtility::translate('results.displayrows.details', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.home', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.guest', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.endresult', $this->extensionName),
                'Vor Verl.'
            );
        } else
        {
            $headerend_i = 8;
            // add header-row
            // changed in version 0.0.4: column "details" has been removed by basketball-bund.net
            $result['data'][0] = array(
                LocalizationUtility::translate('results.displayrows.number', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.matchday', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.date', $this->extensionName),
                //LocalizationUtility::translate('results.displayrows.details', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.home', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.guest', $this->extensionName),
                LocalizationUtility::translate('results.displayrows.endresult', $this->extensionName),
            );
        }
        //start table
        $i = 0;
        foreach ($tableRows as $wert)
        {
            $i++;
            //the two three rows should not be shown
            if ($i <= $headerend_i)
                continue;

            //write table-content
            if ($i > $headerend_i)
            {
                //split to field-data
                preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $wert[1], $tabledata, PREG_SET_ORDER);
                $j = 0;
                foreach ($tabledata as $tabledata_single)
                {
                    preg_match_all('/<div[^>]+>(.*?)<\\/div>/', $tabledata_single[1], $tabledata_single_clear,
                        PREG_SET_ORDER);
                    $result['data'][$i - $headerend_i][$j] = empty($tabledata_single_clear) ? trim($tabledata_single[1]) : trim($tabledata_single_clear[0][1]);
                    $j++;
                }
            }
        }
        $result['link'] = 'http://www.basketball-bund.net/index.jsp?Action=103&liga_id=' . $team->getLeague();
        //return tabledata
        return $result;
    }

    /**
     * Get the playing schedule data
     *
     * @param Team $team
     * @return array
     */
    private function getPlayingSchedule(Team $team)
    {
        //import table from basketball-bund.net
        $importPath = 'http://www.basketball-bund.net/public/spielplan_list.jsp?print=1&viewDescKey=sport.dbb.liga.SpielplanViewPublic/index.jsp_&liga_id=' . $team->getLeague();
        $import = $this->importService->import($importPath);
        if ($import['success'] === false) {
            return array(
                'data' => array(
                    'Fehler'
                )
            );
        }

        $tableRows = $this->importService->getTableRowsFromImport($import['import']);
        $result = array();
        //if necessary create checkbox to display all teams
        if ($this->settings['displayselection'] == 'otherteamsbyjq')
            $result['preHeader'] = '<div class="checkbox_displayall">' . LocalizationUtility::translate('displayAllMatches',
                    $this->extensionName) . ' <input type="checkbox" name="" id="playingschedule_allteams" /></div>';

        //add header row
        $result['data'][0] = array(
            LocalizationUtility::translate('playing_schedule.displayrows.number', $this->extensionName),
            LocalizationUtility::translate('playing_schedule.displayrows.day', $this->extensionName),
            LocalizationUtility::translate('playing_schedule.displayrows.date', $this->extensionName),
            LocalizationUtility::translate('playing_schedule.displayrows.home', $this->extensionName),
            LocalizationUtility::translate('playing_schedule.displayrows.guest', $this->extensionName),
            LocalizationUtility::translate('playing_schedule.displayrows.hall', $this->extensionName),
            LocalizationUtility::translate('playing_schedule.displayrows.referee', $this->extensionName)
        );
        $i = 0;
        $j = 0;
        $first_j = 0;
        //$last_j = 0;
        $whole_tablerow = array();
        //$sr2 = 0;
        foreach ($tableRows as $wert)
        {
            $used = 0;
            $i++;
            //the two three rows should not be shown
            if ($i <= 9)
                continue;

            //workaround due to problems with referee: write a new array with one table-row in one array-number instead of two or three
            if ($i > 9)
            {
                if (strstr($wert[0], '<b>COA</b>') and $used == 0)
                {
                    $whole_tablerow[$j] .= $wert[0];
                    $used = 1;
                }
                if (strstr($wert[0], '<b>SR2</b>') and !strstr($wert[0], '<span title="">') and $used == 0)
                {
                    $whole_tablerow[$j] .= $wert[0];
                    $used = 1;
                }
                if ($used == 0)
                {
                    if (strstr($wert[0], '<b>SR1</b>'))
                    {
                        if ($j == 0 and $first_j == 0)
                        {
                            $whole_tablerow[$j] = $wert[0];
                            $first_j = 1;
                        } else
                        {
                            $whole_tablerow[$j] .= '</table><td></tr>';
                            $j++;
                            $whole_tablerow[$j] = $wert[0];
                        }
                    } elseif (strstr($wert[0], '<b>SR2</b>'))
                    {
                        if ($j == 0 and $first_j == 0)
                        {
                            $whole_tablerow[$j] = $wert[0];
                            $first_j = 1;
                        } else
                        {
                            $whole_tablerow[$j] .= '';
                            $j++;
                            $whole_tablerow[$j] = $wert[0];
                        }
                    } else
                    {
                        $j++;
                        $whole_tablerow[$j] = $wert[1];
                    }
                }
            }
        }
        //use rewritten array with referee
        $i = 1;
        foreach ($whole_tablerow as $row)
        {
            //split to field-data
            preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $row, $col, PREG_SET_ORDER);

            // handle two referees:
            if (count($col) == 8)
            {
                preg_match_all('/<table[^>]+>(.*?)<\\/table>/', $row, $referees, PREG_SET_ORDER);
                preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $referees[0][1], $referee, PREG_SET_ORDER);
                $col[6][0] = strip_tags(trim($referee[0][1]), '<b>') . '<br/>' . strip_tags(trim($referee[1][1]),
                        '<b>');
                $col[6][1] = $col[6][0];
                unset($col[7]);
            }

            $j = 0;
            foreach ($col as $col_data)
            {
                //write field
                $col_data[1] = str_replace('../images/icons/verlegt.png',
                    'http://www.basketball-bund.net/images/icons/verlegt.png', $col_data[1]);
                $col_data[1] = str_replace('../images/icons/abgesagt.png',
                    'http://www.basketball-bund.net/images/icons/abgesagt.png', $col_data[1]);
                $col_data[1] = strip_tags($col_data[1], '<img><br><b>');
                $result['data'][$i][$j] = stristr($col_data[0], '<strike>') ? '<span class="strike">' . trim($col_data[1]) . '</span>' : trim($col_data[1]);
                $j++;
            }
            $i++;
        }
        $result['link'] = 'http://www.basketball-bund.net/index.jsp?Action=101&liga_id=' . $team->getLeague();
        //return tabledata
        return $result;
    }

    /**
     * Get the statistic data
     *
     * @param Team $team
     * @return array
     */
    private function getStatistic(Team $team)
    {
        //import table from basketball-bund.net
        $importPath = 'http://www.basketball-bund.net/liga/statistik_team.jsp?print=1&viewDescKey=sport.dbb.views.TeamStatView/templates/base_template.jsp_&liga_id=' . $team->getLeague();
        $import = $this->importService->import($importPath);
        if ($import['success'] === false) {
            return array(
                'data' => array(
                    'Fehler'
                )
            );
        }

        $tableRows = $this->importService->getTableRowsFromImport($import['import']);
        $result = array();
        //add header row
        $result['data'][0] = array(
            LocalizationUtility::translate('statistic.displayrows.rank', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.team', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.points', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.baskets', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.difference', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.fe', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.percentage', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.double', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.triple', $this->extensionName),
            LocalizationUtility::translate('statistic.displayrows.fouls', $this->extensionName)
        );
        $i = 0;
        $content_table = '';
        foreach ($tableRows as $wert)
        {
            $i++;
            //the first 13 rows should not be shown
            if ($i <= 13)
                continue;

            //write table-content
            if ($i > 13)
            {
                //split to field-data
                preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $wert[1], $row, PREG_SET_ORDER);
                $j = 0;
                foreach ($row as $field)
                {
                    //write field
                    $field[1] = str_replace('align="center"', '', $field[1]);
                    $field[1] = str_replace('align="right"', '', $field[1]);
                    $content_table .= $field[0];
                    $result['data'][$i - 13][$j] = trim($field[1]);
                    $j++;
                }
            }
        }
        $result['link'] = 'http://www.basketball-bund.net/statistik.do?reqCode=statTeam&liga_id=' . $team->getLeague();
        //return tabledata
        return $result;
    }

}