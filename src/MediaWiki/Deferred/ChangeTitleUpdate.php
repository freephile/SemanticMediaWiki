<?php

namespace SMW\MediaWiki\Deferred;

use DeferrableUpdate;
use DeferredUpdates;
use SMW\MediaWiki\Jobs\UpdateJob;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\Site;
use Title;

/**
 * Run a deferred update job for a changed title instance to re-parse the content
 * of those associated titles and make sure that its content (incl. any
 * self-reference) is correctly represented.
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class ChangeTitleUpdate implements DeferrableUpdate {

	/**
	 * @var Title|null
	 */
	private $oldTitle;

	/**
	 * @var Title|null
	 */
	private $newTitle;

	/**
	 * @since 3.0
	 *
	 * @param Title|null $oldTitle
	 * @param Title|null $newTitle
	 */
	public function __construct( ?Title $oldTitle = null, ?Title $newTitle = null ) {
		$this->oldTitle = $oldTitle;
		$this->newTitle = $newTitle;
	}

	/**
	 * @since 3.0
	 *
	 * @param Title|null $oldTitle
	 * @param Title|null $newTitle
	 */
	public static function addUpdate( ?Title $oldTitle = null, ?Title $newTitle = null ) {
		// Avoid deferring the update on CLI (and the DeferredUpdates::tryOpportunisticExecute)
		// since we use a Job instance to carry out the change
		if ( Site::isCommandLineMode() ) {
			$changeTitleUpdate = new self( $oldTitle, $newTitle );
			$changeTitleUpdate->doUpdate();
		} else {
			DeferredUpdates::addUpdate( new self( $oldTitle, $newTitle ) );
		}
	}

	/**
	 * @see DeferrableUpdate::doUpdate
	 *
	 * @since 3.0
	 */
	public function doUpdate() {
		$applicationFactory = ApplicationFactory::getInstance();
		$jobFactory = $applicationFactory->newJobFactory();

		$parameters = [
			UpdateJob::FORCED_UPDATE => true,
			'origin' => 'ChangeTitleUpdate'
		];

		if ( $this->oldTitle !== null ) {
			$jobFactory->newUpdateJob( $this->oldTitle, $parameters )->run();
		}

		if ( $this->newTitle !== null ) {
			$jobFactory->newUpdateJob( $this->newTitle, $parameters )->run();
		}
	}

}
