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

 	// initiates or updates extensions
	// does not delete extensions if they're disabled
	public function updateExtensions () {
				
		foreach( $this->extensions as $ext_name => $ext_info ) {

			if ( ! $this->isExtensionEnabled( $ext_name ) ) {
				continue;
			}
			
			$ext_dir = "{$this->extensions_dir}/$ext_name";
			
			// Check if extension directory exists, update extension accordingly
			if ( is_dir($ext_dir) ) {
				$this->checkExtensionForUpdates( $ext_name );
			}
			else {
				$this->cloneGitRepo( $ext_name );
			}
			
		}
		
	}
	
	protected function cloneGitRepo ( $ext_name ) {

		echo "\n    CLONING EXTENSION $ext_name\n";
	
		$ext_info = $this->extensions[$ext_name];
	
		// change working directory to main extensions directory
		chdir( $this->extensions_dir );
		
		// git clone into directory named the same as the extension
		echo shell_exec( "git clone {$ext_info['origin']} $ext_name" );
		
		if ( $ext_info['checkout'] !== 'master' ) {
		
			chdir( "{$this->extensions_dir}/$ext_name" );
		
			echo shell_exec( "git checkout " . $ext_info['checkout'] ); 
		
		}
				
	}
	
	protected function checkExtensionForUpdates ( $ext_name ) {
	
		echo "\n    Checking for updates in $ext_name\n";
	
		$ext_info = $this->extensions[$ext_name];
		$ext_dir = "{$this->extensions_dir}/$ext_name";
		
		if ( ! is_dir("$ext_dir/.git") ) {
			echo "\nNot a git repository! ($ext_name)";
			return false;	
		}
		
		// change working directory to main extensions directory
		chdir( $ext_dir );
		
		// git clone into directory named the same as the extension
		echo shell_exec( "git fetch origin" );

		$current_sha1 = shell_exec( "git rev-parse --verify HEAD" );
		$fetched_sha1 = shell_exec( "git rev-parse --verify {$ext_info['checkout']}" );
		
		if ($current_sha1 !== $fetched_sha1) {
			echo "\nCurrent commit: $current_sha1";
			echo "\nChecking out new commit: $fetched_sha1\n";
			echo shell_exec( "git checkout {$ext_info['checkout']}" );
		}
		else {
			echo "\nsha1 unchanged, no update required ($current_sha1)";
		}
		
		return true;
	
	}
	
	protected function isExtensionEnabled ( $ext_name ) {
		$ext_info = $this->extensions[$ext_name];
		
		if ( ! isset($ext_info["enable"]) || $ext_info["enable"] === true )
			return true; // enabled if no mention, or if explicitly set to true
		else if ( $this->is_dev_environment && $ext_info["enable"] == "dev"  )
			return true;
		else
			return false;
	}

	public function loadExtensions () {
		global $wgVersion;
		foreach( $this->extensions as $ext_name => $ext_info ) {

			if ( ! $this->isExtensionEnabled( $ext_name ) ) {
				continue;
			}

			require_once "{$this->extensions_dir}/$ext_name/$ext_name.php";
			
			if ( isset($ext_info['callback']) )
				call_user_function( $ext_info['callback'] );
		}
			
	}

}
