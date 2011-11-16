<?php
/**
 * Saves the assessments that an asessor made on a candidates outcomes. Called from the ajax
 * requests and the form action of view_submissions.php
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */



require_once('../../../config.php');

global $USER, $CFG, $PARSER, $PAGE;


// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

require_once($CFG->dirroot.'/lib/gradelib.php');


/*
if (!$access_isassessor) {
    print_error('nopageaccess', 'block_assmgr');
}
*/
// get the id of the course and candidate
$course_id    = $PARSER->required_param('course_id',    PARAM_INT);
$candidate_id = $PARSER->required_param('candidate_id', PARAM_INT);

$context = (!empty($course_id)) ? get_context_instance(CONTEXT_COURSE, $course_id) : get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_context($context);

$PAGE->set_context($coursecontext);


// get the outcomes
$outcomes  = $PARSER->required_param('outcomes', PARAM_ARRAY);
$ajax      = $PARSER->optional_param('ajax', false, PARAM_BOOL);
// for the AJAX save operation only
$ajaxsave  = $PARSER->optional_param('ajaxsave', false, PARAM_BOOL);
$formid    = $PARSER->optional_param('formid', 'error', PARAM_ALPHA);

$dbc = new assmgr_db();
$return_message = '';
// process the portfolio outcomes
if(!empty($outcomes)) {

    $grades = array();

    // step through each outcome the portfolio achieved
    foreach ($outcomes as $outcome_id => $grade) {
    	
    	
    	//as the grades name is the only real link back to outcome provided by (the data returned from) grade_get_grades function we need
		//to query the grade_items table to retrieve the itemnumber of the outcome for this activity (if it exists) 
		//we can then use this information to get the grade from the data grade_get_grades returns 
        $outcome_grade_item		=	$dbc->get_overall_course_outcome($course_id,$outcome_id);
    	
        if (!empty($outcome_grade_item))	{
        	//we need to create the grade item
        	
        	$outcome	=	$dbc->get_outcome($outcome_id);
        	
        	$scale				=	$dbc->get_scale($outcome->scaleid);
        		
        	//create the grade item
        	$grade					=	new grade_item();
        	$grade->courseid		=	$course_id;
        	$grade->itemname		=	$outcome->shortname;
        	$grade->itemtype		=	'outcome';
        	$grade->itemmodule		=	'';
        	$grade->iteminstance	=	$outcome->id;
        	$grade->itemnumber		=	'';
        	$grade->scaleid			=	$outcome->scaleid;
        	$grade->gradetype 		= 	GRADE_TYPE_SCALE;
        	$grade->outcomeid		=	$outcome->id;
        	$grade->iteminfo		= 	$outcome->description;

        	//insert the grade item
        	$grade->insert();

        }
		
		$outcomegrade				=	new stdClass();
        $outcomegrade->userid		=	$candidate_id;
        $outcomegrade->rawgrade		=	$grade;
					//($source, $courseid, $itemtype, $itemmodule, $iteminstance, $itemnumber, $grades=NULL, $itemdetails=NULL)
        grade_update('outcome',$course_id,"outcome",NULL,$outcome_id,NULL,$outcomegrade);
    	
        // check if the outcome was awarded an actual grade
        
    }

    $course = $dbc->get_course($course_id);
    $candidate = $dbc->get_user($candidate_id);

}

if ($ajax) {

    // TODO - same on success and failure. needs error

        foreach ($outcomes as $outcome_id => $outcomescaleitem) {
            $outcome = $dbc->get_outcomes($course_id, $outcome_id);
            $scale = $dbc->get_scale($outcome->scaleid, $outcome->gradepass);
         
            $grades		=	grade_get_grades($course_id, 'outcome', NULL, $outcome_id,$candidate_id);
            
           	if (!empty($grades)) {
            	$grades		=	array_pop($grades->items);
            	$scale_item = !empty($grades->grades[$candidate_id]->grade) ? $grades->grades[$candidate_id]->grade : null;
           	} else {
           		$scale_item	=	null;
           	}
            echo $scale->render_scale_item($scale_item);
        }


} else {
    redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&candidate_id={$candidate_id}#submittedevidence", $return_message, REDIRECT_DELAY);
}
?>