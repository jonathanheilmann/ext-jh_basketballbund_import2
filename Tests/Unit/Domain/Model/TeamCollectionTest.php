<?php

namespace Heilmann\JhBasketballbundImport2\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Jonathan Heilmann <mail@jonathan-heilmann.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class \Heilmann\JhBasketballbundImport2\Domain\Model\TeamCollection.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Jonathan Heilmann <mail@jonathan-heilmann.de>
 */
class TeamCollectionTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \Heilmann\JhBasketballbundImport2\Domain\Model\TeamCollection
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \Heilmann\JhBasketballbundImport2\Domain\Model\TeamCollection();
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getTitleReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleForStringSetsTitle() {
		$this->subject->setTitle('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'title',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getTeamsReturnsInitialValueForTeam() {
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getTeams()
		);
	}

	/**
	 * @test
	 */
	public function setTeamsForObjectStorageContainingTeamSetsTeams() {
		$team = new \Heilmann\JhBasketballbundImport2\Domain\Model\Team();
		$objectStorageHoldingExactlyOneTeams = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneTeams->attach($team);
		$this->subject->setTeams($objectStorageHoldingExactlyOneTeams);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneTeams,
			'teams',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addTeamToObjectStorageHoldingTeams() {
		$team = new \Heilmann\JhBasketballbundImport2\Domain\Model\Team();
		$teamsObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$teamsObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($team));
		$this->inject($this->subject, 'teams', $teamsObjectStorageMock);

		$this->subject->addTeam($team);
	}

	/**
	 * @test
	 */
	public function removeTeamFromObjectStorageHoldingTeams() {
		$team = new \Heilmann\JhBasketballbundImport2\Domain\Model\Team();
		$teamsObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$teamsObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($team));
		$this->inject($this->subject, 'teams', $teamsObjectStorageMock);

		$this->subject->removeTeam($team);

	}
}
