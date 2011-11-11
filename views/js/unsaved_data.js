/**
 * This object will monitor multiple forms on one page for unsaved data and warn
 * the user if they try to leave without saving it.
 *
 * It will also make sure that pages with multiple forms save all forms when any of the submit
 * buttons are pressed
 *
 * To use it, you need to specify the id of the form and the name you want on
 * the warning, changing only $formsaveargs. You will also need a hidden input
 * called datachanged in the form, which is initially set to 'false'
 *
 * Put the following code on the page: $unsavedmodule = array( 'name' =>
 * 'unsaved_data_check', 'fullpath' =>
 * '/blocks/assmgr/views/js/unsaved_data_check.js', 'requires' => array() );
 * $formsaveargs = array('form' => 'outassessform', 'tablename' =>
 * get_string('submittedevidence', 'block_assmgr'));
 * $PAGE->requires->js_init_call('M.blocks_assmgr_unsaved_data_check.subscribe_to_form',
 * $formsaveargs, true, $unsavedmodule);
 */
if (typeof (M.blocks_assmgr_unsaved_data.checker.ajaxcallback) != 'object') {

    M.blocks_assmgr_unsaved_data.checker = (function() {

        // Private vars
        //
        // store the forms to monitor and some details
        //var formschanged = {};
        var formstomonitor = [];
        var formsnames = {};
        var assmgrframesrefs = [];
        var assmgrframestextboxes = [];
        var submitclicked = false;

        // Private functions

        // go up the DOM tree till we get to a form element or run out of
        // options
        function get_form_id(element) {

            if ((typeof(element.tagName) == 'undefined') || (element.tagName.toLowerCase() != 'form')) {

                if (element.body) {

                    // we have hit the document tag and therefore need to stop
                    return false;

                } else {

                    element = element.parentNode;
                    var returnvalue = get_form_id(element);
                    return returnvalue;
                }

            } else {

                return element.getAttribute('id').toLowerCase();
            }
        }


        function form_is_monitored(formid) {

            for (var i=0; i<formstomonitor.length; i++) {

                if (formstomonitor[i] == formid) {
                    return true;
                }
            }
            return false;
        }

        function htmlIsEmpty(html) {

            var emptycheck = html.replace(/^\s+|\s+$/g, '');

            if ((emptycheck == '') || (emptycheck == '<br>') ||(emptycheck == '&lt;br&gt;')) {
                return true;
            } else {
                return false;
            }

        }

        // public functions

        return {

            subscribe_to_form : function(scope, form, tablename) {

                formsnames[String(form)] = tablename;

                var formsstring = formstomonitor.toString();

                if (formsstring.search(form) == -1) {
                    formstomonitor.push(form);
                }
            },

            // Define a method to be called whenever a form element has it's
            // value changed
            value_changed : function(e) {

                if (typeof(e.target) != 'undefined') {

                    if ((e.target.tagName.toUpperCase() == 'INPUT') && (e.target.type.toUpperCase() == 'CHECKBOX')) {
                        return;
                    }

                    // get the form id and see if it's one we should be monitoring
                    var formid = get_form_id(e.target);

                    if (formid && form_is_monitored(formid)) {

                        // don't count the vertical pagination thingy
                        if (!Boolean(e.target.name.toLowerCase().match(/pagesize/))) {

                            var form = document.getElementById(formid);
                            form.datachanged.value = 'true';

                        }
                    }
                }
            },

            /**
             * This function is called fromn the onclick of the body, so it fires anytime you click on
             * anything. It checks all of the iframes in the document for changes and is a workaround
             * for the problem that the HTMLara things don't respond to the onchange that monitors
             * all other parts of the forms.
             */
            text_typed : function(e) {

                // will hold data from the iframe that the user sees on the screen
                var newdata = '';
                // will hold data from the actual textarea field which is loaded at the start
                // with the existing database values and is only updated by HTMLarea to match the text that
                // the user has typed when the form is submitted
                var originaldata = '';

                // need to loop through each iframe checking for changes. Not all will be in the same form
                for (var i=0; i<assmgrframesrefs.length; i++) {

                    newdata = assmgrframesrefs[i].body.innerHTML;
                    // hacky fix to remove carriage return and/or extra whitespace that's
                    // automatically present
                    newdata = newdata.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");

                    originaldata = assmgrframestextboxes[i].innerHTML;

                    // for some reason an empty HTMLarea has a <br> tag in it
                    if ((newdata == "<br>") && (originaldata == '')) {
                       continue;
                    }

                    if (newdata != originaldata) {
                        // find out what form this one is in and mark it as changed
                        document.getElementById(get_form_id(assmgrframestextboxes[i])).datachanged.value = 'true';

                    }
                }
            },

            /**
             * We need to track whether the submit button has been clicked, in which case we don't
             * complain, but fire the data save function. More than one form on the page means that
             * we will need to use ajax to save them sequentially, then redirect, otherwise data from
             * the forms whose buttons were not clicked will not be saved.
             */
            submit_clicked : function(e) {

                if (typeof(e.target) != 'undefined') {

                    // TODO moodle activity needs to be OK with a link being clicked
                    var tagset         = false;
                    var tagisinput     = false;
                    var tagislink      = false;
                    var hrefok         = false;
                    var typeset        = false;
                    var typeissubmit   = false;
                    var typeisimage    = false;

                    // does the clicked thing have a tag
                    tagset = (typeof (e.target.tagName) != 'undefined');

                    if (tagset) {
                        tagisinput = (e.target.tagName.toLowerCase() == 'input');
                        tagislink = (e.target.tagName.toLowerCase() == 'a');
                    } else {
                        return;
                    }

                    typeset = (typeof (e.target.type) != 'undefined');

                    if (typeset) {
                        typeissubmit = (e.target.type.toLowerCase() == 'submit');
                        typeisimage  = (e.target.type.toLowerCase() == 'image');
                    }

                    if (typeisimage) {
                        // TODO this should be part of an array, which has OK links added dynamically
                        // when a form is subscribed
                        //imagepaginates = Boolean(e.target.src.toLowerCase().match(/(moveright|moveright2|moveleft|moveleft2).gif$/));
                    }

                    // Either a normal submit button has been pressed or the link on the add moodle
                    // activity page, so we flag that this form has been submitted and needs no warning
                    if ((tagisinput && typeissubmit) || (tagislink && hrefok)) {

                        submitclicked = true;

                        // save the submitted form so we know what to do next
                        var formid = get_form_id(e.target);
                        formsubmitted = formid;

                    }
                }
            },

            form_check: function(formid) {

                var form = Dom.get(formid);

                if (form && form.datachanged.value == 'true') {

                    if (!confirm(M.block_assmgr_vars.formunsaved)) {
                        return false;
                    }
                }
                return true;

            },

            // Before leaving the page, check that it's OK
            leaving_page : function(e) {

                // do nothing if ajax has been used already to submit the forms successfully
                if (submitclicked) {
                    return;
                }

                var formstoconfirm = formstomonitor;
                var warnings = '';
                var formid = '';
                var form = '';

                // loop through the names of the forms and record any that have
                // been changed and which are not the one being submitted
                var formlen = formstoconfirm.length;

                for (var i = 0; i < formlen; i++) {

                    form = document.getElementById(formstoconfirm[i]);

                    if (form == null) {
                        continue;
                    }

                    if (typeof(form.datachanged) != 'undefined') {

                        var datachanged = (form.datachanged.value == 'true');

                        if (datachanged) {
                            formid = formstoconfirm[i];
                            warnings += ' ' + formsnames[formid];
                        }
                    }
                }

                // if any are there, we need to alert the user
                if (warnings.length > 0) {

                    // abit of browser sniffing is required as yui oddly doesn't deal well with this
                    if ((YAHOO.env.ua.gecko > 0) || (YAHOO.env.ua.ie > 0)) {
                        // firefox and IE
                        e.returnValue = 'You have unsaved changes in' + warnings;
                    } else {
                        // chrome, safari, others
                        return 'You have unsaved changes in' + warnings;
                    }
                }
            },

            addiframelistener : function() {

                var assmgriframes = document.getElementsByTagName('iframe');

                if (assmgriframes.length > 0) {

                    // each iframe will have an associated text box. We use it to see if the contents have changed
                    // assume that the two lists here will be the same length so we can use the same index
                    // key on both
                    assmgrframestextboxes = YAHOO.util.Dom.getElementsByClassName('form-textarea');

                    // store refs to the available frames so they can be looped through later
                    for (var i=0; i < assmgriframes.length; i++) {

                        // Ignore this one as YUI adds it internally for the tooltips or something.
                        // We only want the ones for the HTML areas
                        if (assmgriframes[i].id == '_yuiResizeMonitor') {
                            continue;
                        }

                        if (assmgriframes[i].contentDocument) {
                            assmgrframesrefs.push(assmgriframes[i].contentDocument);
                        } else if (assmgriframes[i].contentWindow) {
                            // for IE 5.5, 6 and 7:
                            assmgrframesrefs.push(assmgriframes[i].contentWindow.document);
                        }
                    }

                    YAHOO.util.Event.addListener(document.body, 'click',
                            M.blocks_assmgr_unsaved_data.checker.text_typed, true);

                } else {
                    window.setTimeout(M.blocks_assmgr_unsaved_data.checker.addiframelistener, 1000);
                }
            }

         

        };
    })();

    // Attach the functions to the page root elements so that form
    // events will bubble up
    YAHOO.util.Event.addListener(window, 'beforeunload',
            M.blocks_assmgr_unsaved_data.checker.leaving_page);
    YAHOO.util.Event.addListener(document.body, 'change',
            M.blocks_assmgr_unsaved_data.checker.value_changed);

    // html editor is in iframes.
    // TODO doesn't work with change but does work with click
    // the javascript doesn't load immediately and YUI onready stuf doesn't like things that don't have IDs,
    // so we need to keep trying to add the listeners till the elements are present
    window.setTimeout(M.blocks_assmgr_unsaved_data.checker.addiframelistener, 1000);

    YAHOO.util.Event.addListener(document.body, 'click',
            M.blocks_assmgr_unsaved_data.checker.submit_clicked);


}