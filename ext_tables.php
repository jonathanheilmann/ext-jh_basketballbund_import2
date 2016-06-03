<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Team',
	'basketball-bund.net - Team'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Teamcollection',
	'basketball-bund.net - Teamcollection'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'basketball-bund.net - import 2');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jhbasketballbundimport2_domain_model_team', 'EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_csh_tx_jhbasketballbundimport2_domain_model_team.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jhbasketballbundimport2_domain_model_team');
$GLOBALS['TCA']['tx_jhbasketballbundimport2_domain_model_team'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_db.xlf:tx_jhbasketballbundimport2_domain_model_team',
		'label' => 'team',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'team,league,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Team.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_jhbasketballbundimport2_domain_model_team.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jhbasketballbundimport2_domain_model_cache', 'EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_csh_tx_jhbasketballbundimport2_domain_model_cache.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jhbasketballbundimport2_domain_model_cache');
$GLOBALS['TCA']['tx_jhbasketballbundimport2_domain_model_cache'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_db.xlf:tx_jhbasketballbundimport2_domain_model_cache',
		'label' => 'ce_uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'ce_uid,settings_md5,data,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Cache.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_jhbasketballbundimport2_domain_model_cache.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jhbasketballbundimport2_domain_model_teamcollection', 'EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_csh_tx_jhbasketballbundimport2_domain_model_teamcollection.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jhbasketballbundimport2_domain_model_teamcollection');
$GLOBALS['TCA']['tx_jhbasketballbundimport2_domain_model_teamcollection'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jh_basketballbund_import2/Resources/Private/Language/locallang_db.xlf:tx_jhbasketballbundimport2_domain_model_teamcollection',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'title,teams,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/TeamCollection.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_jhbasketballbundimport2_domain_model_teamcollection.gif'
	),
);
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

// include flexform
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$extensionName.'_teamcollection'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$extensionName.'_teamcollection'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($extensionName.'_teamcollection', 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/teamcollection.xml');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$extensionName.'_team'] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$extensionName.'_team'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($extensionName.'_team', 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/team.xml');

// hide cache-table in backend
$GLOBALS['TCA']['tx_jhbasketballbundimport2_domain_model_cache']['ctrl']['hideTable'] = 1;