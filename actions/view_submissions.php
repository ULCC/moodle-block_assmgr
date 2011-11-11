<?php
/**
 * This page creates the the submission matrix for a portfolio, showing all
 * submitted evidence along with their grades.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

if (!defined('MOODLE_INTERNAL')) {
    // this must be included from a Moodle page
    die('Direct access to this script is forbidden.');
}


global $OUTPUT, $CFG, $PAGE;




require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_course_activities.class.php');

$renderer			=	$PAGE->get_renderer('block_assmgr');

//create a class holding the activites that are in the current course
$courseactiviites	=	new assmgr_course_activities($course_id,$candidate_id);

$courseactiviites->is_assessor	=	true;

$activities 		=	$courseactiviites->get_course_activities();
$outcomes 			=	$courseactiviites->get_course_outcomes();





// render the table
require_once($CFG->dirroot.'/blocks/assmgr/views/view_submissions.html');
?>