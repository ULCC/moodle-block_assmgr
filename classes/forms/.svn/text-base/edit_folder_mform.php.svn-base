<?php
/**
 * @TODO comment this
 *
 * @copyright &copy; 2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class edit_folder_mform extends assmgr_moodleform {

    private $folder_id;
    private $candidate_folders;
    private $course_id;

    /**
     * Constructor
     *
     * @param string $course_id The id of the course
     * @param string $folder_id The id of the folder that may being edited
     * @return void
     */
    function __construct($course_id, $folder_id=null, $parent_id=null) {
        global $CFG,$USER;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();

        // assign the params
        $this->folder_id = (!empty($folder_id)) ? $folder_id : null;
        $this->course_id = $course_id;
        $this->parent_id = (!empty($parent_id)) ? $parent_id : null;

        // get the current url, and encode it for safe transport as a url param
        $this->here = urlencode(base64_encode("{$CFG->wwwroot}/blocks/assmgr/actions/edit_folder.php?course_id={$this->course_id}"));

        //  retrieve the current users folder infomation
        $this->candidate_folders = $this->dbc->get_folders($USER->id);

        if(!empty($folder_id)) {

            // exclude all descendents of the current folder
            $exclude = array($folder_id);
            $recurse = true;

            while($recurse) {
                $recurse = false;

                if(!empty($this->candidate_folders)) {

                    foreach($this->candidate_folders as $i => $f) {

                        if(in_array($f->folder_id, $exclude)) {
                            // add the child to the exclude list
                            $exclude[] = $i;
                            // remove the folder from the array
                            unset($this->candidate_folders[$i]);
                            // recurse through the list again as there might be grandchildren
                            $recurse = true;
                        }
                    }
                }
            }
        }

        // call the parent constructor
        parent::__construct();
    }

    /**
     * The initial definition of the form and its elements.
     *
     * @return void
     */
    function definition() {
        global $USER, $CFG;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'folder_id', $this->folder_id);
        $mform->setType('folder_id', PARAM_INT);
        $mform->addElement('hidden', 'course_id', $this->course_id);
        $mform->setType('course_id', PARAM_INT);

        // put all the evidece form elements into a fieldset
        $mform->addElement('header', 'fields', get_string('folderdetails', 'block_assmgr'));

        // ---------------------------------------------------------------------
        // --                           NAME                                  --
        // ---------------------------------------------------------------------

        $mform->addElement('text',
                            'name',
                            get_string('foldername','block_assmgr'),
                            array('size' =>'35')
                          );
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

         // ---------------------------------------------------------------------
        // --                           PARENT                                  --
        // ---------------------------------------------------------------------

        $options = array('' => '');
        if(!empty($this->candidate_folders)) {
            foreach($this->candidate_folders as $folder_obj) {
                 $options[$folder_obj->id]  = $folder_obj->name;
                }
        }

        $mform->addElement('select','parent_id',get_string('folderparent', 'block_assmgr'),$options);
        $mform->addRule('parent_id', null, 'required', null, 'client');

        // the function set_data
        // DOESN'T set the parent_id properly
        // BECAUSE THE TABLE COL FOR THE PARENT_ID IS NAMED FOLDER_ID
        // SO I HAVE TO SET THE PARENT_ID ANYWAY
        // if there is not a parent_id
        if (empty($this->parent_id)) {
            //find the folder with that name that belongs to the user
            $default_folder  = $this->dbc->get_default_folder($course_id, $USER->id);

            //set this as the default course
            if (!empty($default_folder)) {
                $mform->setDefault('parent_id', $default_folder->id);
            }
        } else {
            $mform->setDefault('parent_id', $this->parent_id);
        }

        // add the submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Performs server-side validation of the unique constraints.
     *
     * @param object $data The data to be saved
     * @return array any errors found
     */
    function validation($data, $files) {
        global $USER;

        $errors = array();

        $data = (object) $data;

        $errors = parent::validation($data, $files);

        if ($this->dbc->folder_name_exists($data->name, $USER->id, $data->parent_id, $data->folder_id)) {
            $errors['name'] = get_string('foldernameerror', 'block_assmgr', $data->name);
        }

        return $errors;
    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return mixed the id of the new record or false
     */
    function process_data($data) {

        global $USER;

        if(empty($data->folder_id)) {
            $log_action = get_string('logfoldercreate', 'block_assmgr');
            $result = $this->dbc->create_folder($data->name, $USER->id, $data->parent_id);
        } else {
            $log_action = get_string('logfolderupdate', 'block_assmgr');
            $result = $this->dbc->set_folder($data->folder_id, $data->name, $data->parent_id);
        }

        $log_info = $data->name;
        assmgr_add_to_log($this->course_id, $log_action, null, $log_info);

        return $result;
    }

}
?>