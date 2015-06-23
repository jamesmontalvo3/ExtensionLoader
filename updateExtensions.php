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


// @todo: does this always work if extensions are not in $IP/extensions ??
// this was what was done by SMW
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';
require_once $basePath . '/maintenance/Maintenance.php';


class ExtensionLoaderUpdateExtensions extends Maintenance {
	
	public function __construct() {
		parent::__construct();
		
		$this->mDescription = "Update or install extensions managed by ExtensionLoader.";
	}
	
 	// initiates or updates extensions
	public function execute() {
		$this->extensionLoader = ExtensionLoader::$loader;

		foreach( $this->extensionLoader->extensions as $extName => $conf ) {
			
			$extensionDir = $this->extensionLoader->extDir . "/$extName";
			
			// Check if extension directory exists, update extension accordingly
			if ( is_dir( $extensionDir ) ) {
				$this->checkExtensionForUpdates( $extName );
			}
			else {
				$this->cloneGitRepo( $extName );
			}
			
		}


		$this->output( "\n Finished recording the state of wiki watching. \n" );
	}


	/**
	 *  'git' => 'https://git.wikimedia.org/...',
	 *  'checkout' => 'master|tags/1.24.1|REL1_25|2b449A',
	 *
	 **/
	protected function cloneGitRepo ( $extName ) {

		$this->output( "\n    CLONING EXTENSION $extName\n" );
	
		$conf = $this->extensionLoader->extensions[$extName];
	
		// change working directory to main extensions directory
		chdir( $this->extensionLoader->extDir );
		
		// git clone into directory named the same as the extension
		$this->output( shell_exec( "git clone {$conf['git']} $extName" ) );
		
		if ( $conf['checkout'] !== 'master' ) {
		
			chdir( "{$this->extensionLoader->extDir}/$extName" );
		
			$this->output( shell_exec( "git checkout " . $conf['checkout'] ) ); 
		
		}
				
	}
	
	protected function checkExtensionForUpdates ( $extName ) {
	
		$this->output( "\n    Checking for updates in $extName\n" );
	
		$conf = $this->extensionLoader->extensions[$extName];
		$extensionDirectory = "{$this->extensionLoader->extDir}/$extName";
		
		if ( ! is_dir("$extensionDirectory/.git") ) {
			$this->output( "\nNot a git repository! ($extName)" );
			return false;	
		}
		
		// change working directory to main extensions directory
		chdir( $extensionDirectory );
		
		// git clone into directory named the same as the extension
		$this->output( shell_exec( "git fetch origin" ) );

		$checkoutType = 'branch';
		if ( isset( $conf['tag'] ) ) {
			$checkoutType = 'tag';
			$checkout = 'tags/' . $conf['tag'];
		}
		else if ( isset( $conf['branch'] ) ) {
			$checkout = 'origin/' . $conf['branch'];
		}
		else {
			$checkoutTest = 'origin/master';
			$checkout = 'origin/master'
		}

		$currentSha1 = shell_exec( "git rev-parse --verify HEAD" );
		$fetchedSha1 = shell_exec( "git rev-parse --verify $checkout" );
		
		if ($currentSha1 !== $fetchedSha1) {
			$this->output( "\nCurrent commit: $currentSha1" );
			$this->output( "\nChecking out new commit for $checkout: $fetchedSha1\n" );
			
			if ( $checkoutType === 'tag' ) {
				// checkout the tagged commit
				$this->output( shell_exec( "git checkout $checkout" ) );
			}
			else {
				// move local master pointer to the location of origin/master
				$this->output( shell_exec( "git reset --hard $checkout" ) );
			}
		}
		else {
			$this->output( "\nsha1 unchanged, no update required ($currentSha1)" );
		}
		
		return true;
	
	}






}

$maintClass = "ExtensionLoaderUpdateExtensions";
require_once( DO_MAINTENANCE );