<?php

namespace Onoi\Tesa\Synonymizer;

/**
 * @license GPL-2.0-or-later
 * @since 0.1
 *
 * @author mwjames
 */
class NullSynonymizer implements Synonymizer {

	/**
	 * @since 0.1
	 *
	 * @param string $word
	 *
	 * @return string
	 */
	public function synonymize( $word ) {
		return $word;
	}

}