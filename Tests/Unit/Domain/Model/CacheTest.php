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
 * Test case for class \Heilmann\JhBasketballbundImport2\Domain\Model\Cache.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Jonathan Heilmann <mail@jonathan-heilmann.de>
 */
class CacheTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \Heilmann\JhBasketballbundImport2\Domain\Model\Cache
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \Heilmann\JhBasketballbundImport2\Domain\Model\Cache();
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getCeUidReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getCeUid()
		);
	}

	/**
	 * @test
	 */
	public function setCeUidForIntegerSetsCeUid() {
		$this->subject->setCeUid(12);

		$this->assertAttributeEquals(
			12,
			'ceUid',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSettingsMd5ReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getSettingsMd5()
		);
	}

	/**
	 * @test
	 */
	public function setSettingsMd5ForStringSetsSettingsMd5() {
		$this->subject->setSettingsMd5('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'settingsMd5',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getDataReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getData()
		);
	}

	/**
	 * @test
	 */
	public function setDataForStringSetsData() {
		$this->subject->setData('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'data',
			$this->subject
		);
	}
}
