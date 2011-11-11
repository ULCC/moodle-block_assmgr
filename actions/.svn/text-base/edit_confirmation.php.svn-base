<?php

/**
 * This page displays a candidates portfolio to a asessor so that they the portfolio maybe assessed
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
require_once($CFG->dirroot.'/blocks/assmgr/db/accesscheck.php');

// include the form lib
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_confirmation_mform.php');

// this is the id of the portfolio
$evidence_id = $PARSER->required_param('evidence_id', PARAM_INT);

//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

$dbc = new assmgr_db();

$evidence = $dbc->get_evidence_resource($evidence_id);
$candidate_id = $evidence->candidate_id;

// check confirmation status
$needs_confirmation = false;
$confirmation_status = get_string('unnecssary', 'block_assmgr');
$confirmation = $dbc->get_confirmation($evidence_id);

if(!empty($confirmation)) {
    $needs_confirmation = ($confirmation->status == CONFIRMATION_PENDING);
    $confirmation_status = confirmation_status($confirmation->status);
}

if(!empty($evidence->folder_id)) $folder = $dbc->get_folder($evidence->folder_id);

$foldername = (!empty($folder)) ? $folder->name : get_string('none', 'block_assmgr');

$submissioncoursess = $dbc->get_submission_courses_by_evidence($evidence->id);
$displaycourses = '';
foreach ($submissioncoursess as $course) {
    $displaycourses .= $course->fullname.'<br />';
}

$assessed_status = get_string('notassessed', 'block_assmgr');
$verified_status = (!empty($evidence->verified_status)) ? get_string('verified', 'block_assmgr') : get_string('notverified', 'block_assmgr');
$evidence_status = $dbc->has_submission($evidence->id)  ? get_string('submitted', 'block_assmgr') : get_string('notsubmitted', 'block_assmgr');

if(!$access_canconfirm) {
    print_error('nopageaccess', 'block_assmgr');
}

if(empty($evidence)) {
    print_error('evidenceretrieve', 'block_assmgr');
}

if ($evidence->candidate_id == $USER->id) {
    print_error('cantconfirmownevidence', 'block_assmgr');
}

if(!empty($evidence)) {
     if($evidence->candidate_id != $evidence->creator_id && !$access_isassessor) {
        print_error('canteditassessorevidence', 'block_assmgr');
    }

    $submissions = $dbc->get_submissions_by_evidence($evidence->id);

    if (!empty($submissions)) {
        foreach ($submissions as $submission) {
            check_portfolio(NULL, NULL, $submission->portfolio_id);
        }
    }
}

$evidence_resource = new $evidence->resource_type;
$evidence_resource->load($evidence->id);

$typeheader = get_string('confirmevidence', 'block_assmgr');
$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => '', 'type' => 'title');
$navlinks[] = array('name' => get_string('unconfirmedevidence','block_assmgr'), 'link' => "list_unconfirmed.php?course_id={$course_id}", 'type' => 'title');
$navlinks[] = array('name' => get_string('confirmevidence','block_assmgr'), 'link' => '', 'type' => 'title');
$navlinks[] = array('name' => $evidence->name, 'link' => '', 'type' => 'title');

$course = $dbc->get_course($course_id);

// MOODLE LOG confirmation edit
$log_action = get_string('logconfirmedit', 'block_assmgr');
$log_url = "edit_confirmation.php?course_id={$course_id}&amp;evidence_id={$evidence_id}";
$log_info = $evidence->name;
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// setup the page title and heading
$PAGE->title = $course->shortname.': '.get_string('blockname', 'block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/edit_confirmation.php', $PARSER->get_params());


$confirmationform = new edit_confirmation_mform($evidence, $foldername, $evidence_status, $confirmation_status, $evidence_resource);

if ($confirmationform->is_cancelled()) {

    $backurl = $CFG->wwwroot.'/blocks/assmgr/actions/list_unconfirmed.php?course_id='.$course_id;
    redirect($backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);

} else if ($confirmationform->is_submitted()) {

    if(!$access_canconfirm) {
        print_error('nopageaccess', 'block_assmgr');
    }
    // check the validation rules
    if($confirmationform->is_validated()) {
        // process the data
        $success = $confirmationform->process_data($confirmationform->get_data(), $access_isassessor, $access_iscandidate);
    }
} else {

    // pre fill the form fields with data
    $data = new stdClass;

    $data->course_id = $course_id;
    $data->candidate_id = $candidate_id;
    $data->evidence_id = $evidence_id;
    $data->name = $evidence->name;
    $data->description = $evidence->description;
    $data->folder = $foldername;
    $data->lastchanged = userdate($evidence->timemodified, get_string('strftimedate', 'langconfig'));
    $data->currentstatus = $evidence_status;
    $data->confirmationstatus = $confirmation_status;
    $data->resourcetype = $evidence_resource->audit_type();
    $data->resource = $evidence_resource->get_content();
    $data->submittedcourses = $displaycourses;

    $confirmationform->set_data($data);

    // pre fill the data from the previous confirmation if there was one
    if(!empty($confirmation)) {
        $confirmationform->set_data($confirmation);
    }

    $confirmationform->set_data($data);
}

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_confirmation.html');

?>