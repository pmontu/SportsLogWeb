/* HOME PAGE
*
*
*
******************************************/
/*google plus bar script*/
(function () {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
/*tweet script*/
!function (d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (!d.getElementById(id)) { js = d.createElement(s); js.id = id; js.src = "//platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs); } }(document, "script", "twitter-wjs");
/*google analytics*/
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-12617654-9']);
_gaq.push(['_trackPageview']);

(function () {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
/* View 1
 * Home Page Show event */
function showHomePage() {

    /* Login Logout HomePage Visiblity*/
    $("#divLogin").hide('slow');
    $('#divLogout').show();

    /* get roots */
    downloadRoots();
}
function downloadRoots() {
    $.getJSON(
	    "webservice.php?action=getRoots&bustcache=" + new Date().getTime(),
	    function (data) {
	        if (data.Success) {
	            $("#divRoots").empty();
	            $.each(data.Data, function (i, Root) {
	                $("#divRoots").append("<li><a href='#' rootid='" + Root.rootid + "' class='root' expanded='false'>" + Root.root + "</a></li>");
	            });
	            $("#divRootsAdd").show("slow");

	            /* root expansion */
	            $(".root").click(root_click);
	        }
	        else {
	            alert("error\n" + data.Error);
	        }
	    }
    );
}
function root_click(event) {
    event.preventDefault();
    rootid = $(this).attr("rootid");
    expanded = $(this).attr("expanded");
    if (expanded != "true") {
        $(this).attr("expanded", "true");

        /* days */
        $(this).parent().after(
                '<li><div id="divdays' + rootid + '"><ul style="margin-left: 35px;">' +
                '<li style="display: none;">' +
                '<div id="divdays' + rootid + 'newtext" style="display: none;"><input type=text/><img src="images/Save_16x16.png" rootid="' + rootid + '" /></div>' +
                '<div id="divdays' + rootid + 'newsave"><img src="images/Add_16x16.png" rootid="'+rootid+'" /></div>' +
                '</li></ul></div></li>'
            );
        $('#divdays' + rootid + 'newsave > img').click(function (event) {
            rootid = $(this).attr("rootid");
            $('#divdays' + rootid + 'newsave').hide();
            $('#divdays' + rootid + 'newtext').show('slow');
        });

        /* download days */
        downloadDays();

        /* add new day */
        addNewDay();

    }
    else {
        $(this).attr("expanded", "false");
        lihavingalldayshtml = $('#divdays' + rootid).parent();
        lihavingalldayshtml.hide('slow', function () {
            lihavingalldayshtml.remove();
        });

    }
}
function downloadDays() {
    $.post(
		"webservice.php?action=getDays&bustcache=" + new Date().getTime(),
		{ rootid: rootid },
		function (data) {
		    if (data.Success) {
		        $.each(data.Data, function (i, Day) {
		            $('#divdays' + rootid + ' > ul > li:last').before('<li style="display: none;"><a href="#" dayid="' + Day.dayid + '" class="day' + rootid + '" expanded="false">' + Day.day + '</a></li>');
		        });
		        $('#divdays' + rootid + ' > ul > li').show('slow');

		        /* day expansion */
		        $('.day' + rootid).click(day_click);
		    }
		    else {
		        alert("error\n" + data.Error);
		    }
		},
		"json"
	);
}
function addNewDay() {
    $('#divdays' + rootid + 'newtext > img').click(function (event) {
        rootid = $(this).attr("rootid");
        day = $('#divdays' + rootid + 'newtext > input').val();
        $.post(
		    "webservice.php?action=setNewDay&bustcache=" + new Date().getTime(),
		    {
		        rootid: rootid,
		        day: day
		    },
		    function (data) {
		        if (data.Success) {
		            dayid = data.Data.dayid;
		            $('#divdays' + rootid + ' > ul > li:last').before('<li><a href="#" dayid="' + dayid + '" class="day" expanded="false" id="day' + dayid + '">' + day + '</a></li>');
		            $('#divdays' + rootid + 'newtext').hide();
		            $('#divdays' + rootid + 'newsave').show('slow');
		            $('#day' + dayid).click(day_click);
		        }
		        else {
		            alert("error\n" + data.Error);
		        }
		    },
		    "json"
	    );
    });
}
function day_click(event) {
    event.preventDefault();
    dayid = $(this).attr("dayid");
    expanded_day = $(this).attr("expanded");

    if (expanded_day != "true") {
        $(this).attr("expanded", "true");

        /* fields */
        $(this).parent().after(
                '<li>' +
                '<div id="divfields' + dayid + '"><ul class="ml35mb0">' +
                '<li class="mb0" style="display:none;"><table class="mb0"><tr><td>' +
                '<div id="divfields' + dayid + 'newsave"><img src="images/Add_16x16.png" dayid="' + dayid + '"/></div>' +
                '<div id="divfields' + dayid + 'newtext" style="display: none;"><input type=text/><img src="images/Save_16x16.png" dayid="' + dayid + '"/></div>' +
                '</td></tr></table></li>' +
                '</ul></div></li>'
            );
        $('#divfields' + dayid + 'newsave > img').click(function (event) {
            dayid = $(this).attr("dayid");
            $('#divfields' + dayid + 'newsave').hide();
            $('#divfields' + dayid + 'newtext').show('slow');
        });

        /* add new field - new row new column */
        addNewRowNewColumn();

        /* download fields */
        downloadFields(dayid);
    }
    else {
        $(this).attr("expanded", "false");
        fieldshtml = $('#divfields' + dayid).parent();
        fieldshtml.hide('slow', function () {
            fieldshtml.remove();
        });
    }
}
function addNewRowNewColumn() {
    $('#divfields' + dayid + 'newtext > img').click(function (event) {
        dayid = $(this).attr("dayid");
        field_newrow = $('#divfields' + dayid + 'newtext > input').val();
        $.post(
		    "webservice.php?action=setNewRowNewColumn&bustcache=" + new Date().getTime(),
		    {
		        newrownewcolumn: field_newrow,
		        dayid: dayid
		    },
		    function (data) {
		        if (data.Success) {
		            $("#divfields" + dayid + ">ul>li:last").before('<li class="mb0"><table class="mb0"><tr id="day' + dayid + 'fields' + data.Data.rowid + '">' +
                        "<td fieldid=" + data.Data.fieldid + ">" + field_newrow + "</td>" +
                        '</tr></table></li>');
		            $('#divfields' + dayid + 'newtext').hide();
		            $('#divfields' + dayid + 'newsave').show('slow');

                    /* add divs - new field*/
		            $('#day' + dayid + 'fields' + data.Data.rowid).append('<td>' +
                            '<div id="divday' + dayid + 'fields' + data.Data.rowid + 'text" style="display:none;"><input type=text><img src="images/Save_16x16.png" dayid="' + dayid + '"/></div>' +
                            '<div id="divday' + dayid + 'fields' + data.Data.rowid + 'add"><img src="images/Add_16x16.png" dayid="' + dayid + '"/></div>' +
                            '</td>');
                    /* add */
		            $('#divday' + dayid + 'fields' + data.Data.rowid + 'add>img').click(function (event) {
		                dayid = $(this).attr("dayid");
		                $(this).parent().hide();
		                $('#divday' + dayid + 'fields' + data.Data.rowid + 'text').show('slow');
		            });
                    /* save */
		            $('#divday' + dayid + 'fields' + data.Data.rowid + 'text>img').click(function (event) {
		                dayid = $(this).attr("dayid");
		                newcolumnfield = $(this).prev().val();
		                rowid = data.Data.rowid;
		                $.post(
		                        "webservice.php?action=setNewColumnField&bustcache=" + new Date().getTime(),
		                        {
		                            newcolumnfield: newcolumnfield,
		                            dayid: dayid,
		                            rowid: rowid
		                        },
		                        function (data) {
		                            if (data.Success) {
		                                $('#day' + dayid + 'fields' + rowid + '>td:last').before("<td fieldid=" + data.Data.fieldid + " rowid='" + rowid + "' columnid='" + data.Data.columnid + "'>" + newcolumnfield + "</td>");
		                                $('#divday' + dayid + 'fields' + rowid + 'text').hide();
		                                $('#divday' + dayid + 'fields' + rowid + 'add').show('slow');
		                            }
		                            else {
		                                alert("error\n" + data.Error);
		                            }
		                        },
		                        "json"
	                        );
		            });

		        }
		        else {
		            alert("error\n" + data.Error);
		        }
		    },
		    "json"
	    );
    });
}
function downloadFields(dayid) {

    $.post(
	    "webservice.php?action=getFields&bustcache=" + new Date().getTime(),
	    {
		    dayid: dayid
	    },
	    function (data) {
		    if (data.Success) {
		        if (data.Data) {
		            $.each(data.Data, function (i, Column) {
		                $("#divfields" + dayid + ">ul>li:last").before('<li class="mb0" style="display:none;"><table class="mb0"><tr id="day' + dayid + 'fields' + i + '"></tr></table></li>');
		                $.each(Column, function (j, Field) {
		                    $('#day' + dayid + 'fields' + i).append("<td fieldid=" + Field.fieldid + " rowid='" + i + "' columnid='" + j + "'>" + Field.field + "</td>");
                        });
                        /* add divs - new field*/
		                $('#day' + dayid + 'fields' + i).append('<td>' +
                            '<div id="divday' + dayid + 'fields' + i + 'text" style="display:none;"><input type=text><img src="images/Save_16x16.png" dayid="' + dayid + '"/></div>' +
                            '<div id="divday' + dayid + 'fields' + i + 'add"><img src="images/Add_16x16.png" dayid="' + dayid + '"/></div>' +
                            '</td>');
                        /* add */
		                $('#divday' + dayid + 'fields' + i + 'add>img').click(function (event) {
		                    dayid = $(this).attr("dayid");
		                    $(this).parent().hide();
		                    $('#divday' + dayid + 'fields' + i + 'text').show('slow');
                        });
                        /* save */
		                $('#divday' + dayid + 'fields' + i + 'text>img').click(function (event) {
		                    dayid = $(this).attr("dayid");
		                    newcolumnfield = $(this).prev().val();
		                    rowid = $(this).parent().parent().prev().attr("rowid");
		                    $.post(
		                        "webservice.php?action=setNewColumnField&bustcache=" + new Date().getTime(),
		                        { 
		                            newcolumnfield: newcolumnfield,
		                            dayid:dayid,
		                            rowid:rowid
		                        },
		                        function (data) {
		                            if (data.Success) {
		                                $('#day' + dayid + 'fields' + i+'>td:last').before("<td fieldid=" + data.Data.fieldid + " rowid='" + data.Data.rowid + "' columnid='" + data.Data.columnid + "'>" + newcolumnfield + "</td>");
		                                $('#divday' + dayid + 'fields' + i + 'text').hide();
		                                $('#divday' + dayid + 'fields' + i + 'add').show('slow');
		                            }
		                            else {
		                                alert("error\n" + data.Error);
		                            }
		                        },
		                        "json"
	                        );
		                });
		            });
		        }
		        $("#divfields" + dayid + ">ul>li").show('slow');
		    }
		    else {
		        alert("error\n" + data.Error);
		    }
	    },
	    "json"
    );
}
/* View 2 - Add root
 * On button add root click */
function addRoot(){
	$("#divRootsAddButton").hide();
	$("#divRootsAddText").show('slow');	
}
function saveNewRoot() {
    root = $("#txtRootNew").val();
    $.post(
		"webservice.php?action=setNewRoot&bustcache=" + new Date().getTime(),
		{ root: root },
		function (data) {
		    if (data.Success) {
		        //alert(data.Data.RootID);
		        rootid = data.Data.RootID;
		        $("#divRoots").append
					("<li><a href='#' rootid='" + rootid + "' id='root" + rootid + "' class='root' expanded='false'>" + root + "</a></li>");
		        $("#divRootsAddText").hide();
		        $("#divRootsAddButton").show('slow');
		        $("#root" + rootid).click(root_click);
		    }
		    else {
		        alert("error\n" + data.Error);
		    }
		},
		"json"
	);
}
/* USER
*
*
*
******************************************/
/* View 1 
 * on login button click */
function login(){
	 id = $("#id").val();
		password = $("#password").val();
		$.post(				       
		"webservice.php?action=getUserLogin&bustcache="+new Date().getTime(),			   
		{ id: id,
			password: password},
		function(data){
				if(data.Success){
				    //alert(data.Data.Message + "\nuserid:" + data.Data.UserID);
					showHomePage();
				}
				else{
					alert("error\n"+data.Error);
				}
		},
		"json"
		);
}
/* View 2 
 * on signup button click */
function signup(){
	id = $("#id").val();
	password = $("#password").val();
	$.post(				       
	"webservice.php?action=setNewUser&bustcache="+new Date().getTime(),			   
	{ id: id,
		password: password},
	function(data){
			if(data.Success){
				alert(data.Data.Message+"\nuserid:"+data.Data.UserID+"\nid:"+id);
			}
			else{
				alert("error\n"+data.Error);
			}
	},
	"json"
	);
}
/* View 3 
 * on page load */
function pageLoad(){
    $.getJSON(			        
	    "webservice.php?action=getUserLoginStatus&bustcache="+new Date().getTime(),
	    function(data){
	    	if(data.Success){
	    		showHomePage();
	    	}
	    	else{
	    		//alert("error\n"+data.Error);
    		}					  	
	    }
    );
}
/* View 4
 * on logout button click */
function logout() {
    $.getJSON(
	    "webservice.php?action=logout&bustcache=" + new Date().getTime(),
	    function (data) {
	        if (data.Success) {
	            $("#divLogin").show('slow');
	            $('#divLogout').hide();
	            $("#divRoots").empty();
	            $("#divRootsAdd").hide();
	        }
	        else {
	            //alert("error\n"+data.Error);
	        }
	    }
    );
}
/* Controller
*
******************************************/
$(document).ready(function(){
	$("#btnLogin").click(function(event){
		event.preventDefault();
		login();
	});
	$("#btnSignup").click(function(event){
		event.preventDefault();
		signup();
	});
	pageLoad();
	$("#btnLogout").click(function (event) {
	    event.preventDefault();
	    logout();
	});
	$('#btnAddRoot').click(function(event){
		event.preventDefault();
		addRoot();
	});
	$('#btnRootAddSave').click(function(event){
		event.preventDefault();
		saveNewRoot();
    });
});
