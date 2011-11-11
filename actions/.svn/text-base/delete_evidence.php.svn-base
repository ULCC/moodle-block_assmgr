<?php
/**
 * This page deletes a piece of evidence and all of it associations
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

//include the file library
require_once($CFG->libdir.'/filelib.php');

$course_id = $PARSER->required_param('course_id', PARAM_INT);
$evidence_id = $PARSER->required_param('evidence_id', PARAM_INT);
$folder_id = $PARSER->required_param('folder_id', PARAM_INT);

$dbc = new assmgr_db();

// get the evidence
$evidence = $dbc->get_evidence($evidence_id);

// get the candidate id
$candidate_id = $evidence->candidate_id;

// you must be either a candidate or an assessor to delete evidence
if(!$access_iscandidate && !$access_isassessor) {
    print_error('nodeleteevidence', 'block_assmgr');
}

if(!$access_iscandidate && $USER->id != $evidence->creator_id) {
    // assessors can't delete evidence they didn't create
    print_error('noassessownport', 'block_assmgr');
}

if(!$access_isassessor && $USER->id != $candidate_id) {
    // candidates can't delete someone else's evidence
    print_error('nodeleteothersevidence', 'block_assmgr');
}

$result = 'false';

// get the evidence and the resource
$delete_evidence = $dbc->get_evidence_resource($evidence_id);

// first find any submissions this evidence may have
$submitted = $dbc->has_submission($delete_evidence->evidence_id);

// Lock all the portfolios that this evidence is part of
$submissions = $dbc->get_submissions_by_evidence($evidence_id);

if (!empty($submissions)) {

    foreach ($submissions as $submission) {
        check_portfolio(NULL, NULL, $submission->portfolio_id);
    }
}

if(empty($submitted)) {

    // include the class for this type of evidence
    @include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$delete_evidence->resource_type}.php");

    if(!class_exists($delete_evidence->resource_type)) {

        print_error('noclassforresource', 'block_assmgr', '', $delete_evidence->resource_type);
    }

    //instantiate a instance of the evidnece resource
    $resource = new $delete_evidence->resource_type;
    //load the particular resource
    $resource->load($delete_evidence->resource_id);
    //call the class delete function

    if($resource->delete($evidence_id)) {
        $result = 'true';
        $return_message = get_string('evidencedeleted','block_assmgr');

        // MOODLE LOG evidence has been deleted
        $log_action = get_string('logevidencedelete', 'block_assmgr');
        $log_info = $delete_evidence->name.' '.get_string('deletedfrom', 'block_assmgr').' (ID '.$delete_evidence->id.')';
        assmgr_add_to_log($course_id, $log_action, null, $log_info);
    } else {
        $return_message = get_string('evidencenotdeleted','block_assmgr');
    }
} else {
    print_error('cantdeletesubmittedevidence', 'block_assmgr');
}

redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}&candidate_id={$candidate_id}&folder_id={$folder_id}#evidencefolders", $return_message, REDIRECT_DELAY);
?>