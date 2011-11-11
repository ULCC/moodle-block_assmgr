<?php
/**
 * Form for editing AssMgr block instances.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class block_assmgr_edit_form extends block_edit_form {

    /**
     * Adds definitions to the form object which are specific to this sub class
     *
     * @param object $mform
     * @return void
     */
    protected function specific_definition($mform) {
        global $CFG;

        // get the course id
        $course_id = required_param('id', PARAM_INT);

        // get the global config, which we'll use to set the defaults
        $globalconfig = get_config('block_assmgr');

        // include assessment manager db class
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the db class
        $dbc = new assmgr_db();

        // add the fieldset for the evidence types
        $mform->addElement('header', 'resourcetypes', get_string('resourcetypes', 'block_assmgr'));

        $group = 1;
        // add all the resource types
        foreach($globalconfig as $setting => $value) {
            if(substr($setting, 0, 16) == 'assmgr_resource_') {
                if($value) {
                    $type = 'advcheckbox';
                    // place all the advcheckbox elements in a group together
                    $options = array('group' => $group);
                    $msg = null;
                } else {
                    // resource types cannot be enabled here if they are disabled in the block config
                    $type = 'checkbox';
                    $options = array('disabled' => 'disabled');
                    $msg = get_string('disabledinblockconfig', 'block_assmgr');
                }
                $mform->addElement($type, $setting, get_string($setting, 'block_assmgr'), $msg, $options);
                $mform->setDefault($setting, $value);
            }
        }

        // add a javascript controller to act on the group of checkboxes
        $this->add_checkbox_controller($group, get_string('checkallornone'), null, 0);

        // add the fieldset for the quota
        $mform->addElement('header', 'quotaheader', get_string('portfolio_quota', 'block_assmgr'));

        $options = array();

        for ($i = 1;$i < 50; $i++) {
            $size = 5 * $i;
            $options[$size] = "{$size} Mb";
        }

        $mform->addElement('select','portfolio_quota',get_string('portfolio_quota', 'block_assmgr'),$options);
    }
}