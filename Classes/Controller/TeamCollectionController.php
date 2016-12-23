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
use Heilmann\JhBasketballbundImport2\Domain\Model\TeamCollection;
use Heilmann\JhBasketballbundImport2\Service\ImportService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TeamCollectionController
 */
class TeamCollectionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var string
     */
    protected $extensionKey = 'jh_basketballbund_import2';

    /**
     * teamCollectionRepository
     *
     * @var \Heilmann\JhBasketballbundImport2\Domain\Repository\TeamCollectionRepository
     * @inject
     */
    protected $teamCollectionRepository = null;

    /**
     * @var \Heilmann\JhBasketballbundImport2\Service\ImportService
     */
    protected $importService = null;

    /**
     * @var string
     */
    protected $llPrefix = 'LLL:EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_teamcollection.xlf:';

    /**
     * initializeObject
     */
    public function initializeObject() {
        $this->importService = $this->objectManager->get(ImportService::class, $this->settings);
    }

    /**
     * action display
     *
     * @return void
     */
    public function displayAction()
    {
        $cObj = $this->configurationManager->getContentObject();

        //get layout of table
        $layout = $this->importService->getLayout();

        $tags = array('pageId_' . $cObj->data['pid'], 'ceUid_' . $cObj->data['uid']);
        $cacheIdentifier = $this->importService->getCacheIdentifier($tags);
        if (($entry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache($this->extensionKey)->get($cacheIdentifier)) === false)
        {
            //evaluate which teamcollection should be displayed
            /** @var TeamCollection $teamCollection */
            $teamCollection = $this->teamCollectionRepository->findByUid($this->settings['displayteamcollection']);
            $entry = array();
            $entry['data'][0] = array(
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.team', $this->extensionName),
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.number',
                    $this->extensionName),
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.matchday',
                    $this->extensionName),
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.date', $this->extensionName),
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.home', $this->extensionName),
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.guest',
                    $this->extensionName),
                LocalizationUtility::translate($this->llPrefix . 'results.displayrows.team', $this->extensionName)
            );
            foreach ($teamCollection->getTeams() as $key => $team)
                $entry['data'] = array_merge($entry['data'], $this->getTableRow($team, $layout['displayteam']));

            $entry['lastupdate'] = new \DateTime();
            $entry['link'] = 'http://www.basketball-bund.net/';

            GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')
                ->getCache($this->extensionKey)
                ->set($cacheIdentifier, $entry, $tags, $this->settings['cachelifetimeSeconds']);
        }

        $this->view->assign('content', $entry);
        $this->view->assign('layout', $layout);
    }

    /**
     * @param Team $team
     * @param string $displayTeam
     * @return array
     */
    protected function getTableRow(Team $team, $displayTeam)
    {
        //import table from basketball-bund.net
        $importPath = 'http://www.basketball-bund.net/public/ergebnisse.jsp?print=1&viewDescKey=sport.dbb.liga.ErgebnisseViewPublic/index.jsp_&liga_id=' . $team->getLeague();
        $import = $this->importService->import($importPath);
        if ($import['success'] === false) {
            return array('data' => array('Fehler'));
        }
        $tableRows = $this->importService->getTableRowsFromImport($import['import']);
        $result = array();

        //evaluate if quater-results are available of if the match has been accelerated
        if ($tableRows[8][1] == '<td class="sportViewHeader">1. Viertel </td>') {
            $headerend_i = 12;
        } elseif ($tableRows[8][1] == '<td class="sportViewHeader">Vor Verl. </td>') {
            $headerend_i = 9;
        } else {
            $headerend_i = 8;
        }
        //start table
        $i = 0;
        foreach ($tableRows as $wert) {
            $i++;
            //the two three rows should not be shown
            if ($i <= $headerend_i) {
                continue;
            }
            //write table-content
            if ($i > $headerend_i) {
                $res = array_filter($wert, function ($var) use ($displayTeam) {
                    return preg_match("/\b$displayTeam\b/i", $var);
                });
                if (!empty($res)) {
                    //split to field-data
                    preg_match_all('/<td[^>]+>(.*?)<\\/td>/', $wert[1], $tabledata, PREG_SET_ORDER);

                    $j = 1;
                    $result[$i][0] = $team->getTeam();
                    foreach ($tabledata as $tabledata_single) {
                        preg_match_all('/<div[^>]+>(.*?)<\\/div>/', $tabledata_single[1], $tabledata_single_clear,
                            PREG_SET_ORDER);
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
}