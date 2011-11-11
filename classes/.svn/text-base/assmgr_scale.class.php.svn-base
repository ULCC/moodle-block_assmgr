<?php
/**
 * Class to render scales and scale items.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

// fetch the gradelib
require_once($CFG->libdir.'/gradelib.php');

class assmgr_scale extends grade_scale {

    /**
     * The pass threshold for the scale
     */
    var $gradepass = null;

    /**
     * Constructor
     */
    function __construct($params=NULL, $fetch=true) {

        // set the gradepass value and remove it from $params
        if(isset($params['gradepass'])) {
            // decrement the gradepass to match the scale items
            $this->gradepass = $params['gradepass'] - 1;
            unset($params['gradepass']);
        }

        parent::__construct($params, $fetch);
    }

    /**
     * Prints a value from a scale, either as a candidate claim or as an assessor grade
     *
     * @param float $scale_item The optional index of the currently selected item
     * @param bool $claim flag as to whether it is to be printed as a claim or not (optional, defaults to false)
     * @param bool $mform flag to ask for mform compatible output (text only, no HTML)
     * @return string the HTML code for the scale item
     */
    function render_scale_item($scale_item = null, $claim = false, $mform = false) {

        global $CFG;

        if (empty($scale_item)) {
            return '-';
        }

        // cast the item_id to an int (as the gradebook stores this as a float)
        // and decrease the value by one (as load_items() returns the wrong indexes)
        $scale_item = (int) $scale_item-1;

        // load the individual scale items
        $items = $this->load_items();

        if ($claim) {

            //claims cannot be made on a scale by candidates any more
            $title = get_string('candidateclaim', 'block_assmgr');
            $grade = '<span class="gradevalue gradeclaim" title="'.$title.'"><img src="'.$CFG->wwwroot.'/blocks/assmgr/pix/candidatecrit_small.png" class="tick" alt="'.get_string('candidateclaim', 'block_assmgr').'" /></span>';

        } else {

            if ($mform) {
                $grade = $items[$scale_item];
            } else {

                $title = get_string('assessorgrade', 'block_assmgr');

                // is this a pass grade
                $gradeclass = (empty($this->gradepass) || $scale_item >= $this->gradepass) ? 'gradepass' : 'gradefail';

                $grade = "<span class='gradevalue {$gradeclass}' title='{$title}'>{$items[$scale_item]}</span>";
            }
        }

        return $grade;
    }

    /**
     * Returns a select element of the scale items
     *
     * @param float $scale_item the index of the currently selected item (optional)
     * @param array $attributes and array of attributes for the select element (optional)
     * @param bool $mform optional flag (defaults to false) - do we want an array to use in an mform?
     * @return string | array
     */
    function get_select_element($scale_item = null, $attributes = null, $mform = false) {
        // cast the item_id to an int (as the gradebook stores this as a float)
        // and decrease the value by one (as load_items() returns the wrong indexes)
        $scale_item = (int)$scale_item-1;

        // make the attributes string
        $attstring = null;

        if(!empty($attributes)) {

            foreach($attributes as $name => $value) {
                $attstring .= "{$name}='{$value}' ";
            }
        }

        if (!$mform) {
            $selectelem = "<select {$attstring}>";
            // show the standard 'no outcomes' string instead of an empty option
            $selectelem .= '<option value="">'.get_string('nooutcome', 'grades').'</option>';

            foreach($this->load_items() as $idx => $item) {

                $selected = ($scale_item == $idx) ? "selected='selected'" : '';
                $selectelem .= "<option value='".($idx+1)."' $selected>{$item}</option>";
            }
            $selectelem .= '</select>';

            return $selectelem;

        } else {

            // add 'No outcome' as the standard first option with a null value
            $items = $this->load_items();
            array_unshift($items, get_string('nooutcome', 'grades'));
            $items = array_merge(array('' => get_string('nooutcome', 'grades')), $items);
            unset($items[0]);

            return $items;
        }
    }
}