<?php
/**
 * Class to render progress within a unit or qualification.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class assmgr_progress_bar {

    function __construct() {
        global $CFG;

        //include assessment manager db class
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        $this->dbc = new assmgr_db();
    }

    /**
     * Returns a progress bar for a given unit and candidate
     *
     * Works out the percentage of the outcomes which are either achieved or in
     * progress for a particular course and sends the data to the progress bar
     * renderer.
     *
     * @param int $candidate_id The student id
     * @param int $course_id The course id
     * @param bool $access_isassessor Flag determining whether the user is an assessor
     * @param string $size Either 'small' or 'large'
     * @param int $score The optional progress as a numeric sore (achieved * 10000 + incomplete * 100 + claims * 1)
     * @return string
     */
    function get_unit_progress($candidate_id, $course_id, $access_isassessor, $size, $score = false) {

        $params = new object();

        // get the course
        $course = $this->dbc->get_course($course_id);
        $params->coursename = $course->shortname;

        // get the fullname (either 1st or 3rd person)
        $params->fullname = $access_isassessor
            ? get_string('ihave', 'block_assmgr', fullname($this->dbc->get_user($candidate_id)))
            : get_string('youhave', 'block_assmgr');

        // get the number of outcomes
        $numoutcomes = $this->dbc->count_outcomes(array($course_id));

        if($score === false) {
            // get the candidate's progress score
            $score = $this->dbc->get_candidate_progress($candidate_id, array($course_id));
        }

        // calculate the various percentages
        $params->achieved = round(floor($score/10000)/$numoutcomes * 100, 2);
        $params->partial = round(floor(($score%10000)/100)/$numoutcomes * 100, 2);
        $params->claims = round(($score%100)/$numoutcomes * 100, 2);

        // get the language strings
        $params->achieved_str = get_string('hasachieved', 'block_assmgr', $params);
        $params->partial_str = get_string('haspartial', 'block_assmgr', $params);
        $params->claims_str = get_string('hasclaims', 'block_assmgr', $params);

        return $this->get_html($params, $size);
    }

    /**
     * Gets the overall progress bar for a qualification
     *
     * Sums the number of outcomes, number of achieved outcomes and number of in
     * progress outcomes from all of the courses within a qualification's course
     * category, then returns the rendered HTML from render_qualification_progress().
     *
     * @param int $candidate_id Student id of the candidate
     * @param int $category_id the ID number of the qualification's course category
     * @param bool $access_isassessor Flag determining whether the user is an assessor
     * @return string HTML of the progress bar
     */
    function get_qualification_progress($candidate_id, $category_id, $access_isassessor, $size, $score = false) {

        // get all the courses that the candidate is enrolled in in this category
        $courses = $this->dbc->get_enrolled_courses($candidate_id, $category_id);
        $courselist = array_keys($courses);

        $params = new object();

        // get the category
        $category = $this->dbc->get_category($category_id);
        $params->qualname = $category->name;

        // get the fullname (either 1st or 3rd person)
        $params->fullname = $access_isassessor
            ? get_string('ihave', 'block_assmgr', fullname($this->dbc->get_user($candidate_id)))
            : get_string('youhave', 'block_assmgr');

        // get the number of outcomes
        $numoutcomes = $this->dbc->count_outcomes($courselist);

        if($score === false) {
            // get the candidate's progress score
            $score = $this->dbc->get_candidate_progress($candidate_id, $courselist);
        }

        // calculate the various percentages
        $params->achieved = round(floor($score/10000)/$numoutcomes * 100, 2);
        $params->partial = round(floor(($score%10000)/100)/$numoutcomes * 100, 2);
        $params->claims = round(($score%100)/$numoutcomes * 100, 2);

        // get the language strings
        $params->achieved_str = get_string('hasachievedinqual', 'block_assmgr', $params);
        $params->partial_str = get_string('haspartialinqual', 'block_assmgr', $params);
        $params->claims_str = get_string('hasclaimsinqual', 'block_assmgr', $params);

        return $this->get_html($params, $size);
    }

    /**
     * Creates the html to show the progress bar
     *
     * @param object $params
     * @param string $size Large or small
     * @param string $portgrade The optional finalgrade to display
     */
    private function get_html($params, $size) {

        $ids = new object();
        $ids->assessed = 'progress'.uniqueNum();
        $ids->partial = 'progress'.uniqueNum();
        $ids->claims = 'progress'.uniqueNum();

        // generate the javascript to render the tooltips
        $script = "<script type='text/javascript'>\n//<![CDATA[\n";
        foreach($ids as $id) {
            $script .= "new YAHOO.widget.Tooltip('ttA{$id}', {
                            context:'{$id}',
                            effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.20}
                        }); \n";
        }
        $script .= "//]]>\n</script>";

        return "<div class='progress_bar_{$size}'>
                    <div id='{$ids->assessed}' class='bar_avg assessed' title='{$params->achieved_str}' style='width: {$params->achieved}%;'></div>
                    <div id='{$ids->partial}' class='bar_avg incomplete' title='{$params->partial_str}' style='width: {$params->partial}%;'></div>
                    <div id='{$ids->claims}' class='bar_avg claims' title='{$params->claims_str}' style='width: {$params->claims}%;'></div>
                </div>{$script}";
    }
}
?>