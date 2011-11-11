<?php
/**
 * Add or edit a verification form. Called from edit_portfolio.php and edit_submission.php
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

while (($collapsed = preg_replace('|/[^/]+/\.\./|','/', $path_to_config, 1)) !== $path_to_config) {
    $path_to_config = $collapsed;
}
require_once('../../../config.php');

global $USER, $CFG, $PARSER;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

// include the assessment manager parser class
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_verify_form_mform.php');

// Get parameters
if (empty($verification_id)) {
    $verification_id = $PARSER->required_param('verification_id', PARAM_INT);
}

// $portfolio_id is sent as part of the edit_submission get parameters, but not for edit_portfolio, which generates it
if (empty($portfolio_id)) {
    $portfolio_id    = $PARSER->required_param('portfolio_id', PARAM_INT);
}
// is this an edit action?
$existing_verify_form_id  = $PARSER->optional_param('verify_form_id', null, PARAM_INT);
// Is this a submission? Portfolio if it's missing.
$submission_id   = $PARSER->optional_param('submission_id', null, PARAM_INT);

$dbc = new assmgr_db();

// Get DB objects
$portfolio    = $dbc->get_portfolio_by_id($portfolio_id);
$course       = $dbc->get_course($portfolio->course_id);
$category     = $dbc->get_category($course->category);
$candidate    = $dbc->get_user($portfolio->candidate_id);

$verification = $dbc->get_verification($verification_id);

$existing_verify_form = false;
if (!empty($existing_verify_form_id)) {
    $existing_verify_form = $dbc->get_verification_form($existing_verify_form_id);
}

if ($submission_id) {
    $submission   = $dbc->get_submission_by_id($submission_id);
} else {
    $submission = null;
}

// Is the portfolio in use? Lock it if possible.
check_portfolio($candidate->id, $course->id);

//MOODLE LOG verify form viewed
$log_action = get_string('verifyform', 'block_assmgr');
$log_url = "edit_verify_form.php?course_id={$course->id}&amp;verification_id={$verification_id}&amp;portfolio_id={$portfolio_id}";

if (!empty($submission)) {
    $log_url .= "&amp;submission_id={$submission_id}";
}

if (!empty($existing_verify_form)) {
    $log_url .= "&amp;verify_form_id={$existing_verify_form_id}";
}
$logstrings = new stdClass;
$logstrings->candidate = fullname($candidate);
$logstrings->course = $course->shortname;
$logstrings->category = $category->name;
$log_info = get_string('verifyforminfo', 'block_assmgr', $logstrings);
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// instantiate the form
$verifyform = new edit_verify_form_mform($category, $course, $candidate, $verification, $portfolio, $submission, $existing_verify_form);

$backurl = "list_verifications.php?course_id={$course_id}";

// was the form canceled?
if ($verifyform->is_cancelled()) {
    // if canceled then go back to the edit portfolio page
    redirect($backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);

} else if ($verifyform->is_submitted()) {
    // check the validation rules
    if($verifyform->is_validated()) {
        // process the data
        $success = $verifyform->process_data($verifyform->get_data());

        if(!$success) {
            print_error('cantsaveverificationform', 'block_assmgr');
        }

        $return_message  = (empty($existing_verify_form_id)) ? get_string('verificationformsaved','block_assmgr') : get_string('verifyformupdated','block_assmgr');
        //TODO where should this redirect to?
        redirect("{$CFG->wwwroot}/blocks/assmgr/actions/view_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}", $return_message, REDIRECT_DELAY);
    }

} else {

    //TODO pass the right stuff into set_data()
    if (!empty($existing_verify_form)) {

        $existing_verify_form->accurate = (Int)$existing_verify_form->accurate;
        $existing_verify_form->verification_id = $verification_id;

        $verifyform->set_data($existing_verify_form);
    }
}


require_once($CFG->dirroot.'/blocks/assmgr/views/edit_verify_form.html');

// add the javascript to make sure unsaved changes are flagged
$unsavedmodule = array(
        'name'      => 'unsaved_data',
        'fullpath'  => '/blocks/assmgr/views/js/unsaved_data.js',
        'requires'  => array()
);
$formsaveargs = array('form' => 'mform1', 'tablename' => get_string('thisform', 'block_assmgr'));
$PAGE->requires->js_init_call('M.blocks_assmgr_unsaved_data.checker.subscribe_to_form', $formsaveargs, true, $unsavedmodule);

//echo $OUTPUT->footer();
