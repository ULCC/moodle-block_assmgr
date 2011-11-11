<?php

class edit_submission_mform extends assmgr_moodleform {

    private $rowflag;

    /**
     * Constructor passes through all the stuff set up already by the php page
     *
     * @global object $CFG
     * @param <type> $evidence
     * @param string $foldername
     * @param <type> $evidence_status
     * @param <type> $confirmation_status
     * @param <type> $evidence_resource
     * @param <type> $confirmation
     * @param string $needs_confirmation
     * @param bool $access_isverifier is the person viewing the form a verifier?
     * @param <type> $outcomes
     * @param <type> $candidate
     * @param <type> $evidtypes types of evidence
     * @param array $comments previously attached comments
     * @param array $feedbacks actually files that have been attached
     * @param int $submission_id
     * @param int $course_id
     * @param int $portfolio_id
     */
    function __construct($params) {

        global $CFG;

        // TODO this should all be loaded with add_data()
        $this->evidence = $params['evidence'];
        $this->foldername = $params['foldername'];
        $this->evidence_status = $params['evidence_status'];
        $this->evidence_resource = $params['evidence_resource'];
        $this->confirmation_status = $params['confirmation_status'];
        $this->confirmation = $params['confirmation'];
        $this->needs_confirmation = $params['needs_confirmation'];
        $this->access_isverifier = $params['access_isverifier'];
        $this->outcomes = $params['outcomes'];
        $this->candidate = $params['candidate'];
        $this->evidtypes = $params['evidtypes'];
        $this->comment = $params['comment'];
        $this->feedbacks = $params['feedbacks'];
        $this->submission_id = $params['submission_id'];
        $this->course_id = $params['course_id'];
        $this->portfolio_id = $params['portfolio_id'];
        $this->access_iscandidate = $params['access_iscandidate'];
        $this->access_isassessor = $params['access_isassessor'];
        $this->graded = $params['graded'];
        $this->assessor_evidence = $params['assessor_evidence'];
        $this->synchronise = $params['synchronise'];

        // call the parent constructor
        parent::__construct("{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?course_id={$this->course_id}&largefile=1&submission_id={$this->submission_id}");
    }

    /**
     *
     * @global <type> $USER
     * @global <type> $CFG
     */
    function definition() {
        global $USER, $CFG;

        $dbc = new assmgr_db;

        $mform =& $this->_form;

        // the submission is locked if it it set to auto synchronise with the gradebook
        $locked = (bool) $this->synchronise;

        $addclaimstuff = (bool)(!$locked && !$this->access_isverifier && !$this->access_isassessor && !(($this->graded || $this->assessor_evidence) && $this->access_iscandidate));

        $showassessorstuff = (bool)(!$locked && !$this->access_isverifier && $this->access_isassessor && !$this->access_iscandidate);

        if ($this->graded && $this->access_iscandidate) {

            $mform->addElement('static',
                           'lockedmessage',
                           '',
                           get_string('lockedgraded', 'block_assmgr')
                           );
        } else if ($this->assessor_evidence && $this->access_iscandidate) {

            $mform->addElement('static',
                           'lockedmessage',
                           '',
                           get_string('lockedassessor', 'block_assmgr')
                           );
        }

        if ($this->synchronise && !$this->access_iscandidate) {
            $mform->addElement('static',
               'synchronisedevidence',
               '',
               get_string('importedevidence', 'block_assmgr')
               );
        }

        // for the unsaved data check
        $mform->addElement('hidden', 'datachanged', 'false');
        $mform->setType('datachanged', PARAM_ALPHA);

        $mform->addElement('hidden', 'course_id', $this->course_id);
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('hidden', 'candidate_id', $this->candidate->id);
        $mform->setType('candidate_id', PARAM_INT);

        $mform->addElement('hidden', 'submission_id', $this->submission_id);
        $mform->setType('submission_id', PARAM_INT);

        $mform->addElement('hidden', 'portfolio_id', $this->portfolio_id);
        $mform->setType('portfolio_id', PARAM_INT);

        // top bit start
        $fieldsettitle = get_string('details', 'block_assmgr');
        $mform->addElement('header', 'details', $fieldsettitle);

        // top bit with static stuff
        $mform->addElement('static',
                           'name',
                           get_string('name', 'block_assmgr').':',
                           $this->evidence->name
                           );

        $mform->addElement('static',
                           'description',
                           get_string('description', 'block_assmgr').':',
                           assmgr_db::decode_htmlchars($this->evidence->description)
                           );

        $mform->addElement('static',
                           'folder',
                           get_string('folder', 'block_assmgr').':',
                           $this->foldername
                           );

        $mform->addElement('static',
                           'lastchanged',
                           get_string('lastchanged', 'block_assmgr').':',
                           userdate($this->evidence->timemodified, get_string('strftimedate', 'langconfig'))
                           );

        $mform->addElement('static',
                           'status',
                           get_string('status', 'block_assmgr').':',
                           $this->evidence_status
                           );

        $mform->addElement('static',
                           'confirmationstatus',
                           get_string('confirmationstatus', 'block_assmgr').':',
                           $this->confirmation_status
                           );

        $audittype = $this->evidence_resource->audit_type();

        $mform->addElement('static',
                           'resourcetype',
                           get_string('resourcetype', 'block_assmgr').':',
                           $audittype
                           );

        $content = $this->evidence_resource->get_content();

        if ($audittype == get_string('assmgr_resource_text', 'block_assmgr')) {
            $content = assmgr_db::decode_htmlchars($content);
        }

        $mform->addElement('static',
                           'resource',
                           get_string('resource', 'block_assmgr').':',
                           $content
                           );

        if(!empty($this->confirmation->feedback)) {

            $mform->addElement('static',
                           'confirmationfeedback',
                           get_string('confirmationfeedback', 'block_assmgr'),
                           $this->confirmation->feedback
                           );
        }

        if($this->needs_confirmation || empty($this->confirmation)) {

            $fieldsettitle2 = get_string('confirmation', 'block_assmgr');
            $mform->addElement('header', 'confirmation', $fieldsettitle2);

            if ($this->access_isassessor && !$this->access_isverifier) {

                $mform->addElement('checkbox', 'needs_confirmation', get_string('tickrequiresconfirmation', 'block_assmgr'));
                $mform->setType('needs_confirmation', PARAM_ALPHA);

            } else {
                $confirmmessage = ($this->needs_confirmation == true) ? 'requiresconfirmation' : 'confirmationunecessary';
                $mform->addElement('html', '<p>'.get_string($confirmmessage, 'block_assmgr').'</p>');
            }
        }

        // TODO add CSS styles here
        $tablestarthtml   = '<table class="assmgrevidence fit" id="submissionoutcomes">';
        $tableendhtml     = '</table>';
        $headerstarthtml  = '<th>';
        $headerendhtml    = '</th>';
        $rowstarthtml     = '<tr>';
        $rowstartoddhtml  = '<tr class="odd">';
        $rowstartevenhtml = '<tr class="even">';
        $rowendhtml       = '</tr>';
        $cellstarthtml    = '<td>';
        $cellendhtml      = '</td>';


        // Assessment criteria section
        if(!empty($this->outcomes)) {

            $fieldsettitle2 = get_string('assessmentcriteria', 'block_assmgr');
            $mform->addElement('header', 'assessmentcriteria', $fieldsettitle2);

            $mform->addElement('html', $tablestarthtml);

            // Table header row
            $mform->addElement('html', $rowstarthtml);
            $mform->addElement('html', $headerstarthtml);
            $mform->addElement('html', '<p>'.get_string('outcome', 'block_assmgr').'</p>');
            $mform->addElement('html', $headerendhtml);

            $mform->addElement('html', $headerstarthtml);
            $mform->addElement('html', '<p>'.get_string('description', 'block_assmgr').'</p>');
            $mform->addElement('html', $headerendhtml);

            // candidate claim bit
            $mform->addElement('html', $headerstarthtml);
            $mform->addElement('html', '<p>'.get_string('candidateclaim', 'block_assmgr').'</p>');
            $mform->addElement('html', $headerendhtml);

            $mform->addElement('html', $headerstarthtml);
            $mform->addElement('html', '<p>'.get_string('assessorgrade', 'block_assmgr').'</p>');
            $mform->addElement('html', $headerendhtml);

            $mform->addElement('html', $rowendhtml);


            // table body
            foreach($this->outcomes as $outcome) {

                //$mform->addElement('html', $rowstarthtml);
                if ($this->rowflag) {
                    $mform->addElement('html', $rowstartoddhtml);
                } else {
                    $mform->addElement('html', $rowstartevenhtml);
                }
                $this->rowflag = ($this->rowflag) ? false : true;

                $mform->addElement('html', $cellstarthtml);
                $mform->addElement('html', '<p>'.$outcome->shortname.'</p>');
                $mform->addElement('html', $cellendhtml);

                $mform->addElement('html', $cellstarthtml);
                $mform->addElement('html', '<p>'.assmgr_db::decode_htmlchars($outcome->description).'</p>');
                $mform->addElement('html', $cellendhtml);

                // candidate claim bit
                $mform->addElement('html', $cellstarthtml);

                if ($addclaimstuff) {
                    // let them set the claim if they are candidates and it's not locked
                    $key = 'candidate_criteria['.$outcome->id.']';
                    $mform->addElement('checkbox', $key, '');
                    $mform->setType($key, PARAM_ALPHA);

                } else if (!empty($outcome->claim)) {

                    $mform->addElement('html', $outcome->scale->render_scale_item($outcome->claim, true, true));

                }
                $mform->addElement('html', $cellendhtml);

                // Assessor grade bit
                $mform->addElement('html', $cellstarthtml);

                // if it's not locked and it's an assessor (no separate record)
                if ($showassessorstuff) {

                    $grade = isset($outcome->grade) ? $outcome->grade : null;
                    $selectoptions = $outcome->scale->get_select_element($grade, '', true);
                    $mform->addElement('select', "assessor_criteria_grade[{$outcome->id}]", '', $selectoptions);

                    // if it's a candidate or verifier, or if it is locked, we show the scale item
                } else {

                    if (!empty($outcome->grade)) {
                       $mform->addElement('html', $outcome->scale->render_scale_item($outcome->grade));
                    }
                }
                $mform->addElement('html', $cellendhtml);
                $mform->addElement('html', $rowendhtml);
             }

        } else {
            $mform->addElement('html', $rowstarthtml);
                $mform->addElement('html', $cellstarthtml);
                //<td colspan="4">
                    $mform->addElement('html', '<p>'.get_string('none', 'block_assmgr').'</p>');
                $mform->addElement('html', $cellendhtml);
            $mform->addElement('html', $rowendhtml);
        }

        $mform->addElement('html', $tableendhtml);

        // Evidence types bit
        $fieldsettitle2 = get_string('evidencetypes', 'block_assmgr');
        $mform->addElement('header', 'evidencetypesfs', $fieldsettitle2);

        // only show non-graded ones to assessors who will grade them, but
        // not if it has been verified.
        $verifiedalready = !empty($evidence->verified_status);
        // Main evidence types stuff
        if (!empty($this->evidtypes)) {

            foreach ($this->evidtypes as $evidkey => $evidtype) {

                // only show non-selected types to assessors who will select them, but
                // not if it has already been verified as changes are not allowed then -
                // for verifiers and candidates, just show them what the assessor chose.
                $hasbeengraded = !empty($evidtype->grade);

                if ((($this->access_isverifier || !$this->access_isassessor) && !$hasbeengraded)
                    || ($this->access_isassessor && !$this->access_isverifier && $verifiedalready)) {

                    unset($this->evidtypes[$evidkey]);
                    continue;
                }
            }

            if (count($this->evidtypes) == 0) {
                // message to say that no types have been matched yet

                $mform->addElement('html', '<p>'.get_string('noevidencetypesmatched', 'block_assmgr').'</p>');
            } else {

                $tablestarthtml  = '<table class="assmgrevidence" id="evidencetypes">';
                $mform->addElement('html', $tablestarthtml);

                // Table header row
                $mform->addElement('html', $rowstarthtml);
                $mform->addElement('html', $headerstarthtml);
                $mform->addElement('html', '<p>'.get_string('evidencetype', 'block_assmgr').'</p>');
                $mform->addElement('html', $headerendhtml);

                $mform->addElement('html', $headerstarthtml);
                // empty header for the checkboxes
                $mform->addElement('html', $headerendhtml);

                $mform->addElement('html', $rowendhtml);

                foreach($this->evidtypes as $evidkey => $evidtype) {

                    if ($this->rowflag) {
                        $mform->addElement('html', $rowstartoddhtml);
                    } else {
                        $mform->addElement('html', $rowstartevenhtml);
                    }
                    $this->rowflag = ($this->rowflag) ? false : true;

                    $mform->addElement('html', $cellstarthtml);
                    $mform->addElement('html', '<p>'.get_string($evidtype->name, 'block_assmgr').' '.helpbutton($evidtype->description, get_string($evidtype->name, 'block_assmgr'), 'block_assmgr', true, false, '', true).'</p>');
                    $mform->addElement('html', $cellendhtml);

                    $mform->addElement('html', $cellstarthtml);

                    if ($showassessorstuff) {
                        $evidencetypename = 'assessor_type['.$evidtype->id.']';
                        $mform->addElement('checkbox', $evidencetypename, '');
                        $mform->setType($evidencetypename, PARAM_ALPHA);
                    } else {

                        if (!empty($evidtype->grade)) {
                            // show the non--changeable choice
                            $mform->addElement('html', "<img src='".$CFG->wwwroot."/blocks/assmgr/pix/assessorcrit_small.png' alt='".get_string('greenticksmalllight', 'block_assmgr')."' title='".$evidtype->grade."' />");
                        }
                    }

                    $mform->addElement('html', $cellendhtml);
                    $mform->addElement('html', $rowendhtml);

                }
                $mform->addElement('html', $tableendhtml);
            }

        } else {
            $mform->addElement('html', $rowstarthtml);
            $mform->addElement('html', $cellstarthtml);
            $mform->addElement('html', '<p>'.get_string('none', 'block_assmgr').'</p>');
            $mform->addElement('html', $cellendhtml);
            $mform->addElement('html', $rowendhtml);
            $mform->addElement('html', $tableendhtml);
        }

        // overall feedback section
        $fieldsettitle2 = get_string('overallfeedback', 'block_assmgr');
        $mform->addElement('header', 'overallfeedback', $fieldsettitle2);

        // show a form to add new comments
        if($showassessorstuff) {
            $mform->addElement('htmleditor', 'comment', get_string('addnewcomment', 'block_assmgr').':', array('canUseHtmlEditor'=>'detect'));
            $mform->addRule('comment', get_string('required'), 'required', null, 'client');

            if(!empty($this->comment->feedback)) {
                $mform->setDefault('comment', $this->comment->feedback);
            }
            $mform->setType('comments', PARAM_RAW);
        } else {
            // show any previous comments
            if(!empty($this->comment)) {
                $mform->addElement('html', '<p class="assmgrevidencetitle">');
                $mform->addElement('html', get_string('previouscomments', 'block_assmgr').':');
                $mform->addElement('html', '</p>');
                $mform->addElement('html', '<p>');
                $mform->addElement('html', '<span>');
                $mform->addElement('html',  fullname($this->comment).', '.userdate($this->comment->timemodified, get_string('strftimedate', 'langconfig')).'<br />');
                $mform->addElement('html', '</span>');
                $mform->addElement('html', '<span>');
                $mform->addElement('html', $this->comment->feedback.'<br />');
                $mform->addElement('html', '</span>');
                $mform->addElement('html', '</p>');
            }  else {
                $mform->addElement('html', '<p>'.get_string('nofeedbackyet', 'block_assmgr').'</p>');
            }
        }

        //Show any previously uploaded files
        if (!empty($this->feedbacks)) {

            $mform->addElement('html', '<p class="assmgrevidencetitle">');
            $mform->addElement('html', get_string('attachedfiles', 'block_assmgr').':');
            $mform->addElement('html', '</p>');

            foreach ($this->feedbacks as $feedback) {

                $mform->addElement('html', '<p>');

                $mform->addElement('html', '<span>');
                $mform->addElement('html', fullname($dbc->get_user($feedback->creator_id)).', '.userdate($feedback->timemodified, get_string('strftimedate', 'langconfig')).'<br />');
                $mform->addElement('html', '</span>');

                // file type icon
                if (function_exists("file_mimetype_icon")) { // moodle 2
                    // icon src
                    $icon_src = $OUTPUT->pix_url(file_mimetype_icon(mimeinfo('type', $feedback->filename)));

                    //icon alt
                    $icon_alt = mimeinfo('icon', $feedback->filename);
                } else { // moodle 1
                    // icon src
                    $icon = mimeinfo('icon', $feedback->filename);
                    $icon_src = $CFG->pixpath.'/f/'.$icon;

                    // icon alt
                    $icon_alt = $icon;
                }

                // link for the file
                // Moodle 2
                if (function_exists("get_file_storage")) {
                    $context = get_context_instance(CONTEXT_BLOCK, $this->course_id);


                    $ffurl = $CFG->wwwroot."/pluginfile.php/".$context->id."/block_assmgr/$this->submission_id/".$feedback->filename;
                } else {
                    // Moodle 1

                    $feedbacks_dir = assmgr_submission_folder($this->evidence->candidate_id, $this->submission_id);
                    $ffurl = get_file_url("$feedbacks_dir/$feedback->filename", array('forcedownload'=>1));


                }
                // print link (to force download)

                $fileurl = '<a href="'.$ffurl.'" ><img src="'.$icon_src.'" class="icon" alt="'.$icon_alt.'" /> '.
                           $feedback->filename.'</a><br />';
                $mform->addElement('html', '<span>');
                $mform->addElement('html', $fileurl);
                $mform->addElement('html', '</span>');

                $mform->addElement('html', '</p>');

            }
        } else {
            $mform->addElement('html', '<p>'.get_string('nofeedbackfilesyet', 'block_assmgr').'</p>');
        }

        if ($showassessorstuff) {
            // allow new files to be uploaded
            $mform->addElement('file', 'newfile', get_string('attachafile', 'block_assmgr'));
        }

        if ($addclaimstuff || $showassessorstuff) {
            // submit and cancel buttons
            $this->add_action_buttons(true, get_string('submit'));
        }
    }

    /**
     * Saves the posted data to the database.
     *
     * @param object $data The data to be saved
     * @return bool True regardless
     */
    function process_data($data, $access_isassessor, $access_iscandidate) {

        global $USER, $CFG, $SESSION;

        // load the upload manager class file
        require_once($CFG->dirroot.'/lib/uploadlib.php');

        // bounce the user if they are not an assessor or a candidate
        if(!$access_isassessor && !$access_iscandidate) {
            print_error('cantmakeclaim', 'block_assmgr');
        }

        $comment = (isset($data->comment)) ? $data->comment : '';
        $needs_confirmation = (isset($data->needs_confirmation)) ? 1 : 0;

        // Lock portfolio if possible
        check_portfolio(null, null, $data->portfolio_id);

        $dbc = new assmgr_db();

        // get the portfolio
        $portfolio = $dbc->get_portfolio_by_id($data->portfolio_id);

        // get the submission
        $submission = $dbc->get_submission_by_id($data->submission_id);

        // get the evidence
        $evidence = $dbc->get_evidence($submission->evidence_id);

        // get the candidate id
        $candidate_id = $evidence->candidate_id;

        // bounce the user if this is not their evidence
        if ($access_iscandidate && ($USER->id != $candidate_id)) {
            print_error('cantassessownevidence', 'block_assmgr');
        }

        $course = $dbc->get_course($data->course_id);

        // Save assessor stuff if it's there
        if ($access_isassessor) {

            // MOODLE LOG submission assessed
            $log_action = get_string('logsubassessed', 'block_assmgr');
            $a = new stdClass;
            $a->name = $evidence->name;
            $a->course = $course->shortname;
            $log_info = get_string('logsubassessedinfo', 'block_assmgr', $a);
            assmgr_add_to_log($data->course_id, $log_action, null, $log_info);

            // save the assessor's comment
            $ret = $dbc->set_submission_comment($submission->id, $comment);

            // step through each outcome the evidence achieved
            foreach ($data->assessor_criteria_grade as $outcome_id => $awarded) {
                // check if the outcome was awarded an actual grade
                if (empty($awarded)) {
                    unset($data->assessor_criteria_grade[$outcome_id]);
                }
            }

            // insert the grades into the gradebook
            $dbc->set_submission_grades($submission->id, $data->assessor_criteria_grade);

            // update the needsassess flag on the portfolio
            $dbc->set_portfolio_needsassess($portfolio->id);

            // process the evidence types
            if (!empty($data->assessor_type)) {

                foreach ($data->assessor_type as $evidence_type_id => $awarded) {
                    // check if the evidence type was actually ticked
                    if(empty($awarded)) {
                        unset($data->assessor_type[$evidence_type_id]);
                    }
                }
            } else {
                $data->assessor_type = array();
            }

            // insert the evidence types into the gradebook
            $dbc->set_submission_evidence_types($submission->id, $data->assessor_type);

            // does this evidence need confirmation
            if($needs_confirmation) {
                // is there already a confirmation record (with any status)
                if(!$dbc->has_confirmation($evidence->id)) {
                    // there isn't so we need to create a pending confirmation
                    $dbc->set_confirmation($evidence->id, CONFIRMATION_PENDING);
                }
            } else {
                // this submission doesn't need confirmation, so we should delete
                // any pending confirmation records there might be, but any other
                // conifrmation records should remain
                $dbc->delete_confirmation($evidence->id, CONFIRMATION_PENDING);
            }

            // process the uploaded file (if any)
            $upload_error = false;

            // file directory
            $dir = assmgr_submission_folder($evidence->candidate_id, $data->submission_id);

            // init the upload manager class (specify the file IS NOT REQUIRED)
            $um = new upload_manager('newfile', false, true, $course, false, 0, true, true);

            // if there is a file
            if(isset($_FILES['newfile']['tmp_name']) && ($_FILES['newfile']['tmp_name'] != "")) {

                // fix Moodle Lib notice bug
                $_FILES["newfile"]['uploadlog'] = "";

                // file validation (basically for file SIZE)
                if($um->validate_file($_FILES["newfile"])) {
                    // there are two different procedures for Moodle 1 and Moodle 2
                    // Moodle 2
                    if(function_exists("get_file_storage")) {
                        // TODO for some reason the context id IS NOT WORKING PROPERLY...

                        $block = $DB->get_record('block', array('name'=>'assmgr'));
                        $context = get_context_instance(CONTEXT_BLOCK, $block->id);

                        $browser = get_file_browser();

                        $file_info = $browser->get_file_info($context, 'block_assmgr', 0, "/", "pippo");

                        $file = $_FILES['newfile'];
                        $newfilename = clean_param($file['name'], PARAM_FILE);

                        if (is_uploaded_file($_FILES['newfile']['tmp_name'])) {

                            if (!$file_info->create_file_from_pathname($newfilename, $_FILES['newfile']['tmp_name'], $USER->id)) {
                                $fs = get_file_storage();
                                $time = time();
                                $file_record = array('contextid'=>$context->id, 'filearea'=>'block_assmgr', 'itemid'=>0, 'filepath'=>'/'.$data->submission_id.'/', 'filename'=>$newfilename,
                                                     'timecreated'=>$time, 'timemodified'=>$time);

                                if ($fs->create_file_from_pathname($file_record, $_FILES['newfile']['tmp_name'])) {

                                    if (!$dbc->create_submission_feedback($data->submission_id, $newfilename)) {
                                        print_error('cantsavefeedbackfile', 'block_assmgr');
                                    }
                                }

                            }
                        }

                    } else { // Moodle 1

                        if ($um->process_file_uploads($dir)) { // the file is not too big
                            // save the feedback file (ONLY if is uploaded!)

                            if ($um->files["newfile"]["originalname"] != "") {

                                if (!$dbc->create_submission_feedback($data->submission_id, $um->get_new_filename())) {
                                    print_error('cantsavefeedbackfile', 'block_assmgr');
                                }
                            }
                        }
                    }


                } else { // not valid (too big)

                    // ERROR: the file is too big
                    $upload_error = true;
                    $SESSION->block_assmgr_uploaderror = get_string('filetoobig', 'block_assmgr');
                }
            }

            // in case of errors
            if($upload_error) {

                // redirect to the form and display the error
                $return_message = get_string('uploadtoobig', 'block_assmgr');
                redirect("{$CFG->wwwroot}/blocks/assmgr/actions/edit_submission.php?submission_id={$data->submission_id}&course_id={$data->course_id}#id_newfile", $return_message, REDIRECT_DELAY);
            }

        } else if ($access_iscandidate) {

            // the claim is locked if the evidence has been graded, or if it was submitted by someone else
            $locked = ($dbc->has_submission_grades($submission->id) || $evidence->candidate_id != $submission->creator_id);

            if($locked) {
                print_error('noeditclaim', 'block_assmgr');
            }

            //MOODLE LOG claim made
            $log_action = get_string('logsubclaimcreate', 'block_assmgr');
            $a = new stdClass;
            $a->name = $evidence->name;
            $a->shortname = $course->shortname;
            $log_info = get_string('logsubclaimsave', 'block_assmgr', $a);
            assmgr_add_to_log($data->course_id, $log_action, null, $log_info);

            // process the submission claims
            if (!empty($data->candidate_criteria)) {

                foreach ($data->candidate_criteria as $outcome_id => $awarded) {
                    // check if the evidence type was actually ticked
                    if(empty($awarded)) {
                        unset($data->candidate_criteria[$outcome_id]);
                    }
                }
            } else {
                $data->candidate_criteria = array();
            }

            // save the submission claims
            $dbc->set_submission_claims($submission->id, $data->candidate_criteria);
        }

        // getting this far with no errors means OK
        return true;
    }

    /**
     * TODO comment this
     */
    function definition_after_data() {
        global $PARSER;

        //check to see if the large file flag has been set
        $large_file = $PARSER->optional_param('largefile',NULL,PARAM_INT);
        if (!empty($large_file) && empty($_POST) && empty($_FILES)) {
           $this->_form->setElementError('newfile', get_string('uploadserverlimit'));
        }
    }
}