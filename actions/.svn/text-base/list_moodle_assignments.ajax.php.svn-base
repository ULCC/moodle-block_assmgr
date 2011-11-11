<?php
/**
 * My assignments ajax file
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

//include the default class
require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_ajax_table.class.php');

// db class manager
$dbc = new assmgr_db();

// get the the page params
$course_id = $PARSER->required_param('course_id', PARAM_INT);
$candidate_id = $PARSER->required_param('candidate_id', PARAM_INT);
$resource_type_id = $PARSER->required_param('resource_type_id', PARAM_INT);

$folder_id = $PARSER->optional_param('folder_id', 0, PARAM_INT);

// set up the flexible table for displaying the portfolios
$flextable = new assmgr_ajax_table('assmgr_activities', false);

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/edit_evidence.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;folder_id={$folder_id}&amp;resource_type_id={$resource_type_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/list_moodle_assignments.ajax.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;folder_id={$folder_id}&amp;resource_type_id={$resource_type_id}");

$columns = array(
    'course_name',
    'module_name',
    'assignment_name',
    'actions'
);
$flextable->define_columns($columns);

$headers = array(
    get_string('course', 'block_assmgr'),
    get_string('module', 'block_assmgr'),
    get_string('activity', 'block_assmgr'),
    ''
);
$flextable->define_headers($headers);

// make the table sortable
$flextable->sortable(true, 'course_name', 'ASC');
$flextable->no_sorting('actions');

$flextable->initialbars(true);

$flextable->set_attribute('summary', get_string('listassignments', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

$flextable->setup();

// fetch all the activites
$activities = $dbc->get_candidate_activities($USER->id, $flextable);

if(!empty($activities)) {
    
    foreach($activities as $activity) {
        // make the confirmation link
        $assname = str_replace("'"," ",$activity->assignment_name);

        $data = array();
        $data['course_name'] = $activity->course_name;
        $data['module_name'] = get_string('modulename', $activity->module_name);
        $data['assignment_name'] = $activity->assignment_name;
        //$data['actions'] = "<input type='radio' name='selected' value='{$activity->module_name}/{$activity->activity_id}' />";

        $selectstr = get_string('select', 'block_assmgr');
        $onclick = "return set_chosen_activity(\"{$activity->module_name}\", {$activity->activity_id}, \"{$assname}\");";
        $selectcourse = "<a href='#' onclick='{$onclick}'>{$selectstr}</a>";
        $data['actions'] = $selectcourse;

        $flextable->add_data_keyed((array)$data);
    }
}

$flextable->print_html();
