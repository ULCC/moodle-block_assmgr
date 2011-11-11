/**
 * Slider for the edit_portfolio page 
 *
 * @version  1.0
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

/**
 * Initialisation function that sets up the javascript for the page.
 */
M.blocks_assmgr_edit_assess_date = {
		sel_elements: null,
		init : function(Y,elementnames,calendarid,dateformat,startdate,pastdate,futuredate) {

			this.sel_elements   = new Array();
			this.sel_elements[0]   		=  document.getElementsByName(elementnames['day'])[0];	
			this.sel_elements[1]   	=  document.getElementsByName(elementnames['month'])[0];	
			this.sel_elements[2]   	=  document.getElementsByName(elementnames['year'])[0];
			 
					
			yui_calendar(this.sel_elements,calendarid,dateformat,startdate,pastdate,futuredate,document.body);
		}
}