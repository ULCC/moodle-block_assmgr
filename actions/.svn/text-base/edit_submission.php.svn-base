<?php
/**
 * This page allows a piece of evidence to be assessed
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

global $USER, $CFG, $PAGE, $PARSER;

//include the assessment manager parser class
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_parser.class.php');

//include assessment manager db class
require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

//load the access rights of the current user
require_once($CFG->dirroot.'/blocks/assmgr/db/accesscheck.php');

//include the library file
require_once($CFG->dirroot.'/blocks/assmgr/lib.php');

// include the form lib
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_formslib.php');

// include the moodle library file
require_once($CFG->libdir.'/filelib.php');

// load the upload manager class file
require_once($CFG->dirroot.'/lib/uploadlib.php');

//include the mform class
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/edit_submission_mform.php');

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

//get the id of the submission
$submission_id = $PARSER->required_param('submission_id', PARAM_INT);

//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

$verify_form_id  = $PARSER->optional_param('verify_form_id', null, PARAM_INT);

//get the verificiation id if it exists
$verification_id = $PARSER->optional_param('verification_id', null, PARAM_INT);

// instantiate the db
$dbc = new assmgr_db();

if (!empty($verification_id)) {
    if (!$access_isverifier) {
        print_error('nosubmissionverify', 'block_assmgr');
    }
    $verification = $dbc->get_verification($verification_id);
} else {
    $access_isverifier = false;
}

// get the course
$course = $dbc->get_course($course_id);

// get the category
$coursecat = $dbc->get_category($course->category);

// get the cateogry id
$category_id = $PARSER->optional_param('category_id', $course->category, PARAM_INT);

// get the submission
$submission = $dbc->get_submission_by_id($submission_id);

if(empty($submission)) {
    print_error('submissionretrieveerror', 'block_assmgr');
}

// get the evidence
$evidence = $dbc->get_evidence_resource($submission->evidence_id);

// get the candidate id
$candidate_id = $evidence->candidate_id;

// get the candidate
$candidate = $dbc->get_user($candidate_id);

// Is the portfolio in use? Lock it if possible.
check_portfolio($candidate->id, $course->id);

//if this is the users evidence throw an error
if($access_isassessor && ($USER->id == $candidate_id)) {
    print_error('cantassessownsubmission', 'block_assmgr');
}

// is this the user's evidence
if($access_iscandidate && ($USER->id != $candidate_id)) {
    print_error('noclaimothersevidence', 'block_assmgr');
}

// include the class for this type of evidence
@include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$evidence->resource_type}.php");

if(!class_exists($evidence->resource_type)) {
    print_error('noclassforresource', 'block_assmgr', '', $evidence->resource_type);
}

// instantiate the evidence resource
$evidence_resource = new $evidence->resource_type;
$evidence_resource->load($evidence->resource_id);

// setup the navigation breadcrumbs
$navlinks[] = array('name' => get_string('blockname','block_assmgr'), 'link' => null, 'type' => 'title');

if ($access_iscandidate) {
    // candidate breadcrumbs
    $navlinks[] = array('name' => get_string('myportfolio', 'block_assmgr'), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#submittedevidence", 'type' => 'title');
    if($dbc->has_submission_grades($submission_id)) {
        $typeHeader = get_string('viewsubmission', 'block_assmgr');
    } else {
        $typeHeader = get_string('selfassesssubmission', 'block_assmgr');
    }
}elseif ($access_isassessor && !$access_isverifier) {
    // assessor breadcrumbs
    $navlinks[] = array('name' => $coursecat->name, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}", 'type' => 'title');
    $navlinks[] = array('name' => $course->shortname, 'link' => $CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course_id}", 'type' => 'title');
    $navlinks[] = array('name' => fullname($candidate), 'link' => $CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}#submittedevidence", 'type' => 'title');
    $typeHeader = get_string('assesssubmission', 'block_assmgr');
} else {
    // verifier breadcrumbs
    $navlinks[] = array('name' => get_string('listverifications', 'block_assmgr'),  'link' => $CFG->wwwroot.'/blocks/assmgr/actions/list_verifications.php?course_id='.$course_id, 'type' => 'title');
    $navlinks[] = array('name' => userdate($verification->timecreated, get_string('strftimedate', 'langconfig')),  'link' => null, 'type' => 'title');
    $navlinks[] = array('name' => get_string('verificationsample', 'block_assmgr'),  'link' => $CFG->wwwroot.'/blocks/assmgr/actions/edit_verification.php?course_id='.$course_id.'&amp;verification_id='.$verification_id, 'type' => 'title');
    $navlinks[] = array('name' => get_string('conductverification', 'block_assmgr'),  'link' => $CFG->wwwroot.'/blocks/assmgr/actions/view_verification.php?course_id='.$course_id.'&amp;verification_id='.$verification_id, 'type' => 'title');
    $typeHeader = get_string('viewsubmission', 'block_assmgr');
}

$navlinks[] = array('name' => $typeHeader, 'link' => '', 'type' => 'title');
$navlinks[] = array('name' => $evidence->name, 'link' => '', 'type' => 'title');

if(!empty($evidence->folder_id)) {
    $folder = $dbc->get_folder($evidence->folder_id);
}

$foldername = (!empty($folder)) ? $folder->name : get_string('none','block_assmgr');

$evidence_status = ($dbc->has_submission($evidence->id))  ? get_string('submitted','block_assmgr') : get_string('notsubmitted','block_assmgr');

// check confirmation status
$needs_confirmation = false;
$confirmation_status = get_string('unnecssary','block_assmgr');
$confirmation = $dbc->get_confirmation($evidence->id);

if(!empty($confirmation)) {
    $needs_confirmation = ($confirmation->status == CONFIRMATION_PENDING);
    $confirmation_status = confirmation_status($confirmation->status);
}

$assessed_status = $dbc->has_submission_grades($submission->id) ? get_string('assessed','block_assmgr') : get_string('notassessed','block_assmgr');

$verified_status = (!empty($evidence->verified_status)) ? get_string('verified','block_assmgr') : get_string('notverified','block_assmgr');

// setup the page title and heading
$PAGE->title = $course->shortname.': '.get_string('blockname','block_assmgr');
$PAGE->set_heading($course->fullname);
$PAGE->set_navigation = assmgr_build_navigation($navlinks);
$PAGE->set_url('/blocks/assmgr/actions/edit_submission.php', $PARSER->get_params());

// get the list of outcomes and evidence types
$outcomes = $dbc->get_outcomes($course_id);
$outcomes = (!$outcomes) ? array() : $outcomes;

$evidtypes = $dbc->get_evidence_types();
$evidtypes = (!$evidtypes) ? array() : $evidtypes;

// get the outcome claims and grades
$cand_claims = $dbc->get_submission_claims($submission->id);
$assess_outcomes = $dbc->get_submission_grades($submission->id);

// claims are locked after the submission has been assessed, or if the submission was made by an assessor
$graded = $dbc->has_submission_grades($submission->id);
$assessor_evidence = ($candidate_id != $submission->creator_id);

// merge the claims and grades into the outcomes
foreach($outcomes as $id => $outcome) {
    // is there a claim for this outcome
    if(!empty($cand_claims[$id])) {
        $outcomes[$id]->claim = true;
    }
    // is there a grade for this outcome
    if(!empty($assess_outcomes[$id])) {
        $outcomes[$id]->grade = $assess_outcomes[$id]->grade;
    }

    $outcomes[$id]->scale = $dbc->get_scale($outcome->scaleid, $outcome->gradepass);
}

// get the evidence type claims and grades
$assess_evidtypes = $dbc->get_submission_evidence_types($submission->id);

// merge the claims and grades into the evidtypes
foreach($evidtypes as $id => $evidtype) {
    // is there a grade for this evidtype
    if(!empty($assess_evidtypes[$id])) {
        $evidtypes[$id]->grade = true;
    }
}

// get any submission comments
$comment = $dbc->get_submission_comment($submission->id);

// get any submission feedback files
$feedbacks = $dbc->get_submission_feedbacks($submission->id);
// if there are feedbacks I need the directory (to create the link)
if ($feedbacks) {
    $feedbacks_dir = assmgr_submission_folder($evidence->candidate_id, $submission->id);
}

// TODO needs conditional stuff?
// TODO is this using the right name?
//MOODLE LOG submission assessment viewed
$log_action = get_string('logsubassessedit', 'block_assmgr');
$log_url = "edit_submission.php?course_id={$course_id}&amp;submission_id={$submission_id}";
$logstrings = new stdClass;
$logstrings->name = fullname($candidate);
$logstrings->course = $course->shortname;
$log_info = get_string('logsubassesseditinfo', 'block_assmgr', $logstrings);
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

// New stuff for mform conversion
$params = array(
        'evidence' => $evidence,
        'foldername' =>$foldername,
        'evidence_status' => $evidence_status,
        'confirmation_status' => $confirmation_status,
        'evidence_resource' => $evidence_resource,
        'confirmation' => $confirmation,
        'needs_confirmation' => $needs_confirmation,
        'access_isverifier' => $access_isverifier,
        'outcomes' => $outcomes,
        'candidate' => $candidate,
        'evidtypes' => $evidtypes,
        'comment' => $comment,
        'feedbacks' => $feedbacks,
        'submission_id' => $submission_id,
        'course_id' => $course_id,
        'portfolio_id' => $submission->portfolio_id,
        'access_iscandidate' => $access_iscandidate,
        'access_isassessor' => $access_isassessor,
        'graded' => $graded,
        'assessor_evidence' => $assessor_evidence,
        'synchronise' => $submission->synchronise
);


$mform = new edit_submission_mform($params);

// was the form canceled?
if ($mform->is_cancelled()) {
    //if canceled then go back to the edit portfolio page

    // TODO no backurl
    if ($access_iscandidate) {
        $backurl = '/blocks/assmgr/actions/edit_portfolio.php?course_id='.$course_id.'#submittedevidence';
    } else if ($access_isassessor) {
        //TODO - needs a fix here so that cancel works
        $backurl = '/blocks/assmgr/actions/edit_portfolio.php?course_id='.$course_id.'&candidate_id='.$candidate->id.'#submittedevidence';
    } else if ($access_isverifier) {
        $backurl = '';
    }

    redirect($CFG->wwwroot.$backurl, get_string('changescancelled', 'block_assmgr'), REDIRECT_DELAY);
}

// set the data from any existing claim (assessor choices only)
$evidencemerge = array();

foreach ($evidtypes as $evidtype) {

    if (isset($evidtype->grade)) {
        $key = 'assessor_type['.$evidtype->id.']';
        $evidencemerge[$key] = ($evidtype->grade) ? 1 : 0;
    }
}

$outcomesmerge = array();

foreach ($outcomes as $outcome) {

    if (isset($outcome->grade)) {
        $key = 'assessor_criteria_grade['.$outcome->id.']';
        $outcomesmerge[$key] = $outcome->grade;

    }

    if (isset($outcome->claim)) {
        // get the data for the claims made by the candidate
        $key = 'candidate_criteria['.$outcome->id.']';
        $outcomesmerge[$key] = $outcome->claim;
    }
}

$confirmmerge = array();
$confirmmerge['needs_confirmation'] = ($needs_confirmation) ? 1 :0;

$mform->set_data($confirmmerge);
$mform->set_data($outcomesmerge);
$mform->set_data($evidencemerge);

// has the form been submitted?
if($mform->is_submitted()) {
    // check the validation rules
    if($mform->is_validated()) {
        // process the data
        $success = $mform->process_data($mform->get_data(), $access_isassessor, $access_iscandidate);

        if(!$success) {

            if ($access_iscandidate) {
                $error = 'cantsaveclaim';
            } else if ($access_isassessor) {
                $error = 'cantsaveassessment';
            }
            print_error($error, 'block_assmgr');
        }

        if ($access_iscandidate) {
            $return_message = 'evidenceclaimssaved';
        } else if ($access_isassessor) {
            $return_message = 'outcomeassessmentssaved';
        }

        $return_url = $CFG->wwwroot.'/blocks/assmgr/actions/edit_portfolio.php?course_id='.$course_id.'&amp;candidate_id='.$candidate_id.'#submittedevidence';
        redirect($return_url, get_string($return_message, 'block_assmgr'), REDIRECT_DELAY);
    }
}

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_submission.html');
?>