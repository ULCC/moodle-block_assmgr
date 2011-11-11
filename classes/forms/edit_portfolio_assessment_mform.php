<?php
/**
 * Form class for adding a grade to a portfolio. Called from edit_portfolio.php
 *
 * @copyright &copy; 2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class edit_portfolio_assessment_mform extends moodleform {

    function __construct($course_scale, $course, $candidate, $course_comments, $access_canviewuserdetails) {

        global $CFG;

        $this->dbc = new assmgr_db();
        $this->course_scale = $course_scale;
        $this->course = $course;
        $this->candidate = $candidate;
        $this->course_comments = $course_comments;
        $this->access_canviewuserdetails = $access_canviewuserdetails;

        // call the parent constructor
        parent::__construct($CFG->wwwroot.'/blocks/assmgr/actions/edit_portfolio_assessment.php');

    }

    function definition() {

        global $CFG,$OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('header', 'portfoliogrades', get_string('grade', 'block_assmgr'));

        $scale_items = $this->course_scale->load_items();
        array_unshift($scale_items, get_string('nograde', 'moodle'));
        $mform->addElement('select', 'course_grade', get_string('grade', 'block_assmgr'), $scale_items);
        $mform->setType('course_grade', PARAM_INT);

        // for the unsaved data check
        $mform->addElement('hidden', 'datachanged', 'false');
        $mform->setType('datachanged', PARAM_ALPHA);

        // for showing that this form is the last one on the page and needs a reirect
        $mform->addElement('hidden', 'ajaxredirect', 'true');
        $mform->setType('ajaxredirect', PARAM_ALPHA);

        // for the ajax call
        $mform->addElement('hidden', 'ajaxsave', 'false');
        $mform->setType('ajaxsave', PARAM_ALPHA);

        $mform->addElement('hidden', 'formid', '');
        $mform->setType('formid', PARAM_ALPHANUM);

        $dbc = new assmgr_db();

        if (!empty($this->course_comments)) {
            $mform->addElement('static', 'commentslabel', get_string('previouscomments', 'block_assmgr').':');

            foreach ($this->course_comments as $comment) {

                if ($this->access_canviewuserdetails) {
                				
                    $username = $OUTPUT->user_picture($dbc->get_user($comment->userid), array('course'=>$this->course->id,'return'=>true))."<a href='{$CFG->wwwroot}/user/view.php?id={$comment->userid}&amp;course={$this->course->id}'  class=\"userlink\">".fullname($comment)."</a>";
                } else {
                    $username = $OUTPUT->user_picture($dbc->get_user($comment->userid), array('course'=>$this->course->id,'return'=>true)).fullname($comment)."</a>";
                }

                $mform->addElement('html', '<div class="fitem"><div class="fitemtitle">'.$username.'</div><div class="felement">'.userdate($comment->timemodified, get_string('strftimedate', 'langconfig')).'<br/>'.assmgr_db::decode_htmlchars($comment->feedback).'</div></div>');
            }
        }
        $mform->addElement('htmleditor', 'course_comment', get_string('newcomment', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
        $mform->setType('course_comment', PARAM_RAW);

        if (count($this->course_comments) < 1) {
            $mform->addRule('course_comment', get_string('required'), 'required', null, 'client');
        }

        $mform->addElement('hidden', 'course_id', $this->course->id);
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('hidden', 'candidate_id', $this->candidate->id);
        $mform->setType('candidate_id', PARAM_INT);


        $this->add_action_buttons(false, get_string('submit'));

    }

    /**
     * Processes the form data
     */
    function process_data($data, $access_isassessor) {

        global $USER, $CFG, $PARSER;

        if(!$access_isassessor) {
            print_error('nopageaccess', 'block_assmgr');
        }

        $dbc = new assmgr_db();

        $return_message = '';

        // save the portfolio (i.e. course) grade and comment
        $course = $dbc->get_course($data->course_id);
        $candidate = $dbc->get_user($data->candidate_id);

        
        
        
        return $dbc->set_course_grade($data->course_id, $data->candidate_id, $data->course_grade, $data->course_comment);
    }
}
?>