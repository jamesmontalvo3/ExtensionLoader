<?php




foreach( $egExtmanConfig as $extName => $stuff ) {

	// load extension
	if ( ! $stuff['composer'] ) {
		if ( isset( $stuff[ 'entry' ] ) {
			$entry = $stuff[ 'entry' ];
		}
		else {
			$entry = $extName . '.php';
		}
		require_once "$IP/extensions/$extName/$entry";
	}

	// apply global variables
	if ( isset( $stuff['globals'] ) && is_array( $stuff['globals'] ) ) {
		foreach( $stuff['globals'] as $var => $value ) {
			$GLOBALS[$var] = $value;
		}
	}

	// run extenion setup function
	if ( isset( $stuff['afterFn'] ) ) {
		$stuff['afterFn'](); // @todo: what should be the inputs to this function? any?
	}

}