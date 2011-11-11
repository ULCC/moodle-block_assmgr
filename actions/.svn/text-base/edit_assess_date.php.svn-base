<?php

/**
 * This file gets the date (from an assessor user) on which a assesor intends to assess a portfolio
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

// include the moodle form for this table
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_assess_date_mform.php');

//include the moodle message library
require_once($CFG->dirroot.'/message/lib.php');


if(!$access_isassessor) {
    print_error('nopageaccess', 'block_assmgr');
}

//require the course id
$course_id = $PARSER->required_param('course_id',PARAM_INT);

//require the candidates id
$candidate_id = $PARSER->required_param('candidate_id',PARAM_INT);

//if present get the id of the event that will be edited
$event_id = $PARSER->optional_param('event_id', NULL, PARAM_INT);

//if present get the id of the group that will be edited
$group_id = $PARSER->optional_param('group_id', NULL, PARAM_INT);

//if present get the id of the event that will be edited
$repeat_id = $PARSER->optional_param('repeat_id', NULL, PARAM_INT);

$dbc = new assmgr_db();

//find the portfolio if it has been set
$portfolio = $dbc->get_portfolio($candidate_id, $course_id);
$course = $dbc->get_course($course_id);
$coursecat = $dbc->get_category($course->category);
$candidate =$dbc->get_user($candidate_id);

if (!empty($event_id)) $event = $dbc->get_future_assessment_event_by_id($event_id);

//MOODLE LOG future assessment viewed
$log_action = get_string('logviewfutureassessment', 'block_assmgr');
$log_url = "edit_assess_date.php?course_id={$course_id}&candidate_id={$candidate_id}";
$log_info = get_string('logportfoliofutureassessdatesviewed', 'block_assmgr', $course->shortname);
add_to_log($course_id, 'assmgr', $log_action, '/actions/'.$log_url, $log_info);
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// setup the navigation breadcrumbs
$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');
$navlinks[] = array('name' => $coursecat->name, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}", 'type' => 'title');
$navlinks[] = array('name' => $course->shortname, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}", 'type' => 'title');
$navlinks[] = array('name' => fullname($candidate), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#submittedevidence", 'type' => 'title');
$navlinks[] = array('name' => get_string('setassessmentdate','block_assmgr'), 'link' => null, 'type' => 'title');

// setup the page title and heading
$PAGE->title = get_string('setassessmentdate','block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/edit_assess_date.php', $PARSER->get_params());

// instantiate the form
$mform = new edit_assess_date_mform($candidate_id,$course_id,$event_id,$repeat_id,$group_id);
if (!empty($event_id)) {
    $event->timestart = $event->timestart;
    if (!empty($event->courseid)) {
        $event->assesstype = 'Course';
    } else if (!empty($event->groupid)){
        $event->assesstype = 'Group';
    } else {
        $event->assesstype = 'User';
    }
    $mform->set_data($event);
}


$backurl = "list_portfolio_assessments.php?course_id={$course_id}";

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
            print_error('datesavefail', 'block_assmgr');
        }

        $return_message = (empty($event_id)) ? get_string('futureassessmentdateset', 'block_assmgr') : get_string('futureassessmentdateupdate', 'block_assmgr');

        redirect("{$CFG->wwwroot}/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}", $return_message, REDIRECT_DELAY);
    }
}

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_assess_date.html');

echo $OUTPUT->footer();
?>