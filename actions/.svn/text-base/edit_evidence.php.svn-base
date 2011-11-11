<?php
/**
 * This page allows a candidate to create or edit a piece of evidence
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

global $USER, $CFG, $SESSION, $PARSER;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// get the required params
$course_id = $PARSER->required_param('course_id', PARAM_INT);
$resource_type_id = $PARSER->required_param('resource_type_id', PARAM_INT);

// get the optional param
$candidate_id = $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT);
$evidence_id = $PARSER->optional_param('evidence_id', null, PARAM_INT);

// instantiate the db
$dbc = new assmgr_db();

// Is the portfolio in use? Lock it if possible.
//$portfolio = $dbc->get_portfolio($candidate_id, $course_id);
check_portfolio($candidate_id, $course_id);

// get the candidate, course and category
$candidate = $dbc->get_user($candidate_id);
$course = $dbc->get_course($course_id);
$coursecat = $dbc->get_category($course->category);

if($access_isassessor) {
    // don't use the folder is, but create an hidden field instead
    $default_folder  = $dbc->get_default_folder($course_id, $candidate_id);

    //set this as the default course
    if (!empty($default_folder)) $folder_id = $default_folder->id;

} else {
    $folder_id = $PARSER->optional_param('folder_id', null, PARAM_INT);
}

// you must be either a candidate or an assessor to edit evidence
if(!$access_iscandidate && !$access_isassessor) {
    print_error('noeditevidencepermission', 'block_assmgr');
}

if($access_iscandidate && $USER->id != $candidate_id) {
    // candidates can't edit someone else's evidence
    print_error('noeditothersevidence', 'block_assmgr');
}



if(!empty($evidence_id)) {
    // get the evidence record
    $evidence = $dbc->get_evidence($evidence_id);

    // get the confirmation record, if there is one
    $confirmation = $dbc->get_confirmation($evidence->id);

    if($USER->id != $evidence->creator_id) {
        print_error('noeditotherscreatedevidence', 'block_assmgr');
    }

    if($dbc->has_submission($evidence->id)) {
        //display error msg assesse may not edit a submitted piece of evidence
        print_error('canteditsubmittedevidence', 'block_assmgr');
    }

    if(!empty($confirmation->status) && $confirmation->status != CONFIRMATION_PENDING) {
        // display error message, as you cannot edit confirmed/rejected evidence
        $status = confirmation_status($confirmation->status);
        print_error('canteditstatus', 'block_assmgr', $status);
    }

    $typeHeader = get_string('editevidencetitle','block_assmgr');
} else {
    $typeHeader = get_string('createevidence','block_assmgr');
}

if($access_isassessor) {
    // make sure the candidate is actually a candidate in this context
    $iscandidate = has_capability('block/assmgr:creddelevidenceforself', $coursecontext, $candidate_id, false);
    if(!$iscandidate && $evidence->creator_id != $USER->id) {
        print_error('editcandidatenotoncourse', 'block_assmgr');
    }
}


// setup the navigation breadcrumbs
$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');

if($access_isassessor) {
    // assessor breadcrumbs
    $navlinks[] = array('name' => $coursecat->name, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}", 'type' => 'title');
    $navlinks[] = array('name' => $course->shortname, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}", 'type' => 'title');
    $navlinks[] = array('name' => fullname($candidate), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#evidencefolders", 'type' => 'title');
    $page_heading = get_string('candidateportfolio', 'block_assmgr', fullname($candidate));
} else {
    // candidate breadcrumbs
    $page_heading = get_string('myportfolio', 'block_assmgr');
    $navlinks[] = array('name' => $page_heading, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#evidencefolders", 'type' => 'title');
}

$navlinks[] = array('name' => $typeHeader, 'link' => '', 'type' => 'title');
if(!empty($evidence_id)) {
    $navlinks[] = array('name' => $evidence->name, 'link' => '', 'type' => 'title');
}

//This section of code checks the candidates quota usage.
//if found to be over quota and the resource in use uses file storage
//the candidate is redirected to the edit_portfolio.php page with a friendly warning
$quota = get_user_quota($candidate_id,$course->category);
$over_quota = false;
// TODO: it should never be zero...?
if(!empty($quota)) {
    $quota_usage = get_user_quota_usage($candidate_id,$course->category);

    $quota_in_bytes  = $quota * 1024 * 1024;
    $quota_usage_percentage = round(($quota_usage/$quota_in_bytes) * 100,2);
    // print string


    if ($quota_usage_percentage > 100) {
        $over_quota = true;
        $a = new stdClass;
        $a->quota = $quota;
        $a->quota_usage = formatfilesize($quota_usage);
        $a->percentage = $quota_usage_percentage;
    }
}


// get the resource type
$resource_type = $dbc->get_resource_type($resource_type_id);

// include the class for this type of evidence
@include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$resource_type->name}.php");

if(!class_exists($resource_type->name)) {
    print_error('noclassforresource', 'block_assmgr', '', $resource_type->name);
}

$evidclass = new $resource_type->name();

if ($over_quota && $evidclass->file_storage()) {

    redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&candidate_id={$candidate_id}", get_string('candidateoverquota', 'block_assmgr', $a), REDIRECT_DELAY);
}

$evidclass->edit($candidate_id, $course_id, $resource_type_id, $evidence_id, $folder_id, $access_isassessor, $_SERVER['QUERY_STRING'] );

if(!empty($evidence->id)) {
    $log_action = get_string('logevidenceedit', 'block_assmgr');
    $log_url = "edit_evidence.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;evidence_id={$evidence_id}&amp;folder_id={$folder_id}";
    $log_info = $evidence->name;
    assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);
}

// render the page
require_once($CFG->dirroot.'/blocks/assmgr/views/edit_evidence.html');
?>