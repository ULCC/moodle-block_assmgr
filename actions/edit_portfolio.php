<?php
/**
 * This page displays all the submissions made in a portfolio, from either
 * the candidate's or the assessor's perspective, depending on the candidate_id
 * and the user's capabilities
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.5
 */

//include moodle config

require_once('../../../config.php');

global $USER, $CFG, $PARSER,$PAGE;

// Meta includes
require_once($CFG->dirroot.'/blocks/assmgr/actions_includes.php');

//get the id of the course that is currently being used
$course_id = $PARSER->required_param('course_id', PARAM_INT);

if ($course_id == SITEID) {
    print_error('errorinsitecourse','block_assmgr');
}

$coursecontext = get_context_instance(CONTEXT_COURSE, $course_id);

$PAGE->set_context($coursecontext);

$candidate_id = $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT);

// instantiate the db
$dbc = new assmgr_db();


// you must be either a candidate or an assessor to edit a portfolio
if(!$access_iscandidate && !$access_isassessor) {
    print_error('noeditportfoliopermission','block_assmgr');
}

if($access_iscandidate && $USER->id != $candidate_id) {
    // candidates can't edit someone else's portfolio
    print_error('noeditothersportfolio', 'block_assmgr');
}

if($access_isassessor) {
    // assessors can't assess their own portfolio
    if($USER->id == $candidate_id) {
        print_error('cantassessownportfolio', 'block_assmgr');
    }

    // make sure the candidate is actually a candidate in this context
    $iscandidate = has_capability('block/assmgr:creddelevidenceforself', $coursecontext, $candidate_id, false);

    if(!$iscandidate) {
        print_error('portfolionotincourse', 'block_assmgr');
    }
}

// get the candidate, course and category
$candidate = $dbc->get_user($candidate_id);
$course = $dbc->get_course($course_id);
$coursecat = $dbc->get_category($course->category);

// get the portfolio if it exists
$portfolio_id = check_portfolio($candidate_id, $course_id);


// get the configuration for this instance
$config = $dbc->get_instance_config($course_id);

// is the current portfolio locked?
if($dbc->lock_exists($portfolio_id)) {
    // renew the lock if it belongs to the current user
    if($dbc->lock_exists($portfolio_id, $USER->id)) {
        $dbc->renew_lock($portfolio_id, $USER->id);
    } else {
        // otherwise throw an error
        print_error('portfolioinuse', 'block_assmgr');
    }
} else {
    // create a new lock
    $dbc->create_lock($portfolio_id, $USER->id);
}


if($access_isassessor) {
    // references to the candidate should be in the 3rd person
    $page_heading = get_string('candidateportfolio', 'block_assmgr', fullname($candidate));
} else {
    // references to the candidate should be in the 1st person
    $page_heading = get_string('myportfolio', 'block_assmgr');
}

// setup the navigation breadcrumbs
$PAGE->navbar->add(get_string('blockname', 'block_assmgr'),null,'title');

if($access_isassessor) {
    // assessor breadcrumbs
	$PAGE->navbar->add($coursecat->name,$CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$coursecat->id}",'title');
	$PAGE->navbar->add($course->shortname,$CFG->wwwroot."/blocks/assmgr/actions/list_portfolio_assessments.php?course_id={$course->id}",'title');
	$PAGE->navbar->add(fullname($candidate),null,'title');

} else {
	// candidate breadcrumbs
	$PAGE->navbar->add(get_string('myportfolio', 'block_assmgr'),null,'title');
}

// setup the page title and heading
$PAGE->set_title($course->shortname.': '.get_string('blockname','block_assmgr'));
$PAGE->set_heading($course->fullname);
$PAGE->set_url('/blocks/assmgr/actions/edit_portfolio.php', $PARSER->get_params());


//MOODLE LOG candidate portfolio viewed

$log_action = get_string('logportfolioview', 'block_assmgr');
$log_url = "edit_portfolio.php?course_id={$course_id}&amp;candidate_id={$candidate_id}";
$logstrings = new stdClass;
$logstrings->name = fullname($candidate);
$logstrings->course = $course->shortname;
$log_info = get_string('logportfolioviewinfo', 'block_assmgr', $logstrings);
assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

$param = $dbc->get_instance_config($course->id);

require_once($CFG->dirroot.'/blocks/assmgr/views/edit_portfolio.html');
?>