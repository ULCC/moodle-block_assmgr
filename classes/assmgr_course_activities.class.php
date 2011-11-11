<?php

global $CFG;

require_once($CFG->dirroot.'/lib/gradelib.php');

class assmgr_course_activities implements renderable	{
	
	/*
	 * id of the course the activities should be in 
	 */
	public 			$course_id;
	
	/*
	 * id of the groups the activities should be in 
	 */
	public			$group_id;
	
	/*
	 * id of the candidate_id
	 */
	public			$candidate_id;
	
	/*
	 * holds the database connection class
	 */
	public 			$dbc;
	
	
	/*
	 * not sure if this is the best way to pass whether the user is an assessor 
	 * to the renderer
	 */
	public			$is_assessor;
	
	
	/**
	 * 
	 * Constructs the assmgr_course_activites class
	 * @param int $course_id	the course that the activites will be taken from 
	 * @param int $candidate_id the candidate whom the activities are for (placed here
	 * @param int $group_id		the group_id that the activites must be in   
	 * for expected futuree use)
	 * 
	 */
	function __construct($course_id,$candidate_id=null,$group_id=null)	{
		global $CFG,$DB;

        // include the assmgr db
        require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

        // instantiate the assmgr db
        $this->dbc = new assmgr_db();
		
		
		$this->course_id		=	$course_id;
		$this->group_id			=	$group_id;
		$this->candidate_id		=	$candidate_id;

	}
	
	/**
	 * Returns an object containing the activities in the course (given in the class constructor)
	 * 
	 * @return array of objects containing course activity details or false 
	 */
	function get_course_activities()		{
		return $this->dbc->get_course_activities_matrix($this->course_id,$this->group_id); 
	} 
	
	
	
	/**
	 * 
	 * Returns the outcomes for the current course (given in course constructor) 
	 * 
	 * @return mixed array all outcomes for the current current course or bool false
	 */
	function get_course_outcomes()	{
		return $this->dbc->get_outcomes($this->course_id);
	}
	
	/**
	 * Returns the grade for the current candidate in the given module instance in current course
	 * 
	 * @param 	int $instance_id the instance id of the activity taken from the course_module table
	 * @param	int $activitymodule the name of the module 
	 * 
	 *  @return	mixed object containing grade data or false
	 */
	function get_candidate_activity_grade($instance_id,$activitymodule)	{
		
		$grade	=	 grade_get_grades($this->course_id,'mod',$activitymodule,$instance_id,$this->candidate_id);
		
		return (isset($grade->items[0]->grades[$this->candidate_id]->grade)) ? $grade->items[0]->grades[$this->candidate_id]->grade : "";
	}
	
	/**
	 * Returns the grade for the current candidate in the given module instance in current course
	 * 
	 * @param 	int $outcome_id the instance id of the activity taken from the course_module table
	 * 
	 *  @return	mixed object containing grade data or false
	 */
	function get_candidate_course_outcome_grade($outcome_id)	{
		$grades	=	 grade_get_grades($this->course_id,'outcome',NULL,$outcome_id,$this->candidate_id);

       	if (!empty($grades)) {
           	$grades		=	array_pop($grades->items);
           	$grade = !empty($grades->grades[$this->candidate_id]->grade) ? $grades->grades[$this->candidate_id]->grade : null;
       	} else {
       		$grade	=	null;
       	}
		return $grade;
	}
	
	/**
	 * Returns the grade for a particular outcomes for the 
	 * 
	 * @param 	int $instance_id	the instance id of the activity taken from the course_module table
	 * @param	int $activitymodule the name of the module
	 * @param	int	$outcome_id		the id of the outcome that the grade will be returned for
	 * 
	 *  @return	mixed object containing grade_outcome data or false
	 */
	function get_candidate_activity_outcome($instance_id,$activitymodule,$outcome_id)	{
		
		//as the grades name is the only real link back to outcome provided by (the data returned from) grade_get_grades function we need
		//to query the grade_items table to retrieve the itemnumber of the outcome for this activity (if it exists) 
		//we can then use this information to get the grade from the data grade_get_grades returns 
		$outcome_grade_item		=	$this->dbc->get_activity_outcome($this->course_id,$activitymodule,$instance_id,$outcome_id);
		
		//if the outcome grade item exists 	
		if (!empty($outcome_grade_item)) {
			//get the outcome grades 
			$grade		=	 	grade_get_grades($this->course_id,'mod',$activitymodule,$instance_id,$this->candidate_id);
			$outcome	=		$grade->outcomes[$outcome_grade_item->itemnumber]->grades[$this->candidate_id];			
			
			//append additional data to the outcome object
			$outcome->scaleid		=	$grade->outcomes[$outcome_grade_item->itemnumber]->scaleid;
			$outcome->name			=	$grade->outcomes[$outcome_grade_item->itemnumber]->name;
			$outcome->itemnumber	=	$grade->outcomes[$outcome_grade_item->itemnumber]->itemnumber;
			return $outcome;
		}
		
		return false;
		
		
	}
	
	function get_edit_activity_link($cm_id,$instance_id) {
        global $CFG, $OUTPUT;

        $title = get_string('editactivitygrades', 'block_assmgr');
        $url = "{$CFG->wwwroot}/blocks/assmgr/actions/edit_activitygrades.php?cm_id={$cm_id}&amp;course_id={$this->course_id}&amp;candidate_id={$this->candidate_id}&amp;instance_id={$instance_id}";

        $link = "<a class='editing_update' title='{$title}' href='{$url}'>
                    <img src='".$OUTPUT->pix_url('t/edit')."' class='iconsmall' alt='{$title}' />
                </a>";

        return $link;
    }
	
	
	
}

?>