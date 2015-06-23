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


// @todo: FIXME this needs to work if the extensions directory is not $IP/extensions
require_once( __DIR__ . '/../../maintenance/Maintenance.php' );

class WatchAnalyticsRecordState extends Maintenance {
	
	public function __construct() {
		parent::__construct();
		
		$this->mDescription = "Update or install extensions managed by ExtensionLoader.";
	}
	
	public function execute() {
		ExtensionLoader::$loader->updateExtensions( $this );

		$this->output( "\n Finished recording the state of wiki watching. \n" );
	}
}

$maintClass = "WatchAnalyticsRecordState";
require_once( DO_MAINTENANCE );