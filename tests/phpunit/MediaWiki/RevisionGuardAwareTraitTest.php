<?php

namespace SMW\Tests\MediaWiki;

use SMW\MediaWiki\RevisionGuardAwareTrait;

/**
 * @covers \SMW\MediaWiki\RevisionGuardAwareTrait
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.2
 *
 * @author mwjames
 */
class RevisionGuardAwareTraitTest extends \PHPUnit\Framework\TestCase {

	public function testSetRevisionGuard() {
		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$revisionGuard = $this->getMockBuilder( '\SMW\MediaWiki\RevisionGuard' )
			->disableOriginalConstructor()
			->getMock();

		$revisionGuard->expects( $this->once() )
			->method( 'isSkippableUpdate' );

		$instance = $this->newRevisionGuardAware();

		$instance->setRevisionGuard(
			$revisionGuard
		);

		$instance->callIsSkippableUpdate( $title );
	}

	private function newRevisionGuardAware() {
		return new class() {

			use RevisionGuardAwareTrait;

			public function callIsSkippableUpdate( $title ) {
				$this->revisionGuard->isSkippableUpdate( $title );
			}
		};
	}

}
