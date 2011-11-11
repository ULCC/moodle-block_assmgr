<?php
/**
 * Ajax file for View Verification Portfolios
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

global $USER, $CFG, $PARSER, $SITE;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

//include the progress_bar class
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_progress_bar.class.php');

//include the ajax class
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
$flextable = new assmgr_ajax_table('assmgr_verif_ports');

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/view_verification.php?course_id={$course_id}&amp;verification_id={$verification_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/view_verification_portfolios.ajax.php?course_id={$course_id}&amp;verification_id={$verification_id}");
$flextable->define_fragment('portfoliosamples');

// set the basic details to dispaly in the table
$columns = array(
    'fullname',
    'course',
    'progress',
    'finalgrade',
    'verified',
    'accurate',
    'constructive',
    'needs_amending',
    'verify'
);

$headers = array(
    '',
    get_string('course', 'block_assmgr'),
    get_string('progress', 'block_assmgr'),
    get_string('finalgrade', 'block_assmgr'),
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
$flextable->no_sorting( 'view');
$flextable->no_sorting( 'verify');

$flextable->initialbars(true);

// set the table attributes
$flextable->set_attribute('summary', get_string('portfoliosamples', 'block_assmgr'));
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'generaltable fit');

$flextable->setup();

// fetch all the candidates needing assessment
$portfolios = $dbc->get_verification_portfolio_matrix($verification->category_id, $verification->course_id, $verification->assessor_id, $flextable);

// instantiate the progress_bar
$progress = new assmgr_progress_bar();

if(!empty($portfolios)) {

    foreach($portfolios as $port) {

        if ($port->verified) {
            $verifylink = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$port->course_id}&amp;candidate_id={$port->candidate_id}&amp;verification_id={$verification->id}&amp;verify_form_id={$port->verify_form_id}#verificationform'>".get_string('edit', 'block_assmgr')."</a>";
        } else {
            $verifylink = "<a href='{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$port->course_id}&amp;candidate_id={$port->candidate_id}&amp;verification_id={$verification->id}#verificationform'>".get_string('verify', 'block_assmgr')."</a>";
        }
        // convert the decimal into a percentage
        $progbar = $progress->get_unit_progress($port->candidate_id, $port->course_id, true, 'small');

        $scale = $dbc->get_scale($port->scale_id, $port->gradepass);

        if ($access_canviewuserdetails) {
            $username = print_user_picture($dbc->get_user($port->candidate_id), $course_id, null, 0, true)."<a href='{$CFG->wwwroot}/user/view.php?id={$port->candidate_id}&amp;course={$SITE->id}'>".fullname($port)."</a>";
        } else {
            $username = print_user_picture($dbc->get_user($port->candidate_id), $course_id, null, 0, true, false).fullname($port)."</a>";
        }

        $accurate = null;
        if(!is_null($port->accurate)) {
            $accurate = limit_length(($port->accurate) ? get_string('yes') : get_string('no'), 10, $port->accurate_comment);
        }

        $constructive = null;
        if(!is_null($port->constructive)) {
            $constructive = limit_length(($port->constructive) ? get_string('yes') : get_string('no'), 10, $port->constructive_comment);
        }

        $needs_amending = null;
        if(!is_null($port->needs_amending)) {
            $needs_amending = limit_length(($port->needs_amending) ? get_string('yes') : get_string('no'), 10, $port->amendment_comment);
        }

        // build the row
        $data = array(
            'fullname'       =>  $username,
            'course'         =>  $port->course,
            'progress'       =>  $progbar,
            'finalgrade'     =>  $scale->render_scale_item($port->finalgrade),
            'verified'       =>  ($port->verified) ? get_string('yes') : get_string('no'),
            'accurate'       =>  $accurate,
            'constructive'   =>  $constructive,
            'needs_amending' =>  $needs_amending,
            'verify'         =>  $verifylink
        );

        $flextable->add_data_keyed($data);
    }
}

$flextable->print_html();
