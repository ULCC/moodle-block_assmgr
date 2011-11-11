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
class edit_assess_date_mform extends assmgr_moodleform {


    private $candidate_id;
    private $course_id;
    private $group_id;
    private $event_id;
    private $repeat_id;


    /**
     * Constructor
     *
     * @param string $candidate_id The id of the candidate
     * @param string $course_id The id of the course
     * @param string $evidence_id The id of the evidence
     * @return void
     */
    function __construct($candidate_id, $course_id, $event_id = null, $repeat_id = null,$group_id = null) {
        global $CFG;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();

        // assign the params
        $this->candidate_id = $candidate_id;
        $this->course_id = $course_id;
        $this->group_id = $group_id;
        $this->event_id = $event_id;
        $this->repeat_id = $repeat_id;

        // get the current url, and encode it for safe transport as a url param
        $this->here = urlencode(base64_encode("{$CFG->wwwroot}/blocks/assmgr/actions/edit_assess_date.php?candidate_id={$candidate_id}&amp;course_id={$course_id}&amp;group_id={$group_id}&amp;event_id={$event_id}"));
/*
        // fetch the evidence, or fail if the id is wrong
        if (!empty($evidence_id) && ($this->evidence = $this->dbc->get_evidence_resource($evidence_id)) == false) {
            print_error('incorrectevidenceid', 'block_assmgr', $evidence_id);
        }
*/
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

        // for the unsaved data check
        $mform->addElement('hidden', 'datachanged', 'false');
        $mform->setType('datachanged', PARAM_ALPHA);

        $mform->addElement('hidden', 'candidate_id', $this->candidate_id);
        $mform->setType('candidate_id', PARAM_INT);
        $mform->addElement('hidden', 'course_id', $this->course_id);
        $mform->setType('course_id', PARAM_INT);
        $mform->addElement('hidden', 'group_id', $this->group_id);
        $mform->setType('group_id', PARAM_INT);
        $mform->addElement('hidden', 'event_id', $this->event_id);
        $mform->setType('event_id', PARAM_INT);
        $mform->addElement('hidden', 'repeat_id', $this->repeat_id);
        $mform->setType('repeat_id', PARAM_INT);


        // put all the evidece form elements into a fieldset
        $mform->addElement('header', 'fields', get_string('assessmentdate', 'block_assmgr'));

        // ---------------------------------------------------------------------
        // --                           DATE                                  --
        // ---------------------------------------------------------------------

        $mform->addElement('date_selector',
                            'timestart',
                            get_string('eventassessmentdate','block_assmgr'),
                            array('startyear' => 2010,
                                  'stopyear'  => date('Y')+1,
                                  'timezone'  => 99,
                                  'applydst'  => true,
                                  'optional'  => false
                            )
                          );
        $mform->addRule('timestart', null, 'required', null, 'client');
        $mform->setType('timestart', PARAM_RAW);

        // ---------------------------------------------------------------------
        // --                           COMMENT                               --
        // ---------------------------------------------------------------------

        $mform->addElement('htmleditor',
                           'description',
                           get_string('eventassessmentdescription', 'block_assmgr'),
                           array('class' => 'form_input', 'rows'=> '10', 'cols'=>'65')
                          );
        $mform->addRule('description', null, 'maxlength', 65535, 'client');
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->setType('summary', PARAM_RAW);
        // ---------------------------------------------------------------------
        // --                           TYPE                                  --
        // ---------------------------------------------------------------------

        $options = array();
        $options['Course'] = 'Course';
        $options['User'] = 'User';
        if (!empty($this->group_id)) $options['Group'] = 'Group';
        $mform->addElement('select','assesstype',get_string('eventassessmenttype', 'block_assmgr'),$options);
        $mform->addRule('assesstype', null, 'required', null, 'client');

        // add the submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Performs server-side validation of the unique constraints.
     *
     * @param object $data The data to be saved
     * @return array any errors generated
     */
    function validation($data) {
        $errors = array();
        $errors = parent::validation($data,null);
        return $errors;
    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return bool True regardless
     */
    function process_data($data) {

        global $USER;

        $event_group_id = 0;
        $event_course_id = 0;
        $instance = 0;

        $assessment_type = $data->assesstype;
        $assessment_comment = $data->description;
        $assessment_date = $data->timestart;
        $event_id = $data->event_id;
        $repeat_id = $data->repeat_id;
        $course_details = get_context_instance(CONTEXT_COURSE, $data->course_id);
        $course = $this->dbc->get_course($data->course_id);

        switch ($assessment_type) {
            case 'Course':
                $event_course_id = $data->course_id;
                //retrieve all candidates in course and place in candidates array
                $candidates_array = get_users_by_capability($course_details,'block/assmgr:creddelevidenceforself','','','','','','',false,false,false);
                $event_name = get_string('assessorfutureassesscoursename', 'block_assmgr', $course->shortname);
                break;
            case 'Group':
                $event_group_id = $data->group_id;
                //retrieve group details
                $group = $this->dbc->get_group($data->group_id);
                $event_name = get_string('assessorfutureassessgroupname', 'block_assmgr', $group->name);
                //retrieve all candidates in group and place in candidates array
                $candidates_array = $this->dbc->get_group_users($data->group_id);
                break;
            default :
                //it must be a message to the candidate
                $candidate_obj = new object();
                $candidate_obj->id = $data->candidate_id;
                $candidates_array = array($candidate_obj);
                $candidate = $this->dbc->get_user($data->candidate_id);
                $portfolio = $this->dbc->get_portfolio($data->candidate_id,$data->course_id);
                $instance = (!empty($portfolio)) ? $portfolio->id : NULL;
                $candidatename = fullname($candidate);
                $coursename = $course->shortname;
                $event_name = get_string('assessorfutureassessname', 'block_assmgr',(object)compact('candidatename','coursename'));
                break;
        }



        //set the asssessor event and save it
        if (!empty($event_id)) {
            $assessor_event = $this->dbc->get_future_assessment_event_by_id($event_id);
            $log_old_assess_event->date     = userdate($assessor_event->timestart, get_string('strftimedate', 'langconfig'));
            $log_old_assess_event->comment       = $assessment_comment;
        } else {
            $assessor_event = new object();
            $assessor_event->name = $event_name;
            $assessor_event->courseid = 0;
            $assessor_event->groupid = 0;
            //assessor_event->modulename = GRADE_ASSMGR_ITEMMODULE;
            $assessor_event->instance = $instance;
            $assessor_event->eventtype = ASSESSOR_EVENT;
            $assessor_event->format = FORMAT_MOODLE;
            $assessor_event->userid = $USER->id;
            $assessor_event->timeduration = 0;
        }


        $assess_date = $assessment_date;
        $assessor_event->timestart = $assess_date;
        $formated_assess_date = userdate($assessor_event->timestart, get_string('strftimedate', 'langconfig'));
        $assessor_event->description = $assessment_comment;

        if (empty($event_id)) {
            $assessor_event_id = add_event($assessor_event);
            $assessor_event->repeatid = $assessor_event_id;
            update_event($assessor_event);
            $return_message = get_string('futureassessmentdateset', 'block_assmgr');

            $assessor_assmgr_event = new object();
            $assessor_assmgr_event->event_id = $assessor_event_id;
            $assessor_assmgr_event->course_id = $course->id;
            $assessor_assmgr_event->creator_id = $USER->id;
            $this->dbc->create_assmgr_event($assessor_assmgr_event);

        } else {

           $this->dbc->update_event_date($assess_date,$repeat_id);
           $this->dbc->update_event_comment($assessment_comment,$repeat_id);
           $candidate_event = new object();
           $candidate_event->id = $event_id;
           $candidate_event->courseid = $event_course_id;
           $candidate_event->groupid = $event_group_id;
           $candidate_event->userid = (empty($event_group_id) && empty($event_course_id)) ? $data->candidate_id : 0;
           $this->dbc->update_candidate_event($candidate_event);

            $return_message = get_string('futureassessmentdateupdate', 'block_assmgr');
        }

        if (empty($event_id)) {
            //Add Calendar event for assessor
            $candidate_event = new object();
            $candidate_event->name = get_string('candidatefutureassessname', 'block_assmgr', $course->shortname);
            $candidate_event->courseid = $event_course_id;
            $candidate_event->groupid = $event_group_id;
            $candidate_event->repeatid = $assessor_event_id;
            $candidate_event->instance = $instance;
            $candidate_event->eventtype = CANDIDATE_EVENT;
            $candidate_event->format = FORMAT_MOODLE;
            $candidate_event->description = $assessment_comment;
            $candidate_event->timestart = $assess_date;
            //$candidate_event->modulename = GRADE_ASSMGR_ITEMMODULE;
            //the candidate id is only passed to userid in the event that the type is user
            $candidate_event->userid = (empty($event_group_id) && empty($event_course_id)) ? $data->candidate_id : 0;
            $event_id = add_event($candidate_event);

            $candidate_assmgr_event = new object();
            $candidate_assmgr_event->event_id = $event_id;
            $candidate_assmgr_event->course_id = $course->id;
            $candidate_assmgr_event->creator_id = $USER->id;

            $this->dbc->create_assmgr_event($candidate_assmgr_event);

        }



        //send message to candidates telling them an assessment date has been set
        foreach ($candidates_array as $cand) {

            $message_candidate_id = (!empty($event_group_id)) ? $cand->userid : $cand->id;
            $portfolio = $this->dbc->get_portfolio($message_candidate_id,$data->course_id);
                //$this->add_to_audit('assessment_date',LOG_ADD,$log_assess_event);
                $coursename = $course->fullname;
                $langstr = (empty($event_id)) ? 'userfutureassessmsg' : 'userfutureassessmsgupdated';
                $message = get_string($langstr, 'block_assmgr', (object)compact('coursename', 'formated_assess_date', 'assessment_comment'));

                //Sets message details for Targets
                $user_message_from = $this->dbc->get_user($USER->id);
                $candidate_message_to = $this->dbc->get_user($message_candidate_id);

                message_post_message($user_message_from, $candidate_message_to, $message, FORMAT_HTML, '');

                //MOODLE LOG portfolio assessment date updated
                $log_action = get_string('logportassdatecreate', 'block_assmgr');
                $logstrings = new stdClass;
                $logstrings->name = fullname($candidate_message_to);
                $logstrings->course = $course->shortname;
                $log_info = get_string('logportassdatecreateinfo', 'block_assmgr', $logstrings);
                assmgr_add_to_log($data->course_id, $log_action, null, $log_info);



                $log_assess_event->candidate_id  = $message_candidate_id;
                $log_assess_event->course_id     = $data->course_id;
                $log_assess_event->date          = userdate($assessor_event->timestart, get_string('strftimedate', 'langconfig'));
                $log_assess_event->comment       = $assessment_comment;
                if ($assessment_type == 'User') $log_assess_event->portfolio_id    = (!empty($portfolio->id)) ? $portfolio->id : NULL;
                $log_assess_event->group_id    = $data->group_id;

                if (empty($event_id)) {
                    $this->dbc->add_to_audit('assessment_date',LOG_ADD,$log_assess_event);
                } else {
                    $log_old_assess_event->candidate_id  = $message_candidate_id;
                    $log_old_assess_event->course_id     = $data->course_id;
                    $log_old_assess_event->portfolio_id    = (!empty($portfolio->id)) ? $portfolio->id : NULL ;
                    $this->dbc->add_to_audit('assessment_date',LOG_UPDATE,$log_assess_event,$log_old_assess_event);
                }
        }
        return true;
    }

}
?>