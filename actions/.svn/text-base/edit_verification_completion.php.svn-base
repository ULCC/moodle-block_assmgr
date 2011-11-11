<?php
/* 
 * This shows the form which will appear below the portfolio and evidence on the view_verification.php
 * page
 */

//include moodle config
//require_once(dirname(__FILE__).'/../../../config.php');

// remove this when testing is complete
$path_to_config = dirname($_SERVER['SCRIPT_FILENAME']).'/../../../config.php';

while (($collapsed = preg_replace('|/[^/]+/\.\./|','/', $path_to_config, 1)) !== $path_to_config) {
    $path_to_config = $collapsed;
}
require_once('../../../config.php');

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_verification_completion_mform.php');

$dbc = new assmgr_db();

if (empty($verification)) {
    $verification_id = $PARSER->required_param('verification_id', PARAM_INT);
    $verification = $dbc->get_verification($verification_id);
} else {
    $verification_id = $verification->id;
}

$verificationcompleteform = new edit_verification_completion_mform();


if ($verificationcompleteform->is_submitted()) {
    // check the validation rules
    if($verificationcompleteform->is_validated()) {
        // process the data
        $success = $verificationcompleteform->process_data($verificationcompleteform->get_data());

        if(!$success) {
            print_error('cantsaveverificationform', 'block_assmgr');
        }

        $return_message  = get_string('verificationformsaved', 'block_assmgr');
        //TODO where should this redirect to?
        redirect($CFG->wwwroot.'/blocks/assmgr/actions/list_verifications.php?course_id='.$course_id, $return_message, REDIRECT_DELAY);
    }

} else {

        $data = new stdClass;
        $data->course_id = $course_id;
        $data->verification_id = $verification_id;

        // TODO - set course id etc
        $verificationcompleteform->set_data($data);
        $verificationcompleteform->set_data($verification);
    
}

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_verification_completion.html');

?>
