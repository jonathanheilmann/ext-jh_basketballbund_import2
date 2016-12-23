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

// add new Content Elements zu Wizard
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/TSconfig/Page/wizard.txt">');

// Caching
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$_EXTKEY]))
{
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$_EXTKEY] = array();
}
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$_EXTKEY]['groups']))
{
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$_EXTKEY]['groups'] = array('pages', 'all');
}