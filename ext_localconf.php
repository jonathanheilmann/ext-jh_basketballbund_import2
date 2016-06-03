<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Heilmann.' . $_EXTKEY,
	'Team',
	array(
		'Team' => 'display',

	),
	// non-cacheable actions
	array(
		'Team' => 'display',

	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Heilmann.' . $_EXTKEY,
	'Teamcollection',
	array(
		'TeamCollection' => 'display',

	),
	// non-cacheable actions
	array(
		'TeamCollection' => 'display',

	)
);
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

// use two hooks to add a new "clear cache" option
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions'][] = 'EXT:jh_basketballbund_import_2/Classes/Hook/ClearCacheHook.php:&Heilmann\\JhBasketballbundImport2\\Hook\\ClearCacheHook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:jh_basketballbund_import_2/Classes/Hook/ClearCacheHook.php:&Heilmann\\JhBasketballbundImport2\\Hook\\ClearCacheHook->clear';

// add new Content Elements zu Wizard
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/TSconfig/Page/wizard.txt">');