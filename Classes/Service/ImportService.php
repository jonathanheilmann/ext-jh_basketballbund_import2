<?php
namespace Heilmann\JhBasketballbundImport2\Service;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jonathan Heilmann <mail@jonathan-heilmann.de>
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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ImportService
 * @package Heilmann\JhBasketballbundImport2\Service
 */
class ImportService
{

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * ImportService constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get the layout of the table
     *
     * @return array
     */
    public function getLayout()
    {
        $layout = array();
        //evaluate the bordersize for tables
        switch ($this->settings['flexform']['displayborder']) {
            case 0:
                $layout['bordersize'] = 0;
                break;
            case 1:
                $layout['bordersize'] = 1;
                break;
            case 2:
                $layout['bordersize'] = $this->settings['displayborder'];
                break;
        }
        //evaluate which team should be displayed
        if (isset($this->settings['flexform']['displayteam']))
        {
            switch ($this->settings['flexform']['displayteam']) {
                case 1:
                    $layout['displayteam'] = $this->settings['flexform']['displayteamlocal'];
                    break;
                case 2:
                    $layout['displayteam'] = $this->settings['stressteam'];
                    break;
            }  
        }

        //evaluate which team should be stressed
        if (isset($this->settings['flexform']['markteam']))
        {
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
    }

    /**
     * Import data from given path
     *
     * @param $path string
     * @return array
     */
    public function import($path)
    {
        $getUrlResult = GeneralUtility::getUrl($path, 0, false, $report);
        return array(
            'success' => boolval($getUrlResult),
            'import' => boolval($getUrlResult) ? $getUrlResult : 'Could not import path "' . $path . '" <br/> Message: ' . $report['message']
        );
    }

    /**
     * Clean up import and return table rows in array
     *
     * @param $imported
     * @return array
     */
    public function getTableRowsFromImport($imported)
    {
        $tableRows = array();

        //replace some characters
        $imported = preg_replace('/
|
/s', '', $imported
        );
        $imported = str_replace('&nbsp;', ' ', $imported);
        // Remove nobr-tag
        $this->removeNobrTag($imported);
        // Split imported table to rows
        preg_match_all('#\\<tr\\>(.*)\\</tr\\>#Us', $imported, $tableRows, PREG_SET_ORDER);

        return $tableRows;
    }

    /**
     * @param $string
     */
    public function removeNobrTag(&$string)
    {
        $string = str_replace('<NOBR>', '', $string);
        $string = str_replace('</NOBR>', '', $string);
        $string = str_replace('<nobr>', '', $string);
        $string = str_replace('</nobr>', '', $string);
    }

    /**
     * @return string
     */
    public function settingsMd5()
    {
        return md5(serialize($this->settings));
    }

    /**
     * @param array $additionalData
     * @return string
     */
    public function getCacheIdentifier($additionalData = array())
    {
        $identifierArray = $this->settings;
        ArrayUtility::mergeRecursiveWithOverrule($identifierArray, $additionalData);
        return md5(serialize($identifierArray));
    }
}