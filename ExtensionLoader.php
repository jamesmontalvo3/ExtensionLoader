<?php
/**
 * MediaWiki Extension: WatchAnalytics
 * http://www.mediawiki.org/wiki/Extension:WatchAnalytics
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * This program is distributed WITHOUT ANY WARRANTY.
 */

/**
 *
 * @file
 * @ingroup Extensions
 * @author James Montalvo
 * @licence MIT License
 */

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/ExtensionLoader/ExtensionLoader.php" );
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$GLOBALS['wgExtensionCredits']['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'ExtensionLoader',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:ExtensionLoader',
	'author'         => array( '[https://www.mediawiki.org/wiki/User:Jamesmontalvo3 James Montalvo]' ),
	'descriptionmsg' => 'extensionloader-desc',
);

$GLOBALS['wgMessagesDirs']['ExtensionLoader'] = __DIR__ . '/i18n';



class ExtensionLoader {

	public function loadSettings ( $extensionSettings, $extDir=false ) {
		global $egExtensionLoaderConfig;

		if ( ! $extDir ) {
			global $IP;
			$extDir = "$IP/extensions";
		}

		if ( ! is_array( $extensionSettings ) ) {
			$extensionSettings = array( $extensionSettings );
		}

		$egExtensionLoaderConfig = array();

		foreach( $extensionSettings as $file ) {
			require_once $file;
		}
		print_r( $egExtensionLoaderConfig );
		// self::loadExtensions();
	}

	public function loadExtensions () {
		global $egExtensionLoaderConfig;

		foreach( $egExtensionLoaderConfig as $extName => $conf ) {

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
		global $egExtensionLoaderConfig;

		foreach( $egExtensionLoaderConfig as $extName => $conf ) {
			
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
