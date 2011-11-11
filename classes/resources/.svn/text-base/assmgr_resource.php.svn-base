<?php
/**
 * An abstract class that holds methods and attributes common to all evidence
 * classes.
 *
 * @abstract
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
//abstract class assmgr_resource {
class assmgr_resource {

    /**
     * The evidence data
     *
     * @var array
     */
    var $data;

    /**
     * The name of the resource
     *
     * @var string
     */
    var $name;

    /**
     * The moodle form for editing the evidence
     *
     * @var moodleform
     */
    var $mform;

    var $evidence_id;


    var $xmldb_table;

    var $xmldb_field;

    var $xmldb_key;

    var $dbman;

    var $set_attributes;


    /**
     * Constructor
     */
    function __construct() {
        global $CFG,$DB;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();

        $this->name = get_class($this);

        // include the xmldb classes
        require_once($CFG->libdir.'/ddllib.php');

        $this->dbman = $DB->get_manager();

        // if 2.0 classes are available then use them
        $this->xmldb_table = class_exists('xmldb_table') ? 'xmldb_table' : 'XMLDBTable';
        $this->xmldb_field = class_exists('xmldb_field') ? 'xmldb_field' : 'XMLDBField';
        $this->xmldb_key   = class_exists('xmldb_key')   ? 'xmldb_key'   : 'XMLDBKey';
    }

    /**
     *
     */
    public function get_name() {
        return $this->name;
    }

    /**
     *
     */
    public function get_content() {
        return $this->get_link();
    }


    /**
     * Edit the evidence
     *
     * @param object $evidence The optional evidence record to edit
     */
    public final function edit($candidate_id, $course_id, $resource_type_id, $evidence_id, $folder_id, $access_isassessor=false) {
        global $CFG, $PARSER;

        // get the evidence record
        $evidence = $this->dbc->get_evidence($evidence_id);

        // include the moodle form library
        require_once($CFG->libdir.'/formslib.php');
        require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

        // get the name of the evidence class being edited
        $classname = get_class($this).'_mform';

        // include the moodle form for this table
        include_once("{$CFG->dirroot}/blocks/assmgr/classes/resources/plugins/{$classname}.php");

        if(!class_exists($classname)) {
            print_error('noeditevidenceform', 'block_assmgr', '', get_class($this));
        }


        if (!empty($evidence->id)) {
            $resource = $this->dbc->get_evidence_resource($evidence->id);
            $resource_plugin = $this->dbc->get_resource_plugin($resource->tablename, $resource->record_id);
            $non_attrib = array('id', 'timemodified', 'timecreated');

            if (!empty($resource_plugin)) {

                foreach ($resource_plugin as $attrib => $value) {

                    if (!in_array($attrib, $non_attrib)) {
                        $evidence->$attrib = $value;
                    }
                }
            }
        }

        // instantiate the form and load the data
        $this->mform = new $classname($candidate_id, $course_id, $resource_type_id, $evidence_id, $folder_id, $access_isassessor);

        $this->mform->set_data($evidence);

        $backurl = "edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;folder_id={$folder_id}#evidencefolders";

        // was the form canceled
        if ($this->mform->is_cancelled()) {
            // if canceled then go back to the edit portfolio page
            redirect($backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);
        }

        // has the form been submitted
        if ($this->mform->is_submitted()) {
            // check the validation rules
            if ($this->mform->is_validated()) {
                // process the data
                $returned_evidence_id = $this->mform->process_data($this->mform->get_data());

                if (!$returned_evidence_id) {
                    print_error('cantsaveevidence', 'block_assmgr');
                }

                if ($access_isassessor) {
                    $backurl = $CFG->wwwroot.'/blocks/assmgr/actions/save_submission.php?course_id='.$course_id
                            .'&evidence_id='.$returned_evidence_id.'&candidate_id='.$candidate_id;
                }

                // perform the redirect
                redirect($backurl, get_string('changessaved'), REDIRECT_DELAY);
            }
        }
    }

    /**
     * Delete the evidence
     */
    public final function delete($evidence_id) {
        $evidence = $this->dbc->get_evidence_resource($evidence_id);

        // remove the resource record and any files
        if ($this->delete_resource($evidence->tablename, $evidence->record_id)) {

            // now remove the db record
            if ($this->dbc->delete_evidence($evidence_id, true)) return true;
        }
        return false;
    }

    /**
     * Delete the resource
     */
    public function delete_resource($tablename, $id) {
        return $this->dbc->delete_resource_plugin($tablename, $id);
    }

    /**
     * Installs any new plugins
     */
    public static function install_new_plugins() {
        global $CFG;

        // instantiate the assmgr db
        $dbc = new assmgr_db();

        // get all the currently installed evidence resource types
        $resource_types = assmgr_records_to_menu($dbc->get_resource_types(), 'id', 'name');

        $plugins = $CFG->dirroot.'/blocks/assmgr/classes/resources/plugins';

        // get the folder contents of the resource plugin directory
        $files = scandir($plugins);

        foreach($files as $file) {
            // look for plugins
            if(preg_match('/^([a-z_]+)\.php$/i', $file, $matches)) {

                if(!in_array($matches[1], $resource_types) && substr($matches[1], -5)  != 'mform') {
                    // include the class
                    require_once($plugins.'/'.$file);

                    // instantiate the object
                    $class = basename($file, ".php");
                    $resourceobj = new $class();

                    // install the plugin
                    $resourceobj->install();

                    // update the resource_types table
                    $dbc->create_resource_type($resourceobj->get_name());
                }
            }
        }

    }


    function get_resource_enabled_instances($resource_name,$course=null) {

        $enabled_courses = array();

        if (!empty($course)) {
             $course_instances = (is_array($course)) ? $course : array($course);
        } else {
            $course_instances = array();
            //get all courses that the block is attached to
            $block_course =  $this->dbc->get_block_course_ids($course);

            if (!empty($block_course)) {
                foreach ($block_course as $block_c) {
                    array_push($course_instances,$block_c->pageid);
                }
            }
        }

        if (!empty($course_instances)) {
            foreach ($course_instances as $course_id) {
                $instance_config  = (array) $this->dbc->get_instance_config($course_id);
                if (isset($instance_config[$resource_name])) {
                    if (!empty($instance_config[$resource_name])) {
                         array_push($enabled_courses,$course_id);
                    }
                }
            }
        }

        return $enabled_courses;
    }


    /**
     * TODO comment this
     */
    static function update_resources($course_id = null, $candidate_id = null, $verbose = true) {
        global $CFG;

        $plugins = $CFG->dirroot.'/blocks/assmgr/classes/resources/plugins';

        //instantiate the assmgr db
        $dbc = new assmgr_db();

        // get all the currently installed evidence resource types
        $resource_types = assmgr_records_to_menu(assmgr_remove_disbaled_resources($dbc->get_resource_types(),$course_id), 'id', 'name');

        //this section runs the update methods for all plugins
        foreach ($resource_types as $resource_file) {
            // get the resource class definition
            require_once($plugins.'/'.$resource_file.".php");

            // instantiate the object
            $class = basename($resource_file, ".php");
            $resourceobj = new $class();

            // run the update method if it has been defined
            if (is_callable(array($resourceobj, 'update'), true)) {
                $resourceobj->update($course_id, $candidate_id, $verbose);
            }
        }
    }

    /**
     * function used to return configuration settings for a plugin
     */
    function config_settings(&$settings) {
        return $settings;
    }

    /**
     * function used to return the size of the resource currently loaded
     */
    function size() {
        return 0;
    }

    /**
     * function used to return the language strings for the resource
     */
    function language_strings(&$string) {
        return $string;
    }

    /**
     * function used to update records in the resource
     */
    function update() {

    }

    /**
     * function used to determine whether a assessor can make this type of evidence
     */
    public function assessor_create() {
        return true;
    }

    /**
     * function used to specify whether the current resource requires file storage
     */
    public function file_storage() {
        return false;
    }
}
?>