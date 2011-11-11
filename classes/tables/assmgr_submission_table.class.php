<?php
/**
 * Table class to handle the submission matrix displayed in the candidate and
 * assessor views.
 *
 * @uses flexible_table()
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

// fetch the assmgr default table library
require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_ajax_table.class.php');

class assmgr_submission_table extends assmgr_ajax_table {



    /**
     * Set up the table, adjusting some default values
     */
    function setup() {
        parent::setup();

        // get all the scales for the outcomes
        $this->scales = array();
        $this->headeronclick  = 'set_col(this.cellIndex,0)';
        foreach($this->outcomes as $outcome) {
            if(empty($this->scales[$outcome->scaleid])) {
                $this->scales[$outcome->scaleid] = $this->dbc->get_scale($outcome->scaleid, $outcome->gradepass);
            }
        }
    }

    /**
     * Wrap the whole table in a form so we can submit the outcome assessments
     */
    function start_html() {

        if ($this->access_isassessor && !$this->access_isverifier) {
            echo '<form id="outassessform" method="post" action="save_outcomes_assessment.php">';
            echo "<p><input type='hidden' name='course_id' value='{$this->course_id}' />";
            echo "<input type='hidden' name='candidate_id' value='{$this->candidate_id}' />";
            echo "<input type='hidden' name='datachanged' value='false' /></p>";
        }

        parent::start_html();
    }

    /**
     * Prints the HTML after the end of the table, including the submit button
     */
    function finish_html() {

        if ($this->access_isassessor && $this->started_output && !$this->access_isverifier) {
            echo '<tr align="center"><td align="center" colspan="'.count($this->columns).'"><noscript><div class="BtnPosition">
                    <input type="submit" name="Submit" id="submissiontablesubmit" value="'.get_string('saveassessment', 'block_assmgr').'" />
                  </div></noscript></td></tr>';
        }

        if (!$this->started_output) {
            //no data has been added to the table.
            $this->print_nothing_to_display();
        } else {

            echo '</table>';

            if($this->access_isassessor && !empty($this->totalrows)) {
                echo '</form>';
            }

            if ($this->access_isassessor) {
                echo "<script type='text/javascript'>
                          //<![CDATA[
                          M.assmgr.view_submissions.hidecolumns();
                          //]]>
                      </script>";
            }

            if ($this->use_pages) {
                $this->print_paging_bar();
            }

            $this->wrap_html_finish();
            // Paging bar
            if (in_array(TABLE_P_BOTTOM, $this->showdownloadbuttonsat)) {
                echo $this->download_buttons();
            }
        }
    }

    /**
     * Adds extra information to the column headers
     *
     * @param string $column the name of the column e.g. 'outcome3'
     * @return string|null
     */
    function get_header_suffix($column) {

        global $CFG;

        // if this is an outcome column then add the portfolio outcome grade
        if (preg_match('/^outcome([0-9]+)$/', $column, $matches)) {
            $id = $matches[1];
            $outcome = $this->outcomes[$id];

            // get the scale items
            $scale = $this->scales[$outcome->scaleid];

            // get the grade value
            $item_id = (!empty($this->grades[$id])) ? $this->grades[$id]->scale_item : null;

            // Assessor needs to see the dropdowns to change the grade
            if ($this->access_isassessor && !$this->access_isverifier) {
                return '<br/>
                        <div id="outcomediv'.$outcome->id.'" class="assmgroutcomediv">

                             <span class="columngrade hidden" id ="columngrade'.$outcome->id.'">'
                                .$scale->render_scale_item($item_id)
                           .'</span>
                             <span class="columnselect" id ="columnselect'.$outcome->id.'">'
                                .$scale->get_select_element($item_id, array('onclick'=>'suppressClick(event);', 'name'=>"outcomes[{$outcome->id}]", 'id' => 'columnselect'.$outcome->id.'select'))
                           .'</span>
                             <span id="columnedit'.$outcome->id.'" class="commands">
                                <img src="'.$CFG->wwwroot.'/pix/t/edit.gif" id="editicon'.$outcome->id.'" class="editicon iconsmall hidden" title="'
                                  .get_string('changegrade', 'block_assmgr').'" alt="'.get_string('changegrade', 'block_assmgr').'" />
                             </span>
                             <span id="columnloader'.$outcome->id.'">
                             </span>
                         </div>';
            } else {
                // candidate and verifier need to see the allocated grade.

                if (!empty($this->grades[$id])) {
                    return '<br/><div class="hiddenoutcomegrade">'.$scale->render_scale_item($item_id).'</div>';
                }
            }
        }

        return null;
    }

    /**
     * Prints the message that there is nothing to display instead of the table (with filters if needed)
     */
    function print_nothing_to_display() {

        global $OUTPUT;

        // are there any filters set that will effect the number of submissions
        if ($this->are_submissions_filtered()) {
            // print the filters so the user can change them
            $this->print_filters();

            $heading = get_string('nomatchingsubmissions', 'block_assmgr');
        } else {
            $heading = get_string('nosubmissions', 'block_assmgr');
        }

        echo $OUTPUT->heading($heading);
    }

    /**
     * Prints the HTML for the filters at the top of the submissions table
     */
    function print_filters() {
        global $CFG;

        //include the progress_bar class
        require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_progress_bar.class.php');
        ?>
        <div class="filters">
            <form id="<?php echo $this->uniqueid; ?>_filters" method="post" action="<?php echo $this->baseurl.$this->fragment; ?>">
                <table>
                    <tr>
                        <td>
                            <input type="hidden" name="<?php echo $this->uniqueid; ?>[filters][show_assessed]" value="0" />
                            <input type="checkbox" id="<?php echo $this->uniqueid; ?>show_assessed" name="<?php echo $this->uniqueid; ?>[filters][show_assessed]" value="1" <?php if($this->get_filter('show_assessed')) { echo 'checked="checked"'; } ?> />
                            <label for="<?php echo $this->uniqueid; ?>show_assessed">
                                <?php echo get_string('showassessed', 'block_assmgr'); ?>
                            </label>
                        </td>
                        <td>
                            <label for="<?php echo $this->uniqueid; ?>show_outcomes">
                                <?php echo get_string('showview', 'block_assmgr'); ?>
                            </label>
                        </td>
                        <td>
                            <select name="<?php echo $this->uniqueid; ?>[filters][show_outcomes]" id="<?php echo $this->uniqueid; ?>show_outcomes">
                                <option value="1" <?php if($this->get_filter('show_outcomes')) { echo 'selected="selected"'; } ?>><?php echo get_string('assessmentcriteria', 'block_assmgr'); ?></option>
                                <option value="0" <?php if(!$this->get_filter('show_outcomes')) { echo 'selected="selected"'; } ?>><?php echo get_string('evidencetypes', 'block_assmgr'); ?></option>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="<?php echo $this->uniqueid; ?>[filters][show_details]" value="0" />
                            <input type="checkbox" id="<?php echo $this->uniqueid; ?>show_details" name="<?php echo $this->uniqueid; ?>[filters][show_details]" value="1" <?php if($this->get_filter('show_details')) { echo 'checked="checked"'; } ?> />
                            <label for="<?php echo $this->uniqueid; ?>show_details"><?php echo get_string('showdetails', 'block_assmgr'); ?></label>
                        </td>
                        <td>
                            <?php
                            if($this->get_filter('show_outcomes')) { ?>
                                <label for="<?php echo $this->uniqueid; ?>show_outcomes_set">
                                    <?php echo get_string('showcriteria', 'block_assmgr'); ?>
                                </label>
                                <?php
                            } ?>
                        </td>
                        <td>
                            <?php
                            if($this->get_filter('show_outcomes')) { ?>
                                <select name="<?php echo $this->uniqueid; ?>[filters][show_outcomes_set]" id="<?php echo $this->uniqueid; ?>show_outcomes_set">
                                    <option value="<?php echo OUTCOMES_SHOW_ALL; ?>" <?php if($this->get_filter('show_outcomes_set') == OUTCOMES_SHOW_ALL) { echo 'selected="selected"'; } ?>><?php echo get_string('all', 'block_assmgr'); ?></option>
                                    <option value="<?php echo OUTCOMES_SHOW_COMPLETE; ?>" <?php if($this->get_filter('show_outcomes_set') == OUTCOMES_SHOW_COMPLETE) { echo 'selected="selected"'; } ?>><?php echo get_string('complete', 'block_assmgr'); ?></option>
                                    <option value="<?php echo OUTCOMES_SHOW_INCOMPLETE; ?>" <?php if($this->get_filter('show_outcomes_set') == OUTCOMES_SHOW_INCOMPLETE) { echo 'selected="selected"'; } ?>><?php echo get_string('incomplete', 'block_assmgr'); ?></option>
                                    <option value="<?php echo OUTCOMES_SHOW_UNATTEMPTED; ?>" <?php if($this->get_filter('show_outcomes_set') == OUTCOMES_SHOW_UNATTEMPTED) { echo 'selected="selected"'; } ?>><?php echo get_string('ungraded', 'block_assmgr'); ?></option>
                                </select>
                                <?php
                            } ?>
                        </td>
                        <td>
                            <noscript>
                                <div>
                                    <input id="<?php echo $this->uniqueid; ?>apply_filters" type="submit" name="apply_filters" value="<?php echo get_string('applyfilters', 'block_assmgr');?>" />
                                </div>
                            </noscript>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <script type="text/javascript">
            //<![CDATA[
            add_onchange_listeners(
                '<?php echo $this->uniqueid; ?>_filters',
                ajax_submit_wrapper,
                {'form_id':'<?php echo $this->uniqueid; ?>_filters', 'elem_id':'<?php echo $this->uniqueid; ?>_container', 'url':'<?php echo $this->ajaxurl; ?>'},
                ['input', 'select']
            );
            //]]>
        </script>
        <?php
        // show the unit progress
        $progress = new assmgr_progress_bar();

        // get the portfolio grade
        $portgrade = $this->dbc->get_portfolio_grade($this->course_id, $this->candidate_id);
        $finalgrade = !empty($portgrade->grade) ? $portgrade->str_grade : '';

        ?>
        <div class="unitprogress">
            <?php echo get_string('unitprogress','block_assmgr').' - '; ?><?php echo (!empty($finalgrade)) ? get_string('grade','block_assmgr').':'.$finalgrade: '';?>
            <?php echo $progress->get_unit_progress($this->candidate_id, $this->course_id, $this->access_isassessor, 'big'); ?>
        </div>
        <?php
    }

    /**
     * Makes sure the filter settings are used from the last page or sets defaults
     *
     * Called from the constructor
     */
    function set_default_filters() {
        global $SESSION, $PARSER;

        // get the session
        $sess = &$SESSION->flextable[$this->uniqueid];

        // set the defaults if this is this first time the filters are beeing set
        if(!isset($sess->filters['show_assessed'])) {
            $sess->filters['show_assessed'] = 1;
        }
        if(!isset($sess->filters['show_details'])) {
            $sess->filters['show_details'] = 0;
        }
        if(!isset($sess->filters['show_outcomes'])) {
            $sess->filters['show_outcomes'] = 1;
        }
        if(!isset($sess->filters['show_outcomes_set'])) {
            $sess->filters['show_outcomes_set'] = OUTCOMES_SHOW_ALL;
        }
    }

    /**
     * True/false to see if the number of submissions will be be affected by filters
     *
     * @return bool
     */
    function are_submissions_filtered() {
        global $SESSION;

        // the only filter that can affect the number of submissions is show_assessed
        return !$this->get_filter('show_assessed');
    }

    /**
     * Returns an HTML link to a piece of portfolio evidence
     *
     * @param object $row A row from the submissions matrix object
     * @return string HTML link
     */
    function get_evidence_resource_link($row) {
        global $CFG;

        // get the evidence and the resource
        $evidence = $this->dbc->get_evidence_resource($row->evidence_id);

        // include the class for this type of evidence
        @include_once($CFG->dirroot."/blocks/assmgr/classes/resources/plugins/{$evidence->resource_type}.php");

        if(!class_exists($evidence->resource_type)) {
            print_error('noclassforresource', 'block_assmgr', '', $evidence->resource_type);
        }

        $evidence_resource = new $evidence->resource_type;
        $evidence_resource->load($evidence->resource_id);

        return $evidence_resource->get_link();
    }

    /**
     * Returns an HTML link icon for an evidence claim in a portfolio
     *
     * @param object $row A row from the submissions matrix object
     * @return string the HTML for the icon link
     */
    function get_edit_claim_link($row) {
        global $CFG, $OUTPUT;

        $title = get_string('editclaim', 'block_assmgr');
        $url = "{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?submission_id={$row->submission_id}&amp;course_id={$row->course_id}";

        $link = "<a class='editing_update' title='{$title}' href='{$url}'>
                    <img src='".$OUTPUT->pix_url('t/edit')."' class='iconsmall' alt='{$title}' />
                </a>";

        return $link;
    }

    /**
     * Returns an HTML link icon to the edit screen for an assessment in a portfolio
     *
     * @param object $row A row from the submissions matrix object
     * @return string the HTML for the icon link
     */
    function get_edit_assessment_link($row) {
        global $CFG, $OUTPUT;

        $title = get_string('editassessment', 'block_assmgr');
        $url = "{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?submission_id={$row->submission_id}&amp;course_id={$row->course_id}";

        $link = "<a class='editing_update' title='{$title}' href='{$url}'>
                    <img src='".$OUTPUT->pix_url('t/edit')."' class='iconsmall' alt='{$title}' />
                 </a>";

        return $link;
    }

    /**
     * Returns an HTML link icon to delete a submission in a portfolio
     *
     * @param object $row A row from the submissions matrix object
     * @return string the HTML for the icon link
     */
    function get_delete_submission_link($row) {
        global $CFG, $OUTPUT;

        $link = '';
        $reasontohide = false;

        $graded = $this->dbc->has_submission_grades($row->submission_id);
        $mine   = $this->dbc->is_submission_mine($row->submission_id);

        if ($graded) {
            $reasontohide = 'gradedsubmission';
        } else if (!$mine) {
            $reasontohide = 'notmysubmission';
        }

        $title = ($reasontohide) ? get_string('cantdeletesubmission', 'block_assmgr').' '.get_string($reasontohide, 'block_assmgr') : get_string('deletesubmission', 'block_assmgr');

        $url = "{$CFG->wwwroot}/blocks/assmgr/actions/delete_submission.php?course_id={$row->course_id}&amp;candidate_id={$row->candidate_id}&amp;submission_id={$row->submission_id}";

        if ($reasontohide) {
            $link .= "<img src='".$OUTPUT->pix_url('t/delete')."' class='iconsmall greyed_out_icon' alt='{$title}' title='{$title}' />";
        } else {
            $link .= "<a class='editing_delete' title='{$title}' href='{$url}'>
                          <img src='".$OUTPUT->pix_url('t/delete')."' class='iconsmall' alt='{$title}' />
                      </a>";
        }

        return $link;
    }

    /**
     * Returns an HTML link icon to show or hide a submission in a portfolio
     *
     * @param object $row A row from the submissions matrix object
     * @return string the HTML for the icon link
     */
    function get_hidden_submission_link($row, $disabled=false) {

        global $CFG, $OUTPUT;

        $reasontohide = false;
        $graded = $this->dbc->has_submission_grades($row->submission_id);
        $mine   = $this->dbc->is_submission_mine($row->submission_id);

        if ($graded) {
            $reasontohide = 'gradedsubmission';
        } else if (!$mine) {
            $reasontohide = 'notmysubmission';
        }

        if ($row->hidden) {
            $title = get_string('showtoassessor', 'block_assmgr');
            $icon = 't/show';
        } else {
            $title = get_string('hidefromassessor', 'block_assmgr');
            $icon = 't/hide';
        }

        if ($reasontohide) {
            $title = get_string('canthidesubmission', 'block_assmgr').' '.  get_string($reasontohide, 'block_assmgr');;
        }

        if ($reasontohide) {
            $link = "<img src='".$OUTPUT->pix_url($icon)."' class='iconsmall greyed_out_icon' alt='{$title}' title='{$title}' />";
        } else {
            $url = "{$CFG->wwwroot}/blocks/assmgr/actions/save_submission_visibility.php?submission_id={$row->submission_id}&amp;course_id={$row->course_id}";
            $link = "<a class='editing_show' title='{$title}' href='{$url}'>
                        <img src='".$OUTPUT->pix_url($icon)."' class='iconsmall' alt='{$title}' />
                     </a>";
        }

        return $link;
    }

    /**
     * Get the columns to sort by, in the form required by {@link construct_order_by()}.
     * @return array column name => SORT_... constant.
     */
    public function get_sort_columns() {
        $cols = parent::get_sort_columns();

        $primary = key($cols);

        // if the primary sort key is an outcome then we need to add a secondary
        // sort keys for the claims
        if(preg_match('/^outcome([0-9]+)$/', $primary, $matches)) {
            $newcols = array();
            $newcols[$primary] = $cols[$primary];
            $newcols["claim{$matches[1]}"] = $cols[$primary];

            return $newcols;
        }

        return $cols;
    }

    /**
     * This function is not part of the public api.
     * It adds the header HTML, including an extra row on top
     *
     *
     */
    function print_headers(){

        // is it an outcomes table?
        if ($this->get_filter('show_outcomes')) {

            $outcomes = array();

//            foreach ($this->columns as $name => $column) {
//
//                if (strpos($name, 'outcome') !== false) {
//                    $outcomes[] = substr($name, 7);
//                }
//            }

            if ($this->outcomes) {

                echo '<tr>';
                // empty cell for all the non-outcome bits
                echo '<th scope="col" class="headerrow category catlevel1 cell removetopborder" colspan="'.(count($this->columns) - $this->hozcols).'"></th>';

                $currentcategory = false;
                // build an array of category colspans and descriptions to be built once finalised
                $categories = array();
                $categoryids = array();

                // Loop through the outcomes, adding to the array of categories in use as we go.
                // Can't use category ids as the array keys as sometimes there won't be one as
                // we have user-generated outcomes that are not in categories
                foreach ($this->outcomes as $outcome) {

                    if (($currentcategory === false) || ($outcome->categoryid !== $categories[$currentcategory]['id'])) {

                        //we need an array of category ids to get the names and descriptions later.
                        // can't use the main one as it may have empty elements with no id
                        if (!empty($outcome->categoryid)) {
                            $categoryids[] = $outcome->categoryid;
                        }

                        $categories[] = array(
                                'colspan' => 1,
                                'id' => $outcome->categoryid
                        );

                        // get the array key of the category we just added
                        end($categories);
                        $currentcategory = key($categories);

                    } else {

                       $categories[$currentcategory]['colspan']++;
                    }
                }

                // get the names and descriptions of any relevant categories
                $categoryinfo = $this->dbc->get_grade_categories($categoryids);

                // colspans are all set, now make the cells for the categories
                foreach ($categories as $category) {

                    echo '<th scope="col" class="'.$this->header_class.' headerrow category catlevel2 cell" colspan="'.$category['colspan'].'" >';

                    if (!empty($category['id'])) {
                        echo limit_length($categoryinfo[(int)$category['id']]->fullname, 50, $categoryinfo[(int)$category['id']]->description);
                    }
                    echo '</th>';
                }


                echo '</tr>';
            }
        }

        parent::print_headers();
// end new bit
      }

}