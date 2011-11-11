<?php
/**
 * Perfrorms permissions checks against the user to see what they are allowed to
 * do, which are stored as boolean values in local variables.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */


global $CFG, $PARSER;

// get the id of the course
$course_id = $PARSER->required_param('course_id', PARAM_INT);

// the user must be logged in
require_login(0, false);

// disable debugging for this function call as the course may not exist
$debug = $CFG->debug;
$CFG->debug = false;

// get the current course context
$coursecontext = get_context_instance(CONTEXT_COURSE, $course_id);

// restore debugging
$CFG->debug = $debug;

// bail if we couldn't find the course context
if(!$coursecontext) {
    print_error('incorrectcourseid', 'block_assmgr');
}


// IE 6 check
$is_ie6 = stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6');
if ($is_ie6) {
    print_error('incompatablebrowserie6', 'block_assmgr');
}

/**
 * ROLE based flags
 */
// user is a candidate if they can make their own evidence (admins are not candidates)
$access_iscandidate = has_capability('block/assmgr:creddelevidenceforself', $coursecontext, $USER->id, false);

// user is an assessor if they can assess portfolios (admins are assessors)
$access_isassessor = has_capability('block/assmgr:assessportfolio', $coursecontext);

// user is a verifyer if they can verify portfolios (admins are verifiers)
$access_isverifier = has_capability('block/assmgr:verifyportfolio', $coursecontext);

// Put the course breadcrumb back for the candidates.
if ($access_iscandidate && !$access_isassessor) {
    require_login($course_id);
}

/**
 * CAPABILITY based flags
 */
// can the user view others portfolios
$access_otherssub = has_capability('block/assmgr:viewothersportfolio', $coursecontext);

// can the user create thier own evidence
$access_cancreeddel = has_capability('block/assmgr:creddelevidenceforself', $coursecontext);

// can the user create evidence for other users
$access_cancreeddelforothers = has_capability('block/assmgr:creddelevidenceforothers', $coursecontext);

// can the user access other users evidence
$access_othersevid = has_capability('block/assmgr:viewothersevidence', $coursecontext);

// can the user confirm other users evidence
$access_canconfirm = has_capability('block/assmgr:confirmevidence', $coursecontext);

// TODO Can the user view fullnames. Not currently used but maybe we should for consistency with the
// rest of Moodle?
$access_canviewfullname = has_capability('moodle/site:viewfullnames', $coursecontext);

// Can the user view user profiles?
$access_canviewuserdetails = has_capability('moodle/user:viewdetails', $coursecontext);


?>