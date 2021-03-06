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

	public function __construct ( $extDir=false, $styleDir=false ) {

		// used by maintenance script. Maybe there's a better way to do this...
		self::$loader = $this;

		if ( ! $extDir ) {
			global $IP;
			$this->extDir = "$IP/extensions";
		}
		else {
			global $wgExtensionDirectory;
			$wgExtensionDirectory = $extDir;
			$this->extDir = $extDir;
		}

		if ( $styleDir ) {
			global $wgStyleDirectory;
			$wgStyleDirectory = $styleDir;
		}

		self::$loader = $this;

	}


	/**
	 *  For legacy extensions (extensions which have not been upgraded to the new method of
	 *  loading) it is not possible to actually load them from within this function. Limitations
	 *  of PHP and MediaWiki won't allow it. This functions registers the extension so it can
	 *  be included in calls to `updateExtensions.php`, and then returns the proper file path
	 *  to be used in `require_once` calls in `LocalSettings.php` like:
	 *
	 *  require_once $extensionLoader->registerLegacyExtension(
	 *      "ParserFunctions",
	 *      "https://gerrit.wikimedia.org/r/mediawiki/extensions/ParserFunctions.git",
	 *      "REL1_25"
	 *  );
	 *
	 **/
	public function registerLegacyExtension ( $name, $git, $version, $specialEntryPointFileName=false ) {
		global $egExtensionLoaderUpdateScript;
		$this->extensions[$name] = array(
			'git' => $git,
			'version' => $version,
			'specialEntryPointFileName' => $specialEntryPointFileName,
		);
		$entryFile = $specialEntryPointFileName ? $specialEntryPointFileName : $name . '.php';
		if ( $egExtensionLoaderUpdateScript ) {
			// Hack! When running the EL update script you may not want to load
			// extensions since they may not exist yet (you may be running the
			// script just to load them). So instead of returning the location
			// of an extension's entry point, we just return the EL entry point
			// which we know is already loaded, so require_once will skip it.
			return $this->extDir . "/ExtensionLoader/ExtensionLoader.php";
		}
		return $this->extDir . "/$name/$entryFile";
	}

	/**
	 *
	 *
	 **/
	public function load ( $name, $git, $version ) {
		$this->extensions[$name] = array(
			'git' => $git,
			'version' => $version
		);
		global $egExtensionLoaderUpdateScript;
		if ( $egExtensionLoaderUpdateScript ) {
			return; // don't actually load extensions during updateExtensions.php
		}
		wfLoadExtension( $name );
	}

	/**
	 *
	 *
	 **/
	public function multiLoad ( $extensions ) {
		foreach ( $extensions as $ext => $info ) {
			$this->extensions[$ext] = $info;
		}
		global $egExtensionLoaderUpdateScript;
		if ( $egExtensionLoaderUpdateScript ) {
			return; // don't actually load extensions during updateExtensions.php
		}
		wfLoadExtensions( array_keys( $extensions ) );
	}


	/**
	 *
	 *
	 **/
	public function loadSkin ( $name, $git, $version ) {
		$this->skins[$name] = array(
			'git' => $git,
			'version' => $version
		);
		global $egExtensionLoaderUpdateScript;
		if ( $egExtensionLoaderUpdateScript ) {
			return; // don't actually load skins during updateExtensions.php
		}
		wfLoadSkin( $name );
	}

	/**
	 *
	 *
	 **/
	public function multiLoadSkin ( $skins ) {
		foreach ( $skins as $skin => $info ) {
			$this->skins[$ext] = $info;
		}
		global $egExtensionLoaderUpdateScript;
		if ( $egExtensionLoaderUpdateScript ) {
			return; // don't actually load skins during updateExtensions.php
		}
		wfLoadSkins( array_keys( $skins ) );
	}

}
