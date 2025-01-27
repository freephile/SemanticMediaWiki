<?php

namespace SMW\Tests\Integration\MediaWiki\Import\Maintenance;

use SMW\Tests\SMWIntegrationTestCase;
use SMW\Tests\Utils\UtilityFactory;
use Title;

/**
 * @group SMW
 * @group SMWExtension
 * @group semantic-mediawiki-import
 * @group mediawiki-database
 * @group Database
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 1.9.2
 *
 * @author mwjames
 */
class RebuildConceptCacheMaintenanceTest extends SMWIntegrationTestCase {

	private $importedTitles = [];
	private $runnerFactory;
	private $titleValidator;
	private $pageCreator;

	protected function setUp(): void {
		parent::setUp();

		$utilityFactory = UtilityFactory::getInstance();
		$this->runnerFactory  = $utilityFactory->newRunnerFactory();
		$this->titleValidator = $utilityFactory->newValidatorFactory()->newTitleValidator();
		$this->pageCreator = $utilityFactory->newPageCreator();

		$utilityFactory->newMwHooksHandler()
			->deregisterListedHooks()
			->invokeHooksFromRegistry();

		$importRunner = $this->runnerFactory->newXmlImportRunner(
			__DIR__ . '/../Fixtures/' . 'GenericLoremIpsumTest-Mw-1-19-7.xml'
		);

		if ( !$importRunner->setVerbose( true )->run() ) {
			$importRunner->reportFailedImport();
			$this->markTestIncomplete( 'Test was marked as incomplete because the data import failed' );
		}
	}

	protected function tearDown(): void {
		$pageDeleter = UtilityFactory::getInstance()->newPageDeleter();
		$pageDeleter->doDeletePoolOfPages( $this->importedTitles );

		parent::tearDown();
	}

	public function testRebuildConceptCache() {
		$this->importedTitles = [
			'Category:Lorem ipsum',
			'Lorem ipsum',
			'Elit Aliquam urna interdum',
			'Platea enim hendrerit',
			'Property:Has Url',
			'Property:Has annotation uri',
			'Property:Has boolean',
			'Property:Has date',
			'Property:Has email',
			'Property:Has number',
			'Property:Has page',
			'Property:Has quantity',
			'Property:Has temperature',
			'Property:Has text'
		];

		// 1.19 Title/LinkCache goes nuts for when a page in a previous test got
		// deleted
		// $this->titleValidator->assertThatTitleIsKnown( $this->importedTitles );

		$conceptPage = $this->createConceptPage( 'Lorem ipsum concept', '[[Category:Lorem ipsum]]' );
		$this->importedTitles[] = $conceptPage;

		$maintenanceRunner = $this->runnerFactory->newMaintenanceRunner( '\SMW\Maintenance\rebuildConceptCache' );
		$maintenanceRunner->setQuiet();

		$maintenanceRunner
			->setOptions( [ 'status' => true ] )
			->run();

		$this->assertInstanceOf(
			'SMW\DIConcept',
			$this->getStore()->getConceptCacheStatus( $conceptPage->getTitle() )
		);

		$maintenanceRunner
			->setOptions( [ 'create' => true ] )
			->run();

		$maintenanceRunner
			->setOptions( [ 'delete' => true ] )
			->run();

		$maintenanceRunner
			->setOptions( [ 'create' => true, 's' => 1 ] )
			->run();

		$maintenanceRunner
			->setOptions( [ 'create' => true, 's' => 1, 'e' => 100 ] )
			->run();

		$maintenanceRunner
			->setOptions( [ 'create' => true, 'update' => true, 'old' => 1 ] )
			->run();

		$maintenanceRunner
			->setOptions( [ 'delete' => true, 'concept' => 'Lorem ipsum concept' ] )
			->run();
	}

	protected function createConceptPage( $name, $condition ) {
		$this->pageCreator
			->createPage( Title::newFromText( $name, SMW_NS_CONCEPT ) )
			->doEdit( "{{#concept: {$condition} }}" );

		return $this->pageCreator->getPage();
	}

}
