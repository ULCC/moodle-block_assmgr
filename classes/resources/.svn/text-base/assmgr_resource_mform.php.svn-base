<?php
/**
 * Form class for editing evidence.
 *
 * @copyright &copy; 2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
abstract class assmgr_resource_mform extends assmgr_moodleform {

    /**
     * Constructor
     *
     * @param string $candidate_id The id of the candidate
     * @param string $course_id The id of the course
     * @param string $evidence_id The id of the evidence
     */

    function __construct($candidate_id, $course_id, $resource_type_id, $evidence_id = null, $folder_id = null, $access_isassessor=false) {
        global $CFG;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();
        $this->edit = false;
        // assign the params
        $this->candidate_id = $candidate_id;
        $this->course_id = $course_id;
        $this->evidence_id = $evidence_id;
        $this->resource_type_id = $resource_type_id;
        $this->folder_id = $folder_id;
        $this->access_isassessor = $access_isassessor;

        // get the current url, and encode it for safe transport as a url param
        $this->here = urlencode(base64_encode("{$CFG->wwwroot}/blocks/assmgr/actions/edit_evidence.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;evidence_id={$evidence_id}&amp;resource_type_id={$resource_type_id}&amp;folder_id={$folder_id}"));

        // call the parent constructor
        //NOTE I have added the action string to the parent constructor call with the

        parent::__construct("{$CFG->wwwroot}/blocks/assmgr/actions/edit_evidence.php?course_id={$course_id}&candidate_id={$candidate_id}&resource_type_id={$resource_type_id}&evidence_id={$evidence_id}&largefile=1");
    }

    /**
     * The definition of the form and its elements.
     *
     */
    function definition() {
        global $USER, $CFG;

        $mform =& $this->_form;

        // for the unsaved data check
        $mform->addElement('hidden', 'datachanged', 'false');
        $mform->setType('datachanged', PARAM_ALPHA);

        // HIDDEN elements
        $mform->addElement('hidden', 'id', $this->evidence_id);
        $mform->setType('id', PARAM_INT);

        //TODO this element is a duplicate of the id element
        //this element needs to be called evidence_id can
        //we do without the id element and make calls to evidence_id instead?
        $mform->addElement('hidden', 'evidence_id', $this->evidence_id);
        $mform->setType('evidence_id', PARAM_INT);

        $mform->addElement('hidden', 'candidate_id', $this->candidate_id);
        $mform->setType('candidate_id', PARAM_INT);

        $mform->addElement('hidden', 'course_id', $this->course_id);
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('hidden', 'resource_type_id', $this->resource_type_id);
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('hidden', 'creator_id', $USER->id);
        $mform->setType('creator_id', PARAM_INT);

        // put all the evidece form elements into a fieldset
        $mform->addElement('header', 'fields', get_string('evidencedetails', 'block_assmgr'));

        // NAME element
        $mform->addElement(
            'text',
            'name',
            get_string('name', 'block_assmgr'),
            array('class' => 'form_input')
        );
        $mform->addRule('name', null, 'maxlength', 255, 'client');
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_RAW);

        // FOLDER element
        if($this->access_isassessor) {
            $mform->addElement('hidden', 'folder_id', $this->folder_id);
        } else {
            $folders = $this->dbc->get_folders($this->candidate_id);

            $folderstr = get_string('addnew', 'block_assmgr');
            $folderurl = "{$CFG->wwwroot}/blocks/assmgr/actions/edit_folder.php?course_id={$this->course_id}&amp;candidate_id={$this->candidate_id}redirect={$this->here}";

            if(!empty($folders)) {
                $options = array('' => '');
                foreach ($folders as $folder) {
                    $options[$folder->id] = $folder->name;
                }

                // this is a normal select element, with a link to create a new folder
                $mform->addElement(
                    'selectwithlink',
                    'folder_id',
                    get_string('folder', 'block_assmgr'),
                    $options,
                    array('class'=>'form_select'),
                    array(
                        'label' => $folderstr,
                        'link'  => $folderurl
                    )
                );
            } else {
                // display just the link to make a new folder
                $mform->addElement('static', 'folder', get_string('folder', 'block_assmgr'), "<a href='{$folderurl}'>{$folderstr}</a>");
            }

            $mform->addRule('folder_id', null, 'required', null, 'client');
            $mform->setType('folder_id', PARAM_INT);
        }


        //find the folder with that name that belongs to the user
        // if the default folder has not been setted
        if((int)$this->folder_id == 0) {
            $default_folder  = $this->dbc->get_default_folder($this->course_id, $USER->id);
            if(!empty($default_folder)) {
                $this->folder_id = $default_folder->id;
            }
        }
        //set this as the default course
        if ($this->folder_id != 0) $mform->setDefault('folder_id', $this->folder_id);


        // DESCRIPTION element
        $mform->addElement(
            'htmleditor',
            'description',
            get_string('description', 'block_assmgr'),
            array('class' => 'form_input', 'rows'=> '10', 'cols'=>'65')
        );
        $mform->addRule('description', null, 'maxlength', 65535, 'client');
        //$mform->addRule('description', null, 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);

        // now add fields specific to this type of evidence
        $this->specific_definition($mform);

        // add the submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Force extending class to add its own form fields
     */
    abstract protected function specific_definition($mform);

    /**
     * Performs server-side validation of the unique constraints.
     *
     * @param object $data The data to be saved
     */
    function validation($data) {
        $this->errors = array();

        // enforce unique constraints on fields in this table
        if ($this->dbc->exists('evidence', array('name', 'candidate_id'), $data)) {
            $this->errors['name'] = get_string('notunique', 'block_assmgr', 'name');
        }

        // now add fields specific to this type of evidence
        $this->specific_validation($data);

        return $this->errors;
    }

    /**
     * Force extending class to add its own server-side validation
     */
    abstract protected function specific_validation($data);

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     */
    function process_data($data) {

        if (empty($data->id)) {
            $data->id = $this->dbc->create_evidence($data);
        } else {
            $this->dbc->set_evidence($data);
        }

        if(!empty($data->id)) {
            $this->specific_process_data($data);
        }

        return $data->id;
    }

    /**
     * Force extending class to add its own processing method
     */
    abstract protected function specific_process_data($data);



}
?>