<?php
/**
 * Saves evidence confirmation.
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

if(!$access_canconfirm) {
    print_error('nopageaccess', 'block_assmgr');
}

// get the evidence id this confirmation is for
$evidence_id = $PARSER->required_param('evidence_id', PARAM_INT);

// instantiate the db class
$dbc = new assmgr_db();

// fetch the evidence
$evidence = $dbc->get_evidence($evidence_id);

// is this the users evidence
if($USER->id == $evidence->candidate_id ) {
    print_error('cantconfirmown', 'block_assmgr');
}

// get the confirmation details
$feedback = $PARSER->optional_param('confirmation_feedback', NULL, PARAM_TEXT);
$confirm = $PARSER->optional_param('confirm', 0, PARAM_BOOL);

// resolve the status
$status = ($confirm)? CONFIRMATION_CONFIRMED : CONFIRMATION_REJECTED;
$confirmation_status = ($confirm) ? get_string('confirmed', 'block_assmgr') : get_string('rejected', 'block_assmgr');

$evidence = $dbc->get_evidence($evidence_id);

//MOODLE LOG confirmation create
$log_action = get_string('logevconfcreate', 'block_assmgr');
$log_info = "{$evidence->name} {$confirmation_status}";
assmgr_add_to_log($course_id, $log_action, null, $log_info);

// save the confirmation
$dbc->set_confirmation($evidence_id, $status, $feedback);

$return_message = get_string('theevidencehasbeen', 'block_assmgr').' '.$confirmation_status;
redirect("{$CFG->wwwroot}/blocks/assmgr/actions/list_unconfirmed.php?course_id={$course_id}&id={$USER->id}", $return_message, REDIRECT_DELAY);
?>