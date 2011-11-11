<?php
/**
 * Ajax file for View Verification Submissions
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

require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_ajax_table.class.php');

// db class manager
$dbc = new assmgr_db();

// get the page params
$course_id = $PARSER->required_param('course_id',PARAM_INT);
$verification_id = $PARSER->required_param('verification_id', PARAM_INT);

if(!$access_isverifier) {
    print_error('nopageaccess', 'block_assmgr');
}

$verification = $dbc->get_verification($verification_id);

// set up the flexible table for displaying the portfolios
$flextable = new assmgr_ajax_table('assmgr_verif_subs');

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/view_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/view_verification_submissions.ajax.php?course_id={$course_id}&amp;verification_id={$verification_id}");
$flextable->define_fragment('submissionsamples');

// set the basic details to dispaly in the table
$columns = array(
    'fullname',
    'course',
    'evidence',
    'verified',
    'accurate',
    'constructive',
    'needs_amending',
    'verify'
);

$headers = array(
    '',
    get_string('course', 'block_assmgr'),
    get_string('evidence', 'block_assmgr'),
    get_string('verified', 'block_assmgr'),
    limit_length(get_string('assessedaccuratelytitle', 'block_assmgr'), 10),
    limit_length(get_string('constructivecomments',    'block_assmgr'), 10),
    limit_length(get_string('decisionamending',        'block_assmgr'), 10),
    ''
);

$flextable->define_columns($columns);
$flextable->define_headers($headers);

// make the table sortable
$flextable->sortable(true, 'verified', 'DESC');
$flextable->no_sorting('verify');
$flextable->initialbars(true);

$flextable->set_attribute('summary', get_string('submissionsamples', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

$flextable->setup();

// fetch all the submission in the sample
$submissions = $dbc->get_verification_submission_matrix($verification->category_id, $verification->course_id, $verification->assessor_id, $flextable);

if(!empty($submissions)) {
    foreach($submissions as $sub) {

        $port = $dbc->get_portfolio($sub->candidate_id, $sub->course_id);

        if ($sub->verified) {
            $verifylink = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?course_id={$port->course_id}&amp;verification_id={$verification->id}&amp;portfolio_id={$port->id}&amp;submission_id={$sub->submission_id}&amp;verify_form_id={$sub->verify_form_id}'>".get_string('edit', 'block_assmgr')."</a>";
        } else {
            $verifylink = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?course_id={$port->course_id}&amp;verification_id={$verification->id}&amp;portfolio_id={$port->id}&amp;submission_id={$sub->submission_id}'>".get_string('verify', 'block_assmgr')."</a>";
        }

        if ($access_canviewuserdetails) {
            $fullname = print_user_picture($dbc->get_user($sub->candidate_id), $course_id, null, 0, true)."<a href=\"{$CFG->wwwroot}/user/view.php?id={$sub->candidate_id}&amp;course={$course_id}\">".fullname($sub)."</a>";
        } else {
            $fullname = print_user_picture($dbc->get_user($sub->candidate_id), $course_id, null, 0, true, false).fullname($sub);
        }

        $accurate = null;
        if(!is_null($sub->accurate)) {
            $accurate = limit_length(($sub->accurate) ? get_string('yes') : get_string('no'), 10, $sub->accurate_comment);
        }

        $constructive = null;
        if(!is_null($sub->constructive)) {
            $constructive = limit_length(($sub->constructive) ? get_string('yes') : get_string('no'), 10, $sub->constructive_comment);
        }

        $needs_amending = null;
        if(!is_null($sub->needs_amending)) {
            $needs_amending = limit_length(($sub->needs_amending) ? get_string('yes') : get_string('no'), 10, $sub->amendment_comment);
        }

        // build the row
        $data = array(
            "fullname"      =>  $fullname,
            "course"        =>  $sub->course,
            "evidence"      =>  $sub->evidence,
            "verified"      =>  ($sub->verified) ? get_string('yes') : get_string('no'),
            'accurate'      =>  $accurate,
            'constructive'  =>  $constructive,
            'needs_amending'=>  $needs_amending,
            "verify"        =>  $verifylink
        );

        $flextable->add_data_keyed($data);
    }
}

$flextable->print_html();
