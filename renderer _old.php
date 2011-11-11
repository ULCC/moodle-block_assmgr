<?php


class block_assmgr_renderer extends plugin_renderer_base {
	
	function  assmgr_course_activities() {
		return $this->render(new assmgr_course_activities);
	}
	
	function render_assmgr_course_activities(assmgr_course_activities $courseactivities)	{
		global	$CFG;
		
		require_once($CFG->dirroot.'/blocks/assmgr/lib.php');
		require_once($CFG->dirroot.'/lib/gradelib.php');
		
		$activities 			=		$courseactivities->get_course_activities();
		$outcomes 				=		$courseactivities->get_course_outcomes();
		
		$out					=		"";
		
		$out					.=		html_writer::start_tag('div', array('id' => 'new_activitycontainer'));
		
		//create the table activity name and grade table
		$table					=		new html_table();
		$table->id 				=		'new_activitynametable';
		$table->data 			=		array();
		
		$row = new html_table_row();
		$row->cells['activityname']			= 		new html_table_cell();
		$row->cells['activityname']->text	=       get_string('activity','block_assmgr');
		$row->cells['grade']				= 		new html_table_cell();
		$row->cells['grade']->text			=       get_string('grade','block_assmgr');
		$table->data[]						=		$row;	
		
		foreach($activities as $a)	{
			
			// Construct an empty row first, then add to it as we go
            $row = new html_table_row();
            $row->cells['activityname']			= 		new html_table_cell();
            $row->cells['activityname']->text	=       limit_length($a->name, 20, $a->name);
            $row->cells['grade']				= 		new html_table_cell();
            $grade	=	$courseactivities->get_candidate_activity_grade($a->cminstance,$a->modulename);
            $row->cells['grade']->text			=       (!empty($grade)) ? $grade : "" ;
            
            $table->data[]	=	$row;	
		}
		
		$out									.=		html_writer::table($table);
		$out									.=		html_writer::start_tag('div', array('id' => 'new_activitywrap'));
		
		//create the outcome table
		$table									=		new html_table();
		$table->id 								=		'new_activityoutcometable';
		$table->data 							=		array();
		$data									=		array();
		$row 									= 		new html_table_row();
		foreach($outcomes	as $o) {
			$row->cells[$o->id]					= 		new html_table_cell();
			$row->cells[$o->id]->text			=       limit_length($o->shortname, 20, $o->fullname);
		}
		$table->data[]	=	$row;
		
		
		foreach($activities as $a)	{
			$row 									= 		new html_table_row();
			foreach ($outcomes	as $o) {
				$row->cells[$o->id]					= 		new html_table_cell();		
				$outcome	=	$courseactivities->get_candidate_activity_outcome($a->cminstance,$a->modulename,$o->id);
				$row->cells[$o->id]->text			=       (!empty($outcome)) ? $outcome->str_grade: "&nbsp;";
				//get outcome grade
			}
			$table->data[]	=	$row;
		}

		$out									.=		html_writer::table($table);
		$out 									.= 		html_writer::end_tag('div');
		$out 									.= 		html_writer::end_tag('div');
		return $out;
	}
	
	
}


?>