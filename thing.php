<?php

class Extman {

	public function loadSettings () {
		global $egExtmanConfig;
		$egExtmanConfig = array();
		$files = func_get_args();
		foreach( $files as $file ) {
			require_once $file;
		}
		print_r( $egExtmanConfig );
		// self::loadExtensions();
	}

	public function loadExtensions () {

		foreach( $egExtmanConfig as $extName => $conf ) {

			// load extension
			if ( ! $conf['composer'] ) {
				$entry = isset( $conf['entry'] ) ? $conf['entry'] : $extName . '.php';
				require_once "$IP/extensions/$extName/$entry";
			}

			// apply global variables
			if ( isset( $conf['globals'] ) && is_array( $conf['globals'] ) ) {
				foreach( $conf['globals'] as $var => $value ) {
					$GLOBALS[$var] = $value;
				}
			}

			// run extenion setup function
			if ( isset( $conf['afterFn'] ) ) {
				$conf['afterFn'](); // @todo: what should be the inputs to this function? any?
			}

		}

	}

}