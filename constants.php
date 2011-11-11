<?php
/**
 * Static constants for the Assessment Manager.
 *
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */

// -----------------------------------------------------------------------------
// --                       CONFIRMATION                                      --
// -----------------------------------------------------------------------------
define('CONFIRMATION_CONFIRMED',    1);
define('CONFIRMATION_PENDING',      2);
define('CONFIRMATION_REJECTED',     3);

// -----------------------------------------------------------------------------
// --                         GRADEBOOK                                       --
// -----------------------------------------------------------------------------
// the grade book integration strings
if(!defined('GRADE_ASSMGR_SOURCE')) {
    define('GRADE_ASSMGR_SOURCE',       'blocks/assmgr');
}
if(!defined('GRADE_ASSMGR_ITEMTYPE')) {
    define('GRADE_ASSMGR_ITEMTYPE',     'outcome');
}
if(!defined('GRADE_ASSMGR_ITEMMODULE')) {
    define('GRADE_ASSMGR_ITEMMODULE',   'block_assmgr');
}

// -----------------------------------------------------------------------------
// --                       OUTCOME FILTERS                                   --
// -----------------------------------------------------------------------------
define('OUTCOMES_SHOW_ALL',         1);
define('OUTCOMES_SHOW_COMPLETE',    2);
define('OUTCOMES_SHOW_INCOMPLETE',  3);
define('OUTCOMES_SHOW_UNATTEMPTED', 4);

// -----------------------------------------------------------------------------
// --                         ACTIONS LOG                                     --
// -----------------------------------------------------------------------------
define('LOG_ADD',                   1);
define('LOG_UPDATE',                2);
define('LOG_DELETE',                3);
define('LOG_VIEW',                  4);
define('LOG_ASSESSMENT',            5);
define('LOG_CLAIM',                 6);
define('LOG_VERIFY',                7);

define('ASSMGR_LOG_MODULE',         'admin');
define('ASSMGR_LOG_URL_PREFIX',     '../blocks/assmgr/actions');

// -----------------------------------------------------------------------------
// --                        CALENDAR EVENTS                                  --
// -----------------------------------------------------------------------------
define('ASSESSOR_EVENT',            1);
define('CANDIDATE_EVENT',           0);

// -----------------------------------------------------------------------------
// --                         GENERAL STUFF                                   --
// -----------------------------------------------------------------------------
//param array defined with 0x40000 in line with other mooodle PARAM constant
define('PARAM_ARRAY',               0x40000);

// a blacklist of file extensions commonly blocked because they can carry viruses
define('FILE_EXTENSION_BLACKLIST', 'ade, adp, app, asp, bas, bat, chm, cmd, com, cpl, crt, csh,exe, fxp, hlp, hta, htr, inf, ins, isp, jar, js, jse, ksh, lnk, mda, mdb, mde, mdt, mdw, mdz, mht, msc, msi, msp, mst, ops, pcd, pif, prf, prg, reg, scf, scr, sct, shb, shs, url, vb, vbe, vbs, wsc, wsf, wsh');

define('REDIRECT_DELAY',            1);

define('MAXLENGTH_BREADCRUMB',      130);

define('BLOCK_NAME',                'assmgr');
?>