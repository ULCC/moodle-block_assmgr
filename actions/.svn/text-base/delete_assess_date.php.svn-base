<?php
/**
 * This page deletes a portfolios assessment date event from a assessors calendar
 * and a candidates calendar it also sends a message to both to the candidate
 * informing them of the change.
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
$event_id = $PARSER->required_param('event_id',PARAM_INT);

//if present get the id of the event that will be edited
$repeat_id = $PARSER->required_param('repeat_id',PARAM_INT);

$dbc = new assmgr_db();

$event = $dbc->get_calendar_event($event_id);
$assessor_event = $dbc->get_calendar_event($repeat_id);

$course = $dbc->get_course($course_id);


$return_message = get_string('assessdatenotdeleted', 'block_assmgr');

if (!empty($event) && !empty($assessor_event) ) {

    $dbc->delete_assessment_event($event_id);
    $dbc->delete_assmgr_calendar_event($event_id);

    // delete the assessment dates from the calendar itself
    $dbc->delete_assessment_event($repeat_id);
    $dbc->delete_assmgr_calendar_event($repeat_id);

    $return_message = get_string('assessdatedeleted', 'block_assmgr');

    $event_course = $dbc->get_course($event->course_id);

    if (!empty($event->course_id)) {
        $course_details = get_context_instance(CONTEXT_COURSE, $event->course_id);
        $candidates_array = get_users_by_capability($course_details,'block/assmgr:creddelevidenceforself','','','','','','',false,false,false);

    } else if (!empty($event->groupid)) {
       $candidates_array = $dbc->get_group_users($event->groupid);
    } else {

       $candidate_obj = new object();
       $candidate_obj->id = $event->userid;
       $candidates_array = array($candidate_obj);
    }

    foreach ($candidates_array as $cand) {
        $message_candidate_id = (!empty($event->groupid)) ? $cand->userid : $cand->id;
        $assessment_date = userdate($event->timestart, get_string('strftimedate', 'langconfig'));
        $coursename = $event_course->fullname;
        $message = get_string('userfutureassessdeleted','block_assmgr', (object)compact('coursename', 'assessment_date'));
        $messagefrom = $dbc->get_user($USER->id);
        $messageto = $dbc->get_user($message_candidate_id);
        message_post_message($messagefrom, $messageto, $message, FORMAT_HTML, '');

        // MOODLE LOG assessment event has been deleted
        $log_action = get_string('logassessdatedelete', 'block_assmgr');
        $a = new stdClass;
        $a->name = fullname($messageto);
        $a->course = $event_course->shortname;
        $log_info = get_string('logsceduledassesscancel', 'block_assmgr', $a);
        assmgr_add_to_log($course_id, $log_action, null, $log_info);

    }


}

redirect("{$CFG->wwwroot}/blocks/assmgr/actions/list_assess_dates.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;delete=1", $return_message, REDIRECT_DELAY);
?>