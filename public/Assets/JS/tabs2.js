var colors = ["'rgb(255, 118, 142)'", "'#ADD3ED'", "'rgb(255, 136, 136)'", "'#ffcc66'", "'#5BC85B'", "'#ff6600'", "'#7B68EE'", "'#FF4500'", "'#7FB5DA'", "'#9400D3'"];
var backgroundColors = ["rgb(255, 118, 142)", "#3498db", "red", "gold", "#7CFC00", "#ff6600", "#7B68EE", "#FF4500", "#7FB5DA", "#9400D3"];
var ajaxDirectory = 'Assets/AJAX/';
var buttons = [];
var tabs = [1];
var possibleCombinations = 0;
var ranges = [];
var ranges2 = [];
var finalRanges2 = [];
var filterType = '';

//NEW VARIABLES
var color = ["pink", "#ADD3ED", "#FF8888", "#ffcc66", "#5BC85B", "#ff9933", "#BCB2F9", "#FF8E64", "#D8E9F5", "#D67CFC"];
var selectedColors = ["rgb(255, 118, 142)", "#3498db", "red", "gold", "#7CFC00", "#ff6600", "#7B68EE", "#FF4500", "#7FB5DA", "#9400D3"];
var disabledColor = "gray";
var messageType = {lockdown: "Lockdown", error: "Error", success: "Success"};
var messages = {lockdown: "Sorry, the website went into complete lockdown. Try reloading the page in a few minutes",
                error: "Sorry, the task performed was unable to complete successfully. Try again or try reloading the web page",
                success: "Task was performed successfully"};
var geneOperation;
var firstTime = [];
var shownButtons = [];


const SITE_WIDE_MESSAGE = "siteWideMessage";
const MAX_TAB_NO = 8;


window.onload = function () {
    //Set up the initial parameters for Data 1 and Data 2
    makeButtons(1);
    //Adds Data 2
    addDiv();
    //Set focus to Data 1
    changeTabColor(1, color[1]);

};

/**
 * This function checks to see if the response from the server
 * is a valid one. It can fail if the response is blank or
 * when the response status is a value other than 0
 * @param $response - the xhr.responseText
 */
function checkXHRStatus(response, elementId) {
    elementId = (elementId == undefined || elementId == "") ? SITE_WIDE_MESSAGE : elementId;

    if(response == undefined || response === '') {
        //check for response input
        showMessage(messages.error, elementId);
        return false;
    }

    response = JSON.parse(response);

    if(response.Status == undefined || response.Status === '') {
        showMessage(messages.error, elementId);
        return false;
    }
    var message = (response.Message == undefined || response.Message === "") ? "" : response.Message;

    //Status
    if(response.Status == messageType.lockdown) {
        showMessage(((message === "") ? messages.lockdown : message), elementId, true);
        return false;
    } else if(response.Status == messageType.error) {
        showMessage(((message === "") ? messages.error : message), elementId);
        return false;
    }

    return true;
}

/**
 * Sets the message at the specified elementId that was already
 * pre built in html. Uses the stay and length attributes to
 * distinguish if the message should slide up after length
 *
 * @param message - Message
 * @param elementId - Element Id with the pre built html
 * @param stay {boolean} - If the message should slide up
 * @param length - Duration of message before sliding up
 */
function showMessage(message, elementId, stay, length) {
    if(elementId == undefined || $("#" + elementId).length == 0) elementId = SITE_WIDE_MESSAGE;
    if(message == undefined) return;

    stay = (stay == undefined || stay != true) ? false : true;
    length = (stay || length == undefined) ? 5000 : length;

    $("#" + elementId).html(message);
    var parentDiv = $("#" + elementId).parent().closest('div')
    parentDiv.show();

    if(!stay) {
        setTimeout(function() { releaseMessage(parentDiv)}, length);
    }
}

/**
 * Hides the message from the divElement parameter
 * @param divElement
 */
function releaseMessage(divElement) {
    divElement.slideUp("slow");
}

/**
 * GOAL: The ability to add new data sets
 *  -Creates a new tab
 *  -Div element for the parameters
 *  -Creates the starting up buttons
 *
 */
function addDiv() {
    //Add the new tab in the array
    var tabNo = (tabs.length != 0) ? tabs[tabs.length - 1] + 1 : 1;
    tabs.push(tabNo);

    //Add the relevant tab and div elements
    $("#liSubmit").before('<li>' +
        '<a href="#data' + tabNo + '" id="aData' + tabNo + '" ' +
        'onclick="changeTabColor(' + tabNo + ');" ' +
        'style="border: 5px solid ' + color[tabNo % MAX_TAB_NO] + ';' +
               'background-color: ' + color[tabNo % MAX_TAB_NO] + ';">Data ' + tabNo + '</a>' +
        '</li>');
    $("#add").before(
        '<div id="data' + tabNo + '" class="tab">' +
        '<form name="data' + tabNo + '" id="fData' + tabNo + '">' +
            '<div class="message ui-state-error ui-corner-all no-display">' +
                '<span class="ui-icon-alert ui-icon" style="float: left;"></span>' +
                '<span id="message' + tabNo +'"></span>' +
            '</div>' +
            '<div class="no-display" style="text-align:center;">' +
                '<span id="messageInfo' + tabNo + '"></span>' +
            '</div>' +
            '<div class="choice">' +
                '<span class="basicType">Data Type:</span>' +
                '<div id="dataType' + tabNo + '"></div>' +
            '</div>' +
            '<div class="choice">' +
                '<span class="basicType">Cell Type:</span>' +
                '<div id="cellType' + tabNo + '"></div>' +
            '</div>' +
            '<div class="choice">' +
                '<span class="basicType">Stimulation:</span>' +
                '<div id="stimulation' + tabNo + '"></div>' +
            '</div>' +
            '<div class="choice strain">' +
                '<span class="complexType">Strain:</span>' +
                '<div id="strain' + tabNo + '"></div>' +
            '</div>' +
            '<div class="choice timePoint">' +
                '<span class="complexType">Time Point (Minutes):</span>' +
                '<div id="timePoint' + tabNo + '"></div>' +
            '</div>' +
            '<div class="choice concentration">' +
                '<div id="concentration' + tabNo + '">' +
                '</div>' +
            '</div>' +
            '<div class="choice experimentalist">' +
                '<span class="complexType">Experimentalist:</span>' +
                '<div id="experimentalist' + tabNo + '"></div>' +
            '</div>' +
            '<div class="replicate">' +
                '<span class="complexType">Replicate:</span>' +
                '<div id="replicate' + tabNo + '"></div>' +
            '</div>' +
            '<input type="button" class="button" value="Reset Values" ' +
                'style="border: 3px solid ' + color[tabNo % MAX_TAB_NO] + ';' +
                       'color: ' + color[tabNo % MAX_TAB_NO] + ';" ' +
                'onclick="parametersManagement(' + tabNo + ', \'Reset\');" />' +
            '<input type="button" class="button" value="Delete Data" ' +
                'style="border: 3px solid ' + color[tabNo % MAX_TAB_NO] + ';' +
                       'color: ' + color[tabNo % MAX_TAB_NO] + '; margin-left:5px" ' +
                'onclick="parametersManagement(' + tabNo + ', \'Delete\');" />' +
        '</form>' +
        '</div>');

    //create the startup buttons
    makeButtons(tabNo);

    //Set the user to the new created tab
    changeTabColor(tabNo, color[tabNo]);

    //maintains tab structure
    tabsStructureStatus();
}

/**
 * GOAL: Maintains tab structure
 * - A user may not exceed 10 data sets
 */
function tabsStructureStatus() {
    if (tabs.length < MAX_TAB_NO) {
        $("#aAdd").show();
    } else $("#aAdd").hide();
}

function makeButtons(tabNo) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'parameters.php?params=All&id=' + tabNo);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if(!checkXHRStatus(xhr.responseText)) return;
            var jsonResponse = JSON.parse(xhr.responseText);

            manageButtons(tabNo, jsonResponse.Buttons);
        } else if(xhr.status != 200) {
            checkXHRStatus();
        }
    };
    xhr.send(null);
}

/**
 * Create buttons based on the data given. The tabNo tells
 * the function which tab to place and create the buttons
 *
 * @param tabNo
 * @param data
 */
function manageButtons(tabNo, data) {
    //sanitize inputs
    if(data == undefined) return;
    if(tabNo == undefined ) return;

    if(data.New.count <= 0) return;

    for (var i = 0; i < data.New.count; i++) {
        var value = data.New[i].Value.replace(/_/g, ' '); //sanitize name value for users
        var idValue = data.New[i].Value;

        value = value.split(':').join('/');

        var type  = data.New[i].Type;

        var id    = (type == 'concentration') ? type + '_' + data.New[i].Stimulant + '_' + tabNo : type + tabNo;

        if(firstTime.indexOf(type) == -1) {
            $('#' + id).html('');
            firstTime.push(type);
        }

        var uniqueId = '';

        if (type == 'concentration') {
            uniqueId = data.New[i].Type + '_' + data.New[i].Stimulant + '_' + data.New[i].Value + '_' + tabNo;

            if(shownButtons.indexOf(uniqueId) !== -1)
            {
                continue;
            }

            if($('#' + id).length == 0) {
                $('#' + type + tabNo).append('<div id="' + id + '"></div>');
            }
            if($('#' + id).html() == '') {
                $('#' + id).append('<span class="complexType">Concentration ' + data.New[i].Stimulant + ':</span>');
            }
            $('#' + id).append('<input type="button" value="' + value + '" name="' + data.New[i].Value + '" class="button2" state="unpressed" style="background-color: ' + color[tabNo % MAX_TAB_NO] + ';" id="' + uniqueId + '" onclick="switchState(\'' + data.New[i].Value + '\',' + tabNo + ', \'' + data.New[i].Type + '_' + data.New[i].Stimulant + '\');"/>');
            firstTime.pop();
        } else {
            var id = type + tabNo;

            uniqueId = data.New[i].Type + '_' + idValue + '_' + tabNo;

            if(shownButtons.indexOf(uniqueId) !== -1)
            {
                continue;
            }

            if(shownButtons.indexOf(data.New[i].Type + '_' + idValue + '_' + tabNo) !== -1)
            {
                continue;
            }

            shownButtons.push(data.New[i].Type + '_' + idValue + '_' + tabNo);

            $('#' + id).append('<input type="button" value="' + value + '" name="' + idValue + '" class="button2" state="unpressed" style="background-color: ' + color[tabNo % MAX_TAB_NO] + ';" id="' + uniqueId + '" onclick="switchState(\'' + idValue + '\',' + tabNo + ', \'' + data.New[i].Type + '\');"/>');
        }

        shownButtons.push(uniqueId);

    }
}

/**
 * Sets the tabNo tab to active while displaying its content
 * and formatting the borders to match the tab color
 * @param tabNo
 */
function changeTabColor(tabNo, tabColor, results) {
    var divNavElement   = (results == undefined) ? 'aData' + tabNo  : 'aResults' + tabNo;
    var classElement    = (results == undefined) ? '.tabs'          : '.tabs2';
    var divElement      = (results == undefined) ? 'data'           : 'results';
    var tabElement      = (results == undefined) ? '.tab-content'   : 'tab-content2';
    tabColor            = (tabColor == undefined)? color[tabNo % MAX_TAB_NO]: tabColor;

    var tab = document.getElementById(divNavElement).getAttribute('href');
    // Show/Hide Tabs
    $(classElement + ' ' + tab).show().siblings().hide();
    // Change/remove current tab to active
    $(divElement + tabNo).parent('li').addClass('active').siblings().removeClass('active');
    $(tabElement).css('border', '2px solid ' + tabColor);
}

//

/**
 * Finds the current buttons state and sends it to the updateParameters
 * method for an ajax request required after each parameter change
 *
 * @param name
 * @param tabNo
 * @param type
 */
function switchState(name, tabNo, type) {
    var elementID = '#' + type + '_' + name + '_' + tabNo;
    elementID = elementID.replace(/\./g, '\\.'); //makes sure to escape "."
    elementID = elementID.replace(/:/g, '\\:');

    var currentState = $(elementID).attr("state");

    updateParameters(name, currentState, tabNo, type);
}

/**
 * Sends ajax request to the server and updates all of the parameters
 * (buttons).
 *
 * @param name - value
 * @param state - pressed or unpressed
 * @param tabNo
 * @param type - concentration, timePoint etc...
 */
function updateParameters(name, state, tabNo, type) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'parameters.php?value=' + name +
                                                    '&id=' + tabNo +
                                                    '&state=' + state +
                                                    '&type=' + type +
                                                    '&params=Partial');

    xhr.onreadystatechange = (function () {
        if (xhr.status == 200 && xhr.readyState == 4) {
            if(!checkXHRStatus(xhr.responseText)) return;

            var response = JSON.parse(xhr.responseText);
            var currentButtons = $("#data" + tabNo + " :input[class=button2]");

            resetDisplay(tabNo, response); //resets the display

            currentButtons.each(function() {
                changeButtonStatus($(this), response.Buttons, tabNo);
            });

            isCompleted(response.Display.Completed, tabNo);
        }
        else if(xhr.status != 200) {
            checkXHRStatus();
        }
    });
    xhr.send(null);
}

function isCompleted(completedStatus, tabNo) {
    if(completedStatus == 1) {
        var style = ' style="color:green;font-size:14px;"';
        showMessage('<strong ' + style + '>Completed</strong>', 'messageInfo' + tabNo, true);
    } else {
        showMessage('', 'messageInfo' + tabNo, false, 10);
    }
}

/**
 * Finds the button from one of the following categories given by the server:
 * Available, Selected, Disabled and updates its properties and css.
 * @param currentButton
 * @param response
 * @param tabNo
 */
function changeButtonStatus(currentButton, response, tabNo) {
    var currentButtonId = currentButton.attr('id');

    var available = response.Available;
    var selected = response.Selected;

    if(searchArray(selected, currentButtonId, tabNo)) {
        currentButton.attr("state", "pressed");
        currentButton.css("background-color", selectedColors[tabNo % MAX_TAB_NO]);
        return;
    }
    if(searchArray(available, currentButtonId, tabNo)) {
        currentButton.attr("state", "unpressed");
        currentButton.css("background-color", color[tabNo]);
        return;
    }

    currentButton.attr("state", "disabled");
    currentButton.css("background-color", disabledColor);
}

function searchArray(array, currentButtonId, tabNo) {
    var suggestion;

    for(var i = 0; i < array.count; i++) {
        if(array[i].Type == 'concentration') {
            suggestion = array[i].Type + '_' + array[i].Stimulant + '_' + array[i].Value + '_' + tabNo;
        } else {
            suggestion = array[i].Type + '_' + array[i].Value + '_' + tabNo;
        }

        if(suggestion == currentButtonId) {
            return true;
        }
    }

    return false;
}

/**
 * Resets the different elements display properties to the status provided
 * @param status - integer
 */
function resetDisplay(tabNo, response) {
    var status = response.Display.Status;

    var strain          = $("#strain"           + tabNo).parent();
    var timePoint       = $("#timePoint"        + tabNo).parent();
    var concentration   = $('#concentration'    + tabNo).parent();
    var experimentalist = $("#experimentalist"  + tabNo).parent();
    var replicate       = $("#replicate"        + tabNo).parent();
    var tabStatus       = $("#status"           + tabNo).parent();

    strain.css("display", "none");
    timePoint.css("display", "none");
    concentration.css("display", "none");
    experimentalist.css("display", "none");
    replicate.css("display", "none");
    tabStatus.html("");

    switch(status) {
        case 6:
            tabStatus.html('<span style="color:green;">Completed</span>');
        case 5:
            replicate.css("display", "block");
        case 4:
            experimentalist.css("display", "block");
        case 3:
            concentration.css("display", "block");
        case 2:
            timePoint.css("display", "block");
        case 1:
            strain.css("display", "block");
            manageButtons(tabNo, response.Buttons);
            break;
    }
}

/**
 * Resets all the buttons back to the starting position
 * @param tabNo
 * @param response - JSON response from the server
 */
function resetFields(tabNo, response) {
    resetDisplay(tabNo, response);
    manageButtons(tabNo, response.Buttons);
    isCompleted(0, tabNo);
}

/**
 * Deletes an entire data set including the div content for the data set and the
 * navigation tab to access the div content. Changes the focus on the next
 * data set, removes the tabNo from tabs and maintains tab structure
 * @param tabNo
 */
function deleteTab(tabNo) {
    var index = tabs.indexOf(tabNo);

    $('#data' + tabNo).remove(); //removes div content
    $('#aData' + tabNo).remove(); //removes navigation tab

    if (index == 0 && tabs.length != 1) {
        changeTabColor(tabs[index + 1], color[tabs[(index + 1)] % 10]);
    } else if (tabs.length != 1) {
        changeTabColor(tabs[index - 1], color[tabs[(index - 1)] % 10]);
    }
    if (index > -1) {
        tabs.splice(index, 1);
    }

    tabsStructureStatus();
}

/**
 * Parameters management manages the deletion of tabs and the reset of fields
 * @param tabNo
 * @param type - "Delete" or "Reset"
 */
function parametersManagement(tabNo, type) {
    if(!$.isNumeric(tabNo)) return;
    if(type != 'Delete' && type != 'Reset') showMessage(messages.error, 'message' + tabNo);

    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'parameters.php?params=' + type + '&id=' + tabNo);
    xhr.onreadystatechange = (function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            if(!checkXHRStatus(xhr.responseText)) return;

            var response = JSON.parse(xhr.responseText);

            if(response.Status != 0) {
                showMessage(messages.error, 'message' + tabNo);
                return;
            }

            if(type == 'Reset') {
                resetFields(tabNo, response);
            } else if(type == 'Delete') {
                deleteTab(tabNo);
            }
        }
        else if(xhr.status != 200) {
            checkXHRStatus();
        }
    });
    xhr.send(null);
}

function feedbackTab() {
    var divElement = '#feedback';

    $('.tabs ' + divElement).show().siblings().hide();
    $(divElement).parent('li').addClass('active').siblings().removeClass('active');
    $(".tab-content").css('border', '2px solid #808080');

    $(divElement).iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });

    $('#pageSelection').select2({ width: '200px', minimumResultsForSearch: -1 });
}

function feedbackSubmit() {
    var form = new FormData(document.getElementById('feedbackForm'));
    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxDirectory + 'feedback.php');
    //xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = (function() {
        if(xhr.readyState == 4 && xhr.status == 200) {
            if (!checkXHRStatus(xhr.responseText)) return;

            var response = JSON.parse(xhr.responseText);

            if(response.ValidInput == 0) {
                $('#feedbackMessage').html('<span style="color:red;">Errors occured:<br/> ' + response.Errors.join('<br/>') + '</span>');
                return;
            }

            $('#feedbackMessage').html('<span style="color:green;">Thank you for submitting feedback!</span>');



        } else if(xhr.status != 200) {
            checkXHRStatus();
        }
    });
    xhr.send(form);
}

/**
 * Called when the submit tab is clicked on. Shows all the experiment
 * information in one view and if all experiments were completed,
 * it will display a submit button to run the scatter plots
 */
function goSubmit() {
    var currentAttrValue = document.getElementById('aSubmit').getAttribute('href');
    var xhr = new XMLHttpRequest();

    //change the focus tab to submit
    $('.tabs ' + currentAttrValue).show().siblings().hide();
    $('#submitPage').parent('li').addClass('active').siblings().removeClass('active');
    $(".tab-content").css('border', '2px solid #808080');
    $("#details").empty(); //empty the content - will be repopulated in the request response

    xhr.open('POST', ajaxDirectory + 'submit.php');
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if(!checkXHRStatus(xhr.responseText)) return;

            var response = JSON.parse(xhr.responseText);

            for (var m = 0; m < tabs.length; m++) {
                if (response.Data[tabs[m]].Status == "Completed") {
                    $("#details").append("<div>" +
                        "<span style='margin-right:10px;padding-top:12px;float:left;'> Data " + tabs[m] + ": </span>" +
                        "<div style='margin-left:60px;'>" +
                            "<input type='button' class='button' value='Protocol' onclick='showProtocol(" + tabs[m] + ");' style='float:right; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='" + response.Data[tabs[m]].dataType + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='" + response.Data[tabs[m]].cellType + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='" + response.Data[tabs[m]].stimulation.toString().replace(/,/g, ' + ') + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='" + response.Data[tabs[m]].strain.toString().replace(/:/g, '/') +  "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='" + response.Data[tabs[m]].concentration.toString().replace(/,/g, 'mL + ').replace(/:/g, '/') + " mL' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='" + response.Data[tabs[m]].timePoint + " Minutes' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='Done by: " + response.Data[tabs[m]].experimentalist + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='Replicate: " + response.Data[tabs[m]].replicate + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                            "<input type='button' class='button' value='Unique genes: " + response.Data[tabs[m]].geneNo + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/>" +
                        "</div></div>");
                } else {
                    $("#details").append("<div>" +
                        "<span> Data " + tabs[m] + ": " + response.Data[tabs[m]].Status +"</span>" +
                        "</div>" +
                        "<br/>");
                }
            }
            if(response.OneOrganism == 0) {
                $('#details').append('<p>Unfortunately, comparing two different types of organism is not available at this time</p>');
            }
            if (response.ReadyToSubmit == 1) {
                $("#details").append("<div style='height: 35px;'>" +
                    '<input type="button" value="Download Table" class="button" style="border: 3px solid #3498db; color: #3498db;" onclick="downloadTable();"/>' +
                    "<div style='float:right;'>" +
                        "<span style='margin-right: 5px'>Note: If multiple values exist for a specified gene, please select a statistic operation </span>"+
                    "<select name='submitOperation' id='submitOperation'><option>Mean</option><option>Min</option><option>Max</option></select>" +
                    "<input type='button' class='button' value='Submit' style='border: 3px solid #3498db; color: #3498db; margin-left:10px;' onclick='submitData();'/>" +
                    "</div></div>");
                $('#submitOperation').select2({ width: '70px' });
            }
        } else if(xhr.status != 200) {
            checkXHRStatus();
        }
    };
    xhr.send('GetInfo=1');
}


function showProtocol(tabNo) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'getExpInfo.php?type=protocol&id='+tabNo);
    xhr.onreadystatechange = function() {
        if(xhr.status == 200 && xhr.readyState == 4) {
            if(!checkXHRStatus(xhr.responseText)) return;
            var response = JSON.parse(xhr.responseText);
            if(response.Status == 0 || response.Status == 1) {
                $( "#dialog" ).html('<p>'+response.Message.replace(/:/g, '/') +'</p>');
                $( "#dialog" ).dialog({
                    title: "Protocol"
                });
            }
        } else if(xhr.status != 200) {
            checkXHRStatus();
        }
    };
    xhr.send(null);
}

function submitData() {
    possibleCombinations = 0;

    $('#loadingGif').css('display', 'block');
    //reset all divs
    $("#results1").empty()
        .height(0)
        .append('<table id="dc-data-table" style="float:none; display:none;">' +
            '<thead>' +
                '<tr class="header" id="tableHeader">' +
                    '<th>Gene</th>' +
                    '<th>Alternate ID</th>' +
                '</tr>' +
            '</thead>' +
            '</table>');


    $('#results2').empty();
    $("#results3").empty();
    $("#results4").empty();

    changeTabColor(1, '#686868', 1);

    $(".tab-links2").empty()
        .append('<li class="active2">' +
                '<a id="aResults1" href="#results1" onclick="changeTabColor(1, \'#d1d1e0\', 1 );">Scatter Plots</a>' +
            '</li>');

    for (var i = 0; i < tabs.length; i++) {
        $('#tableHeader').append('<th>Data' + tabs[i] + '</th>');
    }

    //operation
    geneOperation = $('#submitOperation').val();

    //Graphing
    makeGraphs();

    // changing the div's height
    $("#results1").height(Math.floor((possibleCombinations + 1) / 2) * 520 + 20)
        .append('<div style="clear:both;">' +
                "<div style='text-align: center;font-weight: bold;text-decoration: underline;'>To move forward please select: </div>" +
            '<input type="radio" name="interU" value="intersection" class="interU">' +
            '<span style="margin-right:350px; margin-left:5px">Select Intersection</span>' +
            '<div style="float:right;"><input type="radio" class="interU" name="interU" value="union"/>' +
            '<span style="margin-right:5px; margin-left:5px;float:right">Select Union</span>' +
            '</div></div>');
    $('#aResults1').css('border', '5px solid #d1d1e0')
        .css('background-color', '#d1d1e0');
    $('input').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });

    resultListeners();

    $(".active2").css("display", "block");
    $("#results1").css("display", "block");
    $(".tab-content2").css("display", "block");

}

function downloadTable() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'table.php');
    xhr.onreadystatechange = (function() {
       if(xhr.readyState == 4 && xhr.status == 200) {
           window.location = ajaxDirectory + 'table.php';
       }
    });
    xhr.send(null);
}

function resultListeners() {
    $('.filterMouse').on('ifChecked', function () {
        if ($(this).val() == "brush") {
            $("#scatter" + $(this).attr("index") + " .brush").css("pointer-events", "all");
            $("#scatter" + $(this).attr("index") + " .chart-body").css("pointer-events", "none");
            $(".hidden" + $(this).attr("index")).css("display", "none");
        }
        else {
            $("#scatter" + $(this).attr("index") + " .brush").css("pointer-events", "none");
            $("#scatter" + $(this).attr("index") + " .chart-body").css("pointer-events", "all");
        }
    });

    $('.interU').on('ifChecked', function () {

        finalRanges2 = [];
        for (var r = 0; r < tabs.length; r++) {
            finalRanges2.push([-(Number.MAX_VALUE), Number.MAX_VALUE]);
        }
        //console.log(finalRanges2);
        //ranges2 = ranges2.split(",");
        var possibleCombinations4 = 0;
        for (var a = 0; a < tabs.length - 1; a++) {
            for (var h = a + 1; h < tabs.length; h++) {
                if (!(jQuery.isEmptyObject(ranges2[possibleCombinations4]))) {
                    //a min
                    if (ranges2[possibleCombinations4][0][0][0] > finalRanges2[a][0]) {
                        finalRanges2[a][0] = ranges2[possibleCombinations4][0][0][0];
                    }
                    //a max
                    if (ranges2[possibleCombinations4][0][1][0] < finalRanges2[a][1]) {
                        finalRanges2[a][1] = ranges2[possibleCombinations4][0][1][0];
                    }
                    //h min
                    if (ranges2[possibleCombinations4][0][0][1] > finalRanges2[h][0]) {
                        finalRanges2[h][0] = ranges2[possibleCombinations4][0][0][1];
                    }
                    //h max
                    if (ranges2[possibleCombinations4][0][1][1] < finalRanges2[h][1]) {
                        finalRanges2[h][1] = ranges2[possibleCombinations4][0][1][1];
                    }
                }
                possibleCombinations4++;
            }
        }
        //console.log(finalRanges2);


        $(".tab-links2").empty()
            .append(
            '<li class="active2"><a id="aResults1" href="#results1" onclick="changeTabColor(1, \'#d1d1e0\', 1 );">Scatter Plots</a></li>' +
            '<li><a href="#results2" id="aResults2" onclick="changeTabColor(2, \'#d1d1e0\', 1);">Chord Diagram And DataTable</a></li>' +
            '<li><a href="#results3" id="aResults3" onclick="changeTabColor(3, \'#d1d1e0\', 1);">Network Charts</a></li>' +
            '<li><a href="#results4" id="aResults4" onclick="changeTabColor(4, \'#d1d1e0\', 1)">Pathways</a></li>');

        $('#aResults1').css('border', '5px solid #d1d1e0')
            .css('background-color', '#d1d1e0');
        $('#aResults2').css('border', '5px solid #d1d1e0')
            .css('background-color', '#d1d1e0');
        $('#aResults3').css('border', '5px solid #d1d1e0')
            .css('background-color', '#d1d1e0');
        $('#aResults4').css('border', '5px solid #d1d1e0')
            .css('background-color', '#d1d1e0');

        //CHORD AND TABLE
        $("#results2").empty();

        //NETWORK
        $("#results3").empty()
            .append('<div id="results3Message"></div>')
            .append('<div id="networkChoice" style="display: block;">' +
                '<p> Please select one of the options, circle is selected by default </p>' +
                '<input type="radio" name="networkChoice" value="Random" class="networkChoice">' +
                '<span style="margin-right:15px; margin-left:5px">Random</span>' +
                '</input><input type="radio" class="networkChoice" name="networkChoice" value="Grid">' +
                '<span style="margin-right:15px; margin-left:5px">Grid</span>' +
                '</input><input type="radio" id="circle" name="networkChoice" value="Circle" class="networkChoice" >' +
                '<span style="margin-right:15px; margin-left:5px">Circle</span>' +
                '</input><input type="radio" class="networkChoice" name="networkChoice" value="Concentric">' +
                '<span style="margin-right:15px; margin-left:5px">Concentric</span>' +
                '</input>' +
                '<div style="float:right; margin-right:20px;">' +
                '<label for="networkGeneNo">Number of Genes: </label><input type="number" name="networkGeneNo" id="networkGeneNo" style="margin-right:10px;" />' +
                '<input type="button" class="button" value="Reset" style="border: 3px solid #3498db; color: #3498db;" onclick="makeNetwork();">' +
                '</div>' +
                '</div>')
            .append("<div id='results3iframe' style='display: block;'></div>");

        $('#networkChoice').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });
        $('#circle').iCheck('toggle');
        makeNetwork("circle");

        //Chord Diagram
        if ($(this).val() == "intersection") {
            $("#results2").height(551).append("<div id='chordMessage'><span style='font-weight: bold;'>Please Note:</span> Chord Diagrams can not be created if intersection of genes are selected in the Scatter Plots page<br/><br/></div>");
            filterType = 'intersection';
        }
        else {
            filterType = 'union';
            $("#results2").append("<div id='results2chord'><div class='first' id='original'></div><div id='filtered'></div></div>");
            var xhr = new XMLHttpRequest();
            xhr.open('GET', ajaxDirectory + 'data.php?type=chord&id=1&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType + '&operation='+geneOperation);
            xhr.onreadystatechange = (function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    makeChordDiagram(1, tabs, response);//original data

                }
            });
            xhr.send(null);

            setTimeout(function() {
                var xhr2 = new XMLHttpRequest();
                xhr2.open('GET', ajaxDirectory + 'data.php?type=chord&id=2&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType + '&operation=' + geneOperation);
                xhr2.onreadystatechange = (function () {
                    if (xhr2.readyState == 4 && xhr2.status == 200) {
                        var response = JSON.parse(xhr2.responseText);
                        makeChordDiagram(2, tabs, response);//original data

                    }
                });
                xhr2.send(null);

                $("#results2").height(1057);
            }, 1000);
        }

        $(".networkChoice").on("ifChecked", function(event){
            //console.log(event.currentTarget.value);
            switch(event.currentTarget.value){
                case "Random":
                    makeNetwork("random");
                    break;
                case "Grid":
                    makeNetwork("grid");
                    break;
                case "Circle":
                    makeNetwork("circle");
                    break;
                case "Concentric":
                    makeNetwork("concentric");
                    break;
                default:
                    makeNetwork("concentric");
            }
        });

        //PATHWAY
        $("#results4").empty()
            .append('<p> Please select a KEGG pathway by typing it into the text box. TLR pathway is selected by default. </p><select name="pathwayOptions[]" multiple="multiple" id="pathwayOp" style="width: 50%;"></select>' +
            '<input type="submit" value="Submit" class="button" style="float:right;border: 3px solid #3498db; color: #3498db;" onclick="pathwayCreate();"/>')
            .append("<div id='results4iframe'></div>");

        var pathwayListXHR = new XMLHttpRequest();
        pathwayListXHR.open('GET', ajaxDirectory + 'information.php?type=pathwayFetch');
        pathwayListXHR.onreadystatechange = (function () {
            if (pathwayListXHR.readyState == 4 && pathwayListXHR.status == 200) {
                var response = JSON.parse(pathwayListXHR.responseText);
                $("#pathwayOp").select2({
                    data: response.Pathway
                });

            }
            //pathwayOp.value = "Toll-"
        });
        //pathwayCreate();
        pathwayListXHR.send(null);




        $("#results2").append('<table id="table_id" class="display"></table><div style="width:1127px;display:none;text-align:center;" id="tabledownload9"><a href="'+ ajaxDirectory + 'table.php?download=DataTable&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType + '&operation='+geneOperation +'"><input type="button" class="button" value="Download Table" style="border: 3px solid #3498db; color: #3498db; margin-left:5px;" /></a></div>');
        displayGraph('Table.Values', '#table_id', '', 'dynamic', '', '', 'tabledownload9', '', '', '', geneOperation);

        pathwayCreate();
    });
}

function gradient(divElement, quantile) {
    if(quantile.down == undefined || quantile.up == undefined) return;

    var x1 = 60, barWidth = 100, y1 = 4, barHeight = 25;

    var svgForLegendStuff = d3.select("#" + divElement).append("svg")
        .attr("width", 230)
        .attr("class","legend")
        .attr("height", 24);

    //create the empty gradient
    var gradient = svgForLegendStuff.append("g")
        .append("defs")
        .append("linearGradient")
        .attr("id","gradient")
        .attr("x1","0%")
        .attr("x2","100%")
        .attr("y1","0%")
        .attr("spreadMethod","pad")
        .attr("y2","0%");

    //define gradient
    gradient.append("svg:stop")
        .attr("offset", "0%")
        .attr("stop-color", "#0000FF")
        .attr("stop-opacity", 1);

    gradient.append("svg:stop")
        .attr("offset", "100%")
        .attr("stop-color",  "#EE3B3B")
        .attr("stop-opacity", 1);

    //fill the gradient
    svgForLegendStuff.append("rect")
        .attr("fill","url(#gradient)")
        .attr("x",x1)
        .attr("y",y1)
        .attr("width",barWidth)
        .attr("height",barHeight)
        .attr("rx",0)
        .style("stroke", "white")
        .style("fill", "url(#gradient)")
        .attr("opacity", 1)
        .attr("ry",0);


    //numeric legend
    var textY = y1 + barHeight/2 + 15;
    svgForLegendStuff.append("text")
        .attr("class","legendText")
        .attr("text-anchor", "left")
        .attr("x",0)
        .attr("y",textY-11)
        .attr("dy",0)
        .text(quantile.down);

    svgForLegendStuff.append("text")
        .attr("class","legendText")
        .attr("text-anchor", "left")
        .attr("x",x1 + barWidth + 10)
        .attr("y",textY-11)
        .attr("dy",0)
        .text(quantile.up);
}

/*
 * Creates the network based on the choice selected as the parameter
 */
function makeNetwork(networkChoice){

    if(networkChoice == undefined)
    {
        networkChoice = $('input[name=networkChoice]:checked').val().toLowerCase();
    }

    geneNo = $('#networkGeneNo').val();

    $("#results3iframe").empty();
    $("#results3Message").empty();
    $("#results3iframe").append('<iframe id="iframe" height="700px" width="1100px"><p> error </p></iframe>');
    $("#iframe").attr('src', 'Assets/Cytoscape/network.php?tabs=' + tabs + '&ranges=' + finalRanges2 + '&filter=' + filterType + '&networkChoice=' + networkChoice + '&operation=' + geneOperation + '&geneNo=' + geneNo);

    $('#networkGeneNo').val(geneNo);

}

/*
 * Creates the pathway based on the value of the pathways or defaults to the TLR Pathway
 */
function pathwayCreate() {
    var pathwayOptions2 = $("#pathwayOp").val();
    if(pathwayOptions2 == null) {
        pathwayOptions2 = ["mmu04620"];
    }

    var dataLegend = [];
    dataLegend.push('<td>Data</td>');
    for(var i = 0; i < tabs.length; i++) {
        dataLegend.push('<td class="tableBorder">' + tabs[i] + '</td>');
    }

    //Reset and Reloads the pathway frame
    $("#results4iframe").empty().append('<div style="text-align:center;padding-left:24px"><table style="margin-left: auto;margin-right: auto;"><tr>' + dataLegend.join('') + '</tr></table></div>');

    for (var m = 0; m < pathwayOptions2.length; m++) {
        $('#results4iframe').append('<div style="text-align:center;" id="gradientiframe' + (m + 2) + '"></div>')
            .append('<iframe id="iframe' + (m + 2) + '" height="800px" width="1100px"><p> error </p></iframe>');
        $("#iframe" + (m + 2)).attr('src', 'Assets/Cytoscape/pathway.php?tabs=' + tabs + '&ranges=' + finalRanges2 + '&filter=' + filterType + '&pathwayOptions=' + pathwayOptions2[m] + '&operation='+geneOperation);
    }

}

function updatePaths(index, data1, data2) {
    var tooltip = d3.select("#tooltip" + index);
    var test = d3.selectAll("#scatter" + index + " .chart-body path");
    test.style("opacity", 0.6);
    d3.selectAll("#scatter" + index + " .chart-body path")
        .on("mouseover", function (d) {
            //Update the tooltip value
            $(".hidden" + index).css("display", "block");
            tooltip.select("#name" + index).text(d.data2.Gene);
            //tooltip.select("#active"+index).text(d.data2.AltID);
            eval("tooltip.select(\"#inactive\"+" + index + ").text(Math.round(+d.data2.Data" + data1 + "*100)/100);");
            eval("tooltip.select(\"#medSal\"+" + index + ").text(Math.round(+d.data2.Data" + data2 + "*100)/100);");
        })
        .on("mouseout", function () {
            //Hide the tooltip
            tooltip.classed("hidden" + index, true);
            $(".hidden" + index).css("display", "none");
        });
}

function testOccurrence(d) {
    var win = window.open('http://www.ncbi.nlm.nih.gov/gene/?term=' + d.data2.Gene, '_blank');
    win.focus();
}


//RESULTS
function makeGraphs() {
    var arrayCombinations = [];
    var scatterNames = [];
    var histogramNames = [];
    var dataAvailable = [];
    var histograms = [];
    var index = 0;
    var Histfilters = [[0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0], [0, 0]];
    var filter1 = [];
    //finalRanges = [];
    ranges2 = [];
    possibleCombinations = 0;
    var possibleCombinations3 = 0;
    for (var a = 0; a < tabs.length - 1; a++) {
        for (var h = a + 1; h < tabs.length; h++) {
            possibleCombinations3++;
        }
    }


    for (var a = 0; a < tabs.length - 1; a++) {
        for (var h = a + 1; h < tabs.length; h++) {
            possibleCombinations++;
            index = possibleCombinations;
            $("#results1").append('<div class="eachGraph" style="float:' + ((index % 2 == 1) ? "left" : "right") + ';">' +
                '<div>' +
                '<input type="radio" name="type' + index + '" value="brush" class="brush' + index + ' filterMouse" index="' + index + '" checked="checked" style="position:fixed;">' +
                '<span style="margin-right:15px; margin-left:5px">Filter</span></input>' +
                '<input type="radio" name="type' + index + '" index="' + index + '" value="mouseover" class="filterMouse" style="position:fixed;">' +
                '<span style="margin-right:5px; margin-left:5px">Mouseover</span></input>' +
                '<span style="padding-left:20px;" id="correlation' + index +'"></span>' +
                '<aside id="tooltip' + index + '" class="hidden' + index + ' tooltip">' +
                '<span>Gene Name: </span>' +
                '<span id="name' + index + '" style="color:black;"></span>' +
                '<br/><span>Data ' + tabs[a] + ': </span>' +
                '<span id="inactive' + index + '" style="color:black;"></span>' +
                '<br/><span>Data ' + tabs[h] + ': </span>' +
                '<span id="medSal' + index + '" style="color:black;"></span></aside></div>' +
                '<div id="chart-hist-data' + index + tabs[a] + '" class="firstHist" onclick="brush(' + index + ');">' +
                '<div class="clearfix"></div></div>' +
                '<div id="chart-hist-data' + index + tabs[h] + '" class="secondHist" onclick="brush(' + index + ');">' +
                '<div class="clearfix"></div></div>' +
                '<div id="scatter' + index + '" onclick="filterdisable();" class="scatter">' +
                '<div class="clearfix"></div></div></div>');//
        }
    }


    //console.log(possibleCombinations);
    //d3.csv("DataFiles/Bin36.csv", function(data) {
    setTimeout(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', ajaxDirectory + 'data.php?filter=intersection&type=getCorrelation&operation=' + geneOperation);
        xhr.onreadystatechange = (function() {
            if(xhr.status == 200 && xhr.readyState == 4) {
                if(!checkXHRStatus(xhr.responseText)) return;
                var index = 0;
                var response = JSON.parse(xhr.responseText);
                for (var a = 0; a < tabs.length - 1; a++) {
                    for (var h = a + 1; h < tabs.length; h++) {
                        index++;
                        $('#correlation' + index).html('Correlation: ' + response[a][h]);
                    }
                }
            } else if(xhr.status != 200) {
                checkXHRStatus();
            }
        });
        xhr.send(null);
    }, 2000);
    d3.csv(ajaxDirectory + "data.php?type=retrieveAllIntersect&filter=intersection&operation=" + geneOperation, function (data) {
        data.forEach(function (d) {
            for (var z = 0; z < tabs.length; z++) {
                eval("d.Data" + tabs[z] + " =Math.round(d.Data" + tabs[z] + "*10)/10;");
            }

            d.Gene = d.Gene;
            //d.AltID = d.AltID;
            d.Dummy = "";
        });
        var ndx = crossfilter(data);



        for (var z = 0; z < tabs.length; z++) {
            eval("var hist" + tabs[z] + "Dim = ndx.dimension(function(d) {return +d.Data" + tabs[z] + ";})");
            eval("var hist" + tabs[z] + "DimExtent = d3.extent(data, function(d) { return d.Data" + tabs[z] + "; });")
            eval("hist" + tabs[z] + "DimExtent[1] = hist" + tabs[z] + "DimExtent[1] + 1; hist" + tabs[z] + "DimExtent[0] = hist" + tabs[z] + "DimExtent[0] - 1;");
            eval("var hist" + tabs[z] + "Group = hist" + tabs[z] + "Dim.group();");

        }
        //console.log(finalRanges);
        for (var m = 1; m < possibleCombinations + 1; m++) {
            eval("var scatter" + m + "=dc.scatterPlot('#scatter" + m + "');");
        }

        var possibleCombinations2 = 0;
        for (var a = 0; a < tabs.length - 1; a++) {
            for (var h = a + 1; h < tabs.length; h++) {
                possibleCombinations2++;
                eval("var dim" + possibleCombinations2 + " = ndx.dimension(function (d) {return [+d.Data" + tabs[a] + ", +d.Data" + tabs[h] + "];});");
                eval("var group" + possibleCombinations2 + " = dim" + possibleCombinations2 + ".group().reduce(function reduceAdd(p, v) {p = v;p.count = 1;return p;},function reduceRemove(p, v) {p = v;p.count = 0;return p;},function reduceInitial() {return   {count : 0,Gene : '',Data" + tabs[a] + " : 0,Data" + tabs[h] + " : 0};});");
                eval("scatter" + possibleCombinations2 + ".width(420).height(382).x(d3.scale.linear().domain(hist" + tabs[a] + "DimExtent)).y(d3.scale.linear().domain(hist" + tabs[h] + "DimExtent)).yAxisLabel('Data" + tabs[h] + "').xAxisLabel('Data" + tabs[a] + "').symbolSize(4).clipPadding(10).dimension(dim" + possibleCombinations2 + ").group(group" + possibleCombinations2 + ");scatter" + possibleCombinations2 + ".on('renderlet',function(chart) {dc.events.trigger(function() {ranges2[" + (possibleCombinations2 - 1) + "] = scatter" + possibleCombinations2 + ".filters();}); /*console.log('ranges='+ranges2);*/ setAxisColor(" + possibleCombinations2 + ", " + tabs[a] + ", " + tabs[h] + ");updatePaths(" + possibleCombinations2 + "," + tabs[a] + "," + tabs[h] + "); chart.selectAll('.symbol').on('click', function(d) { testOccurrence(d);});});");
                eval("var data" + possibleCombinations2 + tabs[a] + "HistChart  = dc.barChart('#chart-hist-data" + possibleCombinations2 + tabs[a] + "');");
                eval("var data" + possibleCombinations2 + tabs[h] + "HistChart  = dc.barChart('#chart-hist-data" + possibleCombinations2 + tabs[h] + "');");
                eval("data" + possibleCombinations2 + tabs[a] + "HistChart.width(400).height(120).dimension(hist" + tabs[a] + "Dim).group(hist" + tabs[a] + "Group).x(d3.scale.linear().domain(hist" + tabs[a] + "DimExtent)).centerBar(true).gap(1).elasticY(true).xUnits(function(){return 70;});");
                eval("data" + possibleCombinations2 + tabs[h] + "HistChart.width(410).height(120).dimension(hist" + tabs[h] + "Dim).group(hist" + tabs[h] + "Group).x(d3.scale.linear().domain(hist" + tabs[h] + "DimExtent)).centerBar(true).gap(1).elasticY(true).xUnits(function(){return 70;});");
            }
        }

        //change heights
        var x = '';
        var row = 1;
        var arrayOfI = [];
        var a = 0;
        // Create table
        var datatable = dc.dataTable("#dc-data-table");
        var GeneSymbol = ndx.dimension(function (d) {
            return d.Gene;
        });
        var GeneSymbolGroup = GeneSymbol.group().reduceCount();

        var string = "datatable.columns([function(d) {if(x!=d.Gene){arrayOfI[a] = row;a++;x=d.GeneSymbol;}row++;return d.Gene;},";
        for (var t = 0; t < tabs.length; t++) {
            if (t != tabs.length - 1) {
                string += "function(d) {return d.Data" + tabs[t] + ";},"
            }
            else {
                string += "function(d) {return d.Data" + tabs[t] + ";}])"
            }
        }
        string += ".dimension(GeneSymbol).group(function(d) {return d.Dummy;}).size(Infinity).on('renderlet', function (table) {table.selectAll('.dc-table-group').classed('header', true); findMinMax();});"

        eval(string);


        dc.renderAll();
        $('#loadingGif').css('display', 'none');

    });

    $('input').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });





}

function disableBrush() {
    $(".secondHist .brush").css("pointer-events", "none");
    $(".secondHist .chart-body").css("pointer-events", "all");
    $(".firstHist .brush").css("pointer-events", "none");
    $(".firstHist .chart-body").css("pointer-events", "all");
}


function setAxisColor(index, name1, name2) {
    disableBrush();
    $("#scatter" + index + " .x-axis-label").css("fill", backgroundColors[name1]);
    $("#scatter" + index + " .y-axis-label").css("fill", backgroundColors[name2]);
    $("#chart-hist-data" + index + name1 + " rect.bar").css("fill", backgroundColors[name1]);
    $("#chart-hist-data" + index + name2 + " rect.bar").css("fill", backgroundColors[name2]);
    var color1 = Color(backgroundColors[name1]);
    var color2 = Color(backgroundColors[name2]);
    var mix = RGBMix(color1, color2);
    $("#scatter" + index + " path.symbol").css("fill", mix.hexString());
}

function brush(index) {
    $('.brush' + index).iCheck('check');
}
function filterdisable() {

    $('.interU').iCheck('uncheck');
}
Array.max = function (array) {
    return Math.max.apply(Math, array);
};

Array.min = function (array) {
    return Math.min.apply(Math, array);
};

function findMinMax() {
    ranges = [];
    var s = 2;
    for (var q = 0; q < tabs.length; q++) {
        eval("var data" + tabs[q] + "= $('._" + s + "').map(function() {return parseFloat($(this).text());}).get(); var data" + tabs[q] + "Range = []; data" + tabs[q] + "Range.push(Array.min(data" + tabs[q] + "));data" + tabs[q] + "Range.push(Array.max(data" + tabs[q] + ")); ranges.push(data" + tabs[q] + "Range);");
        s++;
    }
    //console.log("original" + ranges);


}

function RGBMix(color1, color2) {
    var r = (color1.red() + color2.red()) / 2;
    var g = (color1.green() + color2.green()) / 2;
    var b = (color1.blue() + color2.blue()) / 2;

    return Color().rgb([r, g, b]);
}

//chord graph
function makeChordDiagram(id, allData, matrix) {
    var colorChord = [];


    //var matrix = [[10,180,60],[0,20,30],[0,0,40]];
    for (var i = 0; i < allData.length; i++) {
        colorChord[i] = backgroundColors[allData[i]];
    }

    var chord = d3.layout.chord()
        .padding(.05)
        .sortSubgroups(d3.descending)
        .matrix(matrix);

    var width = 530,
        height = 500,
        innerRadius = Math.min(width, height) * .38,
        outerRadius = innerRadius * 1.05;

    var fill = d3.scale.ordinal()
        .domain(d3.range(4))
        .range(colorChord);

    if (id == 1) {
        var div = d3.select("#original");
    } else {
        var div = d3.select("#filtered");
    }

    var svg = div.append("svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

    svg.append("g").selectAll("path")
        .data(chord.groups)
        .enter().append("path")
        .style("fill", function (d) {
            return fill(d.index);
        })
        .style("stroke", function (d) {
            return fill(d.index);
        })
        .attr("d", d3.svg.arc().innerRadius(innerRadius).outerRadius(outerRadius))
        .on("mouseover", fade(.1))
        .on("mouseout", fade(1));

    var ticks = svg.append("g").selectAll("g")
        .data(chord.groups)
        .enter().append("g").selectAll("g")
        .data(groupTicks)
        .enter().append("g")
        .attr("transform", function (d) {
            return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
                + "translate(" + outerRadius + ",0)";
        });
    ticks.append("line")
        .attr("x1", 1)
        .attr("y1", 0)
        .attr("x2", 5)
        .attr("y2", 0)
        .style("stroke", "#000");

    ticks.append("text")
        .attr("x", 8)
        .attr("dy", ".35em")
        .attr("transform", function (d) {
            return d.angle > Math.PI ? "rotate(180)translate(-10)" : null;
        })
        .style("text-anchor", function (d) {
            return d.angle > Math.PI ? "end" : null;
        })
        .text(function (d) {
            return d.label;
        });

    svg.append("g")
        .attr("class", "chord")
        .selectAll("path")
        .data(chord.chords)
        .enter().append("path")
        .attr("d", d3.svg.chord().radius(innerRadius))
        .style("fill", function (d) {
            return fill(d.target.index);
        })
        .style("opacity", 1);

    // Returns an array of tick angles and labels, given a group.
    function groupTicks(d) {
        /*var k = (d.endAngle - d.startAngle) / d.value;
         //console.log(k);
         return d3.range(0, d.value, 100).map(function(v, i) {
         // console.log(v);
         // console.log(d.value);
         return {

         angle: v * k + d.startAngle,
         label: i % 5 ? null : Math.round(v/d.value*100) + "%"
         };
         });
         */
        var k = (d.endAngle - d.startAngle) / d.value;
        //console.log(d);
        //console.log("k:" + k);
        var numberValue = Math.round(d.value);
        var digits = (numberValue.toString().length) - 2;

        var interval = Math.pow(10, digits);

        var remainder = (Math.floor(numberValue / (interval * 10)) * interval).toString().substr(1);
        //console.log(remainder);
        //console.log(interval);
        return d3.range(0, numberValue, interval).map(function (v, i) {
            //console.log("v:" + v);
            //console.log("i:" + i);
            return {
                angle: v * k + d.startAngle,
                label: i % 5 ? null : v / interval + remainder
            };
        });
    }

    // Returns an event handler for fading a given chord group.
    function fade(opacity) {
        return function (g, i) {
            svg.selectAll(".chord path")
                .filter(function (d) {
                    return d.source.index != i && d.target.index != i;
                })
                .transition()
                .style("opacity", opacity);
        };
    }
}

//DATATABLE
function displayGraph(fileName, tableId, display, title, firstwidth, lastwidth, download, scroll, filter, order) {
    $.ajax({
        "url": ajaxDirectory + 'data.php?type=dataTable&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType + '&operation=' + geneOperation,
        "success": function (json) {
            json.bDestroy = true;

            //json.fnInitComplete = function() { displayGraphState(); };
            json.deferRender = true;
            if (firstwidth != undefined && firstwidth != '') {
                json.aoColumns[0].width = firstwidth;
            }
            if (lastwidth != undefined && lastwidth != '') {
                json.sScrollX = '100%';
                var number = json.aoColumns.length;
                json.aoColumns[number - 1].sWidth = lastwidth;
            }
            if (scroll != undefined && scroll != '') {
                json.scrollX = true;
                json.sScrollY = "400px;";
                json.aoColumns[1].sWidth = scroll;
                json.bAutoWidth = false;
            }
            if (order != undefined && order != '') {
                json.order = [[order, "asc"]];
            }
            if (filter != undefined && filter != '') {
                if (filter == 'commonseed') {
                    $.fn.dataTable.ext.search.push(
                        function (settings, data, dataIndex) {
                            var min = parseFloat($('#minScore').val());
                            var max = parseFloat($('#maxScore').val());
                            var score = parseFloat(data[2]) || 0; // use data for the age column

                            if (( isNaN(min) && isNaN(max) ) || ( isNaN(min) && score <= max ) || ( min <= score && isNaN(max) ) || ( min <= score && score <= max )) {
                                return true;
                            }
                            return false;
                        }
                    );

                    var tablegess = $(tableId).DataTable(json);
                    $('#minScore, #maxScore').keyup(function () {
                        tablegess.draw();
                    });
                }
                if (filter == 'pathway') {

                    $.fn.dataTableExt.afnFiltering.push(
                        function (oSettings, aData, iDataIndex) {
                            //console.log(oSettings.sTableId);
                            if (oSettings.sTableId == "table11") {
                                var min0 = parseFloat($('#minScore0').val());
                                var max0 = parseFloat($('#maxScore0').val());
                                var score0 = parseFloat(aData[4]) || 0; // use data for the age column
                                if (( isNaN(min0) && isNaN(max0) ) || ( isNaN(min0) && score0 <= max0 ) || ( min0 <= score0 && isNaN(max0) ) || ( min0 <= score0 && score0 <= max0 )) {
                                    return true;
                                }
                                return false;
                            }
                            return true;
                        }
                    );


                    $('#minScore0, #maxScore0').keyup(function () {
                        var tablegess0 = $('#table11').DataTable(json);
                        tablename = 'pathway1';
                        tablegess0.draw();
                    });
                    $(tableId).dataTable(json);
                }

            }
            else if (filter == undefined || filter == '') {
                $(tableId).dataTable(json);
            }
        },
        "dataType": "json"
    });
    if (download != '' && download != undefined) {
        document.getElementById(download).style.display = "block";
    }
}
