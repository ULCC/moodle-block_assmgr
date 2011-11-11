<?php
/**
 * This page saves the candidate portfolio page in the assessment manager
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

//include the file library
require_once($CFG->libdir.'/filelib.php');

//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id',PARAM_INT);

//get the id of the evidence that is currently being used
$evidence_id = $PARSER->required_param('evidence_id',PARAM_INT);

//get the id of the evidence that is currently being used
$candidate_id = $PARSER->required_param('candidate_id',PARAM_INT);

// Lock portfolio if possible
check_portfolio($candidate_id, $course_id);

$dbc = new assmgr_db();

// get the evidence
$evidence = $dbc->get_evidence($evidence_id);
$portfolio = $dbc->get_portfolio($candidate_id, $course_id);

// TODO this should check who the current user is and if they are the same as the candidate
if(!$access_cancreeddelforothers && !$access_cancreeddel) {
    print_error('nopageaccess', 'block_assmgr');
}

$sub = new object();
$sub->portfolio_id = $portfolio->id;
$sub->evidence_id = $evidence_id;
$sub->creator_id = $USER->id;

// save evidence submission
// TODO should be checking this was a success
$submission_id = $dbc->create_submission($sub);

// as there is a new submission we need to flag the portoflio as needing assessment
$dbc->set_portfolio_needsassess($portfolio->id, true);

$course = $dbc->get_course($course_id);

//MOODLE LOG submission created
$log_action = get_string('logsubcreated', 'block_assmgr');
$logstrings = new object();
$logstrings->name = $evidence->name;
$logstrings->course = $course->shortname;
$log_info = get_string('logsubcreatedinfo', 'block_assmgr', $logstrings);
assmgr_add_to_log($course_id, $log_action, null, $log_info);

$return_message = get_string('evidencesubmitted', 'block_assmgr');

redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?submission_id={$submission_id}&course_id={$course_id}&candidate_id={$candidate_id}", $return_message, REDIRECT_DELAY);
?>