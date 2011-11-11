<?php
/**
 * This answer to the evidence and folders page.
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

// include the evidence class
require_once($CFG->dirroot.'/blocks/assmgr/classes/resources/assmgr_resource.php');

// include the flex table class
require_once($CFG->dirroot."/blocks/assmgr/classes/tables/assmgr_ajax_table.class.php");

// db class manager
$dbc = new assmgr_db();

// get the page params
$course_id = $PARSER->required_param('course_id', PARAM_INT);
$candidate_id = $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT);
$folder_id = $PARSER->optional_param('folder_id', null, PARAM_INT);

if(empty($folder_id) && $access_iscandidate) {
    // select the default folder for this course
    $folder = $dbc->get_default_folder($course_id, $candidate_id);
    $folder_id = $folder->id;
}

// stuff to prepare for generating the dropdown

$course = $dbc->get_course($course_id);

//This section of code checks th candidates quota usage.
//if found to be over quota all resources that use file storage
//are removed from the create evidence drop down.
$quota = get_user_quota($candidate_id, $course->category);
$over_quota = false;
// TODO: it should never be zero...?
if(!empty($quota)) {
    $quota_usage = get_user_quota_usage($candidate_id,$course->category);
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

    $resource_t = $dbc->get_resource_types();
    $resources = array();
    $plugins = $CFG->dirroot.'/blocks/assmgr/classes/resources/plugins';
    if (!empty($resource_t)) {
        foreach ($resource_t as $res_type) {
            require_once($plugins.'/'.$res_type->name.".php");
            $class = basename($res_type->name, ".php");
            $resourceobj = new $class();
            if (!($over_quota == true && $resourceobj->file_storage() == true) && !($resourceobj->assessor_create() == false && $access_isassessor)) {
                $resources[] = $res_type;
            }
        }
    }


// get all the evidence resource types
if (empty($resources)) {
    $resources = array();
}

$resources = assmgr_remove_disbaled_resources($resources,$course->id);

foreach ($resources as $resource) {
    $resource->name .= '_description';
}


$resource_types = assmgr_records_to_menu($resources, 'id', 'name', 'get_string', 'block_assmgr');

// Make the dropdown
echo '<div class="create_evidence">';

helpbutton('createevidence', get_string('createevidence', 'block_assmgr'), 'block_assmgr');
echo $OUTPUT->single_select(
    "{$CFG->wwwroot}/blocks/assmgr/actions/edit_evidence.php?course_id={$course_id}&amp;candidate_id="
    .$candidate_id."&amp;folder_id={$folder_id}",
    'resource_type_id',
    $resource_types,
    null,
    array(get_string('createevidencedots', 'block_assmgr'))
);

echo '</div>';



// create the flexible table for displaying the records
$flextable = new assmgr_ajax_table('evidence_table');

$flextable->define_baseurl($CFG->wwwroot."/blocks/assmgr/actions/edit_portfolio.php?course_id="
    .$course_id."&amp;candidate_id={$candidate_id}&amp;folder_id={$folder_id}");
$flextable->define_ajaxurl($CFG->wwwroot."/blocks/assmgr/actions/view_evidence_folders.ajax.php?course_id="
    .$course_id."&amp;candidate_id={$candidate_id}&amp;folder_id={$folder_id}");
$flextable->define_fragment('evidencefolders');
$flextable->nothing = 'emptyfolder';

// set the basic details to dispaly in the table
$columns = array(
    'name',
    'description',
    'timemodified'
);

$headers = array(
    get_string('name', 'block_assmgr'),
    get_string('description', 'block_assmgr'),
    get_string('date', 'block_assmgr')
);

$flextable->define_columns($columns);
$flextable->define_headers($headers);

// setup the options for the table
$flextable->sortable(true, 'name', 'ASC');

$flextable->set_attribute('summary', get_string('evidence', 'block_assmgr'));
$flextable->set_attribute('id', 'evidence-table');
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'flexible generaltable fit');

// setup the table - now we can use it
$flextable->setup();

// get evidences
if($access_isassessor) {
    $evidence = $dbc->get_evidence_by_candidate_matrix($candidate_id, $USER->id, $flextable);
} else {
    $evidence = $dbc->get_evidence_matrix($candidate_id, $folder_id, $flextable);
}

if(!empty($evidence)) {
    // get the portfolio id
    $portfolio = $dbc->get_portfolio($candidate_id, $course_id);

    foreach($evidence as $evid){
        // get the type
        $resource = $dbc->get_evidence_resource($evid->id);

        // include the class for this type of evidence
        @include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$resource->resource_type}.php");

        if(!class_exists($resource->resource_type)) {
            print_error('noclassforresource', 'block_assmgr', $resource->resource_type);
        }

        $evidence_resource = new $resource->resource_type;
        $evidence_resource->load($resource->resource_id);

        // view button
        $viewlink = $evidence_resource->get_link();

        // submit button
        $submitButton =null;

        if(!$dbc->has_submission($evid->id, $portfolio->id)) {
            $submitButton = "<a id='submit_evidence_".$evid->id."' title='".get_string('submit', 'block_assmgr')
                ."' class='SubmitBtn' href='{$CFG->wwwroot}/blocks/assmgr/actions/save_submission.php?course_id="
                .$course_id."&amp;evidence_id={$evid->id}&amp;candidate_id={$candidate_id}'><img src='"
                .$OUTPUT->pix_url('t/email') . "' class='iconsmall' alt='".get_string('submit', 'block_assmgr')."' /></a>";
        } else {
            $submitButton = "<img src='".$OUTPUT->pix_url('t/email') . "' class='iconsmall greyed_out_icon' alt='"
                .get_string('cantsubmitevidence', 'block_assmgr')."' title='".get_string('cantsubmitevidence', 'block_assmgr')."' />";
        }

        // you cannot edit or delete submitted evidences
        $editButton = '';
        $deleteButton = '';
        if(!$dbc->has_submission($evid->id)) {

            $edittitle = get_string('editevidencetitle', 'block_assmgr');
            $editUrl = "{$CFG->wwwroot}/blocks/assmgr/actions/edit_evidence.php?course_id="
                    .$course_id."&amp;resource_type_id={$resource->resource_type_id}&amp;candidate_id="
                    .$candidate_id."&amp;evidence_id={$evid->id}&amp;folder_id={$evid->folder_id}";
            $editButton = "<a title='{$edittitle}' class='EditBtn' href='{$editUrl}'>
                               <img src='".$OUTPUT->pix_url('t/edit')."' class='iconsmall' alt='"
                               .get_string('editevidencetitle', 'block_assmgr')."' />
                           </a>";

            $deletetitle = get_string('delete', 'block_assmgr');
            $deleteUrl = "{$CFG->wwwroot}/blocks/assmgr/actions/delete_evidence.php?course_id="
                    .$course_id."&amp;evidence_id={$evid->id}&amp;folder_id={$evid->folder_id}";
            $deleteButton = "<a title='{$deletetitle}' class='DeleteBtn' href='{$deleteUrl}'>
                                 <img src='".$OUTPUT->pix_url('t/delete')."' class='iconsmall' alt='"
                                 .get_string('delete', 'block_assmgr')."' />
                             </a>";
        } else {

            $edittitle = get_string('canteditsubmittedevidence', 'block_assmgr');
            $editButton = "<img src='".$OUTPUT->pix_url('t/edit')."' class='iconsmall greyed_out_icon' alt='".$edittitle."' title='".$edittitle."' />";

            $deletetitle = get_string('cantdeletesubmittedevidence', 'block_assmgr');
            $deleteButton = "<img src='".$OUTPUT->pix_url('t/delete')."' class='iconsmall greyed_out_icon' alt='".$deletetitle."' title='".$deletetitle."' />";

        }

        // assessors cannot move evidence
        $movebutton = '';
        /*
        if(!$access_isassessor) {
            $movebutton = "<a id='move_evidence_".$evid->id."' title='"
                            .get_string('moveevidence', 'block_assmgr')."' class='MoveBtn' href='"
                            .$CFG->wwwroot."/blocks/assmgr/actions/edit_evidence.php?course_id=".
                            $course_id."&amp;resource_type_id={$resource->resource_type_id}&amp;candidate_id="
                            .$candidate_id."&amp;evidence_id={$evid->id}&amp;folder_id="
                            .$evid->folder_id."'><img src='".$OUTPUT->pix_url('i/move_2d')
                            ."' class='iconsmall' alt='".get_string('moveevidence', 'block_assmgr')."' /></a>";
        }*/

        // add the data to the array
        $data = array();
        $data["name"] = $viewlink.'<span class="commands">'.$editButton.$deleteButton.$submitButton.$movebutton.'</span>';
        $data["description"] = limit_length($evid->description, 60);
        $data["timemodified"] = userdate($evid->timemodified, get_string('strftimedate', 'langconfig'));

        $flextable->add_data_keyed($data);
    }
}


// print the table
$flextable->print_html();
?>