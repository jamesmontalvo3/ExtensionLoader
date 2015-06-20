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
		global $egExtmanConfig;

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

 	// initiates or updates extensions
	// does not delete extensions if they're disabled
	public function updateExtensions () {
		global $egExtmanConfig;

		foreach( $egExtmanConfig as $extName => $conf ) {
			
			$ext_dir = "{$this->extensions_dir}/$extName";
			
			// Check if extension directory exists, update extension accordingly
			if ( is_dir($ext_dir) ) {
				$this->checkExtensionForUpdates( $extName );
			}
			else {
				$this->cloneGitRepo( $extName );
			}
			
		}
		
	}
	
	protected function cloneGitRepo ( $extName ) {

		echo "\n    CLONING EXTENSION $extName\n";
	
		$conf = $this->extensions[$extName];
	
		// change working directory to main extensions directory
		chdir( $this->extensions_dir );
		
		// git clone into directory named the same as the extension
		echo shell_exec( "git clone {$conf['origin']} $extName" );
		
		if ( $conf['checkout'] !== 'master' ) {
		
			chdir( "{$this->extensions_dir}/$extName" );
		
			echo shell_exec( "git checkout " . $conf['checkout'] ); 
		
		}
				
	}
	
	protected function checkExtensionForUpdates ( $extName ) {
	
		echo "\n    Checking for updates in $extName\n";
	
		$conf = $this->extensions[$extName];
		$ext_dir = "{$this->extensions_dir}/$extName";
		
		if ( ! is_dir("$ext_dir/.git") ) {
			echo "\nNot a git repository! ($extName)";
			return false;	
		}
		
		// change working directory to main extensions directory
		chdir( $ext_dir );
		
		// git clone into directory named the same as the extension
		echo shell_exec( "git fetch origin" );

		$current_sha1 = shell_exec( "git rev-parse --verify HEAD" );
		$fetched_sha1 = shell_exec( "git rev-parse --verify {$conf['checkout']}" );
		
		if ($current_sha1 !== $fetched_sha1) {
			echo "\nCurrent commit: $current_sha1";
			echo "\nChecking out new commit: $fetched_sha1\n";
			echo shell_exec( "git checkout {$conf['checkout']}" );
		}
		else {
			echo "\nsha1 unchanged, no update required ($current_sha1)";
		}
		
		return true;
	
	}
	
	protected function isExtensionEnabled ( $extName ) {
		$conf = $this->extensions[$extName];
		
		if ( ! isset($conf["enable"]) || $conf["enable"] === true )
			return true; // enabled if no mention, or if explicitly set to true
		else if ( $this->is_dev_environment && $conf["enable"] == "dev"  )
			return true;
		else
			return false;
	}

	public function loadExtensions () {
		global $wgVersion;
		foreach( $this->extensions as $extName => $conf ) {

			if ( ! $this->isExtensionEnabled( $extName ) ) {
				continue;
			}

			require_once "{$this->extensions_dir}/$extName/$extName.php";
			
			if ( isset($conf['callback']) )
				call_user_function( $conf['callback'] );
		}
			
	}

}
