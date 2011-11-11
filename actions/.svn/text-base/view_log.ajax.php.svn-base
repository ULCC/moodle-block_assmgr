<?php
/**
 * This answer to the log page.
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

// meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

// include the moodle form library
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

// fetch the audit table library
require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_log_table.class.php');

// db class manager
$dbc = new assmgr_db();

$course_id = $PARSER->required_param('course_id', PARAM_INT);
$candidate_id = $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT);
$verification_id = $PARSER->optional_param('verification_id', null, PARAM_INT);
$submission_id = $PARSER->optional_param('submission_id', null, PARAM_INT);
$evidence_id = $PARSER->optional_param('evidence_id', null, PARAM_INT);

//if the submission id is set we will have to retrieve the id of candidate another way
if (!empty($submission_id)) {
    $candidate = $dbc->get_submission_candidate($submission_id);
    $candidate_id = $candidate->id;
}

// get the portfolio id
if (empty($verification_id)) {
    $portfolio = $dbc->get_portfolio($candidate_id, $course_id);
    $portfolio_id = $portfolio->id;
}

$classname = (empty($evidence_id) && empty($submission_id)) ? 'assmgr_log_table' : 'assmgr_ajax_table';

if (!empty($evidence_id)) {
    $class_id = "assmgr_log_evidencecourse_id{$course_id}candidate_id{$candidate_id}";
} else if (!empty($submission_id)) {
    $class_id = "assmgr_log_submissioncourse_id{$course_id}candidate_id{$candidate_id}";
} else  {
    $class_id = "assmgr_logcourse_id{$course_id}candidate_id{$candidate_id}";
}

$flextable = new $classname($class_id);

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;verification_id={$verification_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/view_log.ajax.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;verification_id={$verification_id}&amp;evidence_id={$evidence_id}&amp;submission_id={$submission_id}");
$flextable->define_fragment('actionslog');

$columns = array(
    'date',
    'fullname',
    'type',
    'entity',
    'fieldheader',
    'new_value'
);

$headers = array(
    get_string('logdate', 'block_assmgr'),
    get_string('username', 'block_assmgr'),
    get_string('logtype', 'block_assmgr'),
    get_string('logentity', 'block_assmgr'),
    get_string('logfield', 'block_assmgr'),
    get_string('logchange', 'block_assmgr')
);

$flextable->define_columns($columns);
$flextable->define_headers($headers);

// make the table sortable
$flextable->sortable(true, 'date', 'DESC');
$flextable->no_sorting('new_value');

$flextable->column_suppress('date');
$flextable->column_suppress('fullname');
$flextable->column_suppress('type');
$flextable->column_suppress('entity');

$flextable->set_attribute('summary', get_string('actionslog', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

// setup the table - now we can use it
$flextable->setup();

// fetch the list of users
$flextable->users = (!empty($verification_id)) ? $dbc->get_log_verifiers(array($verification_id)) : $dbc->get_log_users($candidate_id, $course_id);

// fetch all the the correct log matrix
if (!empty($verification_id)) {
    $matrix = $dbc->get_log_matrix_verification(array($verification_id), $flextable);
} else if (!empty($evidence_id)) {
    $matrix = $dbc->get_log_matrix_evidence(array($evidence_id), $flextable);
} else if (!empty($submission_id)) {
    $matrix = $dbc->get_log_matrix_submission(array($submission_id), $flextable);
} else {
    $matrix = $dbc->get_log_matrix_portfolio(array($portfolio->candidate_id), array($portfolio->course_id), $flextable);
}

if (!empty($matrix)) {
    foreach ($matrix as $log_event) {
        $data = array();
        $data['date'] = userdate($log_event->date, get_string('strftimedatetime', 'langconfig'));
        $data['fullname'] = fullname($log_event);
        $data['type'] = $log_event->type;
        $data['entity'] = $log_event->entity;
        $data['fieldheader'] = $log_event->fieldheader;
        $data['new_value'] = limit_length($log_event->change, 75);

        $flextable->add_data_keyed($data);
    }
}

$flextable->print_html();