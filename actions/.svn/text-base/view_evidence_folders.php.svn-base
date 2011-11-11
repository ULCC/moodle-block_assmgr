<?php
/**
 * This displays the evidence and folders that the user created.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

if (!defined('MOODLE_INTERNAL')) {
    // this must be included from a Moodle page
    die('Direct access to this script is forbidden.');
}

global $CFG;

//This section of code checks the candidates quota usage.
//if found to be over quota all resources that use file storage
//are removed from the create evidence drop down.
$quota = get_user_quota($candidate_id,$course->category);
$over_quota = false;
// TODO: it should never be zero...?
if(!empty($quota)) {
    $quota_usage = get_user_quota_usage($candidate_id, $course->category);
    $quota_in_bytes  = $quota * 1024 * 1024;
    $quota_usage_percentage = round(($quota_usage/$quota_in_bytes) * 100,2);

    // print string
    $a = new stdClass;
    $a->quota = $quota;
    $a->quota_usage = formatfilesize($quota_usage);
    $a->percentage = $quota_usage_percentage;

    if ($quota_usage_percentage > 100) {
        $over_quota = true;
    }
}

if (!empty($over_quota)) {
    $resource_t = $dbc->get_resource_types();
    $resources = array();
    $plugins = $CFG->dirroot.'/blocks/assmgr/classes/resources/plugins';
    if (!empty($resource_t)) {
        foreach ($resource_t as $res_type) {
            require_once($plugins.'/'.$res_type->name.".php");
            $class = basename($res_type->name, ".php");
            $resourceobj = new $class();
            if ($resourceobj->file_storage() == false) {
                $resources[] = $res_type;
            }
        }
    }
} else {
    $resources = $dbc->get_resource_types();
}


// get all the evidence resource types
if (empty($resources)) {
    $resources = array();
}
foreach ($resources as $resource) {
    $resource->name .= '_description';
}

$resource_types = assmgr_records_to_menu($resources, 'id', 'name', 'get_string', 'block_assmgr');



// sort the localised names into alphabetical order, preserving keys
asort($resource_types);

$url = $CFG->wwwroot.'/blocks/assmgr/actions/view_evidence.php?course_id='.$course_id;
$url2 = $CFG->wwwroot.'/blocks/assmgr/actions/edit_evidence.php?candidate_id='.$candidate_id.'&amp;course_id='.$course_id.'&amp;portfolio_id='.$portfolio_id;

$param = array();
$param["url"] = $url;
$param["url2"] = $url2;
$param["ty"] = "normal";
$param["course_id"] = $course_id;
$param["candidate_id"] = $candidate_id;
$param["wwwroot"] = $CFG->wwwroot;
$param["fragment"] = 'evidencefolders';
$param["portfolio_id"] = $portfolio_id;

if($access_iscandidate) {
    // the candidate can see all their evidence, and all their folders
    $folders = $dbc->get_child_folders($candidate_id, null);
    if(empty($folders)) {
       $folders = array();
    }

    if($dbc->evidence_exists($candidate_id)) {
        $root = new object();
        $root->id = null;
        $root->name = "root";
        $folders[] = $root;
        $fold = build_folders($folders, $param, 0, $folder_id);
    } else {
        $noevidstr = get_string('noevidence','block_assmgr');
        if(!empty($folders)) {
            $fold = build_folders($folders, $param, 0, $folder_id);
        } else {
            $fold['htmloutput'] = get_string('noevidencefolder','block_assmgr');
            $fold['javascriptoutput'] = "";
            $fold['javascripteventsoutput'] = "";
            $fold['cssoutput'] = "";
        }
    }
}

// render the evidence
require_once($CFG->dirroot.'/blocks/assmgr/views/view_evidence_folders.html');
?>