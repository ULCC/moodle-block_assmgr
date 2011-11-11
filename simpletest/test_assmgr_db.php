<?php
/**
 * Unit tests for /blocks/assmgr/db/assmgr_db.php
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
if (!defined('MOODLE_INTERNAL')) {
    // this must be included from a Moodle page
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir . '/simpletestcoveragelib.php');              // Include the test libraries
require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');    // include the file being tested

/**
 * Test functions that rely on the DB tables for Assessment Manager block
 */
class assmgr_db_test extends UnitTestCaseUsingDatabase {

    // test coverage
    public static $includecoverage = array('blocks/assmgr/db/assmgr_db.php');

    // tables (needed by the installation procedure)
    // I have to create test tables to don't
    // touch the database
    public $grade_tables = array('lib' => array(
                                        //'block', 'block_instances', 'cache_flags', 'capabilities', 'config', 'config_log', 'config_plugins',
                                        'block', 'cache_flags', 'capabilities', 'config', 'config_plugins',
                                        'context', 'context_temp', 'course', 'course_allowed_modules', 'course_categories',
                                        //'course_meta', 'course_modules', 'course_sections', 'events_handlers', 'grade_categories',
                                        'course_meta', 'course_sections', 'events_handlers', 'grade_categories',
                                        //'grade_categories_history', 'grade_grades', 'grade_items', 'grade_items_history',
                                        //'grade_outcomes', 'grade_outcomes_courses', 'grade_outcomes_history', 'log', 'modules',
                                        'log', 'modules',
                                        //'portfolio_instance', 'repository', 'role', 'role_assignments', 'role_capabilities',
                                        'portfolio_instance', 'repository', 'role',
                                        //'scale', 'scale_history', 'sessions', 'user'),
                                        'scale', 'user'),
                                 'mod/glossary' => array(
                                        'glossary_formats'),
                                 'mod/forum' => array(
                                        'forum'),
                                 'blocks/assmgr' => array(
                                        'block_assmgr', 'block_assmgr_claim', 'block_assmgr_confirmation', 'block_assmgr_evidence',
                                        'block_assmgr_evidence_type', 'block_assmgr_feedback', 'block_assmgr_folder',
                                        'block_assmgr_lock', 'block_assmgr_log', 'block_assmgr_portfolio',
                                        'block_assmgr_resource', 'block_assmgr_resource_type', 'block_assmgr_sub_evid_type')
                            );

    private $assmgr_db;       // database class
    private $testdependencies = array();  // test dependencies

    // testing useful variables
    private $candidate_id = 4;  // candidate id (learner01 => 4)
    private $folder_id;         // the folder id

    /**
     * Constructor runs default functions and sets up tables
     *
     * @return void
     */
    function __construct() {
        parent::__construct();
        $this->manualSetUp();
    }

    /**
     * Runs at the start and makes sure that the test DB is in use, not the real one
     *
     * @return void
     */
    function setUp(){
        global $DB;
        if ($DB !== $this->testdb) {
            $this->switch_to_test_db();
        }
    }

    /**
     * Runs at the end. Currently empty.
     *
     * @return void
     */
    function tearDown() {
        // do nothing
    }

    /**
     * manual setup of tables (to avoid creating test tables for any single test)
     *
     * @return void
     */
    public function manualSetUp() {
        global $DB;

        // instantiate the assmgr db
        $this->assmgr_db = new assmgr_db();

        $this->switch_to_test_db(); // All operations until end of test method will happen in test DB

        // create test tables
        foreach ($this->grade_tables as $dir => $tables) {
            $this->create_test_tables($tables, $dir); // Create tables
        }

        // fill tables (copying from the mdl db)
        // GET datas
        $this->revert_to_real_db();
        $users = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {user}"
                                    );
        $capabilities = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {capabilities}"
                                    );
        $config = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {config}"
                                    );
        $configplugins = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {config_plugins}"
                                    );
        $course = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {course}"
                                    );
        $modules = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {modules}"
                                    );
        $glossary_formats = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {glossary_formats}"
                                    );
        $block = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {block}"
                                    );
        $repository = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {repository}"
                                    );
        $role = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {role}"
                                    );
        /*$role_capabilities = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {role_capabilities}"
                                    );*/
        $course_sections = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {course_sections}"
                                    );
        $events_handlers = $DB->get_records_sql(
                                        "SELECT *
                                         FROM {events_handlers}"
                                    );

        // INSERT datas
        $this->switch_to_test_db();
        foreach($users as $user){
            $DB->insert_record("user", $user, true);
        }
        foreach($capabilities as $capability){
            $DB->insert_record("capabilities", $capability, true);
        }
        foreach($config as $conf){
            $DB->insert_record("config", $conf, true);
        }
        foreach($configplugins as $configplugin){
            $DB->insert_record("config_plugins", $configplugin, true);
        }
        foreach($course as $cour){
            $DB->insert_record("course", $cour, true);
        }
        foreach($modules as $module){
            $DB->insert_record("modules", $module, true);
        }
        foreach($glossary_formats as $glossary_format){
            $DB->insert_record("glossary_formats", $glossary_format, true);
        }
        foreach($block as $bl){
            $DB->insert_record("block", $bl, true);
        }
        foreach($repository as $repo){
            $DB->insert_record("repository", $repo, true);
        }
        foreach($role as $ro){
            $DB->insert_record("role", $ro, true);
        }
        /*foreach($role_capabilities as $role_capability){
            $DB->insert_record("role_capabilities", $role_capability, true);
        }*/
        foreach($course_sections as $course_section){
            $DB->insert_record("course_sections", $course_section, true);
        }
        foreach($events_handlers as $events_handler){
            $DB->insert_record("events_handlers", $events_handler, true);
        }

        // default context
        $context = new stdClass();
        $context->contextlevel = 10;
        $context->instanceid = 0;
        $context->path = "/1";
        $context->depth = 1;
        $DB->insert_record("context", $context, true);
        $context->contextlevel = 50;
        $context->instanceid = 1;
        $context->path = "/1/2";
        $context->depth = 2;
        $DB->insert_record("context", $context, true);
        $context->contextlevel = 40;
        $context->instanceid = 1;
        $context->path = "/1/3";
        $context->depth = 2;
        $DB->insert_record("context", $context, true);
        $context->contextlevel = 80;
        $context->instanceid = 1;
        $context->path = "/1/2/4";
        $context->depth = 3;
        $DB->insert_record("context", $context, true);
    }


    // STARTING UNITS TESTS

    /**************************************/
    /*      DATABASE FUNCTIONS TESTS      */
    /**************************************/

    /**
     * Creates a folder record test
     *
     * @return void
     */
    function test_create_folder() {
        global $DB;

        $this->testdependencies['test_create_folder'] = true;

        // try to create a folder
        $name           = "test_folder";
        $candidate_id   = $this->candidate_id;
        $parent_id      = null;

        $folder_id = $this->assmgr_db->create_folder($name, $candidate_id, $parent_id);

        // check if has been added properly
        $folder = $DB->get_record("block_assmgr_folder", array("id" => $folder_id));
        $this->testdependencies['test_create_folder'] &= $this->assertIsA($folder, "stdClass", 'Insert new folder test');
        if(!empty($folder)){ // if it's empty it means an assert ahs been already prompted
            $this->testdependencies['test_create_folder'] &= $this->assertEqual($folder->name, $name, 'Insert new folder test');
            $this->testdependencies['test_create_folder'] &= $this->assertNull($folder->folder_id, 'Insert new folder test');
            $this->testdependencies['test_create_folder'] &= $this->assertEqual($folder->id, $folder_id, 'Insert new folder test');

            // save for next test
            $this->folder_id = $folder_id;
        }

    }

    /**************************************/
    /*       ENCODING/DECODING TESTS      */
    /**************************************/
    /**
     * Tests encoding of strings before they are saved to the database.
     *
     * @return void
     */
    function test_encode() {
        // test encoding HTML reserved characters
        $test1 = '"\'&<>';
        $result1 = $this->assmgr_db->encode($test1);
        $answer1 = '&quot;&#039;&amp;&lt;&gt;';
        $this->assertEqual($result1, $answer1, 'Test encoding of HTML reserved characters');

        // test encoding ISO 8859-1 symbols
        $test2 = '¡¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶·¸¹º»¼½¾¿×÷';
        $result2 = $this->assmgr_db->encode($test2);
        $answer2 = '&iexcl;&cent;&pound;&curren;&yen;&brvbar;&sect;&uml;&copy;&ordf;&laquo;&not;&reg;&macr;&deg;&plusmn;&sup2;&sup3;&acute;&micro;&para;&middot;&cedil;&sup1;&ordm;&raquo;&frac14;&frac12;&frac34;&iquest;&times;&divide;';
        $this->assertEqual($result2, $answer2, 'Test encoding of ISO 8859-1 symbols');

        // test encoding ISO 8859-1 characters
        $test3 = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ';
        $result3 = $this->assmgr_db->encode($test3);
        $answer3 = '&Agrave;&Aacute;&Acirc;&Atilde;&Auml;&Aring;&AElig;&Ccedil;&Egrave;&Eacute;&Ecirc;&Euml;&Igrave;&Iacute;&Icirc;&Iuml;&ETH;&Ntilde;&Ograve;&Oacute;&Ocirc;&Otilde;&Ouml;&Oslash;&Ugrave;&Uacute;&Ucirc;&Uuml;&Yacute;&THORN;&szlig;&agrave;&aacute;&acirc;&atilde;&auml;&aring;&aelig;&ccedil;&egrave;&eacute;&ecirc;&euml;&igrave;&iacute;&icirc;&iuml;&eth;&ntilde;&ograve;&oacute;&ocirc;&otilde;&ouml;&oslash;&ugrave;&uacute;&ucirc;&uuml;&yacute;&thorn;&yuml;';
        $this->assertEqual($result3, $answer3, 'Test encoding of ISO 8859-1 characters');

        // test not double encoding
        $test4 = $test1.$test2.$test3;
        $result4 = $this->assmgr_db->encode($this->assmgr_db->encode($test4));
        $answer4 = $answer1.$answer2.$answer3;
        $this->assertEqual($result4, $answer4, 'Test not double encoding of characters');

        // test remove MS word symbol characters
        $test5 = 'No ₣₧₫ℓ more ℅№Ω☼ junk';
        $result5 = $this->assmgr_db->encode($test5);
        $answer5 = 'No  more  junk';
        $this->assertEqual($result5, $answer5, 'Test remove MS Word symbol characters');

        // test string in non UTF-8 character set with special chars
        $charsets = array("ASCII", "Windows-1252", "ISO-8859-15", "ISO-8859-1", "ISO-8859-6", "CP1256");
        foreach($charsets as $charset) {
            $test6 = iconv('UTF-8', $charset, $test4);
            $result6 = $this->assmgr_db->encode($test6);
            $answer6 = $answer4;
            $this->assertEqual($result6, $answer6, "Test encoding of {$charset} string with special chars");
        }
    }

    /**
     * Tests decoding of strings. Needs writing as currently empty.
     *
     * @return void
     */
    function test_decode() {
        //decode(&$data)
    }

}
?>