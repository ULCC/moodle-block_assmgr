<?php
/**
 * This answer to the submissions  page.
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

//include the submission_table class
require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_ajax_table.class.php');

if(!$access_isassessor) {
    print_error('nopageaccess', 'block_assmgr');
}

// db class manager
$dbc = new assmgr_db();

$tablename = "assessment_dates";

//require the course id
$course_id = $PARSER->required_param('course_id',PARAM_INT);

//require the candidates id
$candidate_id = $PARSER->required_param('candidate_id',PARAM_INT);

// fetch the update preferences flag
$group_id = $PARSER->optional_param('group_id', 0, PARAM_INT);

//find the portfolio if it has been set
$portfolio = $dbc->get_portfolio($candidate_id, $course_id);
$course = $dbc->get_course($course_id);
$candidate =$dbc->get_user($candidate_id);

// set up the flexible table for displaying the portfolios
$flextable = new assmgr_ajax_table($tablename);

// set the URLs
$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/list_assess_dates.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;group_id={$group_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/list_assess_dates.ajax.php?course_id={$course_id}&amp;candidate_id={$candidate_id}&amp;group_id={$group_id}");

// set the basic details to dispaly in the table
$headers = array(
    get_string('eventassessmentdate', 'block_assmgr'),
    get_string('description', 'block_assmgr'),
    get_string('type', 'block_assmgr'),
    '',
    '',
);

$columns = array(
    'date',
    'description',
    'type',
    'edit',
    'delete'
);

$flextable->define_headers($headers);
$flextable->define_columns($columns);

// make the table sortable
$flextable->sortable(true, 'date', 'DESC');
$flextable->no_sorting('type');
$flextable->no_sorting('edit');
$flextable->no_sorting('delete');

// set the table attributes
$flextable->set_attribute('summary', get_string('listassessmentdates', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

$flextable->setup();

// fetch all the candidates needing assessment
$matrix = $dbc->get_assessment_date_matrix($portfolio->id, $portfolio->course_id, $candidate_id, $group_id, $flextable);

if (!empty($matrix)) {

    foreach ($matrix as $ass_event) {

        $data = array();
        $data['date'] = userdate($ass_event->date, get_string('strftimedate', 'langconfig'));
        $data['description'] = limit_length($ass_event->description, 50);
        $data['edit'] = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/edit_assess_date.php?candidate_id={$candidate_id}&amp;course_id={$course_id}&amp;event_id={$ass_event->id}&amp;group_id={$ass_event->groupid}&amp;repeat_id={$ass_event->repeatid}' >".get_string('listassessmentedit', 'block_assmgr')."</a>";
        $data['delete'] = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/delete_assess_date.php?candidate_id={$candidate_id}&amp;course_id={$course_id}&amp;event_id={$ass_event->id}&amp;group_id={$ass_event->groupid}&amp;repeat_id={$ass_event->repeatid}' >".get_string('listassessmentdelete', 'block_assmgr')."</a>";

        if (!empty($ass_event->courseid)) {
            $data['type'] = get_string('course', 'block_assmgr');
        } else if (!empty($ass_event->groupid)){
            $data['type'] = get_string('group', 'block_assmgr');;
        } else {
            $data['type'] = get_string('user', 'block_assmgr');;
        }

        $flextable->add_data_keyed($data);
    }
}

$flextable->print_html();