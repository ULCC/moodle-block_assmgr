/**
 * Attaches javascript calendars to the date filters. 
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 1.0
 */

// global variables
var Dom = YAHOO.util.Dom;
var Event = YAHOO.util.Event;

// TODO remove in 2.0
if (typeof(M) == 'undefined') { M = {};}

/**
 * Initialisation function that sets up the javascript for the page.
 */
M.blocks_assmgr_view_log = {

    addlistener :function() {
        //YAHOO.util.Event.delegate("assmgr_log_filters", "change", function(e, matchedEl, container) {alert('changed');}, 'input, select' );
    },

	init : function(from_elements, to_elements) {

        this.addlistener();
	
		//this section handles the setup for the from date
		sel_elements = new Array();
		sel_elements[0] = document.getElementsByName(from_elements['day'])[0];	
		sel_elements[1] = document.getElementsByName(from_elements['month'])[0];	
		sel_elements[2] = document.getElementsByName(from_elements['year'])[0];
		//yui_calendar(sel_elements,from_elements['calendarid'],from_elements['dateformat'],from_elements['startdate'],from_elements['pastdate'],from_elements['futuredate'],document.getElementById('actionslog_container'));

		//this section handles the setup for the to date
		sel_elements = new Array();
		sel_elements[0] = document.getElementsByName(to_elements['day'])[0];	
		sel_elements[1] = document.getElementsByName(to_elements['month'])[0];	
		sel_elements[2] = document.getElementsByName(to_elements['year'])[0];
		//yui_calendar(sel_elements,to_elements['calendarid'],to_elements['dateformat'],to_elements['startdate'],to_elements['pastdate'],to_elements['futuredate'],document.getElementById('actionslog_container'));

    }
}