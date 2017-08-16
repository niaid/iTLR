//var numberOfTabs = 3;
var i = 3;
var colors = ["'rgb(255, 118, 142)'", "'#ADD3ED'", "'rgb(255, 136, 136)'", "'#ffcc66'", "'#5BC85B'", "'#ff6600'", "'#7B68EE'", "'#FF4500'", "'#7FB5DA'", "'#9400D3'"];
var color = ["pink", "#ADD3ED", "rgb(255, 136, 136)", "#ffcc66", "#5BC85B", "#ff9933", "#BCB2F9", "#FF8E64", "#D8E9F5", "#D67CFC"]
var backgroundColors = ["rgb(255, 118, 142)", "#3498db", "red", "gold", "#7CFC00", "#ff6600", "#7B68EE", "#FF4500", "#7FB5DA", "#9400D3"];
//var colorA = ["\'rgb(255, 118, 142)\'", "\'#ADD3ED\'", "\'rgb(255, 136, 136)\'", "\'#FFD700\'", "\'#FFA500\'", "\'#7CFC00\'", "\'#7B68EE\'", "\'#FF4500\'", "\'#7FB5DA\'", "\'#9400D3\'"];
//var testDataType = ["Microarray", "RNA_Seq", "ChIP_Seq", "Proteomics", "RNAi_Screen"];
//var testCellType = ["BMDM", "RAW", "THP1", "IMMs"];
//var testStimulation = ["LPS", "PolyIC", "R848", "Pam3Csk"];
var ajaxDirectory = 'Assets/AJAX/';
var buttons = [];
var tabs = [1, 2];
//var numberOfTabs;
var possibleCombinations = 0;
var numerator;
//var denomenator;
var ranges = [];
var ranges2 = [];
var finalRanges2 = [];
var filterType = '';
window.onload = function () {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'parameters.php?params=All');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            buttons = JSON.parse(xhr.responseText);
            makeButtons(1);
            makeButtons(2);
        }
    };
    xhr.send(null);
};

function makeButtons(id, data, type) {
    if (typeof data == "undefined") {
        var name = '';
        for (var b = 0; b < buttons.newButtons; b++) {
            name = buttons.Buttons[b].Name;
            name = name.replace(/_/g, ' ');

            $('#' + buttons.Buttons[b].Type + id).append('<input type="button" value="' + name + '" name="' + buttons.Buttons[b].Name + '" class="button2" state="unpressed" style="background-color: ' + color[id % 10] + ';" id="' + buttons.Buttons[b].Type + '_' + buttons.Buttons[b].Name + '_' + id + '" onclick="switchState(\'' + buttons.Buttons[b].Name + '\',' + id + ', \'' + buttons.Buttons[b].Type + '\');"/>');
        }
        //$(".button2").attr("state","unpressed");
    } else if (typeof type != "undefined" && type == "concentration" && data.stimulationNo > 1) {
        for (var b = 0; b < data.stimulationNo; b++) {
            if (b == 0) {
                $('#concentration' + id).html('');
            }
            $('#concentration' + id).append('<span style="margin-right:10px;float:left;">Concentration of ' + data.stimulation[b] + ':</span><div id="concentrationInputs' + id + '_' + b + '"></div>');
            for (var a = 0; a < data.newButtons; a++) {
                if (data.Buttons[a].Type == 'concentration_' + data.stimulation[b]) {
                    name = data.Buttons[a].Name;
                    name = name.replace('-', '/');
                    $('#concentrationInputs' + id + '_' + b).append('<input type="button" value="' + name + '" name="' + data.Buttons[a].Name + '" class= "button2" state="unpressed" style ="background-color: ' + color[id % 10] + ';" id="' + data.Buttons[a].Type + '_' + data.Buttons[a].Name + '_' + id + '" onclick="switchState(\'' + data.Buttons[a].Name + '\',' + id + ', \'' + data.Buttons[a].Type + '\');"/>');
                    buttons.Buttons.push({'Type': data.Buttons[a].Type, 'Name': data.Buttons[a].Name})
                }

            }
        }


    } else {
        for (var b = 0; b < data.newButtons; b++) {
            if (b == 0) {
                if (typeof type != "undefined" && type == "concentration") {
                    $('#concentration' + id).html('<span style="margin-right:10px;float:left;">Concentration:</span><div id="' + data.Buttons[b].Type + 'Inputs' + id + '"></div>');
                }
                $('#' + data.Buttons[0].Type + 'Inputs' + id).html('');
            }
            $('#' + data.Buttons[b].Type + 'Inputs' + id).append('<input type="button" value="' + data.Buttons[b].Name + '" name="' + data.Buttons[b].Name + '" class= "button2" state="unpressed" style ="background-color: ' + color[id % 10] + ';" id="' + data.Buttons[b].Type + '_' + data.Buttons[b].Name + '_' + id + '" onclick="switchState(\'' + data.Buttons[b].Name + '\',' + id + ', \'' + data.Buttons[b].Type + '\');"/>');
            buttons.Buttons.push({'Type': data.Buttons[b].Type, 'Name': data.Buttons[b].Name});
        }
        //console.log(buttons);
    }

}

function switchState(name, id, type) {
    var currentState = null;
    var id2 = '#' + type + '_' + name + '_' + id;
    id2 = id2.replace('.', '\\.');
    if ($(id2).attr("state") == "unpressed") {
        $(id2).attr("state", "pressed");
        $(id2).css("background-color", backgroundColors[id]);
        currentState = "pressed";
    } else if ($(id2).attr("state") == "disabled") {
        $(id2).attr("state", "disabled");
        $(id2).css("background-color", "gray");
    }
    else {
        $(id2).attr("state", "unpressed");
        $(id2).css("background-color", color[id]);
        currentState = "unpressed";
    }
    checkState(name, currentState, id, type);
}

function checkState(name, state, id, type) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'parameters.php?value=' + name + '&id=' + id + '&state=' + state + '&type=' + type + '&params=Partial');
    xhr.onreadystatechange = function () {
        if (xhr.status == 200 && xhr.readyState == 4) {
            var buttonsA = JSON.parse(xhr.responseText);
            var currentName = null;
            var currentType = null;
            var current = null;
            if (buttonsA.displayStatus == 0) {
                $("#timePoint" + id).css("display", "none");
                $("#concentration" + id).css("display", "none");
                $("#experimentalist" + id).css("display", "none");
                $("#replicate" + id).css("display", "none");
            } else if (buttonsA.displayStatus == 1) {
                $("#timePoint" + id).css("display", "block");
                $("#concentration" + id).css("display", "none");
                $("#experimentalist" + id).css("display", "none");
                $("#replicate" + id).css("display", "none");
                makeButtons(id, buttonsA);
            } else if (buttonsA.displayStatus == 2) {
                $("#timePoint" + id).css("display", "block");
                $("#concentration" + id).css("display", "block");
                $("#experimentalist" + id).css("display", "none");
                $("#replicate" + id).css("display", "none");
                makeButtons(id, buttonsA, 'concentration');
            } else if (buttonsA.displayStatus == 3) {
                $("#timePoint" + id).css("display", "block");
                $("#concentration" + id).css("display", "block");
                $("#experimentalist" + id).css("display", "block");
                $("#replicate" + id).css("display", "none");
                makeButtons(id, buttonsA);
            } else if (buttonsA.displayStatus == 4) {
                $("#timePoint" + id).css("display", "block");
                $("#concentration" + id).css("display", "block");
                $("#experimentalist" + id).css("display", "block");
                $("#replicate" + id).css("display", "block");
                makeButtons(id, buttonsA);
            } else if (buttonsA.displayStatus == 5) {
                $('#status' + id).html('<span style="color:green;">Completed</span>');
            }
            for (var a = 0; a < buttonsA.ButtonsResetCount; a++) {
                $('#' + buttonsA.ButtonsReset[a] + id + ' .button2').attr('state', 'unpressed').css('background-color', color[id]);
            }
            for (var c = 0; c < buttons.Buttons.length; c++) {
                currentName = buttons.Buttons[c].Name;
                currentType = buttons.Buttons[c].Type;
                current = '#' + currentType + '_' + currentName + '_' + id;
                current = current.replace('.', '\\.');
                //console.log(buttonsA.ButtonsT[buttonsA.ButtonsA.indexOf(currentName)]);
                if (buttonsA.ButtonsA.indexOf(currentName) == -1 && buttonsA.ButtonsT[buttonsA.ButtonsA.indexOf(currentName)] != currentType) {
                    $(current).attr("state", "disabled");
                    $(current).css("background-color", "gray");
                } else if ($(current).attr("state") == "disabled") {
                    $(current).attr("state", "unpressed");
                    $(current).css("background-color", color[id]);
                }
            }
        }
    }
    xhr.send(null);
}

function resetData(id, type) {
    if (type == 'Delete') {
        var index = tabs.indexOf(id);
        $('#data' + id).remove();
        $('#aData' + id).remove();

        if (index == 0) {
            changeTabColor(tabs[index + 1], color[tabs[(index + 1)] % 10]);
        } else {
            changeTabColor(tabs[index - 1], color[tabs[(index - 1)] % 10]);
        }
        if (index > -1) {
            tabs.splice(index, 1);
        }
        if (tabs.length == 9) {
            $("#liAdd").append('<a href="#add" id="aAdd" onclick="addDiv()"><img src="add.png" height="25px" style="margin-bottom: -2px; margin-left:-10px; margin-right:-10px"></a>')
        }
    } else {
        $("#dataType" + id).empty();
        $("#cellType" + id).empty();
        $("#stimulation" + id).empty();
        $("#timePoint" + id).css('display', 'none');
        $("#concentration" + id).css('display', 'none');
        $("#experimentalist" + id).css('display', 'none');
        $("#replicate" + id).css('display', 'none');
        $("#dataType" + id).append('<span style="margin-right:10px">Data Type:</span>');
        $("#cellType" + id).append('<span style="margin-right:10px">Cell Type:</span>');
        $("#stimulation" + id).append('<span style="margin-right:10px">Stimulation:</span>');
        makeButtons(id);
    }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'parameters.php?params=Reset&id=' + id);
    xhr.send(null);
}

function goSubmit() {
    var currentAttrValue = document.getElementById('aSubmit').getAttribute('href');
    jQuery('.tabs ' + currentAttrValue).show().siblings().hide();
    jQuery('#submitPage').parent('li').addClass('active').siblings().removeClass('active');
    $(".tab-content").css('border', '2px solid #ADD8E6');
    $("#details").empty();
    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxDirectory + 'submit.php');
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var details = JSON.parse(xhr.responseText);
            var showSubmit = [];
            for (var m = 0; m < tabs.length; m++) {
                if (details.Data[tabs[m]].Status == "Not Completed") {
                    $("#details").append("<div><span> Data " + tabs[m] + ": Not Completed </span></div><br/>");
                    showSubmit[m] = 0;
                } else {
                    $("#details").append("<div style='height: 35px'><span style='margin-right:10px;'> Data " + tabs[m] + ": </span><input type='button' class='button' value='" + details.Data[tabs[m]].dataType + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='" + details.Data[tabs[m]].cellType + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='" + details.Data[tabs[m]].stimulation.toString().replace(',', ' + ') + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='" + details.Data[tabs[m]].concentration.toString().replace(',', 'mL + ') + " mL' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='" + details.Data[tabs[m]].timePoint + " Minutes' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='done by: " + details.Data[tabs[m]].experimentalist + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='replicate(s): " + details.Data[tabs[m]].replicate + "' style='margin-right:15px; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/><input type='button' class='button' value='Protocol' onclick='showProtocol(" + tabs[m] + ");' style='float:right; border: 3px solid " + color[tabs[m]] + "; color: " + color[tabs[m]] + ";'/></div>");
                    showSubmit[m] = 1;
                }
            }
            if (showSubmit.indexOf(0) == -1) {
                $("#details").append("<div style='height: 35px;'><input type='button' class='button' value='Submit' style='border: 3px solid #3498db; color: #3498db; float:right;' onclick='submitData();'/></div>");
            }

        }
    };
    xhr.send('GetInfo=1&tabs=' + tabs.toString());

}

function showProtocol(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', ajaxDirectory + 'getExpInfo.php?type=protocol&id='+id);
    xhr.onreadystatechange = function() {
        if(xhr.status == 200 && xhr.readyState == 4) {
            var response = JSON.parse(xhr.responseText);
            if(response.Success == 0 || response.Success == 1) {
                $( "#dialog" ).html('<p>'+response.Message+'</p>');
                $( "#dialog" ).dialog({
                    title: "Protocol"
                });
            }
        }
    };
    xhr.send(null);
}


function addDiv() {
    tabs.push(i);
    $("#liSubmit").before('<li><a href="#data' + i + '" id="aData' + i + '" onclick="changeTabColor(' + i + ',' + colors[i % 10] + ');" style=" border: 5px solid ' + color[i % 10] + '; background-color: ' + color[i % 10] + ';">Data ' + i + '</a></li>');
    $("#add").before('<div id="data' + i + '" class="tab"><form name="data' + i + '" id="fData' + i + '"><div id="status' + i + '" class="status"></div><div id="dataType' + i + '" class="choice"><span style="margin-right:10px">Data Type:</span></div><div id="cellType' + i + '" class="choice"><span style="margin-right:10px">Cell Type:</span></div><div id="stimulation' + i + '" class="choice"><span style="margin-right:10px">Stimulation:</span></div><div id="timePoint' + i + '" class="choice timePoint"><span style="margin-right:10px;float:left;">Time Point (Minutes):</span><div id="timePointInputs' + i + '"></div></div><div id="concentration' + i + '" class="choice concentration"><span style="margin-right:10px;float:left;">Concentration:</span><div id="concentrationInputs' + i + '"></div></div><div id="experimentalist' + i + '" class="choice experimentalist"><span style="margin-right:10px;float:left;">Experimentalist:</span><div id="experimentalistInputs' + i + '"></div></div><div id="replicate' + i + '" class="replicate"><span style="margin-right:10px;float:left;">Replicate:</span><div id="replicateInputs' + i + '"></div></div><input type="button" class="button" value="Reset Values" style="border: 3px solid ' + color[i % 10] + '; color: ' + color[i % 10] + ';" onclick="resetData(' + i + ', \'Reset\');" /><input type="button" class="button" value="Delete Data" style="border: 3px solid ' + color[i % 10] + '; color: ' + color[i % 10] + '; margin-left:5px" onclick="resetData(' + i + ', \'Delete\');" /></form></div>');
    makeButtons(i);
    changeTabColor(i, color[i]);
    if (tabs.length == 10) {
        $("#aAdd").remove();
    }
    i++;

}

function changeTabColor(id, color) {
    var currentAttrValue = document.getElementById('aData' + id).getAttribute('href');
    // Show/Hide Tabs
    $('.tabs ' + currentAttrValue).show().siblings().hide();
    // Change/remove current tab to active
    $('#data' + id).parent('li').addClass('active').siblings().removeClass('active');
    $(".tab-content").css('border', '2px solid ' + color);
}

function changeTabColor2(id, color) {
    var currentAttrValue = document.getElementById('aResults' + id).getAttribute('href');
    // Show/Hide Tabs
    $('.tabs2 ' + currentAttrValue).show().siblings().hide();
    // Change/remove current tab to active
    $('#results' + id).parent('li').addClass('active').siblings().removeClass('active');
    $(".tab-content2").css('border', '2px solid ' + color);
}


function submitData() {

    possibleCombinations = 0;
    possibleCombinationsColors = 0;
    $("#results1").empty();
    $("#results2").empty();
    $("#results3").empty();
    $("#results4").empty();
    changeTabColor2(1, '#686868');
    $(".tab-links2").empty();
    $(".tab-links2").append('<li class="active2"><a id="aResults1" href="#results1" onclick="changeTabColor2(1, \'#686868\' );">Scatter Plots</a></li>');
    $("#results1").height(0);
    $("#results1").append('<table id="dc-data-table" style="float:none; display:none;"><thead><tr class="header" id="tableHeader"><th>Gene</th><th>Alternate ID</th></tr></thead></table>');
    for (var i = 0; i < tabs.length; i++) {
        eval("$('#tableHeader').append('<th>Data" + tabs[i] + "</th>')");
    }
    makeGraphs();
    // changing the div's height
    $("#results1").height(Math.floor((possibleCombinations + 1) / 2) * 517 + 20);
    $(".active2").css("display", "block");
    $("#results1").css("display", "block");
    $(".tab-content2").css("display", "block");
    $("#results1").append('<div style="clear:both;"><input type="radio" name="interU" value="intersection" class="interU"><span style="margin-right:807px; margin-left:5px">Select Intersection</span></input><input type="radio" class="interU" name="interU" value="union"><span style="margin-right:5px; margin-left:5px">Select Union</span></input></div>');
    $('#aResults1').css('border', '5px solid #686868');
    $('#aResults1').css('background-color', '#686868');
    $('input').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });


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

        $(".tab-links2").empty();
        $(".tab-links2").append('<li class="active2"><a id="aResults1" href="#results1" onclick="changeTabColor2(1, \'#686868\' );">Scatter Plots</a></li><li><a href="#results2" id="aResults2" onclick="changeTabColor2(2, \'#808080\');">Chord Diagram And DataTable</a></li><li><a href="#results3" id="aResults3" onclick="changeTabColor2(3, \'#A8A8A8\');">Network Charts</a></li><li><a href="#results4" id="aResults4" onclick="changeTabColor2(4, \'#C0C0C0\')">Pathways</a></li>');
        $("#results2").empty();
        $("#results3").empty();
        $("#results4").empty();
        $("#results4").append('<select class="js-data-example-ajax" name="pathwayOptions[]" multiple="multiple" id="pathwayOp" style="width: 50%;"></select><input type="submit" value="Submit" class="button" style="float:right;border: 3px solid #3498db; color: #3498db;" onclick="pathwayCreate();"/>');
        $("#results4").append("<div id='results4iframe'></div>");
        //
        var xhr3 = new XMLHttpRequest();
        xhr3.open('GET', ajaxDirectory + 'information.php?type=pathwayFetch');
        xhr3.onreadystatechange = function () {
            if (xhr3.readyState == 4 && xhr3.status == 200) {
                var response = JSON.parse(xhr3.responseText);
                $(".js-data-example-ajax").select2({
                    data: response.Pathway
                });
            }
        }
        xhr3.send(null);
        $("#results3").append('<div id="results3Message"></div>');
        $("#results3").append('<div id="networkChoice"><p>Please select one of the options, circle is selected by default </p><input type="radio" name="networkChoice" value="Random" class="networkChoice" onclick="makeNetwork(\'random\');"><span style="margin-right:15px; margin-left:5px">Random</span></input><input type="radio" class="networkChoice" onclick="makeNetwork(\'grid\');"name="networkChoice" value="Grid"><span style="margin-right:15px; margin-left:5px">Grid</span></input><input type="radio" name="networkChoice" value="Circle" class="networkChoice"><span style="margin-right:15px; margin-left:5px">Circle</span></input><input type="radio" class="networkChoice" name="networkChoice" value="Concentric"><span style="margin-right:15px; margin-left:5px">Concentric</span></input></div>');
        $('#networkChoice').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });
        $(".networkChoice").on("ifClicked", function(event){
            console.log(event.currentTarget.value);
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

        
        $("#results3").append("<div id='results3iframe'></div>");
        $('#aResults1').css('border', '5px solid #686868');
        $('#aResults1').css('background-color', '#686868');
        $('#aResults2').css('border', '5px solid #808080');
        $('#aResults2').css('background-color', '#808080');
        $('#aResults3').css('border', '5px solid #A8A8A8');
        $('#aResults3').css('background-color', '#A8A8A8');
        $('#aResults4').css('border', '5px solid #C0C0C0');
        $('#aResults4').css('background-color', '#C0C0C0');
        finalRanges2 = [];
        for (var r = 0; r < tabs.length; r++) {
            finalRanges2.push([-(Number.MAX_VALUE), Number.MAX_VALUE]);
        }
        console.log(finalRanges2);
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
        console.log(finalRanges2);

        if ($(this).val() == "intersection") {
            $("#results2").height(551);
            filterType = 'intersection';
        }
        else {
            filterType = 'union';
            $("#results2").append("<div id='results2chord'><div class='first' id='original'></div><div id='filtered'></div></div>");
            var xhr = new XMLHttpRequest();
            xhr.open('GET', ajaxDirectory + 'data.php?type=chord&id=1&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType);
            xhr.onreadystatechange = (function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    makeChordDiagram(1, tabs, response);//original data

                }
            });
            xhr.send(null);

            var xhr2 = new XMLHttpRequest();
            xhr2.open('GET', ajaxDirectory + 'data.php?type=chord&id=2&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType);
            xhr2.onreadystatechange = (function () {
                if (xhr2.readyState == 4 && xhr2.status == 200) {
                    var response = JSON.parse(xhr2.responseText);
                    makeChordDiagram(2, tabs, response);//original data

                }
            });
            xhr2.send(null);

            $("#results2").height(1057);
        }
        

        $("#results2").append('<table id="table_id" class="display"></table><div style="width:1127px;display:none;" id="tabledownload9"><a href="csv/csv.php?csv=GESS2.csv&amp;download_file=true"><input type="button" class="button" value="Download GESS Table" style="border: 3px solid #3498db; color: #3498db; margin-left:5px;; float:left;" ></input></a><input type="button" class="button" value="Run Analysis" style="border: 3px solid #3498db; color: #3498db; margin-left:5px; float:right;" ></input></div>');
        displayGraph('Table.Values', '#table_id', '', 'dynamic', '', '', 'tabledownload9', '', '', '');
    });


}

function makeNetwork(networkChoice){

    $("#results3iframe").empty();
    $("#results3iframe").append('<iframe id="iframe" height="700px" width="1100px"><p> error </p></iframe>');
    $("#iframe").attr('src', 'Assets/Cytoscape/network.php?tabs=' + tabs + '&ranges=' + finalRanges2 + '&filter=' + filterType + '&networkChoice=' + networkChoice);
}

function pathwayCreate() {
    var pathwayOptions2 = $("#pathwayOp").val();
    console.log(pathwayOptions2);
    $("#results4iframe").empty();
    for (var m = 0; m < pathwayOptions2.length; m++) {
        $("#results4iframe").append('<iframe id="iframe' + (m + 2) + '" height="800px" width="1100px"><p> error </p></iframe>');
        $("#iframe" + (m + 2)).attr('src', 'Assets/Cytoscape/pathway.php?tabs=' + tabs + '&ranges=' + finalRanges2 + '&filter=' + filterType + '&pathwayOptions=' + pathwayOptions2[m]);
    }


    /*if (pathwayOptions2 != ''){
     $.ajax({
     url: 'ajax/data.php?type=pathwayDisplay&tabs='+tabs+'&ranges='+finalRanges2+'&filter='+filterType+'&pathwayOptions='+pathwayOptions2,
     type: "GET",
     data: ({
     pathwayOptions2: pathwayOptions2
     }),
     success: function(data)
     {
     alert("yay");
     // You might wanna display a message telling the user that the form was successfully filled out.
     }
     });
     }*/

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
            $("#results1").append('<div class="eachGraph" style="float:' + ((index % 2 == 1) ? "left" : "right") + ';"><div><input type="radio" name="type' + index + '" value="brush" class="brush' + index + ' filterMouse" index="' + index + '" checked="checked" style="position:fixed;"><span style="margin-right:15px; margin-left:5px">Filter</span></input><input type="radio" name="type' + index + '" index="' + index + '" value="mouseover" class="filterMouse" style="position:fixed;"><span style="margin-right:5px; margin-left:5px">Mouseover</span></input><aside id="tooltip' + index + '" class="hidden' + index + ' tooltip"><span>Gene Name: </span><span id="name' + index + '" style="color:black;"></span><br/><span>Data ' + tabs[a] + ': </span><span id="inactive' + index + '" style="color:black;"></span><br/><span>Data ' + tabs[h] + ': </span><span id="medSal' + index + '" style="color:black;"></span></aside></div><div id="chart-hist-data' + index + tabs[a] + '" class="firstHist" onclick="brush(' + index + ');"><div class="clearfix"></div></div><div id="chart-hist-data' + index + tabs[h] + '" class="secondHist" onclick="brush(' + index + ');"><div class="clearfix"></div></div><div id="scatter' + index + '" onclick="filterdisable();" class="scatter"><div class="clearfix"></div></div></div>');//
        }
    }
    //console.log(possibleCombinations);
    //d3.csv("DataFiles/Bin36.csv", function(data) {
    d3.csv(ajaxDirectory + "data.php?type=retrieveAllIntersect&filter=intersection", function (data) {
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
                eval("scatter" + possibleCombinations2 + ".width(420).height(382).x(d3.scale.linear().domain(hist" + tabs[a] + "DimExtent)).y(d3.scale.linear().domain(hist" + tabs[h] + "DimExtent)).yAxisLabel('Data" + tabs[h] + "').xAxisLabel('Data" + tabs[a] + "').symbolSize(4).clipPadding(10).dimension(dim" + possibleCombinations2 + ").group(group" + possibleCombinations2 + ");scatter" + possibleCombinations2 + ".on('renderlet',function(chart) {dc.events.trigger(function() {ranges2[" + (possibleCombinations2 - 1) + "] = scatter" + possibleCombinations2 + ".filters();}); console.log('ranges='+ranges2); setAxisColor(" + possibleCombinations2 + ", " + tabs[a] + ", " + tabs[h] + ");updatePaths(" + possibleCombinations2 + "," + tabs[a] + "," + tabs[h] + "); chart.selectAll('.symbol').on('click', function(d) { testOccurrence(d);});});");
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
    console.log("original" + ranges);


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
        console.log(d);
        console.log("k:" + k);
        var numberValue = Math.round(d.value);
        var digits = (numberValue.toString().length) - 2;

        var interval = Math.pow(10, digits);

        var remainder = (Math.floor(numberValue / (interval * 10)) * interval).toString().substr(1);
        console.log(remainder);
        console.log(interval);
        return d3.range(0, numberValue, interval).map(function (v, i) {
            console.log("v:" + v);
            console.log("i:" + i);
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
        "url": ajaxDirectory + 'data.php?type=dataTable&ranges=' + encodeURIComponent(finalRanges2) + '&filter=' + filterType,
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