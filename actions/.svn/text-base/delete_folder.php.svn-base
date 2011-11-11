<?php
/**
 * This page deletes a folder and sets all of its child folders and evidence to null
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

//include the config file
//require_once(dirname(__FILE__).'/../../../config.php');

// remove this when testing is complete
$path_to_config = dirname($_SERVER['SCRIPT_FILENAME']).'/../../../config.php';
while (($collapsed = preg_replace('|/[^/]+/\.\./|','/',$path_to_config,1)) !== $path_to_config) {
    $path_to_config = $collapsed;
}
require_once('../../../config.php');

global $USER, $CFG, $PARSER;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

//include the file library
require_once($CFG->libdir.'/filelib.php');

//get the id of the folder to be deleted
$folder_id = $PARSER->required_param('folder_id');

//get the id of the current course
$course_id = optional_param('course_id', SITEID, PARAM_INT);

// can the user create evidence, if not then they can not do anything with folders
if(!$access_cancreeddel) {
    print_error('nodeletefolders', 'block_assmgr');
}

$return_message = get_string('foldernotdeleted','block_assmgr');

$dbc = new assmgr_db();

if(!empty($folder_id)) {

    $delete_folder = $dbc->get_folder($folder_id);

    if(!empty($delete_folder)) {
        if ($delete_folder->candidate_id == $USER->id) {

            //find the folder with that name that belongs to the user
            $default_folder  = $dbc->get_default_folder($course_id, $USER->id);

            // move all the evidence and subfolders out of here before deleting
            $dbc->move_evidence($folder_id, $default_folder->id);
            $dbc->move_subfolders($folder_id, $default_folder->id);
            $dbc->delete_folder($folder_id);
            $return_message = get_string('folderdeleted','block_assmgr');

            // MOODLE LOG folder has been deleted
            $log_action = get_string('logfolderdelete', 'block_assmgr');
            $log_info = $delete_folder->name.' '.get_string('deletedfrom', 'block_assmgr').' (ID '.$delete_folder->id.')';
            assmgr_add_to_log($course_id, $log_action, null, $log_info);

        } else {
            $return_message = get_string('foldernotyours','block_assmgr');
        }

    } else {
        $return_message = (empty($delete_folder))
            ? get_string('foldernotfound','block_assmgr')
            : get_string('cantdeleterootfolder','block_assmgr');
    }
} else {
    $return_message = get_string('folderidnotprovided','block_assmgr');
}
redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;folder_id={$folder_id}#evidencefolders", $return_message, REDIRECT_DELAY);
?>