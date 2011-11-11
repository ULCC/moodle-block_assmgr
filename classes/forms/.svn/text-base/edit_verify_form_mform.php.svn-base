<?php
/**
 * Form class for editing verification records.
 *
 * @copyright &copy; 2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class edit_verify_form_mform extends assmgr_moodleform {

    /**
     * Constructor
     *
     * @param object $category The category object corresponding to the qualification
     * @param object $course The course object
     * @param object $candidate The user object for the candidate
     * @param object $verification The verification object
     * @param object $portfolio The portfolio object
     * @param object $evidence The evidence object if this is not a portfolio verfication
     * @param object $verify_form The object with data from the existing verification record in the DB
     * @param bool $combined are we in a combined form, with this one appearing at the bottom?
     * @return void
     */
    function __construct($category, $course, $candidate, $verification, $portfolio, $evidence = null, $verify_form = null, $combined = null) {

        global $CFG;

        $this->category     = $category;
        $this->course       = $course;
        $this->candidate    = $candidate;
        $this->verification = $verification;
        $this->portfolio    = $portfolio;
        $this->evidence     = $evidence;
        $this->verify_form  = $verify_form;
        $this->combined     = $combined;

        // call the parent constructor
        parent::__construct($CFG->wwwroot.'/blocks/assmgr/actions/edit_verify_form.php');
    }

    /**
     * The initial definition of the form and its elements.
     *
     * @return void
     */
    function definition() {

        global $USER, $CFG;

        $mform =& $this->_form;

         if (!empty($this->combined)) {
            $fieldsettitle = get_string('verification', 'block_assmgr');
            $mform->addElement('html', '<fieldset id="verificationfieldset" class="clearfix assmgrfieldset">');
            $mform->addElement('html', '<legend class="ftoggler">'.$fieldsettitle.'</legend>');
        }

        // put all the evidece form elements into a fieldset
        $fieldsettitle = (!empty($port)) ? get_string('verifyportfolio', 'block_assmgr') : get_string('verifysubmission', 'block_assmgr');
        $mform->addElement('header', 'fields', $fieldsettitle);

        // for the unsaved data check
        $mform->addElement('hidden', 'datachanged', 'false');
        $mform->setType('datachanged', PARAM_ALPHA);

        // Add hidden form elements
        $mform->addElement('hidden', 'category_id', $this->category->id);
        $mform->setType('category_id', PARAM_INT);

        $mform->addElement('hidden', 'course_id', $this->course->id);
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('hidden', 'candidate_id', $this->candidate->id);
        $mform->setType('candidate_id', PARAM_INT);

        $mform->addElement('hidden', 'verification_id', $this->verification->id);
        $mform->setType('verification_id', PARAM_INT);

        $mform->addElement('hidden', 'portfolio_id', $this->portfolio->id);
        $mform->setType('portfolio_id', PARAM_INT);


        if (!empty($this->evidence)) {
            $mform->addElement('hidden', 'submission_id', $this->evidence->id);
            $mform->setType('submission_id', PARAM_INT);
        }

        if (!empty($this->verify_form)) {
            $mform->addElement('hidden', 'verify_form_id', $this->verify_form->id);
            $mform->setType('verify_form_id', PARAM_INT);
        }

        // Add visible form elements
        $mform->addElement('static',
                           'qualification',
                           get_string('qualification', 'block_assmgr').':',
                           $this->category->name);

        $mform->addElement('static',
                           'unit',
                           get_string('unit', 'block_assmgr').':',
                           $this->course->shortname);

        if (!empty($this->evidence)) {
            $mform->addElement('static',
                               'evidence',
                               get_string('evidencetitle', 'block_assmgr').':',
                               $this->evidence->name);
        }

        $mform->addElement('static',
                           'candidate',
                           get_string('candidate', 'block_assmgr').':',
                           fullname($this->candidate));

        $selectoptions = array(null => '', 0 => get_string('no'), 1 => get_string('yes'));
        $mform->addElement('select', 'accurate', get_string('assessedaccurately', 'block_assmgr'), $selectoptions);
        $mform->setType('accurate', PARAM_INT);
        $mform->setDefault('accurate', null);
        $mform->addRule('accurate', get_string('required'), 'required', null, 'client');

        $mform->addElement('htmleditor', 'accurate_comment', get_string('comments', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
        $mform->setType('accurate_comment', PARAM_RAW);

        $mform->addElement('select', 'constructive', get_string('feedbackconstructive', 'block_assmgr'), $selectoptions);
        $mform->setType('constructive', PARAM_INT);
        $mform->setDefault('constructive', null);
        $mform->addRule('constructive', get_string('required'), 'required', null, 'client');
        $mform->addElement('htmleditor', 'constructive_comment', get_string('comments', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
        $mform->setType('constructive_comment', PARAM_RAW);

        $mform->addElement('select', 'needs_amending', get_string('needsamending', 'block_assmgr'), $selectoptions);
        $mform->setType('needs_amending', PARAM_INT);
        $mform->setDefault('needs_amending', null);
        $mform->addRule('needs_amending', get_string('required'), 'required', null, 'client');
        $mform->addElement('htmleditor', 'amendment_comment', get_string('comments', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
        $mform->setType('amendment_comment', PARAM_RAW);

        $mform->addElement('htmleditor', 'actions', get_string('actionstaken', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
        $mform->setType('actions', PARAM_RAW);

        $this->add_action_buttons(true, get_string('submit'));

        if (!empty($this->combined)) {
            $mform->addElement('html', '</fieldset>');
        }
    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return bool True regardless
     */
    function process_data($data) {

        $dbc = new assmgr_db();

        if(empty($data->verify_form_id)) {
            return $dbc->create_verification_form($data);
        } else {
            $data->id = $data->verify_form_id;
            return $dbc->set_verification_form($data);

        }
    }

}
