
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



global $CFG;
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/hierselect.php');

class edit_verification_mform extends assmgr_moodleform {

    /**
     * Constructor
     *
     * @param string $course_id The id of the course
     * @param string $verification_id The id of the verification plan
     * @return void
     */
    function __construct($course_id, $verification_id = null) {
        global $CFG;

        $this->course_id = $course_id;
        $this->verification_id = $verification_id;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();

        if(!empty($this->verification_id)) {
            $this->verification = $this->dbc->get_verification($verification_id);
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

        $mform->addElement('hidden', 'course_id', $this->course_id);
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('hidden', 'verification_id', $this->verification_id);
        $mform->setType('verification_id', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'verifier_id', $USER->id);
        $mform->setType('verifier_id', PARAM_INT);

        // put all the form elements into a fieldset
        $mform->addElement('header', 'fields', get_string('verificationsample', 'block_assmgr'));

        // get the current user's access rights
        if(isset($USER->access)) {
            $accessinfo = $USER->access;
        } else {
            $accessinfo = $USER->access = get_user_access_sitewide($USER->id);
        }

        // find all courses that this user has the verify capability on
        $allowedcourses = get_user_courses_bycap(
            $USER->id,
            "block/assmgr:verifyportfolio",
            $accessinfo,
            true,
            'c.sortorder ASC',
            array('category', 'fullname')
        );

        // prepare the options arrays
        $categories = array('' => get_string('allqualifications', 'block_assmgr'));
        $courses = array('' => array('' => get_string('allcourses', 'block_assmgr')));
        $assessors = array('' => '');

        $unvalidated_courses = array();

        foreach ($allowedcourses as $allowedcourse) {
            array_push($unvalidated_courses,$allowedcourse->id);
        }

        $enabled_courses = $this->dbc->get_block_course_ids($unvalidated_courses);

        foreach ($allowedcourses as $index => $allowedcourse) {
            $delete = true;
            foreach ($enabled_courses as $e_course) {
                if ($e_course->pageid == $allowedcourse->id) $delete = false;
            }
            if (!empty($delete))   unset($allowedcourses[$index]);
        }

        foreach ($allowedcourses as $allowedcourse) {
            // get the unique list of categories
            if (empty($categories[$allowedcourse->category])) {
                $categories[$allowedcourse->category] = $this->dbc->get_category_by_course($allowedcourse->id)->name;
                // make sure the first sub-option is blank
                $courses[$allowedcourse->category][''] = get_string('allcourses', 'block_assmgr');
            }

            // nest the courses inside the categories
            $courses[$allowedcourse->category][$allowedcourse->id] = $allowedcourse->shortname;
            // Make a huge list of all courses too
            $courses[''][$allowedcourse->id] = $allowedcourse->shortname;

            // get the current course context
            $coursecontext = get_context_instance(CONTEXT_COURSE, $allowedcourse->id);

            // get all the assessors for this course context
            $users = get_users_by_capability(
                $coursecontext,
                'block/assmgr:assessportfolio',
                'u.id, u.firstname, u.lastname', '', '', '', '', '',
                false
            );

            // In case there are no assessors
            if (empty($assessors[$allowedcourse->category][$allowedcourse->id])) {
                // make sure the first sub-option is blank
                $assessors[$allowedcourse->category][$allowedcourse->id][''] = get_string('allassessors', 'block_assmgr');
            }

            // also make options for when no category and/or course is selected
            if (empty($assessors[$allowedcourse->category][''])) {
                // make sure the first sub-option is blank
                $assessors[$allowedcourse->category][''][''] = get_string('allassessors', 'block_assmgr');
            }

            if (empty($assessors[''][$allowedcourse->id])) {
                // make sure the first sub-option is blank
                $assessors[''][$allowedcourse->id][''] = get_string('allassessors', 'block_assmgr');
            }

            // nest the assessors inside the courses
            foreach ($users as $user) {
                $assessors[$allowedcourse->category][$allowedcourse->id][$user->id] = fullname($user);
                $assessors[$allowedcourse->category][''][$user->id] = fullname($user);
                $assessors[''][''][$user->id] = fullname($user);
            }
        }

        // in case there are no courses
        if(empty($assessors[''][''])) {
            // make sure the first sub-option is blank
            $assessors[''][''][''] = get_string('allassessors', 'block_assmgr');
        }

        // add hierselect element
        $hier = $mform->addElement(
            'hierselect',
            'sample',
            get_string('qualification', 'block_assmgr'),
            array('size' => '1'),
            '&nbsp;',
            array()
        );

        // add the nested options
        $hier->setOptions(array($categories, $courses, $assessors));

        $hier->setLabels(array(
            get_string('qualification', 'block_assmgr'),
            get_string('course', 'block_assmgr'),
            get_string('assessor', 'block_assmgr')
        ));

        // set the default values for the elements, if they're there
        if (isset($this->verification)) {

            $verificationcategory = (!empty($this->verification->category_id)) ? $this->verification->category_id : '';
            $verificationcourse   = (!empty($this->verification->course_id))   ? $this->verification->course_id   : '';
            $verificationassessor = (!empty($this->verification->assessor_id)) ? $this->verification->assessor_id : '';

            $existingdata = array(
                    $verificationcategory,
                    $verificationcourse,
                    $verificationassessor);

            $hier->setValue($existingdata);
        }

        // add the submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return bool True regardless
     */
    function process_data($data) {

        if(empty($data->id)) {
            return $this->dbc->create_verification($data);
        } else {
            return $this->dbc->set_verification($data);
        }
    }

}
?>