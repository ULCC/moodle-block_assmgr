<?php
/**
 * Lists all the candidates and their enrolment relative to assessable evidence.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

 //include moodle config
//require_once(dirname(__FILE__).'/../../../config.php');

// remove this when testing is complete
$path_to_config = dirname($_SERVER['SCRIPT_FILENAME']).'/../../../config.php';
while (($collapsed = preg_replace('|/[^/]+/\.\./|','/',$path_to_config,1)) !== $path_to_config) {
    $path_to_config = $collapsed;
}
require_once('../../../config.php');

global $CFG, $USER, $PARSER, $PAGE;

//include the moodle library
require_once($CFG->dirroot.'/lib/moodlelib.php');

//include the assessment manager parser class
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_parser.class.php');

//include assessment manager db class
require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

//include the library file
require_once($CFG->dirroot.'/blocks/assmgr/lib.php');

//include the static constants
require_once($CFG->dirroot.'/blocks/assmgr/constants.php');

// IE 6 check
$is_ie6 = stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6');
if ($is_ie6) {
    print_error('incompatablebrowserie6', 'block_assmgr');
}

$dbc = new assmgr_db();

// get the course id
$course_id = $PARSER->optional_param('course_id', null, PARAM_INT);

// if there is a course_id: fetch the course, or fail if the id is wrong
if(!empty($course_id) && ($course = $dbc->get_course($course_id)) == false) {
    print_error('incorrectcourseid', 'block_assmgr');
}

// get the category from the course, or from the params
$category_id = empty($course->category) ? $PARSER->optional_param('category_id', null, PARAM_INT) : $course->category;

// if there is a category_id: fetch the category, or fail if the id is wrong
if(!empty($category_id) && ($category = $dbc->get_category($category_id)) == false) {
    print_error('incorrectcategoryid', 'block_assmgr');
}

$context = (!empty($course_id)) ? get_context_instance(CONTEXT_COURSE, $course_id) : get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_context($context);

// setup the navigation breadcrumbs
$PAGE->navbar->add(get_string('blockname', 'block_assmgr'),null,'title');

if(!empty($course)) {
    // category and course breadcrumbs
    $PAGE->navbar->add($category->name,$CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$category->id}",'title');
	$PAGE->navbar->add($course->shortname,$CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course->id}",'title');
} elseif(empty($category_id)) {
    // no category breadcrumbs
    $PAGE->navbar->add(get_string('allmyqualifications', 'block_assmgr'),null,"title");
} else {
    // category breadcrumbs
    $PAGE->navbar->add($category->name,null,"title");
}


// setup the page title and heading
$PAGE->set_title(get_string('blockname','block_assmgr'));
$PAGE->set_heading(get_string('blockname','block_assmgr'));
$PAGE->set_url('/blocks/assmgr/actions/list_portfolio_assessments.php', $PARSER->get_params());

require_once($CFG->dirroot.'/blocks/assmgr/views/list_portfolio_assessments.html');

echo $OUTPUT->footer();
?>