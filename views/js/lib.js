/**
 * Executes an AJAX request and loads the content into the page.
 * 
 * @param elem_id The id of the element to serve the request into
 * @param url The url of the request
 * @return
 */
function ajax_request(elem_id, url) {
    var callback = {
        // if the action is successful then load the content into the page
        success: function(o) {
            document.getElementById(elem_id).innerHTML = o.responseText;
            parse_scripts(elem_id);
        },
        // if it failed then do nothing
        failure: function(o) {
            //alert("ERROR: The AJAX request didn't work");
        }
    }

    // fetch the requested page
    YAHOO.util.Connect.asyncRequest('GET', url.replace(/&amp;/g, '&'), callback);

    // return false to block the anchor firing
    return false;
}

/**
 * Submits a form using AJAX and loads the result into the page.
 * 
 * @param elem_id The id of the element to serve the request into
 * @param url The url of the request
 * @return
 */
function ajax_submit(form_id, elem_id, url) {

    var callback = {
        // if the action is successful then load the content into the page
        success: function(o) {
            document.getElementById(elem_id).innerHTML = o.responseText;
            parse_scripts(elem_id);
        },
        // if it failed then do nothing
        failure: function(o) {
            //alert("ERROR: The AJAX request didn't work");
        }
    }

    // get the form object
    var formObject = document.getElementById(form_id);

    // fetch the form contents
    YAHOO.util.Connect.setForm(formObject);
    
    // submit the form
	YAHOO.util.Connect.asyncRequest('POST', url.replace(/&amp;/g, '&'), callback);
	
    // return false to block the anchor firing
    return false;
}

/**
 * When ajax stuff comes back and gets added via innerHTML, the inline javascripts don't get run.
 * This will run them.
 */
function parse_scripts(elementid) {

    var element = document.getElementById(elementid);
    var scripts = element.getElementsByTagName('script');

    for (var i = 0; i < scripts.length; i++) {

        if (window.execScript) {
            window.execScript(scripts[i].innerHTML);
        } else {
            window.setTimeout(scripts[i].text, 0);
        }
    }
}

/**
 * Prevents onclick events from firing on parent elements. 
 * 
 * @param e
 * @return
 */
function suppressClick(e) {
    var ev=(!e)?window.event:e;//IE:Moz
    ev.stopPropagation?ev.stopPropagation():ev.cancelBubble = true;
}

/**
 * Calculates the height attribute of a rendered element.
 * 
 * @param elem
 * @return
 */
function get_height(elem) {
	
	console.log(elem);
	
	// work out the height of the rendered element minus the extra bits 
	var padding = parseFloat(Dom.getStyle(elem, "padding-top")) + parseFloat(Dom.getStyle(elem, "padding-bottom"));
	var border = parseFloat(Dom.getStyle(elem, "border-top-width")) + parseFloat(Dom.getStyle(elem, "border-bottom-width"));
	
	//additional check added as IE would sometimes return isNaN
	if (isNaN(border)) border = 0;
	
	return elem.offsetHeight - padding - border;
}

/**
 * Animates the opening and closing of accordions.
 * 
 * @param elem
 * @param from
 * @param to
 * @return
 */
function toggle_container(elem, from, to) {

	// disable the onclick so it can't be pressed twice
	elem.onclick = null;

	// add the current id to the location bar
	//window.location.href = new RegExp("[^#]+").exec(window.location.href)+'#'+elem.id;;
	
	// get the top level div for the page
	var page = Dom.get('page');
	
	// get the container to animate
	var container = Dom.get(elem.id+'_container');
	
	if(to == 0) {
		
		// fix the height of the page so the animation isn't screwy
		Dom.setStyle(page, "height", get_height(page)+"px");
		
		// reset the desired height in case ajax has expanded the content
		from = get_height(container);

		
		// add the closed icon
		document.getElementById(elem.id+'_icon').setAttribute('src', M.blocks_assmgr_animate_accordions.closed_image);
		
		// set the overflow to hidden on the container so we don't get scroll bars
		Dom.setStyle(container, "overflow", "hidden");
		
	} else {
		// add the open icon
		document.getElementById(elem.id+'_icon').setAttribute('src', M.blocks_assmgr_animate_accordions.open_image);
	}

	// show the hidden div
	Dom.setStyle(container, "display", "block");	
	
	// set the animation properties
	var attributes = { height: { from: from, to: to} };
	
	// create the animation object
	var anim = new YAHOO.util.Anim(elem.id+'_container', attributes, Math.abs(from-to)/1000);
	
	// set the oncomplete callback
	anim.onComplete.subscribe(function() {
		
		// restore the onclick
		elem.onclick = function() { toggle_container(this, to, from); };

		if(to == 0) {
		
			// hide the container
			Dom.setStyle(container, "display", "none");
			
			// allow the page size to drop back now the animation is complete
			Dom.setStyle(page, "height", "auto");
			
		} else {
			// set the height to auto so it can grow with new ajax content
			Dom.setStyle(container, "height", "auto");
			
			// set the overflow to auto so we can see any expanded content
			Dom.setStyle(container, "overflow", "auto");			
		}

	});

	// do it
	anim.animate();
}

/**
 * Cross browser compatible way of manually triggering events
 * 
 * @param element
 * @param event
 * @return
 */
function fireEvent(element,event){
    if (document.createEventObject){
        // dispatch for IE
        var evt = document.createEventObject();
        return element.fireEvent('on'+event,evt)
    }
    else{
        // dispatch for firefox + others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(event, true, true ); // event type,bubbling,cancelable
        return !element.dispatchEvent(evt);
    }
}

/**
 * Saves the chosen moodle activity into the hidden fields. 
 *  
 * @param modulename
 * @param id
 * @param assignmentname
 * @return
 */
function set_chosen_activity(modulename,id,assignmentname) {
    document.getElementById("chosenass").innerHTML = assignmentname;
    document.getElementById("activity_id").value = id;
    document.getElementById("module_name").value = modulename;
    return false;
}

/**
 * Toggles the highlighting of the evidence folders
 * 
 * @param elem
 * @return
 */
function set_folder_highlight(elem) {
	// get the folder tree
	var tree = document.getElementById('evidenceTree');
	
	// get all elements with a current highlight
	highlights = YAHOO.util.Dom.getElementsByClassName('highlight', null, tree);
	
	// unset any existing highlighting
	for(i=0; i<highlights.length; i++) {
		highlights[i].className = highlights[i].className.replace(' highlight', '');
	}
	
	var parent = elem;
	
	// set the new hightlight
	while(parent.nodeName != 'TABLE') {
		parent = parent.parentNode;
	}
	
	parent.className += ' highlight';
}



/**
 * Displays a Yui calendar on screen
 * 
 * @param calelements an array containing the ids of elements that will trigger the calendars display on click 
 * @param calendarid a string defining the calendars id
 * @param dateformat a string defining the format calendar data will be returned in 
 * @param startdate  a string defining the date that the calendar should start from null if not needed
 * @param pastdate   a date sting in the format m/d/y defining the mindate that the calendar may visit can be false
 * @param futuredate a date sting in the format m/d/y defining the maxdate that the calendar may visit can be false
 * @return
 */
function yui_calendar(calelements,calendarid,dateformat,startdate,pastdate,futuredate,renderobj) {
	
	var Dom = YAHOO.util.Dom;
	var Event = YAHOO.util.Event;
	var dialog;
	var calendar;

	//determine the format that the date will be returned in
	//if the calelements are select boxes they should be in the same order
	//as the date_form
	dateformat_arr = dateformat.split(' ');

	
	//create the dialog
	dialog = new YAHOO.widget.Dialog("container"+calendarid, {
        visible:false,
        context:[calelements[calelements.length-1], "tl", "bl"],
        draggable:false,
        close:true
    });
	
    dialog.setHeader('Pick A Date');
    dialog.setBody('<div id="'+calendarid+'"></div>');
    dialog.render(renderobj);
    dialog.showEvent.subscribe(function() {
        if (YAHOO.env.ua.ie) { dialog.fireEvent("changeContent"); }
    });
    	
    var selectedDate;
    if (startdate!=null) { 
    	selectedDate = startdate; 
    } else { 
    	var currentTime = new Date()
    	selectedDate = currentTime.getMonth() + '/' + currentTime.getDate() + '/' + currentTime.getFullYear();
    }
    
    calendar = new YAHOO.widget.Calendar(calendarid, {
        iframe:false,          // Turn iframe off, since container has iframe support.
        hide_blank_weeks:true // Enable, to demonstrate how we handle changing height, using changeContent
    });

    if (pastdate!=false) {  calendar.cfg.setProperty("mindate",pastdate ,false); }
    if (futuredate!=false) {  calendar.cfg.setProperty("maxdate",futuredate ,false); }
   
    calendar.render();

   
    calendar.selectEvent.subscribe(function(type,args,obj) {
        if (calendar.getSelectedDates().length > 0) {
 	           
            var dates = args[0];   
     	    var date = dates[0];   
     	    var year = date[0], month = date[1], day = date[2];
            for(i = 0; i < dateformat_arr.length; i++) {
            	if (dateformat_arr[i].search('d') > 0 || dateformat_arr[i].search('D') > 0) {
            		for(z = 0; z < calelements[0].length; z++) {
            			if(calelements[0][z].value == day) calelements[0].selectedIndex = z;
            		}
            	} else if (dateformat_arr[i].search('b') > 0|| dateformat_arr[i].search('B') > 0) {
            		for(z = 0; z < calelements[1].length; z++) {
            			if(calelements[1][z].value == month) calelements[1].selectedIndex = z;
            		}
            	} else {
            		for(z = 0; z < calelements[2].length; z++) {
            			if(calelements[2][z].value == year) {
            				calelements[2].selectedIndex = z;
            				fireEvent(calelements[2],'change');
            			}
            		}
            	}
            }
        } 
        dialog.hide();
    });

    calendar.renderEvent.subscribe(function() {
        // Tell Dialog it's contents have changed, which allows 
        // container to redraw the underlay (for IE6/Safari2)
        dialog.fireEvent("changeContent");
    });
    
	//loop through elements and add the calendar display function to the onclick event
	for (i=0; i< calelements.length; i++) {
		Event.addListener(calelements[i], "click", function(e) {
			YAHOO.util.Event.stopEvent(e); 
			dialog.cfg.setProperty("context",[calelements[calelements.length-1], "tl", "bl"]);
			dialog.show();
			
		});
	}
	
	//hides the calendar if the cursor is clicked anywhere outside of the calendar dialog
	Event.on(document, "click", function(e) {
        var el = Event.getTarget(e);
        var dialogEl = dialog.element;
        var hideDialog = true;
        if (el == dialogEl || Dom.isAncestor(dialogEl, el)) {
        	hideDialog = false;
        }
    	for (i=0; i< calelements.length; i++) {
    		if (el == calelements[i] || Dom.isAncestor(calelements[i], el)) {
    			hideDialog = false;
    		}
	   	}
        if (hideDialog == true) dialog.hide();
    });
}

/**
 * This function makes up for IE8's crappy failure to make onChange events bubble. A single delegated
 * listener won't work, so we have to loop through the child elements, inefficiently adding a
 * listener to each one. Not needed for other types of event as they bubble.
 *
 * @param string elementid The id of the DOM element whose children need the listeners
 * @param function functionname The function, defined elsewhere that the listeners should trigger. No quotes!
 * @param object functionarguments
 * @param array elements List of tagnames to search for in the children. These are the ones that will get the listeners
 * @return void
 */
function add_onchange_listeners(elementid, functionname, functionarguments, elements) {

    var rootelement = document.getElementById(elementid);
    
    if (typeof(rootelement) != 'object') {
        return false;
    }

    // Build a string with the function call and arguments
    var functionstring = functionname+'(';
    var comma = false;

    for (var k=0;k<functionarguments.length;k++) {

        if (!comma) {
            comma = true;
        } else {
            functionstring += ', ';
        }

        functionstring += functionarguments[k];
    }

    functionstring += ')';

    for (var i=0;i<elements.length;i++) {

        tagnames = rootelement.getElementsByTagName(elements[i]);

        for (var j=0; j<tagnames.length; j++) {
            YAHOO.util.Event.addListener(tagnames[j], 'change', functionname, functionarguments);
            
            // IE doesn't fire change events on checkboxes and radio buttons until they lose focus
            type = tagnames[j].getAttribute('type');
            if(type == 'checkbox' || type == 'radio') {
            	YAHOO.util.Event.addListener(tagnames[j], 'click', function() { this.blur() });
            }
        }
    }
}

/**
 * This is to make sure that ajax_submit can be called from a YUI listener, which passes an object
 */
function ajax_submit_wrapper(event, object) {
    ajax_submit(object.form_id, object.elem_id, object.url);
}

/** highlight/unset the row of a table **/
function set_row(idx) {
    var table = document.getElementById('user-grades');
    var rowsize = table.rows[idx].cells.length;
    for (var i = 0; i < rowsize; i++) {
        if (table.rows[idx].cells[i]) {
            if (table.rows[idx].cells[i].className.search(/hmarked/) != -1) {
                table.rows[idx].cells[i].className = table.rows[idx].cells[i].className.replace(' hmarked', '');
            } else {
                table.rows[idx].cells[i].className += ' hmarked';
            }
        }
    }
}

/** highlight/unset the column of a table **/
function set_col(col,gradecelloffset) {
    var table = document.getElementById('user-grades');
    //highlight the column header
    flip_vmarked(table,2,col);


    
    //add any grade cell offset (due to colspans) then iterate down the table
    col += gradecelloffset;

    for (var row = 3; row < table.rows.length; row++) {
    	console.log('row '+row+' col'+col);
    	
        flip_vmarked(table,row,col);
    }
}

function flip_vmarked(table,row,col) {
	
    if (table.rows[row].cells[col]) {
        if (table.rows[row].cells[col].className.search(/vmarked/) != -1) {
            table.rows[row].cells[col].className = table.rows[row].cells[col].className.replace(' vmarked', '');
        } else {
            table.rows[row].cells[col].className += ' vmarked';
        }
    }
}