<?php
global $CFG;
require_once($CFG->dirroot.'/lib/pear/HTML/QuickForm/hierselect.php');

/**
 * HTML class for a select type element
 *
 * @author       Steve Bourget
 * @access       public
 */
class MoodleQuickForm_hierselect extends HTML_QuickForm_hierselect{
    /**
     * html for help button, if empty then no help
     *
     * @var string
     */
    var $_helpbutton='';
    var $_hiddenLabel=false;

    function MoodleQuickForm_hierselect($elementName=null, $elementLabel=null, $attributes=null, $separator=null, $linkdata=null) {
        if (!empty($linkdata['link']) && !empty($linkdata['label'])) {
            $this->_link = $linkdata['link'];
            $this->_linklabel = $linkdata['label'];
        }

        if (!empty($linkdata['return'])) {
            $this->_linkreturn = $linkdata['return'];
        }

        parent::HTML_QuickForm_hierselect($elementName, $elementLabel, $attributes, $separator);
    }
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }
    function toHtml(){

        $keys = array_keys($this->_elements);
        for ($i = 0; $i < count($keys) - 1; $i++) {
            // set the id of the element to the id of the object or else we get
            // and XHTML validation error
            $this->_elements[$keys[$i]]->_attributes['id'] = $this->_attributes['id'];
        }

        if ($this->_hiddenLabel){
            $this->_generateId();
            $retval = '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.
                        $this->getLabel().'</label>'.parent::toHtml();
        } else {

            $retval = parent::toHtml();
        }

        if (!empty($this->_link)) {
            if (!empty($this->_linkreturn) && is_array($this->_linkreturn)) {
                $appendchar = '?';
                if (strstr($this->_link, '?')) {
                    $appendchar = '&amp;';
                }

                foreach ($this->_linkreturn as $key => $val) {
                    $this->_link .= $appendchar."$key=$val";
                    $appendchar = '&amp;';
                }
            }

            $retval .= '<a style="margin-left: 5px" href="'.$this->_link.'">'.$this->_linklabel.'</a>';
        }

        return $retval;
    }


   /**
    * Automatically generates and assigns an 'id' attribute for the element.
    *
    * Currently used to ensure that labels work on radio buttons and
    * checkboxes. Per idea of Alexander Radivanovich.
    * Overriden in moodleforms to remove qf_ prefix.
    *
    * @access private
    * @return void
    */
    function _generateId()
    {
        static $idx = 1;

        if (!$this->getAttribute('id')) {
            $this->updateAttributes(array('id' => 'id_'. substr(md5(microtime() . $idx++), 0, 6)));
        }
    } // end func _generateId
    /**
     * set html for help button
     *
     * @access   public
     * @param array $help array of arguments to make a help button
     * @param string $function function name to call to get html
     */
    function setHelpButton($helpbuttonargs, $function='helpbutton'){
        if (!is_array($helpbuttonargs)){
            $helpbuttonargs=array($helpbuttonargs);
        }else{
            $helpbuttonargs=$helpbuttonargs;
        }
        //we do this to to return html instead of printing it
        //without having to specify it in every call to make a button.
        if ('helpbutton' == $function){
            $defaultargs=array('', '', 'moodle', true, false, '', true);
            $helpbuttonargs=$helpbuttonargs + $defaultargs ;
        }
        $this->_helpbutton=call_user_func_array($function, $helpbuttonargs);
    }
    /**
     * get html for help button
     *
     * @access  public
     * @return  string html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }

    /**
     * Slightly different container template when frozen. Don't want to use a label tag
     * with a for attribute in that case for the element label but instead use a div.
     * Templates are defined in renderer constructor.
     *
     * @return string
     */
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }

    function setLabels($labels) {
        // clear any existing seperator
        $this->_separator = array();

        // the first label has already been shown
        unset($labels[0]);

        // insert the labels for the dependent select elements as a seperator between them
        foreach($labels as $label) {
            $this->_separator[] = '</div></div><div class="fitem"><div class="fitemtitle"><label>'.$label.'</label></div><div class="felement fgroup">';
        }
    }

}

MoodleQuickForm::registerElementType('hierselect', "$CFG->libdir/pear/HTML/QuickForm/hierselect.php", 'MoodleQuickForm_hierselect');