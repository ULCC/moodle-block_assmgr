<?php


class block_assmgr_renderer extends plugin_renderer_base {
	/*
	function  assmgr_course_activities() {
		return $this->render(new assmgr_course_activities);
	}
	*/
	function render_assmgr_course_activities(assmgr_course_activities $courseactivities)	{
		global	$CFG,$PAGE;
		
		require_once($CFG->dirroot.'/blocks/assmgr/lib.php');
		
		$is_assessor	=	$courseactivities->is_assessor;

		
		//$jquerycss = new moodle_url($CFG->wwwroot."/blocks/assmgr/views/js/fixedheadertable/css/defaulttheme.css");
		$jquery    		= "/blocks/assmgr/views/js/jquery-1.5.2.min.js";
		//$fixedheader    = "/blocks/assmgr/views/js/jquery.fixedtable.js";
		$fixedheader    = "/blocks/assmgr/views/js/jquery.fixedtable.js";
		$PAGE->requires->js($jquery);
		$PAGE->requires->js($fixedheader);
		//$PAGE->requires->css_theme($jquerycss);
		
		
		$renderer    = "/blocks/assmgr/views/js/renderer.js";
		$PAGE->requires->js($renderer);
		/*
		$jsmodule = array(
    		'name'     	=> 'render_assmgr_course_activities',
    		'fullpath' 	=> '/blocks/assmgr/views/js/renderer.js'
		);

		$PAGE->requires->js_init_call('M.render_assmgr_course_activities.init', null, true, $jsmodule);
		*/
		$activities 			=		$courseactivities->get_course_activities();
		$outcomes 				=		$courseactivities->get_course_outcomes();
		
		if (!empty($activities)) {
			

			
			$out	=	html_writer::start_tag('div',array('id'=>'assessdiv','class'=>'fixedtableclass'));
			
			if (!empty($is_assessor)) {
				//create a form to wrap the table in this will allow us to save the outcome values
				$out					.=		html_writer::start_tag('form', array('id' => 'outassessform',
																					 'method' => 'post',
																					 'action' => 'save_outcomes_assessment.php',));
				
				//
				$out					.=		html_writer::start_tag('input', array('type'=>'hidden','name'=>'course_id','value'=>$courseactivities->course_id));
				$out					.=		html_writer::start_tag('input', array('type'=>'hidden','name'=>'candidate_id','value'=>$courseactivities->candidate_id));
			}
						
			//create the table activity name and grade table
			$table					=		new html_table();
			$table->id 				=		'new_activitynametable';
			$table->class			=		'FixedTables';
	
			//define table headers
			$table->head 			=		array();
			$table->head['activityname']			= 		new html_table_cell();
			$table->head['activityname']->text	=       get_string('activity','block_assmgr');
			$table->head['grade']				= 		new html_table_cell();
			$table->head['grade']->text			=       get_string('grade','block_assmgr');
			
			foreach($outcomes	as $o) {
				$table->head[$o->id]					= 		new html_table_cell();
				
				$currentgradeitem_id									=	$courseactivities->get_candidate_course_outcome_grade($o->id);
				$table->head[$o->id]->text				=   limit_length($o->shortname, 20, $o->fullname)." ".$this->get_outcome_header($o->id,$currentgradeitem_id,$is_assessor);//
			}
			
			
			//create table body content
			$table->data 						=		array();
	
			foreach($activities as $a)	{
				
				// Construct an empty row first, then add to it as we go
	            $row = new html_table_row();
	            $row->cells['activityname']			= 		new html_table_cell();
	            
	            if ($is_assessor)	{
	            	$editlink	=	$courseactivities->get_edit_activity_link($a->cm_id,$a->cminstance);
	            	$row->cells['activityname']->text	=       limit_length($a->name, 20, $a->name)." {$editlink}" ;
	            	
	            } else {
	                $row->cells['activityname']->text	=       limit_length($a->name, 20, $a->name);
	            }
	            
	            
	            
	            
	            $row->cells['grade']				= 		new html_table_cell();
	            $grade	=	$courseactivities->get_candidate_activity_grade($a->cminstance,$a->modulename);
	            $row->cells['grade']->text			=       (!empty($grade)) ? $grade : "" ;
	            
	            foreach ($outcomes	as $o) {
					$row->cells[$o->id]					= 		new html_table_cell();		
					$outcome	=	$courseactivities->get_candidate_activity_outcome($a->cminstance,$a->modulename,$o->id);
					$row->cells[$o->id]->text			=       (!empty($outcome)) ? $outcome->str_grade: "&nbsp;";
					//get outcome grade
				}
				$table->data[]	=	$row;
			}
			
			$out										.=		 html_writer::table($table);
			
			if (!empty($is_assessor)) {
				$out									.=		html_writer::start_tag('noscript');
				$out									.=		html_writer::start_tag('div',array('id'=>'BtnPosition'));
				$out									.=		html_writer::start_tag('input',array('type'=>'submit',
																									  'name'=>'submit',
																									  'id'=>'submissiontablesubmit',
																									  'value'=>get_string('saveassessment', 'block_assmgr')));
				
				$out									.=		html_writer::end_tag('div');
				$out									.=		html_writer::end_tag('noscript');
				$out 									.= 		html_writer::end_tag('form');
		
			}
			
			$out	.=		html_writer::end_tag('div');
		}	else {
			
			$out	=	html_writer::start_tag('div',array('id'=>'nothingtodisplay'));
			$out	.=	get_string('nothingtodisplay');
			$out	.=		html_writer::end_tag('div');
			
			
			
			/*
			$jsmodule = array(
    			'name'     	=> 'view_submissions',
    			'fullpath' 	=> '/blocks/assmgr/views/js/view_submissions.js',
    			'requires'  => array('yui_dom')
			);

			$PAGE->requires->js_init_call('M.assmgr.view_submissions.hidecolumns()', null, true, $jsmodule);
			//$PAGE->requires->js_init_call('M.assmgr.view_submissions.init()', null, true, $jsmodule);
			*/
		}
		
		
		
		return $out;
	}
	
	
	 /**
     * Adds extra information to the column headers
     *
     * @param string $column the name of the column e.g. 'outcome3'
     * @return string|null
     */
    function get_outcome_header($outcome_id,$currentgradeitem_id=false,$isassessor=false) {

        global $CFG;

        $dbc	=	new assmgr_db();
        
        //get the record for the outcome
        $outcome = $dbc->get_outcome($outcome_id);

        // get the scale items
        $scale	=	$dbc->get_scale($outcome->scaleid);
        
        //get the grade item
        
		//check if the user is an assessor
        if (!empty($isassessor)) {
            
            // get the grade value
            //$item_id = (!empty($this->grades[$id])) ? $this->grades[$id]->scale_item : null;

            // Assessor needs to see the dropdowns to change the grade

                return '<br/>
                        <div id="outcomediv'.$outcome->id.'" class="assmgroutcomediv">

                             <span class="columngrade hidden" id ="columngrade'.$outcome->id.'">'
                                .$scale->render_scale_item($currentgradeitem_id)
                           .'</span>
                             <span class="columnselect" id ="columnselect'.$outcome->id.'">'
                                .$scale->get_select_element($currentgradeitem_id, array('onclick'=>'suppressClick(event);', 'name'=>"outcomes[{$outcome->id}]", 'id' => 'columnselect'.$outcome->id.'select'))
                           .'</span>
                             <span id="columnedit'.$outcome->id.'" class="commands">
                                <img src="'.$CFG->wwwroot.'/pix/t/edit.gif" id="editicon'.$outcome->id.'" class="editicon iconsmall hidden" title="'
                                  .get_string('changegrade', 'block_assmgr').'" alt="'.get_string('changegrade', 'block_assmgr').'" />
                             </span>
                             <span id="columnloader'.$outcome->id.'">
                             </span>
                         </div>';
         } else {
                //if not assessor the user may only view the current outcome grade
                    return '<br/><div class="hiddenoutcomegrade">'.$scale->render_scale_item($currentgradeitem_id).'</div>';
         }


        return null;
    }
	
	
	
}


?>