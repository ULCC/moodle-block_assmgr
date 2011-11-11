<?php
/* 
 * Called from edit_portfolio.php if viewed by a verifier. It adds the verification form at the bottom.
 */
include_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_verify_form_mform.php');

$verification = $dbc->get_verification($verification_id);
$verify_form_id  = $PARSER->optional_param('verify_form_id', null, PARAM_INT);
$verify_form = $dbc->get_verification_form($verify_form_id);

$verifyform = new edit_verify_form_mform($coursecat, $course, $candidate, $verification, $port, null, $verify_form, true);

// was the form canceled?
if ($verifyform->is_cancelled()) {

    $backurl = $CFG->wwwroot.'/blocks/assmgr/actions/view_verification.php?course_id='.$course_id.'&verification_id='.$verification_id;
    redirect($backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);
}

// fill with previous data
if (!empty($verify_form_id)) {
    $verifyform->set_data($verify_form);
}

if($verifyform->is_submitted()) {

    if($verifyform->is_validated()) {

        // process the data
        $data = $verifyform->get_data();
        $data->id = $data->verify_form_id;
        $success = $verifyform->process_data($data);

        if(!$success) {
            print_error('cantsaveverificationform', 'block_assmgr');
        }

        $return_message  = (empty($verify_form_id)) ? get_string('verificationformsaved','block_assmgr') : get_string('verifyformupdated','block_assmgr');
        //TODO where should this redirect to?
        redirect("{$CFG->wwwroot}/blocks/assmgr/actions/view_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}", $return_message, REDIRECT_DELAY);
    }
}

include_once($CFG->dirroot.'/blocks/assmgr/views/edit_portfolio_verification_form.html');


?>
