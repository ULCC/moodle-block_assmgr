<?php
/* 
 * This form appears at the bottom of view_verification.php and allows the verifier to specify
 * whether the verification is complete
 */

class edit_verification_completion_mform extends assmgr_moodleform {


    function __construct() {

        global $CFG;

        // call the parent constructor
        parent::__construct($CFG->wwwroot.'/blocks/assmgr/actions/edit_verification_completion.php');

    }

     function definition() {

        $mform =& $this->_form;

        $mform->addElement('hidden', 'verification_id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'course_id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'candidate_id', 0);
        $mform->setType('id', PARAM_INT);

        //$mform->addElement('html', '<fieldset class="clearfix">');

        //$fieldsettitle = get_string('verificationcompletion', 'block_assmgr');
        //$mform->addElement('header', 'verificationcompletion', $fieldsettitle);

        //$mform->addElement('html', '<legend class="ftoggler">'.get_string('verificationcompletion', 'block_assmgr').'</legend>');
        //$mform->addElement('header', 'verificationcompletion', get_string('verificationcompletion', 'block_assmgr'));

        $mform->addElement('checkbox', 'complete', get_string('verificationnowcomplete', 'block_assmgr'));
        $mform->setType('complete', PARAM_ALPHANUM);

        $this->add_action_buttons(false, get_string('submit'));

        //$mform->addElement('html', '</fieldset>');
        
    }

    function process_data($data) {

        $dbc = new assmgr_db();
        $data->id = $data->verification_id;
        if (empty($data->complete)) {
            $data->complete = 0;
        }
        return $dbc->set_verification($data);
    }

}

?>
