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
class view_evidence_mform extends assmgr_moodleform {

    private $evidence_id;
    private $course_id;
    private $resource;
    private $resource_record;
    private $foldername;
    private $evidence_status;
    private $confirmation_status;
    private $confirmation_feedback;
    /**
     * Constructor
     *
     * @param string $course_id The id of the course
     * @param string $folder_id The id of the folder that may being edited
     * @return void
     */
    function __construct($course_id,$evidence_id) {
        global $CFG,$USER;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();
        $this->course_id = $course_id;
        $this->evidence = $this->dbc->get_evidence_resource($evidence_id);
        if (!empty($this->evidence)) {
            if(!empty($this->evidence->folder_id)) $folder = $this->dbc->get_folder($this->evidence->folder_id);

            $this->foldername = (!empty($folder)) ? $folder->name : get_string('none','block_assmgr');

            $this->resource = new $this->evidence->resource_type;
            $this->resource->load($this->evidence->resource_id);

            $this->evidence_status = ($this->dbc->has_submission($evidence_id))  ? get_string('submitted','block_assmgr') : get_string('notsubmitted','block_assmgr');

            $this->confirmation_status = get_string('notapplicable','block_assmgr');
            $this->confirmation_feedback = false;

            $confirmation = $this->dbc->get_confirmation($evidence_id);
            if(!empty($confirmation)) {
                $needs_confirmation = ($confirmation->status == CONFIRMATION_PENDING);
                $this->confirmation_status = confirmation_status($confirmation->status);
                $this->confirmation_feedback = $confirmation->feedback;
            }
        }

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


        // put all the evidece form elements into a fieldset
        $mform->addElement('header', 'fields', get_string('evidencedetails', 'block_assmgr'));

        if (!empty($this->evidence)) {
                // --------------------------------------------------------------------
                // --                           NAME                                 --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'name',
                                    get_string('evidencename','block_assmgr'),
                                    $this->evidence->name);

                // --------------------------------------------------------------------
                // --                           Description                          --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'description',
                                    get_string('description','block_assmgr'),
                                    assmgr_db::decode_htmlchars($this->evidence->description));

                // --------------------------------------------------------------------
                // --                           Folder                               --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'folder',
                                    get_string('foldername','block_assmgr'),
                                    $this->foldername);

                // --------------------------------------------------------------------
                // --                           Last Changed                         --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'lastchanged',
                                    get_string('lastchanged','block_assmgr'),
                                    userdate($this->evidence->timemodified, get_string('strftimedate', 'langconfig')));

                // --------------------------------------------------------------------
                // --                           Status                               --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'status',
                                    get_string('status','block_assmgr'),
                                    $this->evidence_status);

                // --------------------------------------------------------------------
                // --                           Confirmation Status                  --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'confirmationstatus',
                                    get_string('confirmationstatus','block_assmgr'),
                                    $this->confirmation_status);

                // --------------------------------------------------------------------
                // --                           Resource type                        --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'resourcetype',
                                    get_string('resourcetype','block_assmgr'),
                                    $this->resource->audit_type());

                // --------------------------------------------------------------------
                // --                           Resource                             --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'resource',
                                    get_string('resource','block_assmgr'),
                                    assmgr_db::decode_htmlchars($this->resource->get_content()));


                // --------------------------------------------------------------------
                // --                           confirmation feedback                --
                // --------------------------------------------------------------------

                if (!empty($this->confirmation_feedback)) {
                   $mform->addElement('static',
                                    'confirmationfeedback',
                                    assmgr_db::decode_htmlchars($this->confirmation_feedback));
                }

            } else {
                // --------------------------------------------------------------------
                // --                           Evidence Not Found                   --
                // --------------------------------------------------------------------

                $mform->addElement('static',
                                    'evidencenotfound',
                                    get_string('evidencenotfound','block_assmgr'),
                                    null);
            }
        }

    /**
     * Performs server-side validation of the unique constraints.
     *
     * @param object $data The data to be saved
     * @return array any errors found
     */
    function validation($data, $files) {

    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return mixed the id of the new record or false
     */
    function process_data($data) {

    }

}
?>