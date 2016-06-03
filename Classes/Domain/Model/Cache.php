<?php
namespace Heilmann\JhBasketballbundImport2\Domain\Model;

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
 * Collection of multiple teams
 */
class Cache extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * uid of content element the data belongs to
	 *
	 * @var integer
	 * @validate NotEmpty
	 */
	protected $ceUid = 0;

	/**
	 * md5 of plugin-settings
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $settingsMd5 = '';

	/**
	 * the cached data
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $data = '';

	/**
	 * Returns the ceUid
	 *
	 * @return integer $ceUid
	 */
	public function getCeUid() {
		return $this->ceUid;
	}

	/**
	 * Sets the ceUid
	 *
	 * @param integer $ceUid
	 * @return void
	 */
	public function setCeUid($ceUid) {
		$this->ceUid = $ceUid;
	}

	/**
	 * Returns the settingsMd5
	 *
	 * @return string $settingsMd5
	 */
	public function getSettingsMd5() {
		return $this->settingsMd5;
	}

	/**
	 * Sets the settingsMd5
	 *
	 * @param string $settingsMd5
	 * @return void
	 */
	public function setSettingsMd5($settingsMd5) {
		$this->settingsMd5 = $settingsMd5;
	}

	/**
	 * Returns the data
	 *
	 * @return string data
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Sets the data
	 *
	 * @param string $data
	 * @return string data
	 */
	public function setData($data) {
		$this->data = $data;
	}

}