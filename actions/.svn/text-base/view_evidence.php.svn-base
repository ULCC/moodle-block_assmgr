<?php
/**
 * This page allows a piece of evidence to be viewed
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

//include the library file
require_once($CFG->dirroot.'/blocks/assmgr/lib.php');

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

// View evidence form
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/view_evidence_mform.php');




//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id',PARAM_INT);

//get the id of the evidence
$evidence_id = $PARSER->required_param('evidence_id',PARAM_INT);



$dbc = new assmgr_db();

$access_ismyevidence = 0;

$evidence = $dbc->get_evidence_resource($evidence_id);

// get the canidate id
$candidate_id = $evidence->candidate_id;

$candidate = $dbc->get_user($candidate_id);
$course = $dbc->get_course($course_id);
$coursecat = $dbc->get_category($course->category);

if(empty($evidence)) {
    print_error('evidenceretrieve','block_assmgr');
}

//is this the users evidence
if($USER->id == $evidence->candidate_id ) {
    $access_ismyevidence = 1;
}

/* TODO is this code necessary as any one can view as long as they can not edit
if(!empty($evidence)) {

    //removed && $type == 'confirm' as it was causing a warning

     if($evidence->candidate_id != $evidence->creator_id &&  !$access_isassessor ) {
        print_error('canteditassessorevidence','block_assmgr');
    }
}
*/
$review = true;

//only the owner or someone with the capability may view evidence
if(!$access_othersevid && !$access_ismyevidence) {
    print_error('cantviewevidence', 'block_assmgr');
}

if($access_ismyevidence || $access_isassessor) {
    $urlstring = "edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}";
} elseif($access_isverifier) {
    $urlstring = "edit_verification.php?course_id={$course_id}&amp;type=verify";
}

// get the page title
$typeheader = get_string('viewevidence', 'block_assmgr');

// setup the navigation breadcrumbs
$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');
$navlinks[] = array('name' => $coursecat->name, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}", 'type' => 'title');
$navlinks[] = array('name' => $course->shortname, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}", 'type' => 'title');

if($access_isassessor) {
    // assessor breadcrumbs
    $navlinks[] = array('name' => fullname($candidate), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#submittedevidence", 'type' => 'title');

} else {
    $navlinks[] = array('name' => get_string('myportfolio', 'block_assmgr'), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#submittedevidence", 'type' => 'title');
}

// add the page title to breadcrumbs
$navlinks[] = array('name' => $typeheader, 'link' => '', 'type' => 'title');
$navlinks[] = array('name' => $evidence->name, 'link' => '', 'type' => 'title');

// Create the mform
$mform = new view_evidence_mform($course_id,$evidence_id);

// include the class for this type of evidence
@include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$evidence->resource_type}.php");

if(!class_exists($evidence->resource_type)) {
    print_error('noclassforresource', 'block_assmgr', '', $evidence->resource_type);
}

//MOODLE LOG evidence view
$log_action = get_string('logevidenceview', 'block_assmgr');
$log_url = "view_evidence.php?course_id={$course_id}&amp;evidence_id={$evidence_id}";
$log_info = $evidence->name.' '.get_string('viewed', 'block_assmgr');
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// setup the page title and heading
$PAGE->title = $course->shortname.': '.get_string('blockname','block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/view_evidence.php', $PARSER->get_params());

$is_submission = $dbc->get_evidence_submission($evidence_id,$course_id);

require_once($CFG->dirroot.'/blocks/assmgr/views/view_evidence.html');

echo $OUTPUT->footer();
?>