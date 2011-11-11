<?php
/**
 * Add or edit a folder.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

//include moodle config
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

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

//include the assessment manager parser class
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_folder_mform.php');

//get the course_id parameter
$course_id = $PARSER->required_param('course_id', PARAM_INT);

//get the edit parameter
//STATES: true or false
$edit = $PARSER->optional_param('edit', false,PARAM_BOOL);

$folder_id = $PARSER->optional_param('folder_id',NULL,PARAM_INT);

// parent folder id
$parent_id = $PARSER->optional_param('parent_id',NULL,PARAM_INT);

$dbc = new assmgr_db();

//retrieve the current users folder infomation
$candidate_folders = $dbc->get_folders($USER->id);

// Is the portfolio in use?
//$portfolio = $dbc->get_portfolio($USER->id, $course_id);
//
//if (!empty($portfolio)) {
//    // will throw an error/exception on failure
//    lock_portfolio_if_possible($portfolio->id);
//} else {
//    $portfolioid = make_portfolio($USER->id, $course_id);
//    lock_portfolio_if_possible($portfolioid);
//}

//if the type is set to edit then edit must be true
if(!empty($folder_id)) {

    $edit = true;

    $fold = $dbc->get_folder($folder_id);
    if(!empty($fold)) {
        if ($fold->candidate_id == $USER->id) {
            $folder = new object();
            $folder->id = $fold->id;
            // THIS IS A HACK
            // because the folder_id is a hidden field used
            // to store the ID of the folder to modify
            // BUT in the db folder_id is the PARENT FOLDER
            $folder->folder_id = $fold->id;
            $folder->name = $fold->name;
            $folder->candidate_id = $fold->candidate_id;

            // TODO add this!?!? is it necessary???
            //$folder->creator_id = $fold->creator_id;

            // set parent
            $parent_id = $fold->folder_id;
        }
        else {
            print_error('noeditothersfolder', 'block_assmgr');
        }
    }
    else {
        print_error('cantfindfolder', 'block_assmgr');
    }

    unset($candidate_folders[$folder->id]);
}


if(!empty($folder_id)) {
    // exclude all descendents of the current folder
    $exclude = array($folder->id);
    $recurse = true;
    while($recurse) {
        $recurse = false;
        if(!empty($candidate_folders)) {
            foreach($candidate_folders as $i => $f) {
                if(in_array($f->folder_id, $exclude)) {
                    // add the child to the exclude list
                    $exclude[] = $i;
                    // remove the folder from the array
                    unset($candidate_folders[$i]);
                    // recurse through the list again as there might be grandchildren
                    $recurse = true;
                }
            }
        }
    }
}

if (!empty($fold)) {
    //MOODLE LOG folder viewed
    $log_action = get_string('logfolderedit', 'block_assmgr');
    $log_url = "edit_folder.php?course_id={$course_id}&amp;folder_id={$folder_id}";
    $log_info = $fold->name.' '.get_string('viewed', 'block_assmgr');
    assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);
}


// instantiate the form
$mform = new edit_folder_mform($course_id, $folder_id, $parent_id);
if (!empty($folder_id))  $mform->set_data($folder);

$back_folder_id = $folder_id;
if(empty($back_folder_id)) {
    $back_folder_id = $parent_id;
}
$backurl = "edit_portfolio.php?course_id={$course_id}&amp;folder_id={$back_folder_id}#evidencefolders";

// was the form canceled
if ($mform->is_cancelled()) {
    // if canceled then go back to the edit portfolio page
    redirect($backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);
}

// has the form been submitted
if($mform->is_submitted()) {
    // check the validation rules
    if($mform->is_validated()) {
        // process the data
        $success = $mform->process_data($mform->get_data());

        if(!$success) {
            print_error('cantsavefolder', 'block_assmgr');
        }

        $return_message  = (empty($folder_id)) ? get_string('foldersaved','block_assmgr') : get_string('folderupdated','block_assmgr');
        redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;folder_id={$back_folder_id}#evidencefolders", $return_message, REDIRECT_DELAY);
    }
}

$course = $dbc->get_course($course_id);

$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');
$navlinks[] = array('name' => get_string('myportfolio','block_assmgr'), 'link' => "edit_portfolio.php?course_id={$course_id}#evidencefolders", 'type' => 'title');

$pagename = (!empty($folder_id)) ? get_string('editfolder','block_assmgr') : get_string('createfolder','block_assmgr');

$navlinks[] = array('name' => $pagename, 'link' => '', 'type' => 'title');
if(!empty($folder_id)) {
    $navlinks[] = array('name' => $folder->name, 'link' => '', 'type' => 'title');
}
// setup the page title and heading
$PAGE->title = $course->shortname.': '.get_string('blockname','block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/edit_folder.php', $PARSER->get_params());

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_folder.html');

echo $OUTPUT->footer();
?>