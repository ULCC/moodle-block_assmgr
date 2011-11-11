<?php
/**
 * This page allows the verifier to select their desired sample.
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

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

// include the moodle form for this table
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_verification_mform.php');

// get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

// if verification id is set then we are doing an edit operation
$verification_id = $PARSER->optional_param('verification_id', null, PARAM_INT);

if (!$access_isverifier) {
    print_error('nopageaccess', 'block_assmgr');
}

$dbc = new assmgr_db();

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
$navlinks[] = array('name' => get_string('verificationsample', 'block_assmgr'),  'link' => null, 'type' => 'title');

//MOODLE LOG verification edit
$log_action = get_string('logeditverification', 'block_assmgr');
$log_url = "edit_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}";
$log_info = '';
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// setup the page title and heading
$PAGE->title = get_string('verificationsample','block_assmgr').': '.get_string('blockname','block_assmgr');
$PAGE->set_heading(get_string('verificationsample','block_assmgr'));
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/edit_verification.php', $PARSER->get_params());

// instantiate the form
$mform = new edit_verification_mform($course_id, $verification_id);
$mform->set_data($verification);

$backurl = "{$CFG->wwwroot}/blocks/assmgr/actions/list_verifications.php?course_id={$course_id}";

// was the form canceled
if ($mform->is_cancelled()) {
    // if canceled then go back to the edit portfolio page
    redirect($backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);
}

// has the form been submitted
if($mform->is_submitted()) {
    // check the validation rules
    if($mform->is_validated()) {
        // process the data
        $verification_id = $mform->process_data($mform->get_data());

        if(empty($verification_id)) {
            print_error('verificationsavefail', 'block_assmgr');
        }

        $nexturl = "{$CFG->wwwroot}/blocks/assmgr/actions/view_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}";

        $return_message = get_string('verificationsave', 'block_assmgr');
        redirect($nexturl, $return_message, REDIRECT_DELAY);
    }
}

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_verification.html');

echo $OUTPUT->footer();
?>