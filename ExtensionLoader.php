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
	'version'        => '0.1',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:ExtensionLoader',
	'author'         => array( '[https://www.mediawiki.org/wiki/User:Jamesmontalvo3 James Montalvo]' ),
	'descriptionmsg' => 'extensionloader-desc',
);

$GLOBALS['wgMessagesDirs']['ExtensionLoader'] = __DIR__ . '/i18n';


class ExtensionLoader {

	public $extDir;
	public $extensionSettings;
	public $oldExtensions = array();
	static $loader;

	public function __construct ( $extensionSettings, $extDir=false ) {
		
		if ( ! is_array( $extensionSettings ) ) {
			$this->extensionSettings = array( $extensionSettings );
		}

		if ( ! $extDir ) {
			global $IP;
			$this->extDir = "$IP/extensions";
		}
		else {
			$this->extDir = $extDir;
		}

		// initial value for $egExtensionLoaderConfig is empty array
		global $egExtensionLoaderConfig;
		$egExtensionLoaderConfig = array();

		// add to $egExtensionLoaderConfig in settings files
		foreach( $this->extensionSettings as $file ) {
			require_once $file;
		}

		$this->extensions = $egExtensionLoaderConfig;
		// @debug
		// print_r( $egExtensionLoaderConfig );

	}

	// public function loadExtensions () {
	// 	// not sure if this function is needed at this point
	// }

	static public function init ( $extensionSettings, $extDir=false ) {
		self::$loader = new self( $extensionSettings, $extDir );
		global $egExtensionLoaderUpdateScript;
		if ( ! $egExtensionLoaderUpdateScript ) {
			self::$loader->startExtensionLoading();
		}
	}

	public function startExtensionLoading () {
		// this function will load any extensions using the MW 1.25+ extension
		// loading method. For now it is just skipped.
		// @todo: implement prior release

		foreach( $this->extensions as $extName => $conf ) {

			// load extension
			// if ( isset( $conf['composer'] ) && $conf['composer'] === true ) {
			// 	continue;
			// }

			$entry = isset( $conf['entry'] ) ? $conf['entry'] : $extName . '.php';
			
			$extFile = $this->extDir . "/$extName/$entry";
			// echo $this->extDir . "/$extName/$entry<br />";
			$this->oldExtensions[ $extName ] = $extFile;
		}

	}

	public function completeExtensionLoading () {

		foreach( $this->oldExtensions as $extName => $extFile ) {

			$conf = $this->extensions[ $extName ];

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


 // 	// initiates or updates extensions
	// // does not delete extensions if they're disabled
	// public function updateExtensions ( $maintScript ) {
	// 	global $egExtensionLoaderConfig;

	// 	$this->maintScript = $maintScript;

	// 	foreach( $egExtensionLoaderConfig as $extName => $conf ) {
			
	// 		$extensionDir = $this->extDir . "/$extName";
			
	// 		// Check if extension directory exists, update extension accordingly
	// 		if ( is_dir( $extensionDir ) ) {
	// 			$this->checkExtensionForUpdates( $extName );
	// 		}
	// 		else {
	// 			$this->cloneGitRepo( $extName );
	// 		}
			
	// 	}
		
	// }
	

	// /**
	//  *  'git' => 'https://git.wikimedia.org/...',
	//  *  'checkout' => 'master|tags/1.24.1|REL1_25|2b449A',
	//  *
	//  **/
	// protected function cloneGitRepo ( $extName ) {

	// 	$this->maintScript->output( "\n    CLONING EXTENSION $extName\n" );
	
	// 	$conf = $this->extensions[$extName];
	
	// 	// change working directory to main extensions directory
	// 	chdir( $this->extDir );
		
	// 	// git clone into directory named the same as the extension
	// 	$this->maintScript->output( shell_exec( "git clone {$conf['git']} $extName" ) );
		
	// 	if ( $conf['checkout'] !== 'master' ) {
		
	// 		chdir( "{$this->extDir}/$extName" );
		
	// 		$this->maintScript->output( shell_exec( "git checkout " . $conf['checkout'] ) ); 
		
	// 	}
				
	// }
	
	// protected function checkExtensionForUpdates ( $extName ) {
	
	// 	$this->maintScript->output( "\n    Checking for updates in $extName\n" );
	
	// 	$conf = $this->extensions[$extName];
	// 	$extensionDirectory = "{$this->extDir}/$extName";
		
	// 	if ( ! is_dir("$extensionDirectory/.git") ) {
	// 		$this->maintScript->output( "\nNot a git repository! ($extName)" );
	// 		return false;	
	// 	}
		
	// 	// change working directory to main extensions directory
	// 	chdir( $extensionDirectory );
		
	// 	// git clone into directory named the same as the extension
	// 	$this->maintScript->output( shell_exec( "git fetch origin" ) );

	// 	$currentSha1 = shell_exec( "git rev-parse --verify HEAD" );
	// 	$fetchedSha1 = shell_exec( "git rev-parse --verify {$conf['checkout']}" );
		
	// 	if ($currentSha1 !== $fetchedSha1) {
	// 		$this->maintScript->output( "\nCurrent commit: $currentSha1" );
	// 		$this->maintScript->output( "\nChecking out new commit: $fetchedSha1\n" );
	// 		$this->maintScript->output( shell_exec( "git checkout {$conf['checkout']}" ) );
	// 	}
	// 	else {
	// 		$this->maintScript->output( "\nsha1 unchanged, no update required ($currentSha1)" );
	// 	}
		
	// 	return true;
	
	// }

}
