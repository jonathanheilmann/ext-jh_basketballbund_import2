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
class TeamCollection extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * title of teamcollection
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $title = '';

	/**
	 * teams
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Heilmann\JhBasketballbundImport2\Domain\Model\Team>
	 */
	protected $teams = NULL;

	/**
	 * __construct
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all ObjectStorage properties
	 * Do not modify this method!
	 * It will be rewritten on each save in the extension builder
	 * You may modify the constructor of this class instead
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->teams = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Adds a Team
	 *
	 * @param \Heilmann\JhBasketballbundImport\Domain\Model\Team $team
	 * @return void
	 */
	public function addTeam(\Heilmann\JhBasketballbundImport\Domain\Model\Team $team) {
		$this->teams->attach($team);
	}

	/**
	 * Removes a Team
	 *
	 * @param \Heilmann\JhBasketballbundImport\Domain\Model\Team $teamToRemove The Team to be removed
	 * @return void
	 */
	public function removeTeam(\Heilmann\JhBasketballbundImport\Domain\Model\Team $teamToRemove) {
		$this->teams->detach($teamToRemove);
	}

	/**
	 * Returns the teams
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Heilmann\JhBasketballbundImport\Domain\Model\Team> $teams
	 */
	public function getTeams() {
		return $this->teams;
	}

	/**
	 * Sets the teams
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Heilmann\JhBasketballbundImport\Domain\Model\Team> $teams
	 * @return void
	 */
	public function setTeams(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $teams) {
		$this->teams = $teams;
	}

}