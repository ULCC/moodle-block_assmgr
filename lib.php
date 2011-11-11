<?php
/**
 * Library of assorted functions for the Assessment Manager.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

/**
 * Creates a string containing html with all child folders and evidence for the folder
 * Creates also a string containing the javascript defition of the YUI TreeView
 *
 * @param array $folders the folder
 * @param array $param array containing various parameters needed to build output
 * @param int $depth the current depth of recursion
 * @param int $selected_folder_id the folder_id in case a folder has been selected
 * @return array containing html folder output, javascript folder output and the hierachical folder tree
 */
function build_folders($folders, $param, $depth, $selected_folder_id = null) {
    global $CFG, $USER, $OUTPUT;

    // include and instantiate the db class
    static $dbc;
    if (empty($dbc)) {
        require_once($CFG->dirroot."/blocks/assmgr/db/assmgr_db.php");
        $dbc = new assmgr_db();
    }

    $returnHtmloutput = '';
    $usersfolder = array();

    // tree icons directory
    $treeIcons = $CFG->wwwroot.'/blocks/assmgr/pix/tree/folders/';

    if(!empty($folders)) {
        //make a div and start a list
        $returnHtmloutput .= "<ul> ";

        //loop through the contents of the folders
        foreach ($folders as $folder) {

            if($folder->id != null) {
                // get the sub-folders
                $subfolders = $dbc->get_child_folders($param['candidate_id'], $folder->id);

                $class = ($folder->id == $selected_folder_id) ? 'expanded highlight' : '';

                //add a list item to be output
                $returnHtmloutput .= "<li class='{$class}'> ";

                $name = limit_length($folder->name, 33);

                // check if the node has children to determine the proper style
                if(empty($subfolders)) {
                    $name = '<span class=\'assmgr_tree_folder_close\'>'.$name.'</span>';
                }

                $openurl = "{$param['wwwroot']}/blocks/assmgr/actions/edit_portfolio.php?course_id={$param['course_id']}&amp;folder_id={$folder->id}#{$param['fragment']}";
                $ajaxurl = "{$param['wwwroot']}/blocks/assmgr/actions/view_evidence_folders.ajax.php?course_id={$param['course_id']}&amp;folder_id={$folder->id}";
                $openButton = "<a href='{$openurl}' onclick=\"set_folder_highlight(this); return ajax_request('evidence_table_container', '{$ajaxurl}');\">{$name}</a>";

                $editButton = "";
                $deleteButton = "";

                //find the folder with that name that belongs to the user
                $default_folder  = $dbc->get_default_folder($param["course_id"], $USER->id);

                // only if is not the default folder
                if( ($folder->id != $default_folder->id) && ($folder->folder_id != null) ) {
                    // add the folder edit button
                    $editButton = "<a id='edit_folder_".$folder->id."' title='".get_string('editfolder', 'block_assmgr')."' class='EditBtn' href='{$param['wwwroot']}/blocks/assmgr/actions/edit_folder.php?course_id={$param['course_id']}&amp;folder_id={$folder->id}'><img src='".$OUTPUT->pix_url('t/edit') . "' class='iconsmall' alt='".get_string('editfolder', 'block_assmgr')."' /></a>";

                    // add the folder delete button
                    $deleteButton = "<a id='delete_folder_".$folder->id."' title='".get_string('delete', 'block_assmgr')."' class='DeleteBtn' href='{$param['wwwroot']}/blocks/assmgr/actions/delete_folder.php?folder_id={$folder->id}&amp;course_id={$param['course_id']}'><img src='".$OUTPUT->pix_url('t/delete') . "' class='iconsmall' alt='".get_string('delete', 'block_assmgr')."' /></a>";
                }

                $returnHtmloutput .= '<span>'.$openButton."<span class='commands'>".$editButton.$deleteButton."</span></span>";

                $usersfolder[$folder->name] = null;

                if(!empty($subfolders) && $folder->id != null) {

                    $usersfolder[$folder->name] = build_folders($subfolders, $param, $depth, $selected_folder_id);

                    if(!empty($usersfolder[$folder->name]['htmloutput'])) {
                       $returnHtmloutput .= $usersfolder[$folder->name]['htmloutput'];
                    }
                }

                $returnHtmloutput .= "</li>";
            }
        }
    }
    $returnHtmloutput .= "</ul>";

    // to avoid errors I have to "clean" the strings before to return them
    $usersfolder['htmloutput'] = str_replace("<li> </li>", "",$returnHtmloutput);

    return $usersfolder;
}


/**
 * Gets a HTML string showing the current status of a piece of evidence. Not currently used
 *
 * @param $evidence evidence object
 * @return string
 */
function evidence_status($evidence) {
    global $CFG;

    // include and instantiate the db class
    static $dbc;
    if (empty($dbc)) {
        require_once($CFG->dirroot."/blocks/assmgr/db/assmgr_db.php");
        $dbc = new assmgr_db();
    }

    $evStat = array();

    if($evidence->candidate_id != $evidence->creator_id) {
        $evStat[] = get_string('createdbyanassessor', 'block_assmgr');
    }

    if($dbc->has_submission($evidence->id)) {
        $evStat[] = get_string('submitted', 'block_assmgr');
    } else {
        $evStat[] = get_string('notsubmitted', 'block_assmgr');
    }

    if($dbc->has_confirmation($evidence->id)) {
        $conf = $dbc->get_confirmation($evidence->id);
        $evStat[] = confirmation_status($conf->status);
    }

    if(empty($evStat)) {
        $evStat[] = get_string('unknown', 'block_assmgr');
    }

    return implode('<br/>', $evStat);
}

/**
 * Return a human readable HTML description or icon for a supplied status constant
 *
 * @param int $status the status code.
 * @param bool $image (optional, defaults to false) Do want an image instead of text returned?
 * @return string HTML of icon or text
 */
function confirmation_status($status, $image = false) {
    global $CFG;

    switch ($status) {
        case CONFIRMATION_PENDING:
            $token = 'confirmationpending';
            $icon = 'tick_amber_small.gif';
            break;
        case CONFIRMATION_CONFIRMED:
            $token = 'confirmationconfirmed';
            $icon = 'tick_green_small.gif';
            break;
        case CONFIRMATION_REJECTED:
            $token = 'confirmationrejected';
            $icon = 'cross_red_small.gif';
            break;
        default:
            $token = 'unknown';
            break;
    }

    $text = get_string($token, 'block_assmgr');

    if($image) {
        return "<img src='{$CFG->wwwroot}/blocks/assmgr/pix/icons/{$icon}' alt='{$token}' title='{$text}' width='16' height='16' />";
    } else {
        return "<span class='{$token}'>{$text}</span>";
    }
}

/**
 * Checks to see if a portfolio has gone over it's allocated size limit
 *
 * @param int $portsize in bytes
 * @param int $maxsize in megabytes
 * @return bool true if too big
 */
function portfolio_over_size_limit($portsize, $maxsize) {
    $maxsize = $maxsize * 1024;
    $maxsize = $maxsize  * 1024;
    if($portsize > $maxsize) {
        return true;
    } else {
        return false;
    }
}

/**
 * Generates a random number for when something needs to be identified
 * uniquely.
 *
 * @return int A random number
 */
function uniqueNum() {
    return rand().time();
}

/**
 * Deletes a directory
 * TODO this will not work for directories with more than one level of nesting
 *
 * @param string $dir the path to the directory to be deleted
 * @return bool did the delete work?
 */
function deleteDirectory($dir) {
    if(!file_exists($dir))
        return true;

    if(!is_dir($dir) || is_link($dir))
        return unlink($dir);

    foreach (scandir($dir) as $item) {
        if($item == '.' || $item == '..')
            continue;

        if(!deleteDirectory($dir . "/" . $item)) {
            chmod($dir . "/" . $item, 0777);
            if(!deleteDirectory($dir . "/" . $item))
                return false;
        };
    }
    return rmdir($dir);
}

/**
 * Returns the size of a directory in bytes and number of files/sub directories
 *
 * @param string the path to the directory
 * @return array the total size, file count and directory count of the directory's contents
 */
function getDirectorySize($path) {
    $totalsize = 0;
    $totalcount = 0;
    $dircount = 0;
    if($handle = opendir ($path)) {
        while (false !== ($file = readdir($handle))) {
            $nextpath = $path . '/' . $file;
            if($file != '.' && $file != '..' && !is_link ($nextpath)) {
                if(is_dir ($nextpath)) {
                    $dircount++;
                    $result = getDirectorySize($nextpath);
                    $totalsize += $result['size'];
                    $totalcount += $result['count'];
                    $dircount += $result['dircount'];
                } elseif(is_file ($nextpath)) {
                    $totalsize += filesize ($nextpath);
                    $totalcount++;
                }
            }
        }
    }
    closedir ($handle);
    $total['size'] = $totalsize;
    $total['count'] = $totalcount;
    $total['dircount'] = $dircount;
    return $total;
}

/**
 * Takes a number of bytes and turns it into a shorter, rounded form with units e.g. MB, GB
 *
 * @param int $size the number of bytes
 * @return string the rounded number with units
 */
function sizeFormat($size) {
    if($size<1024) {
        return $size." bytes";
    } elseif($size<(1024*1024)) {
        $size=round($size/1024,1);
        return $size." KB";
    } elseif($size<(1024*1024*1024)) {
        $size=round($size/(1024*1024),1);
        return $size." MB";
    } else {
        $size=round($size/(1024*1024*1024),1);
        return $size." GB";
    }
}

/**
 * Adds arecord of an action to the log
 *
 * @param int $course_id
 * @param string $log_action the generic short name for the event
 * @param string $log_url (optional, defaults to ASSMGR_LOG_URL_PREFIX)
 * @param string $log_info Detailed explanation of what has happened
 * @return void
 */
function assmgr_add_to_log($course_id, $log_action, $log_url, $log_info) {

    // prepend the url prefix if the log_url is not empty
    $log_url = empty($log_url) ? '' : ASSMGR_LOG_URL_PREFIX.'/'.$log_url;

    // add to the moodle log
    add_to_log($course_id, ASSMGR_LOG_MODULE, $log_action, $log_url, $log_info);
}

/**
 * Utility function which makes a recordset into an array
 * Similar to recordset_to_menu. Array is keyed by the specified field of each record and
 * either has the second specified field as the value, or the results of the callback function which
 * takes the second field as it's first argument
 *
 * field1, field2 is needed because the order from get_records_sql is not reliable
 * @param records - records from get_records_sql() or get_records()
 * @param field1 - field to be used as menu index
 * @param field2 - feild to be used as coresponding menu value
 * @param string $callback (optional) the name of a function to call in order ot generate the menu item for each record
 * @param string $callbackparams (optional) the extra parameters for the callback function
 * @return mixed an associative array, or false if an error occured or the RecordSet was empty.
 */
function assmgr_records_to_menu($records, $field1, $field2, $callback = null, $callbackparams = null) {

    $menu = array();

    if(!empty($records)) {
        foreach ($records as $record) {
            if(empty($callback)) {
                $menu[$record->$field1] = $record->$field2;
            } else {
                // array_unshift($callbackparams, $record->$field2);
                $menu[$record->$field1] = call_user_func_array($callback,array($record->$field2,$callbackparams));
            }
        }

    }
    return $menu;
}


/**
 * Removes any resources from the given array that are disabled in either the global or instance config.
 *
 * @param array $resources the array containing the resources
 * @param int $userid optional id of the user. If 0 then $USER->id is used.
 * @param bool $return optional defaults to false. If true the list is returned rather than printed
 * @return string HTML
 */
function assmgr_remove_disbaled_resources($resources,$course_id=null) {

    $dbc = new assmgr_db();

    $instance_config  = (array) $dbc->get_instance_config($course_id);

    $resource_array = array();
    $resource_temp = array();

    $globalconfig = get_config('block_assmgr');

     foreach($globalconfig as $setting => $value) {
         if(substr($setting, 0, 16) == 'assmgr_resource_') {
              foreach ($resources as $resource) {
                   if ($setting == $resource->name && !empty($value)) {
                        array_push($resource_temp,$resource);
                   }
              }
         }
     }
    if (!empty($instance_config)) {
        foreach ($resource_temp as $resource) {
            if (isset($instance_config[$resource->name])) {
                if (!empty($instance_config[$resource->name])) {
                     array_push($resource_array,$resource);
                }
            }

        }
    } else {
        $resource_array = $resource_temp;
    }

    return $resource_array;
}


/**
 * Produces a link to a file uploaded by a user. Not used yet or finished.
 *
 * @param string $file the name of the file
 * @param int $userid optional id of the user. If 0 then $USER->id is used.
 * @param bool $return optional defaults to false. If true the list is returned rather than printed
 * @return string HTML
 */
function print_user_files($file, $userid=0, $return=false) {
    global $CFG, $USER;

    if (!$userid) {
        if (!isloggedin()) {
            return '';
        }
        $userid = $USER->id;
    }

    $filearea = "";

    $icon = mimeinfo('icon', $file);
    $ffurl = get_file_url("$filearea/$file", array('forcedownload'=>1));

    $output .= '<img src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
            '<a href="'.$ffurl.'" >'.$file.'</a><br />';

    if ($return) {
        return $output;
    }
    echo $output;
}

/**
 * quick fix to sort out file paths on windows
 *
 * @param string $path the path the fix
 * @param array $override_array that enables the user to set the str_replaced regardless of
 * OS. e.g array('replace' => 'new_value');
 * @return string path with different slashes
 */
function filepath_fix($path,$override_array = null) {
    $slashes = (stristr($_SERVER['SERVER_SOFTWARE'], 'Win')) ? "\\" : "/";
    $badslashes = (stristr($_SERVER['SERVER_SOFTWARE'], 'Win')) ? "/" : "\\";
    if (!empty($override_array) && is_array($override_array)) {
        $badslashes = key($override_array);
        $slashes = current($override_array);
    }
    $path = str_replace($badslashes, $slashes, $path);
    return $path;
}

/**
 * Scans a directory for viruses
 *
 * @param string $dir directory path
 * @return bool did it scan OK?
 */
function clam_scan_moodle_dir($dir) {
    global $CFG, $USER;

    //if the parameter is not a dir or file we will not scan
    if (!is_dir($dir) && !file_exists($dir)) {
        return false;
    }

    $CFG->pathtoclam = trim($CFG->pathtoclam);

    //if clam scan does not exist or the given url is not a executable we will not scan
    if (!$CFG->pathtoclam || !file_exists($CFG->pathtoclam) || !is_executable($CFG->pathtoclam)) {
        return false;
    }

    $cmd = $CFG->pathtoclam .' '. $dir ." 2>&1";

    // before we do anything we need to change perms so that clamscan can read the file (clamdscan won't work otherwise)
    chmod($dir, 0644);

    exec($cmd, $output, $return);

    switch ($return) {
        case 0: // glee! we're ok.
            return 1; // translate clam return code into reasonable return code consistent with everything else.
        case 1:  // bad wicked evil, we have a virus.

            $info->user = fullname($USER);
            $notice = get_string('virusfound', 'moodle', $info);
            $notice .= "\n\n". implode("\n", $output);
            $notice .= "\n\n". clam_handle_infected_file($dir);
            clam_mail_admins($notice);

            return false; // in this case, 0 means bad.
        default:
            // error - clam failed to run or something went wrong
            $notice .= get_string('clamfailed', 'moodle', get_clam_error_code($return));
            $notice .= "\n\n". implode("\n", $output);
            $newreturn = true;
            if ($CFG->clamfailureonupload == 'actlikevirus') {
                $notice .= "\n". clam_handle_infected_file($dir);
                $newreturn = false;
            }
            clam_mail_admins($notice);

            return $newreturn; // return 1 if we're allowing failures.
    }
}

/**
 * Creates a directory file name, suitable for make_upload_directory()
 *
 * @param $userid int The user id
 * @return string path to file area
 */
function assmgr_evidence_folder($user_id, $record_id) {
    global $CFG;

    require_once($CFG->libdir."/moodlelib.php");

    $level1 = floor($user_id / 1000) * 1000;


    $dir = "user/{$level1}/{$user_id}/block_assmgr/evidence/{$record_id}";
    // check if the folder exists already and create it if not
    check_dir_exists($CFG->dataroot.'/'.$dir, true, true);

    return $dir;
}

/**
 * Utility function
 * It returns the upload dir for a submission feedback, starting from the course_id and the submission_id
 * It also checks if the folder exists and in case no, it creates the folder
 *
 * @param $course_id - The ID of the course
 * @param $submission_id - The ID of the submission
 * @return string - The upload directory path
 */
function assmgr_submission_folder($user_id, $submission_id) {
    global $CFG;

    require_once($CFG->libdir."/moodlelib.php");

    $level1 = floor($user_id / 1000) * 1000;

    $dir = "user/{$level1}/{$user_id}/block_assmgr/submission/{$submission_id}";
    // check if the folder exists already and create it if not
    check_dir_exists($CFG->dataroot.'/'.$dir, true, true);

    return $dir;
}

/**
 * gets the quota the total quota that a user has in a course_category
 *
 * @param $candidate_id int The user id of the candidate
 * @param $course_category int The id of the course category the user is in
 * @return string path to file area
 */
function get_user_quota($candidate_id,$course_category) {
    $quota = 0;
    $dbc = new assmgr_db();
    $enrolled_courses = $dbc->get_enrolled_courses($candidate_id,$course_category);
    if (!empty($enrolled_courses)) {
        foreach ($enrolled_courses  as $ecourse) {
            $instance_config = $dbc->get_instance_config($ecourse->id);
            $quota += (!empty($instance_config->portfolio_quota)) ? $instance_config->portfolio_quota : 0;
        }
    }
    return $quota;
}


function get_user_quota_usage($candidate_id) {
    global $CFG;

    $dbc = new assmgr_db();
    $plugins = $CFG->dirroot.'/blocks/assmgr/classes/resources/plugins';

    $candidate_evidence = $dbc->get_evidence_by_candidate($candidate_id,$candidate_id);

    $usage = 0;
    if  (!empty($candidate_evidence)) {
        foreach ($candidate_evidence as $evidence) {

            $evidence_resource = $dbc->get_evidence_resource($evidence->id);

            if (!empty($evidence_resource)) {
                $resource = $dbc->get_resource_plugin("{$evidence_resource->tablename}",$evidence_resource->record_id);

                 require_once($plugins.'/'.$evidence_resource->resource_type.".php");
                 // instantiate the object
                $class = basename($evidence_resource->resource_type, ".php");
                $resourceobj = new $class();
                $method = array($resourceobj, 'size');

                 //check whether the config_settings method has been defined
                if (is_callable($method,true)) {
                    $resourceobj->load($evidence_resource->resource_id);
                    $usage += $resourceobj->size();
                }
            }
        }
    }

    return $usage;
}

/**
 * This function is called from the main block file when it is rendered, so that
 * if the block has not yet been set up correclty, the user will see an error message
 * explaining what needs to be done.
 *
 * @return an array of items to be used instead of the original block contents
 */
function problems_with_block_setup($course_id) {

    global $CFG, $DB, $SITE;

    require_once($CFG->dirroot.'/lib/grade/constants.php');

    //$courseid = required_param('id', PARAM_INT);

    // The return array will replace the block's contents if there are errors
    $errorstoreturn = array();
    // Flag that gets switched to false if any of the setup checks fail
    $setupcorrectly = true;

    // Does the block have an overall configuration (whole site)?
    $blockconfig = get_config('block_assmgr');

    if (!$blockconfig) {
        $linkstart = '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=blocksettingassmgr" >';
        $errorstoreturn[] = get_string('blocknoconfig', 'block_assmgr', $linkstart);
    }

    // Are outcomes enabled at site level?
    $release = array_shift(explode(' ', $CFG->release));
    $linkstart = '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=gradessettings" >';

    if ($release >= 2.0) {

        if (!$CFG->enableoutcomes == 1) {
            $errorstoreturn[] = get_string('blocknooutcomesetting', 'block_assmgr', $linkstart);
        }

    } else {

        if (!$CFG->enableoutcomes == 2) {
            $errorstoreturn[] = get_string('blocknooutcomesetting', 'block_assmgr', $linkstart);
        }
    }

    // Are scales set to be included in aggregation across the site?
    if ($CFG->grade_includescalesinaggregation) {
        $linkstart = '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=gradessettings" >';
        $errorstoreturn[] = get_string('blocknoscalesetting', 'block_assmgr', $linkstart);
    }

    if ($course_id != $SITE->id) {

        // Does the course have any outcomes associated with it yet?
        $sql = "SELECT COUNT(go.id)
                        FROM {grade_outcomes_courses} go, {course} c
                       WHERE go.courseid=c.id
                         AND c.id={$course_id}";

        if (!$DB->count_records_sql($sql)) {
            $linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/outcome/course.php?id='.$course_id.'" >';
            $errorstoreturn[] = get_string('blocknooutcomes', 'block_assmgr', $linkstart);
        }

        //Get the course grade item and category
        $sql = "courseid={$course_id} AND itemtype='course'";
        $courseitem = $DB->get_record_select('grade_items', $sql);

        $sql = "courseid={$course_id} AND parent IS NULL";
        $coursecategory = $DB->get_record_select('grade_categories', $sql);

        // does the course category exist and have the right aggregation type
        if (!$coursecategory) {
            $linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/tree/index.php?showadvanced=0&id='.$course_id.'" >';
            $errorstoreturn[] = get_string('blockcategoryexist', 'block_assmgr', $linkstart);
        } else if ($coursecategory->aggregation == GRADE_AGGREGATE_SUM) {
            $linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/tree/category.php?courseid='.$course_id.'&id='.$coursecategory->id.'&gpr_type=edit&gpr_plugin=tree&gpr_courseid='.$course_id.'" >';
            $errorstoreturn[] = get_string('blockcategoryaggscale', 'block_assmgr', $linkstart);
        }

        // Is the grade category setting for aggregating grade outcomes set to off?
        $sitelevel = $CFG->grade_aggregateoutcomes;
        $siteforce = (($CFG->grade_aggregateoutcomes_flag == 1) || ($CFG->grade_aggregateoutcomes_flag == 3));
        $courselevel = $coursecategory->aggregateoutcomes;

        if ($sitelevel && $siteforce) {

            // Site setting is on and set to force. Doesn't matter what the course level setting is
            $linkstart = '<a href="'.$CFG->wwwroot.'/admin/settings.php?section=gradecategorysettings" >';
            $errorstoreturn[] = get_string('blocknositeoutcomesaggregation', 'block_assmgr', $linkstart);

        } else if (!$siteforce && $courselevel) {

            // Is it set at course level?
            if ($coursecategory->aggregateoutcomes) {
                // Need to turn off course level aggregation
                $a = new stdClass;
                $a->linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/tree/category.php?courseid='.$course_id.'&id='.$coursecategory->id.'&gpr_type=edit&gpr_plugin=tree&gpr_courseid='.$course_id.'" >';                $errorstoreturn[] = get_string('blockitemexist', 'block_assmgr', $linkstart);
                $a->advanced = ($CFG->grade_aggregateoutcomes_flag & 2) ? ' '.get_string('clickadvanced', 'block_assmgr') : '';
                $errorstoreturn[] = get_string('blockcategoryoutcomesaggregated', 'block_assmgr', $a);
            }
        }


        // Does the course level grade item exist and have the right aggregation type?
        if (!$courseitem) {
            $linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/tree/index.php?showadvanced=0&id='.$course_id.'" >';
            $errorstoreturn[] = get_string('blockitemexist', 'block_assmgr', $linkstart);
        } else {
            // If the item exists, the category will too, so we can assume it's there for use in the link
            if (!($courseitem->gradetype == GRADE_TYPE_SCALE)) {
                $linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/tree/category.php?courseid='.$course_id.'&id='.$coursecategory->id.'&gpr_type=edit&gpr_plugin=tree&gpr_courseid='.$course_id.'" >';
                $errorstoreturn[] = get_string('blockitemgradetypewrong', 'block_assmgr', $linkstart);
            }
            if (!$courseitem->scaleid) {
                $linkstart = '<a href="'.$CFG->wwwroot.'/grade/edit/tree/category.php?courseid='.$course_id.'&id='.$coursecategory->id.'&gpr_type=edit&gpr_plugin=tree&gpr_courseid='.$course_id.'" >';
                $errorstoreturn[] = get_string('blockitemnoscale', 'block_assmgr', $linkstart);
            }
        }
    }

    // Capability check - if the user is an admin they need to know what to fix
    // otherwise a generic message
    $isadmin = false;

    $coursecontext = get_context_instance(CONTEXT_COURSE, $course_id);

    if (has_capability('moodle/site:manageblocks', $coursecontext)) {
        $isadmin = true;
    }

    // Final display/return of errors
    if (empty($errorstoreturn)) {
        return false;
    } else {

        if ($isadmin) {
            return $errorstoreturn;
        } else {
            return array(get_string('blockmissingparameters', 'block_assmgr'));
        }
    }
}

/**
 * This function converts a given filesize in bytes to the most logical output
 * bytes Megabytes gigabytes
 * @param int $bytes the filesize in bytes
 * @param int precision the precision to which the result should be returned
 *
 * @return string the bytes in there most appropriate human readable form
 */
function formatfilesize($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * This function recursivly deletes the given directory and files direcotries
 * within it
 * @param string $dir the directory
  */
function remove_directory($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") remove_directory($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }

/**
 * Wrapper for native assmgr_build_navigation() function that truncates the length of
 * each of the breadcrumbs to ensure that they all fit neatly on the page
 */
function assmgr_build_navigation($breadcrumbs) {

    // determine the total length of all the breadcrumbs
    $length = 0;
    foreach($breadcrumbs as $crumb) {
        $length += strlen($crumb['name']);
    }

    // if it too long then we need to truncate
    if($length > MAXLENGTH_BREADCRUMB) {
        // calculate the per crumb limit
        $limit = round(MAXLENGTH_BREADCRUMB/count($breadcrumbs));
        // enforce it
        foreach($breadcrumbs as $id => $crumb) {
            $breadcrumbs[$id]['name'] = limit_length($crumb['name'], $limit);
        }
    }

    return build_navigation($breadcrumbs);
}

/**
 * uninstalls all resource tables
 *
 */
function uninstall_resources()  {
    global $CFG, $DB;
    require_once($CFG->dirroot."/blocks/assmgr/db/assmgr_db.php");
    $dbc = new assmgr_db();

    $resource_tables    =   $dbc->get_resource_types();

    if (!empty($resource_tables)) {
        foreach ($resource_tables as $resource_t) {
            // include the class for this type of evidence
            if (!empty($resource_t->name)) {

                if (file_exists($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$resource_t->name}.php")) {
                    @include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$resource_t->name}.php");
                    $resource = new $resource_t->name();

                    if (!empty($resource))  {
                        $resource->uninstall();
                    }
                }
            }
        }
    }
}



/**
 * Lock the portfolio is possible, or throw an error if not.
 *
 * @param int $portfolio_id
 * @param object $dbc recycled database access object. Saves memory compared to making a new one
 * @return void will lock the portfoilo or throw an exception
 */
// is the current portfolio locked?
function lock_portfolio_if_possible($portfolio_id) {

    global $USER, $CFG;

    //include assessment manager db class
    require_once($CFG->dirroot."/blocks/assmgr/db/assmgr_db.php");
    $dbc = new assmgr_db();

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
}

/**
 * Checks for a portfolio and locks it or makes a portfolio if one is not found
 *
 * @param int $candidateid
 * @param int $courseid
 * @param int $portfolioid optional. If this is provided, the other two parameters are ignored.
 * @return int the id of the created portfolio
 */
function check_portfolio($candidate_id, $course_id, $portfolioid = NULL) {

    global $CFG;

    //include assessment manager db class
    require_once($CFG->dirroot."/blocks/assmgr/db/assmgr_db.php");

    $dbc = new assmgr_db();

    $block_instance = $dbc->get_block_course_ids($course_id);

    if (empty($block_instance)) {
        print_error('blocknotmounted', 'block_assmgr');
    }

    // Get the portfolio one way or another
    if (empty($portfolioid)) {
        $portfolio = $dbc->get_portfolio($candidate_id, $course_id);
    } else {
        $portfolio = $dbc->get_portfolio_by_id($portfolioid);
        if (empty($portfolio)) {
            print_error('cantfindportfolio', 'block_assmgr');
        }
    }

    if (!empty($portfolio)) {
        $portfolio_id = $portfolio->id;

        // will throw an error/exception on failure
        lock_portfolio_if_possible($portfolio_id);
    } else {

        $candidate = $dbc->get_user($candidate_id);
        $course = $dbc->get_course($course_id);

        // create a new portfolio for the user
        $portfolio_id = $dbc->create_portfolio($candidate->id, $course->id);

        // add the course level grade items
        $dbc->create_portfolio_grade_items($course_id);

        //MOODLE LOG candidate portfolio created
        $log_action = get_string('logportfoliocreate', 'block_assmgr');
        $log_url = "edit_portfolio.php?course_id={$course->id}&amp;candidate_id={$candidate->id}";
        $logstrings = new stdClass;
        $logstrings->name = fullname($candidate);
        $logstrings->course = $course->shortname;
        $log_info = get_string('logportfoliocreateinfo', 'block_assmgr', $logstrings);
        assmgr_add_to_log($course_id, $log_action, $log_url, $log_info);

        lock_portfolio_if_possible($portfolio_id);
    }

    return $portfolio_id;
}

/**
 * Truncates long strings and adds a tooltip with a longer verison.
 *
 * @param string $string The string to truncate
 * @param int $maxlength The maximum length the string can be. -1 means unlimited, in case you just want a tooltip
 * @param string $tooltip (optional) tooltip to display. defaults to $string
 * @param array $special_case (optional) array of characters/entities that if found in string
 *              stop the truncation and deceoding
 * @return string HTML
 */
function limit_length($html, $maxlength, $tooltip = null) {

	global $PAGE;
	
	
	
    // permit only html tags and quotes so we can parse the tags properly
    $html = assmgr_db::decode_htmlchars(assmgr_db::encode($html));

    $printedlength = 0;
    $position = 0;
    $tags = array();

    $return = null;

    while ($printedlength < $maxlength && preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $html, $match, PREG_OFFSET_CAPTURE, $position)) {

        list($tag, $tagPosition) = $match[0];

        // print text leading up to the tag
        $str = substr($html, $position, $tagPosition - $position);
        if ($printedlength + strlen($str) > $maxlength) {
            $return .= (substr($str, 0, $maxlength - $printedlength));
            $printedlength = $maxlength;
            break;
        }

        $return .= ($str);
        $printedlength += strlen($str);

        if ($tag[0] == '&') {
            // handle the entity
            $return .= ($tag);
            $printedlength++;
        } else {
            // handle the tag
            $tagName = $match[1][0];
            if ($tag[1] == '/') {
                // this is a closing tag

                $openingTag = array_pop($tags);
                assert($openingTag == $tagName); // check that tags are properly nested

                $return .= ($tag);
            } else if ($tag[strlen($tag) - 2] == '/') {
                // self-closing tag
                $return .= ($tag);
            } else {
                // opening tag
                $return .= ($tag);
                $tags[] = $tagName;
            }
        }

        // continue after the tag
        $position = $tagPosition + strlen($tag);
    }

    // print any remaining text
    if ($printedlength < $maxlength && $position < strlen($html)) {
        $return .= (substr($html, $position, $maxlength - $printedlength));
    }

    // add the ellipsis, if truncated
    $return .= (strip_tags($return) != strip_tags($html)) ? '&hellip;' : null;

    // close any open tags
    while (!empty($tags)) {
        $return .= sprintf('</%s>', array_pop($tags));
    }

    // don't show a tooltip if it's set to false, or if no truncate has been done
    if($tooltip === false || ($return == $html && empty($tooltip))) {
        return $return;
    } else {
        // make the tooltip the original string if a specific value was not set
        if(empty($tooltip)) {
            $tooltip = $html;
        }

        $tooltip = assmgr_db::encode($tooltip);

        // generate the unique id needed for the YUI tooltip
        $id = 'tootlip'.uniqueNum();

        $script = "<script type='text/javascript'>
                       //<![CDATA[
                       new YAHOO.widget.Tooltip('ttA{$id}', {
                           context:'{$id}',
                           effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.20}
                       });
                       //]]>
                   </script>";

$script = "";
        return "<span id='{$id}' class='tooltip' title='{$tooltip}'>{$return}</span>{$script}";
        
    }
}