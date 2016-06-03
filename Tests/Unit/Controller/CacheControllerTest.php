<?php
namespace Heilmann\JhBasketballbundImport\Tests\Unit\Controller;
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
 * Test case for class Heilmann\JhBasketballbundImport\Controller\CacheController.
 *
 * @author Jonathan Heilmann <mail@jonathan-heilmann.de>
 */
class CacheControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \Heilmann\JhBasketballbundImport\Controller\CacheController
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = $this->getMock('Heilmann\\JhBasketballbundImport\\Controller\\CacheController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function listActionFetchesAllCachesFromRepositoryAndAssignsThemToView() {

		$allCaches = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$cacheRepository = $this->getMock('Heilmann\\JhBasketballbundImport\\Domain\\Repository\\CacheRepository', array('findAll'), array(), '', FALSE);
		$cacheRepository->expects($this->once())->method('findAll')->will($this->returnValue($allCaches));
		$this->inject($this->subject, 'cacheRepository', $cacheRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('caches', $allCaches);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}
}
