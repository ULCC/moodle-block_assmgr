<?php
/**
 * Upgrade functions for the Assessment Manager.
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installtion to the current version.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

/**
 * Main upgrade function. This will be checked every time the version number of the
 * block increments.
 *
 * @param $oldversion this is passed automatically by Moodle's core so that
 * only relevant upgrades are made.
 * @return bool
 */
function xmldb_block_assmgr_upgrade($oldversion = 0) {

    return true;
}

?>