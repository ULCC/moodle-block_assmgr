<?php
/**
 * Capability definitions for the AssMgr block.
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

// TODO moodle 2.0 complains that this should be $capabilities
$block_assmgr_capabilities = array(

// TODO we need a serious review of these permissions

    'block/assmgr:creddelevidenceforself' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_ALLOW
        )
    ),

    'block/assmgr:creddelevidenceforothers' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

     'block/assmgr:creddelportfolio' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'block/assmgr:confirmevidence' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'block/assmgr:viewothersevidence' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'block/assmgr:viewothersportfolio' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'block/assmgr:assessportfolio' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PREVENT,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    'block/assmgr:verifyportfolio' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'legacy' => array(
            'student' => CAP_PREVENT,
            'admin' => CAP_ALLOW
        )
    ),

);
?>