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
 * Team
 */
class Team extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * readable name of team
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $team = '';

	/**
	 * number of league the team participates
	 *
	 * @var integer
	 * @validate NotEmpty
	 */
	protected $league = 0;

	/**
	 * Returns the team
	 *
	 * @return string $team
	 */
	public function getTeam() {
		return $this->team;
	}

	/**
	 * Sets the team
	 *
	 * @param string $team
	 * @return void
	 */
	public function setTeam($team) {
		$this->team = $team;
	}

	/**
	 * Returns the league
	 *
	 * @return integer $league
	 */
	public function getLeague() {
		return $this->league;
	}

	/**
	 * Sets the league
	 *
	 * @param integer $league
	 * @return void
	 */
	public function setLeague($league) {
		$this->league = $league;
	}

}