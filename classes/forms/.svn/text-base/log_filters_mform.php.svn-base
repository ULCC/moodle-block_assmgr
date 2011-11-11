<?php
/**
 * @copyright &copy; 2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
//require_once($CFG->dirroot.'/blocks/assmgr/classes/forms/filter_date.php');

class log_filters_mform extends assmgr_moodleform {

    /**
     * Constructor
     *
     * @return void
     */
    function __construct($users, $filters, $uniqueid) {

        global $CFG;

        $this->users = $users;
        $this->filters = $filters;
        $this->uniqueid = $uniqueid;

        // call the parent constructor
        parent::__construct();
    }


    function definition() {
        global $PARSER, $USER;

        $mform =& $this->_form;

        // set the form id and the css class
        $mform->_attributes['id'] = 'assmgr_log_filters';
        $mform->_attributes['class'] = 'filters';

        // add some hidden fields for the non-js fallback
        $mform->addElement('hidden', 'course_id', $PARSER->required_param('course_id', PARAM_INT));
        $mform->setType('course_id', PARAM_INT);
        $mform->addElement('hidden', 'candidate_id', $PARSER->optional_param('candidate_id', $USER->id, PARAM_INT));
        $mform->setType('candidate_id', PARAM_INT);
        $mform->addElement('hidden', 'folder_id', $PARSER->optional_param('folder_id', 0, PARAM_INT));
        $mform->setType('folder_id', PARAM_INT);

        $mform->addElement('header', 'dates_fieldset', null);

        // the latest year in the date selectors is the current year
        $dateoptions = array(
                'stopyear' => date('Y'),
                'optional' => false
        );

        // add the checkboxes and date things on the same lines
        $objs = array();
        $objs[] =& $mform->createElement('checkbox', $this->uniqueid.'[filters][fromcheck]', null, get_string('after', 'block_assmgr'));
        $objs[] =& $mform->createElement('date_selector', $this->uniqueid.'[filters][from]', null, $dateoptions);
        $grp =& $mform->addElement('group', 'from_group', get_string('showactions', 'block_assmgr').':', $objs, '', false);

        $objs = array();
        $objs[] =& $mform->createElement('checkbox', $this->uniqueid.'[filters][tocheck]', null, get_string('before', 'block_assmgr'), (!empty($this->filters['from']['day'])));
        $objs[] =& $mform->createElement('date_selector', $this->uniqueid.'[filters][to]', null, $dateoptions);
        $grp =& $mform->addElement('group', 'to_group', '', $objs, '', false);

        // make it so that the checkboxes disable the date thingies
        $mform->disabledIf($this->uniqueid.'[filters][from][day]', $this->uniqueid.'[filters][fromcheck]', 'notchecked');
        $mform->disabledIf($this->uniqueid.'[filters][from][month]', $this->uniqueid.'[filters][fromcheck]', 'notchecked');
        $mform->disabledIf($this->uniqueid.'[filters][from][year]', $this->uniqueid.'[filters][fromcheck]', 'notchecked');

        $mform->disabledIf($this->uniqueid.'[filters][to][day]', $this->uniqueid.'[filters][tocheck]', 'notchecked');
        $mform->disabledIf($this->uniqueid.'[filters][to][month]', $this->uniqueid.'[filters][tocheck]', 'notchecked');
        $mform->disabledIf($this->uniqueid.'[filters][to][year]', $this->uniqueid.'[filters][tocheck]', 'notchecked');

        // add the user selector if there are defined users
        if (!empty($this->users)) {

            $mform->addElement('header', 'users_fieldset', null);
            $userselect = array(null => get_string('allusers', 'block_assmgr'));
            foreach($this->users as $user) {
                $userselect[$user->creator_id] = fullname($user);
            }

            $mform->addElement('html', '<br/>');
            $mform->addElement('select', $this->uniqueid.'[filters][creator_id]', get_string('byuser', 'block_assmgr'), $userselect);
        }

        // addd a no script submit button for non-js fallback
        $submit = "<noscript>
                       <div>
                           <input id='{$this->uniqueid}_apply_filters' type='submit' name='apply_filters' value='".get_string('applyfilters', 'block_assmgr')."' />
                       </div>
                   </noscript>";

        $mform->addElement('html', $submit);
    }
}
?>