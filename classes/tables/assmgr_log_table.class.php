<?php
/**
 * Table class to handle the display of the actions log
 *
 * @uses flexible_table()
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

// fetch the assmgr ajax table library
require_once($CFG->dirroot.'/blocks/assmgr/classes/tables/assmgr_ajax_table.class.php');
require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/log_filters_mform.php');

class assmgr_log_table extends assmgr_ajax_table {

    /**
     * Set the default filter values.
     *
     */
    function setup() {
        global $USER;

        parent::setup();

        // default to all users
        if(!isset($this->filters['user'])) {
            $this->filters['user'] = 0;
        }

        // default to current user
        if(!isset($this->filters['creator_id'])) {
            $this->filters['creator_id'] = $USER->id;
        }

        // default to last login
        if (!isset($this->filters['from'])) {
            $this->filters['fromcheck'] = true;
            $this->filters['from']['day'] = date('d',$USER->lastlogin);
            $this->filters['from']['month'] = date('m',$USER->lastlogin);
            $this->filters['from']['year'] = date('Y',$USER->lastlogin);
        }

        // default to no end
        if(!isset($this->filters['tocheck'])) {
            $this->filters['tocheck'] = false;
        }
    }

    /**
     * Displays filters for the table.
     *
     */
    function print_filters() {

        global $USER, $PAGE, $CFG;

        $mform = new log_filters_mform($this->users, $this->filters, $this->uniqueid);

        $data = array();

        // mangle the filters to fit the form
        foreach($this->filters as $filter => $value) {
            $field = "{$this->uniqueid}[filters][{$filter}]";

            if($filter == 'to' || $filter == 'from') {
                $data[$field] = mktime(0,0,0,$value['month'],$value['day'],$value['year']);
            } else {
                $data[$field] = $value;
            }
        }

        // load the filters into the form
        $mform->set_data($data);

        // show the form
        $mform->display();

        // add the JS that does the onchange because event bubbling doesn't work on selects in IE
        echo ' <script type="text/javascript">
                    //<![CDATA[
                    add_onchange_listeners(
                        "'.$this->uniqueid.'_filters",
                        ajax_submit_wrapper,
                        {"form_id":"'.$this->uniqueid.'_filters", "elem_id":"'.$this->uniqueid.'_container", "url":"'.$this->ajaxurl.'"},
                        ["input", "select"]
                    );
                    //]]>
                </script>';
    }

    /**
     * Parses the filters and returns SQL to apply them.
     *
     * @return string sql to add to where statement.
     */
    function get_sql_filters() {

        $return = array();

        // filter actions by specific user
        if(!empty($this->filters['creator_id'])) {
            $return[] = 'creator.id = '.$this->filters['creator_id'];
        }

        // filter actions by date
        if(!empty($this->filters['from'])) {
            $from = mktime(0,0,0,$this->filters['from']['month'],$this->filters['from']['day'],$this->filters['from']['year']);
            if(!empty($from)) {
                $return[] = "logtable.timecreated >= {$from}";
            }
        }
        if(!empty($this->filters['to'])) {
            $to = mktime(0,0,0,$this->filters['to']['month'],$this->filters['to']['day'],$this->filters['to']['year']);
            if(!empty($to)) {
                // add one day as we want everything before the given day
                $to += 86400;
                $return[] = "logtable.timecreated < {$to}";
            }
        }

        return implode(' AND ', $return);
    }
}
?>