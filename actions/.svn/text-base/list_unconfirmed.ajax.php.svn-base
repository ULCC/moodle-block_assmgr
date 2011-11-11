<?php
/**
 * Ajax file for List Unconfirmed
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

$course_id = $PARSER->required_param('course_id',PARAM_INT);

// if there is a course_id: fetch the course, or fail if the id is wrong
if(empty($course_id) || ($course = $dbc->get_course($course_id)) == false) {
    print_error('incorrectcourseid', 'block_assmgr');
}

if(!$access_canconfirm) {
    print_error('nopageaccess','block_assmgr');
}

// get the optional group param
$group = $PARSER->optional_param('group', -1, PARAM_INT);

// check to see if groups are being used in this course
$currentgroup = groups_get_course_group($course, true);

if(isset($USER->access)) {
    $accessinfo = $USER->access;
} else {
    $accessinfo = $USER->access = get_user_access_sitewide($USER->id);
}

// find all courses that this user has the confirm evidence capability on
$courses = get_user_courses_bycap($USER->id, "block/assmgr:confirmevidence", $accessinfo, true);

// get the list of course id => name pairs
$courselist = array();
if(!empty($courses)) {
    foreach($courses as $cor) {
        $courselist[] = $cor->id;
    }
}

// TODO find all the users this user has the confirm evidence capability on
$userlist = array();

// set up the flexible table for displaying the evidence
$flextable = new assmgr_ajax_table('assmgr_confirmation');

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/list_unconfirmed.php?course_id={$course_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/list_unconfirmed.ajax.php?course_id={$course_id}");
$flextable->nothing = 'nounconfirmedevidence';

// set the basic details to dispaly in the table
$headers = array(
    '',
    get_string('course', 'block_assmgr'),
    get_string('evidencename', 'block_assmgr'),
    get_string('resourcetype', 'block_assmgr'),
    get_string('evidencelastupdated', 'block_assmgr'),
    ''
);

$columns = array(
    'fullname',
    'course_name',
    'evidence_name',
    'type_id',
    'timemodified',
    'actions'
);

$flextable->define_columns($columns);
$flextable->define_headers($headers);

$flextable->initialbars(true);

// make the table sortable
$flextable->sortable(true, 'course_name', 'DESC');
$flextable->no_sorting('type_id');
$flextable->no_sorting('actions');

$flextable->set_attribute('summary', get_string('unconfirmedevidence', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

$flextable->setup();

// fetch all the related unconfirmed evidence
$evidence = $dbc->get_unconfirmed_evidence_matrix($courselist, $userlist, $flextable, $currentgroup);

if(!empty($evidence)) {
    foreach($evidence as $evid) {

        // make the confirmation link
        $confirmurl = "edit_confirmation.php?course_id={$evid->course_id}&amp;evidence_id={$evid->evidence_id}";
        $confirmstr = get_string('confirm', 'block_assmgr');
        $confirm = "<a href='{$confirmurl}'>{$confirmstr}</a>";

        $data = array();

        if ($access_canviewuserdetails) {
            $data['fullname'] = print_user_picture($dbc->get_user($evid->candidate_id), $course_id, null, 0, true)."<a href=\"{$CFG->wwwroot}/user/view.php?id={$evid->candidate_id}&amp;course={$course_id}\" class=\"userlink\">".fullname($evid)."</a>";
        } else {
            $data['fullname'] = print_user_picture($dbc->get_user($evid->candidate_id), $course_id, null, 0, true, false).fullname($evid);
        }
        $data['course_name']   = $evid->course_name;
        $data['evidence_name'] = $evid->evidence_name;
        $data['type_id']       = get_string($evid->resource_type, 'block_assmgr');
        $data['timemodified']  = userdate($evid->timemodified, get_string('strftimedate', 'langconfig'));
        $data['actions']       = $confirm;

        $flextable->add_data_keyed($data);
    }
}

$flextable->print_html();