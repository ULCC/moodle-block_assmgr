<?php
/**
 * Ajax file for List Verifications
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

if(!$access_isverifier) {
    print_error('nopageaccess', 'block_assmgr');
}

// get the category id
$category_id = $course->category;

// set up the flexible table for displaying the portfolios
$flextable = new assmgr_ajax_table('assmgr_verifications');

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/list_verifications.php?course_id={$course_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/list_verifications.ajax.php?course_id={$course_id}");

// set the basic details to dispaly in the table
$headers = array(
    get_string('started', 'block_assmgr'),
    get_string('verifier', 'block_assmgr'),
    get_string('qualification', 'block_assmgr'),
    get_string('course', 'block_assmgr'),
    get_string('assessor', 'block_assmgr'),
    get_string('completed', 'block_assmgr'),
    '',
    ''
);

$columns = array(
    'timecreated',
    'v_firstname',
    'category',
    'course',
    'a_firstname',
    'complete',
    'edit',
    'verify'
);

$flextable->define_columns($columns);
$flextable->define_headers($headers);

// make the table sortable
$flextable->sortable(true, 'timecreated', 'DESC');
$flextable->no_sorting('edit');
$flextable->no_sorting('verify');

$flextable->initialbars(true);

$flextable->set_attribute('summary', get_string('listverifications', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

$flextable->setup();

// fetch all the candidates needing assessment
$verifications = $dbc->get_verification_matrix($flextable);

if(!empty($verifications)) {
    foreach($verifications as $verif) {


        $verifier = $dbc->get_user($verif->verifier_id);
        $assessor = $dbc->get_user($verif->assessor_id);

        $vfullname = '';

        if ($verifier) {

            if ($access_canviewuserdetails) {
                $vfullname = print_user_picture($verifier, $course_id, null, 0, true)."<a href=\"{$CFG->wwwroot}/user/view.php?id={$verif->verifier_id}&amp;course={$course_id}\" class=\"userlink\">".fullname($verifier)."</a>";
            } else {
                $vfullname = print_user_picture($verifier, $course_id, null, 0, true, false).fullname($verifier);
            }
        }

        $afullname = '';

        if ($assessor) {

            if ($access_canviewuserdetails) {
                $afullname = print_user_picture($assessor, $course_id, null, 0, true)."<a href=\"{$CFG->wwwroot}/user/view.php?id={$verif->assessor_id}&amp;course={$course_id}\" class=\"userlink\">".fullname($assessor)."</a>";
            } else {
                $afullname = print_user_picture($assessor, $course_id, null, 0, true, false).fullname($assessor);
            }
        }

        $editlink = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/edit_verification.php?course_id={$course_id}&amp;verification_id={$verif->id}'>".get_string('edit', 'block_assmgr')."</a>";
        $verifylink = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/view_verification.php?course_id={$course_id}&amp;verification_id={$verif->id}'>".get_string('verify', 'block_assmgr')."</a>";

        // build the row
        $data = array();
        $data['timecreated'] = userdate($verif->started, get_string('strftimedate', 'langconfig'));
        $data['v_firstname'] = $vfullname;
        $data['category'] = $verif->category;
        $data['course'] = $verif->course;
        $data['a_firstname'] = $afullname;
        $data['complete'] = (empty($verif->complete)) ? null : userdate($verif->complete, get_string('strftimedate', 'langconfig'));
        $data['edit'] = $editlink;
        $data['verify'] = $verifylink;

        $flextable->add_data_keyed($data);
    }
}

$flextable->print_html();