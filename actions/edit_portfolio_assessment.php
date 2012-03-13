<?php
/**
 * This page allows editing of the portfolio grade and comments.
 * It is called as part of edit_portfolio.php but also on it's own as the form submission action.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

//if (!defined('MOODLE_INTERNAL')) {
//    // this must be included from a Moodle page
//    die('Direct access to this script is forbidden.');
//}

//include moodle config
//require_once(dirname(__FILE__).'/../../../config.php');

require_once('../../../config.php');

global $CFG, $PAGE, $PARSER;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// inlcude the gradelib
require_once($CFG->libdir.'/gradelib.php');

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_portfolio_assessment_mform.php');

$dbc = new assmgr_db();


$PAGE->requires->js('/lib/yui/2.9.0/build/yahoo/yahoo-min.js');
$PAGE->requires->js('/lib/yui/2.9.0/build/event/event-min.js');
$PAGE->requires->js('/lib/yui/2.9.0/build/dom/dom-min.js');

$candidate_id = $PARSER->required_param('candidate_id', PARAM_INT);
$course_id = $PARSER->required_param('course_id', PARAM_INT);

$coursecontext = get_context_instance(CONTEXT_COURSE, $course_id);

$PAGE->set_context($coursecontext);

// get the course scale
$course_scale 	= 	$dbc->get_course_scale($course_id);

$course_grade	=	$dbc->get_course_grade($course_id, $candidate_id);


$course_comments = $dbc->get_course_comments($course_id, $candidate_id);

if(empty($course)) {
    $course = $dbc->get_course($course_id);
}

if (empty($candidate)) {
    $candidate = $dbc->get_user($candidate_id);
}




$portassessform = new edit_portfolio_assessment_mform($course_scale, $course, $candidate, $course_comments, $access_canviewuserdetails);

// this form has no cancal button
if($portassessform->is_submitted()) {
    // check the validation rules
    if($portassessform->is_validated()) {

        $data = $portassessform->get_data();

        // Possibly the grade is empty but there is a comment. Avoid 'unset variable error'
        if (empty($data->course_grade)) {
            $data->course_grade = null;
        }
        // Make sure there is something to submit. Sometimes both will be empty but this is still a change.
        if (!empty($data->course_grade) || !empty($data->course_comment) || ($data->course_grade != $course_grade)) {

            // process the data
            $success = $portassessform->process_data($data, $access_isassessor);
            // no need for error handling - the form data processing function throws an error if needed
            // and the redirect/OK message is handled from there too.

            if (!$success) {
                print_error('portfoliogradecouldnotbesaved', 'block_assmgr');
            }

            if ($data->ajaxsave == 'true') {
                echo $data->formid.' ok';
                die();
            } else {
                $return_message  = (empty($course_grade)) ? get_string('portassesssaved','block_assmgr') : get_string('portassessupdated','block_assmgr');
                redirect($CFG->wwwroot.'/blocks/assmgr/actions/list_portfolio_assessments.php?course_id='.$data->course_id, $return_message, REDIRECT_DELAY);
            }
        }

    } else {
        die('form not validated');
    }

} else {

    if (!empty($course_grade))  {

        $datamerge = array();
        $datamerge['course_grade'] = (Int)$course_grade->grade;

        // the checkbox for 'finished' need to be checked only if a grade has been recorded.
        // The qualifications don't really have an end date, so saying 'failed' is the same as
        // 'not finished'
        if (!is_null($course_grade->grade)) {
            $datamerge['studentfinished'] = 'checked';
        }

        $portassessform->set_data($datamerge);
    }
}

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_portfolio_assessment.html');

// NOTE: the html include is not here any longer because of the need to have this
// php file included early on in edit_portfolio.php so that the submitted form can be caught
// before the HTML page is constructed.