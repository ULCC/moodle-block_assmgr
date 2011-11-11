<?php
/**
 * This page allows the verifier to conduct verifications on their chosen sample.
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

//include the progress_bar class
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_progress_bar.class.php');

// get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

// if verification id is set then we are doing an edit operation
$verification_id = $PARSER->optional_param('verification_id', null, PARAM_INT);

if(!$access_isverifier) {
    print_error('nopageaccess', 'block_assmgr');
}

$dbc = new assmgr_db();

// add the calendar stylesheet
$calurl = new moodle_url($CFG->wwwroot.'/lib/yui/calendar/assets/skins/sam/calendar.css');
$PAGE->requires->css_theme($calurl);

$verification = $dbc->get_verification($verification_id);

//Lock all portfolios if possible
$verifyforms = $dbc->get_verification_forms_by_verification($verification_id);

if ($verifyforms) {
    $checkedportfolios = array();

    foreach ($verifyforms as $verifyform) {

        if (!in_array($verifyform->portfolio_id, $checkedportfolios)) {
            check_portfolio(null, null, $verifyform->portfolio_id);
            $checkedportfolios[] = $verifyform->portfolio_id;
        }
    }
}

$navlinks[] = array('name' => get_string('blockname', 'block_assmgr'), 'link' => null, 'type' => 'title');
$navlinks[] = array('name' => get_string('listverifications', 'block_assmgr'),  'link' => $CFG->wwwroot.'/blocks/assmgr/actions/list_verifications.php?course_id='.$course_id, 'type' => 'title');
$navlinks[] = array('name' => userdate($verification->timecreated, get_string('strftimedate', 'langconfig')),  'link' => null, 'type' => 'title');
$navlinks[] = array('name' => get_string('verificationsample', 'block_assmgr'),  'link' => $CFG->wwwroot.'/blocks/assmgr/actions/edit_verification.php?course_id='.$course_id.'&amp;verification_id='.$verification_id, 'type' => 'title');
$navlinks[] = array('name' => get_string('conductverification', 'block_assmgr'),  'link' => null, 'type' => 'title');

//MOODLE LOG verification edit
$log_action = get_string('logeditverification', 'block_assmgr');
$log_url = "view_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}";
$log_info = '';
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// setup the page title and heading
$PAGE->title = get_string('conductverification','block_assmgr').': '.get_string('blockname','block_assmgr');
$course = $dbc->get_course($course_id);
$candidate = $dbc->get_user($verification->assessor_id);
$PAGE->set_heading(get_string('conductverification', 'block_assmgr'));
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/view_verification.php', $PARSER->get_params());

require_once($CFG->dirroot.'/blocks/assmgr/views/view_verification.html');

echo $OUTPUT->footer();
?>