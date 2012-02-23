<?php

require_once($CFG->libdir . '/grade/grade_item.php');

class assmgr_grade_item extends grade_item {

    public function __construct($params) {
        parent::__construct($params);
    }
    /**
     * Overridden function from the grade_item class, this function now allows the itemtype to also be outcome
     *
     * Is the grade item external - associated with module, plugin or something else?
     * @return boolean
     */
    public function is_external_item() {
        return ($this->itemtype == 'mod' || $this->itemtype == 'outcome') ?  true : false;
    }


    /**
     * Overridden function from the grade_item class, this function now returns all assmgr_grade_item instances based on params.
     *
     * @static
     *
     * @param array $params associative arrays varname=>value
     * @return array array of grade_item instances or false if none found.
     */
    public static function fetch_all($params) {
        return grade_object::fetch_all_helper('grade_items', 'assmgr_grade_item', $params);
    }
}

?>