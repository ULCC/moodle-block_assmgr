<?php
/**
 * Global config file for the the Assessment Manager.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
global $CFG;

require_once($CFG->dirroot.'/blocks/assmgr/constants.php');

//include assessment manager db class
require_once($CFG->dirroot.'/blocks/assmgr/db/assmgr_db.php');



$dbc = new assmgr_db();



// Defaults for various constants

// Portfolio locking
$portlocking = new admin_setting_heading('block_assmgr/portfoliolocking', get_string('portfoliolocking', 'block_assmgr'), '');
$settings->add($portlocking);

$settings->add(new admin_setting_configtext('block_assmgr/defaultexpirytime', get_string('defaultexpirytime', 'block_assmgr'),
                                        get_string('defaultexpirytimeconfig', 'block_assmgr'), 2100));
$settings->add(new admin_setting_configtext('block_assmgr/ajaxexpirytime', get_string('ajaxexpirytime', 'block_assmgr'),
                                        get_string('ajaxexpirytimeconfig', 'block_assmgr'), 300));

//AJAX/flextable
$portlocking = new admin_setting_heading('block_assmgr/ajaxtables', get_string('ajaxtables', 'block_assmgr'), '');
$settings->add($portlocking);

$settings->add(new admin_setting_configtext('block_assmgr/defaulthozsize', get_string('defaulthozsize', 'block_assmgr'),
                                        get_string('defaulthozsizeconfig', 'block_assmgr'), 10));
$settings->add(new admin_setting_configtext('block_assmgr/maxunits', get_string('maxunits', 'block_assmgr'),
                                        get_string('maxunitsconfig', 'block_assmgr'), 5));
$settings->add(new admin_setting_configtext('block_assmgr/maxoutcomesshort', get_string('maxoutcomesshort', 'block_assmgr'),
                                        get_string('maxoutcomesshortconfig', 'block_assmgr'), 5));
$settings->add(new admin_setting_configtext('block_assmgr/maxoutcomeslong', get_string('maxoutcomeslong', 'block_assmgr'),
                                        get_string('maxoutcomeslongconfig', 'block_assmgr'), 8));
$settings->add(new admin_setting_configtext('block_assmgr/maxevidtypesshort', get_string('maxevidtypesshort', 'block_assmgr'),
                                        get_string('maxevidtypesshortconfig', 'block_assmgr'), 5));
$settings->add(new admin_setting_configtext('block_assmgr/maxevidtypeslong', get_string('maxevidtypeslong', 'block_assmgr'),
                                        get_string('maxevidtypeslongconfig', 'block_assmgr'), 10));
$settings->add(new admin_setting_configtext('block_assmgr/defaultverticalperpage', get_string('defaultverticalperpage', 'block_assmgr'),
                                        get_string('defaultverticalperpageconfig', 'block_assmgr'), 10));



?>