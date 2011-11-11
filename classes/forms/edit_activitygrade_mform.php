<?php

global $CFG;

require_once($CFG->dirroot.'/lib/gradelib.php');

class edit_activitygrade_mform extends moodleform {

	public 		$course_id;
	public		$module_id;
	public		$candidate_id;
	public 		$coursemodule_id;
	public 		$instance_id;
	public 		$dbc;
		
  	

    /**
     * 	Constructor passes the data needed to construct the form to enable the outcomes to edited
     *
     *	@param	int	$course_id			the id of the course that the activity is is in
     *	@param	int	$candidate_id  		the id of the candidate who created the activity
     *	@param	int	$coursemodule_id	the id of the activities coursemodule record
     *	@param 	int $instance_id		the activities instance id  
     */
    function __construct($course_id,$candidate_id,$coursemodule_id,$instance_id) {

        global $CFG;
        
        $this->dbc 				= new assmgr_db;
        
        $this->course_id		=	$course_id;
        $this->candidate_id		=	$candidate_id;
        $this->coursemodule_id	=	$coursemodule_id;
        $this->instance_id		=	$instance_id;
	
        //we will get the module id by getting the coursemodule record
        $cm						=	$this->dbc->get_coursemodule($coursemodule_id);
        
        $this->module_id		=	$cm->module;
        
        $urlparams	=	"course_id={$course_id}&candidate_id&cm_id={$coursemodule_id}&instance_id={$instance_id}";
        
        // call the parent constructor
        parent::__construct("{$CFG->wwwroot}/blocks/assmgr/actions/edit_activitygrades.php?{$urlparams}");
    }

    /**
     *
     */
    function definition() {
        global $USER, $CFG;

        $mform =& $this->_form;

        
        // course_id
        $mform->addElement('hidden', 'course_id', $this->course_id);
        $mform->setType('course_id', PARAM_INT);
        
		// module_id
        $mform->addElement('hidden', 'module_id', $this->module_id);
        $mform->setType('module_id', PARAM_INT);

		// candidate_id
        $mform->addElement('hidden', 'candidate_id', $this->candidate_id);
        $mform->setType('candidate_id', PARAM_INT);        
        
		// coursemodule_id
        $mform->addElement('hidden', 'coursemodule_id', $this->coursemodule_id);
        $mform->setType('coursemodule_id', PARAM_INT);     

		// instance_id
        $mform->addElement('hidden', 'instance_id', $this->instance_id);
        $mform->setType('instance_id', PARAM_INT);             
                
        $course			=	$this->dbc->get_course($this->course_id);
        $candidate		=	$this->dbc->get_user($this->candidate_id);
       
       	$module			=	$this->dbc->get_module($this->module_id);
       	
       	//get the grade item for the activity
       	$grade			= 	grade_get_grades($this->course_id,'mod',$module->name,$this->instance_id,$this->candidate_id);
        
       	
       	//set the course name 
        $mform->addElement('static','coursename',get_string('course', 'block_assmgr'),$course->shortname);

        //set the candidate name
        $mform->addElement('static','candidatename',get_string('user', 'block_assmgr'),fullname($candidate));

        //set the activity
        $mform->addElement('static','activtyname',get_string('activity', 'block_assmgr'),$grade->items[0]->name);
       
        
        $mform->addElement('static','grade',get_string('grade','block_assmgr'),$grade->items[0]->grades[$this->candidate_id]->grade);
        
        //get the outcomes for the course that the activity is in 
        $outcomes		=	$this->dbc->get_outcomes($this->course_id);
        
        foreach($outcomes as $o)	{
        	
        	//get the scale for the outcomes
        	$scale	=	$this->dbc->get_scale($o->scaleid);
        	$options	=	array();
        	
        	foreach ($scale->load_items() as $idx => $item)	{
        		$options[$idx+1]	=	$item;	
        	}
        	
        	$mform->addElement('select',$o->id."_field",$o->shortname,$options);
        	
			//as the grades name is the only real link back to outcome provided by (the data returned from) grade_get_grades function we need
			//to query the grade_items table to retrieve the itemnumber of the outcome for this activity (if it exists) 
			//we can then use this information to get the grade from the data grade_get_grades returns 
        	$outcome_grade_item		=	$this->dbc->get_activity_outcome($this->course_id,$module->name,$this->instance_id,$o->id);
        	
        	if (!empty($outcome_grade_item)) {
        		$outgrade	=		$grade->outcomes[$outcome_grade_item->itemnumber]->grades[$this->candidate_id];        	
        		$mform->setDefault($o->id."_field", $outgrade->grade);
        	}
        	
        	
        }
        
        

        // submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return bool True regardless
     */
    function process_data($data) {

        global $USER, $CFG, $SESSION;

        
        
        $module			=	$this->dbc->get_module($this->module_id);
        
        $source		=	"mod/{$module->name}";	
        
        
        
        
        //get the outcomes for the course that the activity is in 
        $outcomes		=	$this->dbc->get_outcomes($this->course_id);
        $newitemnumber		=	1000;
        
        $grades	=	array();	
        
        foreach($outcomes as $o)	{
        	
        	$fieldname		=	$o->id."_field";
        	
        	$fieldvalue		=	$data->$fieldname;
        	
        	//as the grades name is the only real link back to outcome provided by (the data returned from) grade_get_grades function we need
			//to query the grade_items table to retrieve the itemnumber of the outcome for this activity (if it exists) 
			//we can then use this information to get the grade from the data grade_get_grades returns 
        	$outcome_grade_item		=	$this->dbc->get_activity_outcome($this->course_id,$module->name,$this->instance_id,$o->id);
        	
        	//I am not sure if setting the itemnumber to 0 will work for the outcomes as there is usually more than one.
        	//we may have to create itemnumbers manually
        	$itemnumber				=	($outcome_grade_item)	?	$outcome_grade_item->itemnumber	:	$newitemnumber;
        	
        	if (!empty($outcome_grade_item))	{ 
        	      	//not sure if this will work 
        			$grades[$outcome_grade_item->itemnumber]	=	$fieldvalue;
        	} else {
        		
        		$scale				=	$this->dbc->get_scale($o->scaleid);
        		
        		//create the grade item
        		$grade				=	new grade_item();
        		$grade->courseid	=	$this->course_id;
        		$grade->itemname	=	$o->shortname;
        		$grade->itemtype	=	'mod';
        		$grade->itemmodule	=	$module->name;
        		$grade->iteminstance	=	$this->instance_id;
        		$grade->itemnumber		=	$itemnumber;
        		$grade->scaleid			=	$o->scaleid;
        		$grade->gradetype 		= GRADE_TYPE_SCALE;
        		$grade->outcomeid		=	$o->id;

        		//insert the grade item
        		$grade->insert($source);
        		
        		
        		// we can now set the outcome grade 
        		$grades[$itemnumber]	=	$fieldvalue;
        	}

			//we need to set the new itemnumber to the value of the current itemnumber and then increment it 
			//this will make sure that any new outcome that we are adding will keep the correct number sequence
			//in the grade_items table.
        	if (!empty($outcome_grade_item->itemnumber))	$newitemnumber	=	$itemnumber;
        	
        	$newitemnumber++;
        }
        
        if (!empty($grades)) grade_update_outcomes($source,$this->course_id,"mod",$module->name,$this->instance_id,$this->candidate_id,$grades);
        
        // getting this far with no errors means OK
        return true;
    }

    /**
     * TODO comment this
     */
    function definition_after_data() {
        global $PARSER;

    }
}