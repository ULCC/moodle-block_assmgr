<?php
/**
 * This list all unconfirmed evidence
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

global $USER, $CFG, $PARSER;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

//require the course id
$course_id = $PARSER->required_param('course_id');

$dbc = new assmgr_db();

// if there is a course_id: fetch the course, or fail if the id is wrong
if(empty($course_id) || ($course = $dbc->get_course($course_id)) == false) {
    print_error('incorrectcourseid', 'block_assmgr');
}

if(!$access_canconfirm) {
    print_error('nopageaccess','block_assmgr');
}

// check to see if groups are being used in this course
$currentgroup = groups_get_course_group($course, true);

$baseurl = "{$CFG->wwwroot}/blocks/assmgr/actions/list_unconfirmed.php?course_id={$course_id}";

$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');
$navlinks[] = array('name' => get_string('unconfirmedevidence','block_assmgr') , 'link' => null, 'type' => 'title');

// setup the page title and heading
$PAGE->title = $course->shortname.': '.get_string('blockname','block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/list_unconfirmed.php', $PARSER->get_params());

require_once($CFG->dirroot.'/blocks/assmgr/views/list_unconfirmed.html');

echo $OUTPUT->footer();

?>