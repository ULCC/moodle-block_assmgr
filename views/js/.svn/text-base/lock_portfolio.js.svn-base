/**
 *  Use ajax to periodically renew the lock on the current portfolio.
 */

M.assmgr.lock_portfolio.callback = {};

M.assmgr.lock_portfolio.assmgr_renew_lock = function(){
    YAHOO.util.Connect.asyncRequest('POST',
                                    '/blocks/assmgr/actions/lock_portfolio.php',
                                    M.assmgr.lock_portfolio.callback,
                                    'course_id='+M.assmgr.lock_portfolio.course_id+'&candidate_id='+M.assmgr.lock_portfolio.candidate_id);
    // renew the lock every 4 minutes using a recursive function call
    setTimeout('M.assmgr.lock_portfolio.assmgr_renew_lock()', 240000);
};

M.assmgr.lock_portfolio.assmgr_lock_init = function(M, course_id, candidate_id, wwwroot) {
    
    M.assmgr.lock_portfolio.course_id    = course_id;
    M.assmgr.lock_portfolio.candidate_id = candidate_id;
    M.assmgr.lock_portfolio.wwwroot      = wwwroot;

    M.assmgr.lock_portfolio.assmgr_renew_lock();

};

