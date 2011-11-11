<?php
/**
 * This page deletes the association between evidence and a portfolio.
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

// get the id of the course we are currently in
$course_id = $PARSER->required_param('course_id',PARAM_INT);

// get the optional canidate_id
$candidate_id = $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT);

// get the submission id
$submission_id = $PARSER->required_param('submission_id', PARAM_INT);

$dbc = new assmgr_db();

// get the submission
$submission = $dbc->get_submission_by_id($submission_id);

// get the evidence
$evidence = $dbc->get_evidence($submission->evidence_id);

// you must be either a candidate or an assessor to delete submissions
if(!$access_iscandidate && !$access_isassessor) {
    print_error('nodeletesubmissions', 'block_assmgr');
}

if($USER->id != $submission->creator_id) {
    // you can't delete someone else's submissions
    print_error('nodeleteotherssubmissions', 'block_assmgr');
}

// make sure the submission has not been assessed
if($dbc->has_submission_grades($submission_id)) {
    print_error('cantdeleteassessedsubmission', 'block_assmgr');
}

// Is the portfolio in use?
$portfolio = $dbc->get_portfolio($candidate_id, $course_id);

if (!empty($portfolio)) {
    // will throw an error/exception on failure
    check_portfolio($candidate_id, $course_id);
}

// delete the claims
$dbc->delete_submission_claims($submission->id);

// delete the submission itself
$dbc->delete_submission($submission_id);

$return_message = get_string('submissiondeleted', 'block_assmgr');

$portfolio = $dbc->get_portfolio_by_id($submission->portfolio_id);
$course = $dbc->get_course($portfolio->course_id);

// MOODLE LOG submission has been deleted
$log_action = get_string('logsubmissiondelete', 'block_assmgr');
// TODO these should be params into the language string
$log_info = $evidence->name.' '.get_string('deletedfrom', 'block_assmgr').' '.$course->shortname;
assmgr_add_to_log($course_id, $log_action, null, $log_info);

redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&candidate_id={$candidate_id}#submittedevidence", $return_message, REDIRECT_DELAY);
?>