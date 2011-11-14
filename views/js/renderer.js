


jQuery(document).ready(function() {

	test  =  document.getElementById('new_activitynametable');

    var Id = 'submissions_table_container';
    var maintbheight = 555;
    var maintbwidth = 911;

    jQuery(test).fixedTable({
        width: maintbwidth,
        height: maintbheight,
        fixedColumns: 1,
        // header style
        classHeader: "fixedHead",
        // footer style        
        classFooter: "fixedFoot",


        // fixed column on the left        
        classColumn: "fixedColumn",
        // the width of fixed column on the left      
        fixedColumnWidth: 250,
        // table's parent div's id           
        outerId: Id
        // tds' in content area default background color                     
       // Contentbackcolor: "#FFFFFF",
        // tds' in content area background color while hover.     
        //Contenthovercolor: "#99CCFF", 
        // tds' in fixed column default background color   
        //fixedColumnbackcolor:"#187BAF", 
        // tds' in fixed column background color while hover. 
        //fixedColumnhovercolor:"#99CCFF"  
    });

    console.log(jQuery('new_activitynametable').offsetHeight);
});

M.render_assmgr_course_activities = {};



M.render_assmgr_course_activities.init = (function() {});


