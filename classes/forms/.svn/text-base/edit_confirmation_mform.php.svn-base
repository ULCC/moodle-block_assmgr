<?php
/**
 * TODO comment this
 */
class edit_confirmation_mform extends assmgr_moodleform {

    function __construct($evidence, $foldername, $evidence_status, $confirmation_status, $evidence_resource) {

        $this->evidence = $evidence;
        $this->foldername = $foldername;
        $this->evidence_status = $evidence_status;
        $this->evidence_resource = $evidence_resource;
        $this->confirmation_status = $confirmation_status;
        $this->evidence_resource = $evidence_resource;

        // call the parent constructor
        parent::__construct();

    }

    function definition() {

        $mform =& $this->_form;

        // top bit start
        $fieldsettitle = get_string('confirmation', 'block_assmgr');
        $mform->addElement('header', 'confirmation', $fieldsettitle);

        $mform->addElement('hidden', 'course_id');
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('hidden', 'candidate_id');
        $mform->setType('candidate_id', PARAM_INT);

        $mform->addElement('hidden', 'evidence_id');
        $mform->setType('evidence_id', PARAM_INT);

        // for the unsaved data check
        $mform->addElement('hidden', 'datachanged', 'false');
        $mform->setType('datachanged', PARAM_ALPHA);


        // top bit with static stuff
        $mform->addElement('static',
                           'name',
                           get_string('name', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'description',
                           get_string('description', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'folder',
                           get_string('folder', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'lastchanged',
                           get_string('lastchanged', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'currentstatus',
                           get_string('status', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'resourcetype',
                           get_string('resourcetype', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'resource',
                           get_string('resource', 'block_assmgr').':'
                           );

        $mform->addElement('static',
                           'submittedcourses',
                           get_string('submittedcourses', 'block_assmgr').':'
                           );

        $selectoptions = array(
                CONFIRMATION_PENDING => get_string('pending', 'block_assmgr'),
                CONFIRMATION_REJECTED => get_string('reject'),
                CONFIRMATION_CONFIRMED => get_string('confirm'));
        $mform->addElement('select', 'status', get_string('confirmationstatus', 'block_assmgr'), $selectoptions);
        $mform->setType('status', PARAM_INT);
        $mform->setDefault('status', null);
        $mform->addRule('status', get_string('required'), 'required', null, 'client');

        $mform->addElement('htmleditor', 'feedback', get_string('comments', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
        $mform->setType('feedback', PARAM_RAW);

        // submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    function process_data($data) {

        global $CFG, $USER;

        // instantiate the db class
        $dbc = new assmgr_db();

        // fetch the evidence
        $evidence = $dbc->get_evidence($data->evidence_id);

        // is this the users evidence
        if($USER->id == $evidence->candidate_id ) {
            print_error('cantconfirmown', 'block_assmgr');
        }

        // resolve the status
        $confirmation_status = ($data->status < CONFIRMATION_REJECTED) ? get_string('confirmed', 'block_assmgr') : get_string('rejected', 'block_assmgr');

        //MOODLE LOG confirmation create
        $log_action = get_string('logevconfcreate', 'block_assmgr');
        $log_info = "{$evidence->name} {$confirmation_status}";
        assmgr_add_to_log($data->course_id, $log_action, null, $log_info);

        // save the confirmation
        $dbc->set_confirmation($data->evidence_id, $data->status, $data->feedback);

        $return_message = get_string('theevidencehasbeen', 'block_assmgr').' '.$confirmation_status;
        redirect("{$CFG->wwwroot}/blocks/assmgr/actions/list_unconfirmed.php?course_id={$data->course_id}&id={$USER->id}", $return_message, REDIRECT_DELAY);

    }

}

?>
