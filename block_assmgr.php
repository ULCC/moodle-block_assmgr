<?php
/**
 * Block class for the Assessment Manager.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class block_assmgr extends block_list {

    /**
     * Sets initial block variables. Part of the blocks API
     *
     * @return void
     */
    function init() {
        $this->title = get_string('blockname', 'block_assmgr');
        $this->version = 2010072200;
        $this->cron = 43200; //run the cron at minimum once every 12 hours
    }

    /**
     * Sets up the content for the block.
     *
     * @return object The content object
     */
    function get_content() {
        global $CFG, $USER, $COURSE, $SITE;

        // include assessment manager db class
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // include the parser class
        require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_parser.class.php');

        // include the lib file
        require_once($CFG->dirroot.'/blocks/assmgr/lib.php');

        // db class manager
        $dbc = new assmgr_db();

        // get the course id
        $course_id = optional_param('id', $SITE->id, PARAM_INT);

        // get the course
        $course = $dbc->get_course($course_id);

        $coursecontext = get_context_instance(CONTEXT_COURSE, $course_id);

        // are we a student on the course?
        $access_iscandidate = has_capability('block/assmgr:creddelevidenceforself', $coursecontext, $USER->id, false);

        // are we an assessor on the course?
        $access_isassessor = has_capability('block/assmgr:assessportfolio', $coursecontext);

        // cache the content of the block
        if($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        // Candidates should not see the block on the course page
        if ($access_iscandidate && ! $access_isassessor && ($course_id == $SITE->id)) {
            return $this->content;
        }

        // Check to see if everything got setup OK. If not, replace the contents of the block with the error messages
        $setupproblems = false;

       // if ($course_id != $SITE->id) {
            $setupproblems = problems_with_block_setup($course_id);
        //}

        if ($setupproblems) {
            $this->content->items[] = '<strong>'.get_string('errorshaveoccurred', 'block_assmgr').'</strong>';
            $this->content->items = array_merge($this->content->items, $setupproblems);
            $this->content->icons = array_fill(0, count($this->content->items), "<img src='{$CFG->wwwroot}/blocks/assmgr/pix/icon.gif' class='icon' alt='".get_string('assmgricon', 'block_assmgr')."' />");
            return $this->content;
        }

        if($access_iscandidate) {
            $label = get_string('myportfolio', 'block_assmgr');
            $url  = "{$CFG->wwwroot}/blocks/assmgr/actions/edit_portfolio.php?course_id={$course_id}#submittedevidence";
            $this->content->items[] = "<a href='{$url}'>{$label}</a>";
            $this->content->icons[] = "<img src='{$CFG->wwwroot}/blocks/assmgr/pix/icon.gif' class='icon' alt='".get_string('assmgricon', 'block_assmgr')."' title='{$label}' />";
        }

        if($access_isassessor) {
            $label = get_string('assessportfolios', 'block_assmgr');
            $courseurl = ($course_id == $SITE->id) ? '' : "&amp;course_id={$course_id}";
            $url = "{$CFG->wwwroot}/blocks/assmgr/actions/list_portfolio_assessments.php?category_id={$course->category}{$courseurl}";
            $this->content->items[] = "<a href='{$url}'>{$label}</a>";
            $this->content->icons[] = "<img src='{$CFG->wwwroot}/blocks/assmgr/pix/icon.gif' class='icon' alt='".get_string('assmgricon', 'block_assmgr')."' title='{$label}' />";
        }

        return $this->content;
    }

    /**
     * Allow the user to set sitewide configuration options for the block.
     *
     * @return bool true
     */
    function has_config() {
        return true;
    }

    /**
     * Allow the user to set specific configuration options for the instance of
     * the block attached to a course.
     *
     * @return bool true
     */
    function instance_allow_config() {
        return true;
    }

    /**
     * Prevent the user from having more than one instance of the block on each
     * course.
     *
     * @return bool false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Saves the instance congig data to the database
     *
     * @return void appears to return something, but the function it calls in the parent class does not return anything
     */
    function instance_config_save($data) {

        // remove the config_ prefixes
        foreach($data as $key => $value) {
            $key = preg_replace('/config_/', '', $key);
            $data->$key = $value;
        }

        // and now actually save it in the parent class
        return parent::instance_config_save($data);
    }

    /**
     * Only allow this block to be mounted to a course or the home page.
     *
     * @return array
     */
    function applicable_formats() {
        return array(
            'site-index'  => true,
            'course-view' => true,
        );
    }

    /**
     * Runs after install to set config stuff
     *
     * @return void
     */
    function after_install() {

        global $CFG;

        $release = array_shift(explode(' ', $CFG->release));

        if ($release >= 2.0) {

            if (!$CFG->enableoutcomes == 1) {
                $errorstoreturn[] = get_string('blocknooutcomesetting', 'block_assmgr', $linkstart);
                set_config('enableoutcomes', 1);
            }

        } else {

            if (!$CFG->enableoutcomes == 2) {
                set_config('enableoutcomes', 2);
            }
        }

        // If the aggregate outcomes thing is on, set it to not be forced, so it can be overridden.
        if ($CFG->grade_aggregateoutcomes == 1) {

            if (($CFG->grade_aggregateoutcomes_flag == 0) || ($CFG->grade_aggregateoutcomes_flag == 2)) {
                set_config('grade_aggregateoutcomes_flag', $CFG->grade_aggregateoutcomes_flag+1);
            }
        }

        if ($CFG->grade_includescalesinaggregation) {
            set_config('grade_includescalesinaggregation', 0);
        }
    }

    function instance_create() {

        global $CFG, $COURSE, $DB;

        require_once($CFG->dirroot.'/lib/grade/constants.php');

        /*
        
        // Set the right grade type
        $sql = "courseid={$COURSE->id} AND itemtype='course'";
        $courseitem = $DB->get_record_select('grade_items', $sql);

        if ($courseitem && $courseitem->gradetype != GRADE_TYPE_SCALE) {
            $courseitem->gradetype = GRADE_TYPE_SCALE;
            $DB->update_record('grade_items', $courseitem);
        }

        // If there is a course level grade category, sort out it's aggregation type
        $sql = 'courseid='.$COURSE->id.' AND parent IS NULL';
        $coursecategory = $DB->get_record_select('grade_categories', $sql);

        if ($coursecategory && ($coursecategory->aggregation == GRADE_AGGREGATE_SUM)) {
            // It doesn't have to be the mean - this is just the first on the list
            $coursecategory->aggregation = GRADE_AGGREGATE_MEAN;
            $DB->update_record('grade_categories', $coursecategory);
        }

        //set initial quota to 5
        $data = new object();
        $data->portfolio_quota = 5;

        $this->instance_config_save($data);
        
        */
    }

    function before_delete() {

        global $CFG, $DB;

 
    }

    /**
     * Periodic cron to import new Moodle activities and to propagate any changes
     * to their grades from the activity to the submission.
     *
     */
    function cron() {
        global $CFG;

    }
}
?>