<?php
/**
 * This page allows a a user to edit the grade and outcome grades given to a particular activity
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */


require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $OUTPUT;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');

require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_activitygrade_mform.php');


// get the required params
$course_id 			= 	$PARSER->required_param('course_id', PARAM_INT);
$coursemodule_id 	= 	$PARSER->required_param('cm_id', PARAM_INT);
$candidate_id 		= 	$PARSER->required_param('candidate_id', PARAM_INT);
$instance_id		=	$PARSER->required_param('instance_id', PARAM_INT);

// instantiate the db
$dbc = new assmgr_db();
/*
// you must be either a candidate or an assessor to edit a portfolio
if($access_isassessor) {
    print_error('noeditportfoliopermission','block_assmgr');
}


if($access_isassessor) {
    // assessors can't assess their own portfolio
    if($USER->id == $candidate_id) {
        print_error('cantassessownportfolio', 'block_assmgr');
    }

    // make sure the candidate is actually a candidate in this context
    $iscandidate = has_capability('block/assmgr:creddelevidenceforself', $coursecontext, $candidate_id, false);

    if(!$iscandidate) {
        print_error('portfolionotincourse', 'block_assmgr');
    }
}
*/
// get the candidate, course and category
$candidate 		= $dbc->get_user($candidate_id);
$course 		= $dbc->get_course($course_id);
$coursecat 		= $dbc->get_category($course->category);

// get the optional param
$candidate_id = $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT);

//create instance of activity_mform
$mform		=	new edit_activitygrade_mform($course_id,$candidate_id,$coursemodule_id,$instance_id);

$return_url = $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&candidate_id={$candidate_id}";

//was the form cancelled?
if ($mform->is_cancelled()) {
	//send the user back
    redirect($return_url, '', REDIRECT_DELAY);
}


//was the form submitted?
// has the form been submitted?
if($mform->is_submitted()) {
	
	 //get the form data submitted
    $formdata = $mform->get_data();
    
    //save the data to the database
	$mform->process_data($formdata);
	redirect($return_url, '', REDIRECT_DELAY);
	
}

$page_heading = get_string('candidateportfolio', 'block_assmgr', fullname($candidate));

// setup the navigation breadcrumbs
$PAGE->navbar->add(get_string('blockname', 'block_assmgr'),null,'title');

// assessor breadcrumbs
$PAGE->navbar->add($coursecat->name,$CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}",'title');
$PAGE->navbar->add($course->shortname,$CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course->id}",'title');
$PAGE->navbar->add(fullname($candidate),null,'title');
$PAGE->navbar->add(get_string('gradeactivity','block_assmgr'),null,'title');

// setup the page title and heading
$PAGE->set_title($course->shortname.': '.get_string('blockname','block_assmgr'));
$PAGE->set_heading($course->fullname);
$PAGE->set_url('/blocks/assmgr/actions/edit_activitygrades.php', $PARSER->get_params());




echo $OUTPUT->header();

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_activitygrades.html');

echo $OUTPUT->footer();


?>