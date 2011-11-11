<?php

/**
 * Autoload function means that files in the main classes folder (not subfolders)
 * will be included automatically when the classes are instantiated
 */
function __autoload($classname) {
    global $CFG;
    if (file_exists($CFG->dirroot.'/block_assmgr/classes/'.$classname.'class.php')) {
        require_once($CFG->dirroot.'/block_assmgr/classes/'.$classname.'class.php');
    }
}

//include the moodle library
require_once($CFG->dirroot.'/lib/moodlelib.php');

//include the assessment manager parser class
require_once($CFG->dirroot.'/blocks/assmgr/classes/assmgr_parser.class.php');

//include assessment manager db class
require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');

//include the library file
require_once($CFG->dirroot.'/blocks/assmgr/lib.php');

//load the access rights of the current user
require_once($CFG->dirroot.'/blocks/assmgr/db/accesscheck.php');

//include the static constants
require_once($CFG->dirroot.'/blocks/assmgr/constants.php');