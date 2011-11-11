<?php
/**
 * Lists all the verifications that have been performed.
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

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

//require the course id
$course_id = $PARSER->required_param('course_id',PARAM_INT);

$dbc = new assmgr_db();

// if there is a course_id: fetch the course, or fail if the id is wrong
if(empty($course_id) || ($course = $dbc->get_course($course_id)) == false) {
    print_error('incorrectcourseid', 'block_assmgr');
}

if(!$access_isverifier) {
    print_error('nopageaccess', 'block_assmgr');
}

// get the category id
$category_id = $course->category;

$navlinks[] = array('name' => get_string('blockname', 'block_assmgr'), 'link' => null, 'type' => 'title');
$navlinks[] = array('name' => get_string('listverifications', 'block_assmgr'),  'link' => null, 'type' => 'title');

//MOODLE LOG portfolio assessments list
$log_action = get_string('logverificationlist', 'block_assmgr');
$log_url = "/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}";
$log_info = '';
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// setup the page title and heading
$PAGE->title = $course->shortname.': '.get_string('blockname','block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/list_portfolio_assessments.php', $PARSER->get_params());

require_once($CFG->dirroot.'/blocks/assmgr/views/list_verifications.html');

echo $OUTPUT->footer();
?>