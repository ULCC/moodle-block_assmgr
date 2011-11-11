<?php
/**
 * This page displays the units the current candidate is enrolled in along with
 * their progress.
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

global $CFG;

//include the progress_bar class
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_progress_bar.class.php');

// instantiate the progress_bar
$progress = new assmgr_progress_bar();

// fetch the courses that the candidate is enrolled in in this category
$courses = $dbc->get_enrolled_courses($candidate_id, $course->category);

$course_outcomes = $dbc->get_outcomes($course_id);



// render the progress
require_once($CFG->dirroot.'/blocks/assmgr/views/view_units_progress.html');
?>