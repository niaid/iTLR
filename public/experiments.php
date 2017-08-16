<?php
require '../app/includes.php';

/* Clean slate upon reload */
\iTLR\Session\Session::cleanSlate();

?>
<!DOCTYPE html>
<html>
<head>
    <title>iTLR Experiment Comparison</title>
    <!-- CSS IMPORTS -->
    <link href="Assets/CSS/dc.css" rel="stylesheet"/>
    <link href="Assets/CSS/jquery.dataTables.min.css" rel="stylesheet"/>
    <link href="Assets/CSS/iCheck/blue.css" rel="stylesheet"/>
    <link href="Assets/CSS/tabs.css" rel="stylesheet"/>
    <link href="Assets/CSS/select2.min.css" rel="stylesheet"/>
    <link href="Assets/CSS/jquery-ui.css" rel="stylesheet"/>
    <meta name="description" content="Analyze genome-scale response of a small subset of selected experiments to elucidate the effect of stimulations, molecular fingerprints, cell types, and response dynamics."/>
</head>
<body>
<header>
    <span class="title">iTLR Experiment Comparison</span>
    <!--<span class="links"><a href="/"><img src="Assets/Images/home.png" alt="Home" height="32px"/></a></span>
    <span class="links"><a href="/"><img src="Assets/Images/help.png" alt="Home" height="33px"/></a></span>-->
</header>
<div class="tabs ui-state-error ui-corner-all no-display">
    <p>
        <span class="ui-icon ui-icon-alert" style="float: left;"></span>
        <span id="siteWideMessage"></span>
    </p>
</div>
<div class="tabs">
    <!-- Tabs -->
    <ul class="tab-links">
        <li class="active"><a id="aData1" href="#data1" onclick="changeTabColor(1, '#ADD3ED');">Data 1</a></li>
        <li id="liSubmit"><a href="#submitPage" id="aSubmit" onclick="goSubmit()">Submit</a></li>
        <li id="liFeedback"><a href="#feedback" id="aFeedback" onclick="feedbackTab();">Feedback</a></li>
        <li class="links"><a href="/"><img src="Assets/Images/home.png" alt="Home" height="23px"/></a></li>
        <li class="links"><a href="/"><img src="Assets/Images/help.png" alt="Home" height="23px"/></a></li>
        <li id="liAdd"><a href="#add" id="aAdd" onclick="addDiv()"><img src="Assets/Images/add.png" alt="Add"></a>
        </li>
    </ul>
    <div class="tab-content">
        <!-- Data 1 -->
        <div id="data1" class="tab active">
            <form name="data1" id="fData1">
                <!-- Message -->
                <div class="message ui-state-error ui-corner-all no-display">
                    <span class="ui-icon-alert ui-icon" style="float: left;"></span>
                    <span id="message1"></span>
                </div>
                <div class="no-display" style="text-align:center;">
                    <span id="messageInfo1"></span>
                </div>
                <!-- DataType -->
                <div class="choice">
                    <span class="basicType">Data Type:</span>
                    <div id="dataType1"></div>
                </div>
                <!-- CellType -->
                <div class="choice">
                    <span class="basicType">Cell Type:</span>
                    <div id="cellType1"></div>
                </div>
                <!-- Stimulation -->
                <div class="choice">
                    <span class="basicType">Stimulation:</span>
                    <div id="stimulation1"></div>
                </div>
                <!-- Strain -->
                <div class="choice strain">
                    <span class="complexType">Strain:</span>
                    <div id="strain1"></div>
                </div>
                <!-- TimePoint -->
                <div class="choice timePoint">
                    <span class="complexType">Time Point (Minutes):</span>
                    <div id="timePoint1"></div>
                </div>
                <!-- Concentration -->
                <div class="choice concentration">
                    <div id="concentration1">
                    </div>
                </div>
                <!-- Experimentalist -->
                <div class="choice experimentalist">
                    <span class="complexType">Experimentalist:</span>
                    <div id="experimentalist1"></div>
                </div>
                <!-- Replicate -->
                <div class="replicate">
                    <span class="complexType">Replicate:</span>
                    <div id="replicate1"></div>
                </div>
                <!-- Reset Button -->
                <input type="button" class="button" value="Reset Values" onclick="parametersManagement(1, 'Reset');"/>
                <!-- Delete Button -->
                <input type="button" class="button" value="Delete Data" onclick="parametersManagement(1, 'Delete');"/>
            </form>
        </div>
        <!-- Add -->
        <div id="add" class="tab"></div>
        <!-- Feedback -->
        <div id="feedback" class="tab" style="width: 100%;">
            <div id="feedbackMessage"></div>
            <form id="feedbackForm">
                <div style="float: left;">
                    <label for="pageSelection">Select a Page: </label>
                    <select name="pageSelection" id="pageSelection">
                        <option>Data Comparison</option>
                        <option>Gene Page</option>
                    </select>
                </div>
                <div style="float:right; padding-top:2px;">
                    <input type="radio" name="type" id="issue" value="Issue" checked/>
                    <label for="issue">Issue</label><span style="padding-left: 10px;"></span>
                    <input type="radio" name="type" id="comments" value="Comment"/>
                    <label for="comments">Comments</label>
                    <input type="radio" name="type" id="comments" value="Suggested Feature"/>
                    <label for="feature">Suggested Feature</label>
                </div><br/><br/>
                <div>
                    <label for="subject">Subject: </label><input type="text" name="subject" id=subject" size="31"/>
                </div><br/>
                <div>
                    <label for="body">Message: </label><br/>
                    <textarea name="body" id="body" style="width: 100%; height:100px;"></textarea>
                </div><br/>
                <input type="button" class="button" value="Send Feedback" onclick="feedbackSubmit()"/>
            </form>
        </div>
        <!-- Submit -->
        <div id="submitPage" class="tab">
            <div id="details"></div>
            <div id="submitInfo" style="float:right"></div>
        </div>

    </div>
</div>
<!-- Results -->
<div class="tabs2">
    <ul class="tab-links2">
        <li class="active2 no-display">
            <a id="aResults1" href="#results1" onclick="changeTabColor2(1, '#3498db');">Scatter Plots</a>
        </li>
    </ul>
    <div class="tab-content2">
        <div id="results1" style="display:none" class="tab2">
        </div>
        <div id="results2" class="tab2"></div>
        <div id="results3" class="tab2">

            <div id="results3Message">

            </div>
        </div>
        <div id="results4" class="tab2" style="display:none">
            <div id="svgGradient"></div>
        </div>

    </div>
</div>

<!-- jQuery UI Dialog -->
<div id="dialog"></div>
<div class="center" style="display:none" id="loadingGif">
    <img src="Assets/Images/loading.gif" alt="Loading GIF" style="height:200px;float:right;padding-right:5px;"/>
</div>


<script src="Assets/JS/jquery.min.js"></script>
<script src="Assets/JS/d3_2.js"></script>
<script src="Assets/JS/jquery.dataTables.js"></script>
<script src="Assets/JS/cross_filter.js"></script>
<script src="Assets/JS/dc.js"></script> 
<script src="Assets/JS/icheck.js"></script>
<script src="Assets/JS/color-0.4.1.min.js"></script>
<script src="Assets/JS/cytoscape.min.js"></script>
<script src="Assets/JS/tabs2.js"></script>
<script src="Assets/JS/select2.min.js"></script>
<script src="Assets/JS/jquery-ui.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-97388395-1', 'auto');
  ga('send', 'pageview');
</script>
</body>
</html>

