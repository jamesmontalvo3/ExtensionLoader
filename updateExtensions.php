<?php
/**
 * This script updates the extensions managed by the it
 *
 * Usage:
 *  no parameters
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @author James Montalvo
 * @ingroup Maintenance
 */

$egExtensionLoaderUpdateScript = true;

// @todo: does this always work if extensions are not in $IP/extensions ??
// this was what was done by SMW
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../..';
require_once $basePath . '/maintenance/Maintenance.php';


class ExtensionLoaderUpdateExtensions extends Maintenance {

	protected $extensionLoadingErrors = array();

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Update or install extensions managed by ExtensionLoader.";

		// addOption ($name, $description, $required=false, $withArg=false, $shortName=false)
		$this->addOption(
			'only',
			'Only update extensions in comma-separated list (no spaces)',
			false,
			true
		);
		$this->addOption(
			'skip-log',
			'Ignore extensions already in this log, and add to the log any newly updated extensions',
			false,
			true
		);

	}

 	// initiates or updates extensions
	public function execute() {

		$this->extensionLoader = ExtensionLoader::$loader;

		if ( $this->getOption( 'only' ) ) {
			$toLoad = array();
			$didNotLoad = array();
			$onlyExtensions = explode( ',', $this->getOption( 'only' ) );
			foreach ( $onlyExtensions as $ext ) {
				$ext = trim( $ext );
				if ( array_key_exists( $ext, $this->extensionLoader->extensions ) ) {
					$toLoad[] = $ext;
				}
				else {
					$didNotLoad[] = $ext;
				}
			}
			$this->output( "\nUpdating the following extensions:\n\t" . implode( "\n\t", $toLoad ) . "\n" );

			if ( count( $didNotLoad ) > 0 ) {
				foreach( $didNotLoad as $ext ) {
					$this->extensionLoadingErrors[] = "\"$ext\" not loaded: not in extension settings file";
				}
			}
		}
		else {
			$toLoad = array_keys( $this->extensionLoader->extensions );
			$this->output( "Updating all extensions" );
		}

		// if a log file specified, read from it to determine if any extensions
		// need to be skipped
		$skipLogFile = $this->getOption( 'skip-log' );
		if ( $skipLogFile ) {
			if ( file_exists( $skipLogFile ) ) {
				$skipExtensionsRaw = explode( "\n", file_get_contents( $skipLogFile ) );
				$skipExtensions = array();
				foreach ( $skipExtensionsRaw as $extName ) {
					// cleanup name, turn array around for fast hash table lookup
					if ( trim($extName) !== "" ) {
						$skipExtensions[ trim($extName) ] = true;
					}
				}
			}
		}

		$completedExtensions = array();
		foreach( $toLoad as $extName ) {

			if ( isset( $skipExtensions[$extName] ) ) {
				$this->output( "\n## Skipping $extName since it's in skip-log $skipLogFile \n" );
				continue; // in skip log, skip it...
			}

			$extensionDir = $this->extensionLoader->extDir . "/$extName";

			// Check if extension directory exists, update extension accordingly
			if ( is_dir( $extensionDir ) ) {
				$this->checkExtensionForUpdates( $extName );
			}
			else {
				$this->cloneGitRepo( $extName );
			}

			$completedExtensions[] = $extName;

		}

		// add any complete extensions to log file.
		if ( $skipLogFile ) {
			file_put_contents( $skipLogFile, implode( "\n", $completedExtensions) . "\n", FILE_APPEND );
		}

		$this->output( "\n## Finished updating wiki extensions. Remember to run update.php\n" );
		$this->showErrors();
		$this->output( "\n" );
	}


	/**
	 *  'git' => 'https://git.wikimedia.org/...',
	 *  AND EITHER
	 *  'branch' => 'master|REL1_25|2b449A',
	 *  OR
	 *  'tag' => '1.24.1'
	 **/
	protected function cloneGitRepo ( $extName ) {

		$this->output( "\n## CLONING EXTENSION $extName\n" );

		$conf = $this->extensionLoader->extensions[$extName];

		// change working directory to main extensions directory
		chdir( $this->extensionLoader->extDir );

		// git clone into directory named the same as the extension
		$cloneAttempts = 0;
		$maxAttempts = 5;
		while ( ! is_dir( "{$this->extensionLoader->extDir}/$extName" ) ) {
			if ( $cloneAttempts > 0 ) {
				$wait = $cloneAttempts * 5;
				$this->output( "Clone $cloneAttempts failed. Reattempting in $wait seconds...\n" );
				sleep( $wait );
			}

			$this->output( shell_exec( "git clone {$conf['git']} $extName" ) );
			$cloneAttempts++;
			if ( $cloneAttempts > $maxAttempts ) {
				continue; // this isn't ideal. Should error-handle better. @fixme
			}
		}

		chdir( "{$this->extensionLoader->extDir}/$extName" );

		$this->checkDifferenceFromHead( $extName );

	}

	protected function checkExtensionForUpdates ( $extName ) {

		$this->output( "\n## Checking for updates in $extName" );

		$extensionDirectory = "{$this->extensionLoader->extDir}/$extName";

		if ( ! is_dir("$extensionDirectory/.git") ) {
			$this->output( "\nNot a git repository! ($extName)" );
			return false;
		}

		// change working directory to main extensions directory
		chdir( $extensionDirectory );


		// git clone into directory named the same as the extension
		$this->output( shell_exec( "git fetch origin" ) );

		$this->checkDifferenceFromHead( $extName );

		$this->output( "\n" );

		return true;

	}

	public function checkDifferenceFromHead ( $extName ) {

		$conf = $this->extensionLoader->extensions[$extName];

		$currentSha1 = shell_exec( "git rev-parse --verify HEAD" );
		list( $checkoutType, $checkout ) = $this->getCheckoutInfo( $conf['version'] );

		if ( $checkoutType == "tag" || $checkoutType == "sha1" ) {
			$fetchedSha1 = shell_exec( "git rev-parse --verify $checkout" );
		}
		else {
			// for branches you have to specify that you mean the remote branch
			// if you don't do so, you check the state of the local branch which
			// is unchanged at this point since you haven't done a merge.
			$fetchedSha1 = shell_exec( "git rev-parse --verify origin/$checkout" );
		}

		if ($currentSha1 !== $fetchedSha1) {
			$this->output( "\nCurrent commit: $currentSha1" );
			$this->output( "\nChecking out new commit for $checkout: $fetchedSha1\n" );

			if ( $checkoutType === 'branch' ){
				// switch to this branch (if necessary)
				$this->output( shell_exec( "git checkout $checkout" ) );

				// merge in latest
				$this->output( shell_exec( "git merge origin/$checkout" ) );
			}
			else {
				$this->output( shell_exec( "git checkout $checkout" ) );
			}
		}
		else {
			$this->output( "\nsha1 unchanged, no update required ($currentSha1)" );
		}

	}

	/**
	 *  Inputs like:
 	 *	tags/v0.3.0
	 *	branch:REL1_25
	 *	a2e7bc52
	 *
	 *  @return array( $checkoutType, $checkoutName )
	 *	 -> checkoutType like tag, branch, or sha1
	 *	 -> checkoutName like tags/v0.1.0, REL1_25, or a2e7bc52
	 **/
	protected function getCheckoutInfo ( $version ) {
		if ( $this->checkForSha1( $version ) ) {
			$checkoutType = 'sha1';
		}
		elseif ( $this->strHasPrefix( $version, 'tags/' ) ) {
			$checkoutType = 'tag';
		}
		else {
			$checkoutType = 'branch';
		}
		return array( $checkoutType, $version );
	}

	private function strHasPrefix ( $string, $prefix ) {
		if ( substr( $string, 0, strlen( $prefix ) ) === $prefix ) {
			return true;
		}
		else {
			return false;
		}
	}

	// @REMOVE
	// private function splitTypeAndCheckout ( $version, $prefix ) {
	// 	if ( substr( $version, 0, strlen( $prefix ) ) === $prefix ) {
	// 		return array(
	// 			substr( $prefix, 0, -1 ), // remove last character from prefix (the colon)
	// 			substr( $version, strlen( $prefix ) )
	// 		);
	// 	}
	// 	else {
	// 		return false;
	// 	}
	// }

	private function checkForSha1 ( $version ) {
		if ( strlen( $version ) < 6 ) {
			// not long enough...need at least the first 6 chars of commit hash
			return false;
		}
		elseif ( preg_match( '/[^1234567890abcdef]/', $version ) ) {
		    // string contains non-hex characters
			return false;
		}
		else {
			return true;
		}
	}

	protected function showErrors () {

		if ( count( $this->extensionLoadingErrors ) > 0 ) {
			$this->output( "\n## The following errors occurred:" );
			foreach ( $this->extensionLoadingErrors as $i => $err ) {
				$num = $i + 1;
				$this->output( "\n\t($num) $err" );
			}
		}
	}


}

$maintClass = "ExtensionLoaderUpdateExtensions";
require_once( DO_MAINTENANCE );
