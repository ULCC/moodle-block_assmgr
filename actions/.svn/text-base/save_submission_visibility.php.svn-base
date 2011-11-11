<?php
/**
 * This page toggles the visibility of a submission.
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

if(!$access_cancreeddel) {
    print_error('nopageaccess', 'block_assmgr');
}

//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

// get the id of the submission
$submission_id = $PARSER->required_param('submission_id', PARAM_INT);

$dbc = new assmgr_db();

// get the evidence
$submission = $dbc->get_submission_by_id($submission_id);

// Lock portfolio if possible
check_portfolio(null, null, $submission->portfolio_id);

// get the evidence
$evidence = $dbc->get_evidence($submission->evidence_id);

// toggle the visibility of the submission
$submission->hidden = ($submission->hidden)? 0 : 1;
$hidden_str = ($submission->hidden) ? get_string('hidden', 'block_assmgr') : get_string('visible', 'block_assmgr');
$course = $dbc->get_course($course_id);

//MOODLE LOG submission assessed
$log_action = get_string('logsubvisibility', 'block_assmgr', $hidden_str);
$a = new stdClass;
$a->name = $evidence->name;
$a->course = $course->shortname;
$a->hidden = $hidden_str;
$log_info = get_string('logsubvisibilityinfo', 'block_assmgr', $a);
assmgr_add_to_log($course_id, $log_action, null, $log_info);

$dbc->set_submission($submission);
$return_message = get_string('submissionsetto', 'block_assmgr', $hidden_str);

redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}#submittedevidence", $return_message, REDIRECT_DELAY);

?>