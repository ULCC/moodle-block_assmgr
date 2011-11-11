<?php
/**
 * Lists all set dates for the given user portfolio.
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

global $CFG, $USER, $PARSER;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// fetch the table library
require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_tablelib.class.php');


if(!$access_isassessor) {
    print_error('nopageaccess', 'block_assmgr');
}

//require the course id
$course_id = $PARSER->required_param('course_id',PARAM_INT);

//require the candidates id
$candidate_id = $PARSER->required_param('candidate_id',PARAM_INT);

// fetch the update preferences flag
$group_id = $PARSER->optional_param('group_id', 0, PARAM_INT);

// fetch the delete flag
$delete_set = $PARSER->optional_param('delete', 0, PARAM_INT);

$dbc = new assmgr_db();

//find the portfolio if it has been set
$portfolio = $dbc->get_portfolio($candidate_id, $course_id);
$course = $dbc->get_course($course_id);
$coursecat = $dbc->get_category($course->category);
$candidate =$dbc->get_user($candidate_id);

if (empty($portfolio)) {
  // create a new portfolio for the user
  $portfolio_id = $dbc->create_portfolio($candidate_id, $course_id);
  // add the course level grade items
  $dbc->create_portfolio_grade_items($course_id);
  $course = $dbc->get_course($course_id);

    //MOODLE LOG candidate portfolio created
    $log_action = get_string('logportfoliocreate', 'block_assmgr');
    $log_url = "edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}";
    $logstrings = new stdClass;
    $logstrings->name = fullname($candidate);
    $logstrings->course = $course->shortname;
    $log_info = get_string('logportfoliocreateinfo', 'block_assmgr', $logstrings);
    assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

    $portfolio = $dbc->get_portfolio($candidate_id, $course_id);
}

if ($dbc->future_assessment_event_exists($candidate_id, $course_id, $group_id)) {
    // setup the navigation breadcrumbs
    $navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');
    $navlinks[] = array('name' => $coursecat->name, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}", 'type' => 'title');
    $navlinks[] = array('name' => $course->shortname, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}", 'type' => 'title');
    $navlinks[] = array('name' => fullname($candidate), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#submittedevidence", 'type' => 'title');
    $navlinks[] = array('name' => get_string('listassessmentdates','block_assmgr'), 'link' => null, 'type' => 'title');


    // setup the page title and heading
    $PAGE->title = get_string('setassessmentdate','block_assmgr');
    $PAGE->set_heading($course->fullname);
    $PAGE->set_navigation = assmgr_build_navigation($navlinks);
    $PAGE->set_url('/blocks/assmgr/actions/list_assess_dates.php', $PARSER->get_params());

    require_once($CFG->dirroot.'/blocks/assmgr/views/list_assess_dates.html');

    echo $OUTPUT->footer();

} else if (empty($delete_set)) {

    // redirect to add event page
    $return_message = get_string('noassessmentdates', 'block_assmgr');
    redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_assess_date.php?candidate_id={$candidate_id}&course_id={$course_id}",$return_message, REDIRECT_DELAY);
} else {
    // redirect to add event page
    $return_message = get_string('returnedtolist', 'block_assmgr');
    redirect("{$CFG->wwwroot}/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}",$return_message, REDIRECT_DELAY);
}
?>