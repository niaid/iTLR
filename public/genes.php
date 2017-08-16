<?php
require '../app/includes.php';

\iTLR\Session\Session::cleanSlate();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gene</title>
    <link href="Assets/CSS/iCheck/blue.css" rel="stylesheet">
    <link href="Assets/CSS/tabs.css" rel="stylesheet">
    <link rel="stylesheet" href="Assets/CSS/style.min.css" />
    <link href="Assets/CSS/select2.min.css" rel="stylesheet" />
    <meta charset="utf-8">
    <style>
        /* disable text selection */
        .Select2 {
            width:200px;
        }

        svg *::selection {
            background : transparent;
        }

        svg *::-moz-selection {
            background:transparent;
        }

        svg *::-webkit-selection {
            background:transparent;
        }
        rect.selection {
            stroke          : #333;
            stroke-dasharray: 4px;
            stroke-opacity  : 0.5;
            fill            : transparent;
        }

        rect.cell-border {
            stroke: #eee;
            stroke-width:0.3px;
        }

        rect.cell-selected {
            stroke: rgb(51,102,153);
            stroke-width:0.5px;
        }

        rect.cell-hover {
            stroke: #F00;
            stroke-width:0.3px;
        }

        text.mono {
            font-size: 9pt;
            font-family: Consolas, courier;
            fill: #aaa;
        }

        text.text-selected {
            fill: #000;
        }

        text.text-highlight {
            fill: #c00;
        }
        text.text-hover {
            fill: #00C;
        }
        #tooltip {
            position: absolute;
            width: 200px;
            height: auto;
            padding: 10px;
            background-color: white;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
            -webkit-box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.4);
            -moz-box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.4);
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.4);
            pointer-events: none;
        }

        #tooltip.hidden {
            display: none;
        }

        #tooltip p {
            margin: 0;
            font-family: sans-serif;
            font-size: 12px;
            line-height: 20px;
        }
        .legend{
            margin-top: -5px;
            margin-left: 10px;
        }
        table, th, td, caption {
            border: 1px solid black;
            border-collapse: collapse;
            border-spacing: 0.5rem;
            padding: 0.5rem;
        }
        #experimentNameLength {
            font-size: 14px;
        }
        .center {
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: -50px;
            margin-left: -50px;
            width: 100px;
            height: 100px;
        }
    </style>

</head>
<body>
    <header>
        <h1>iTLR Gene Comparison</h1>
    </header>
    <div class="text">
        <div id="information"></div>
        <form name="geneInput" id="geneInput" method="POST" onsubmit="return false;">
            <!-- Input Div -->
            <div>
                <label for="geneText">Type Gene Name(s):</label><textarea name="geneText" id="geneText" placeholder="Tlr1, Tlr2, Tlr3" style="height:50px; width: 1120px; margin-bottom:-5px; margin-right:30px"></textarea>
                <span>OR</span>
                <input type="button" value="Upload Gene File" class="button" style="margin-left:30px;border: 3px solid #3498db; color:#3498db" onclick="HandleFileButtonClick()"/>
                <input type="file" name="geneFile" id="file" style="display:none;" />
                <input type="button" value="Help" class="button" style="margin-left:30px;border: 3px solid #3498db; color:#3498db" onclick="uploadDescription();"/>
            </div>
            <br/>
            <div>
                <select name="cellType[]" title="cellType" id="cellType" class="Select2" data-placeholder="Cell Type:" multiple>
                    <option></option>
                    <option>All</option>
                </select>
                <select name="stimulation[]" title="stimulation" id="stimulation" class="Select2" data-placeholder="Treatment:" multiple>
                    <option></option>
                    <option>All</option>
                </select>
                <select name="dataType[]" title="dataType" id="dataType" class="Select2" data-placeholder="Data Type:" multiple>
                    <option></option>
                    <option>All</option>
                </select>
                <select name="groupBy" title="groupBy" id="groupBy" class="Select2" data-placeholder="Order By">
                    <option></option>
                    <option>Cell Type</option>
                    <option>Stimulation</option>
                    <option>Data Type</option>
                    <option>Time Point</option>
                    <option>Experimentalist</option>
                    <option>Replicate</option>
                    <option>Strain</option>
                </select>
                <input type="submit" id="submitGene" value="Submit" class="button" style="border: 3px solid #3498db; color:#3498db; float:right;" onclick="geneSubmission();"/>

            </div>
            <br/>
            <div id="help" style="display: none; ">
                <table style="width:1010px;margin-left:auto; margin-right:auto;">
                    <tr><th>How should the upload file be structured?</th><th>How should the gene names in the text-box be structured?</th></tr>
                    <tr><td>Mfap3l (No Header Information please)</td><td>Mfap3l,Tcerg1l,Strap,(and so on...),Lman1</td></td></tr>
                    <tr><td>Tcerg1l</td></tr>
                    <tr><td>Strap</td></tr>
                    <tr><td>And so on... (one gene name per line)</td></tr>
                    <tr><td>Lman1</td></tr>
                </table>
            </div>

        </form>

        <div id="chart" style="display:none;">
            <div style="margin-top:15px;">
                <div id="svgGradient"></div>
            </div>
            <div id="svgHeatMap"></div>
        </div>
    </div>
    <div id="testingMaterial" style="display: none;">
        <span id="experimentNameLength"></span>
    </div>
    <div class="center" style="display:none" id="loadingGif">
        <img src="Assets/Images/loading.gif" alt="Loading GIF" style="height:200px;float:right;padding-right:5px;"/>
    </div>

    <div id="tooltip" class="hidden">
    <p><span id="value"></p>
</div>
<script src="Assets/JS/jquery.min.js"></script>
<script src="Assets/JS/d3.js"></script>
    <script src="Assets/JS/select2.min.js"></script>


<!--<div id="chart" style='overflow:auto; width:960px; height:480px;'></div>-->

<script type="text/javascript">
    function HandleFileButtonClick(){
        document.getElementById("file").click();
    }


    $( document ).ready(function() {

        //AJAX request to retrieve select2 data
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'Assets/AJAX/parameters.php?params=All&id=Heatmap');
        xhr.onreadystatechange = (function() {
            if(xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                response = response.Buttons.New;
                initializeSelect2('dataType', response);
                initializeSelect2('cellType', response);
                initializeSelect2('stimulation', response);
                initializeSelect2('groupBy', response);
            }


        });
        xhr.send(null);

    });

    /**
     * Used to initialize select elements with the data passed as a parameter
     * @param name
     * @param data
     */
    function initializeSelect2(name, data) {
        var filteredData = [];
        for(var i = 0; i < data.count; i++) {
            if(data[i].Type == name) {
                var value = data[i].Value.replace(/_/g, ' ');
                value = value.split(':').join('/');

                filteredData.push({'id': data[i].Value, 'text': value});
            }
        }
        $('.Select2[id='+name+']').select2({
            width: 'resolve',
            data: filteredData
        });
    }


    //rgb(164,164,164)
    /**
     *  Converts the #help from display none to block or vice versa.
     *  This method is called onclick from the Help button.
     */
    function uploadDescription() {
        $('#help').css('display', (document.getElementById('help').style.display == 'none') ? 'block' : 'none');
    }

    function submitGenes(){
        $("#chart").css("display","none;");
        $('#experiments').css("display","block");
    }

    var request = false;
    /**
     * @return true or false depending on:
     * 1. The Gene Name field is not empty.
     * 2. There is a file ready to be uploaded.
     */
    function geneSubmission() {
        if(request == true) { return; }
        request = false; //TODO: TRUE
        $('#loadingGif').css('display', 'block');

        var form = document.getElementById('geneInput');
        var validation = false;

        //reset fields
        document.getElementById('svgHeatMap').innerHTML = '';
        document.getElementById('svgGradient').innerHTML = '';
        $("#chart").css("display","none");

        //if the gene name field is not empty
        if(form.geneText.value.trim().replace(' ', '') != '')
            validation = true;
        //if there is a file selected
        if(form.geneFile.files[0])
            validation = true;

        document.getElementById('information').innerHTML = '';
        //Tell user that they must either upload a file or type in genes (comma separated)
        document.getElementById('information').innerHTML = (validation == false) ? '<span style="color:red">Please type a gene name into the text-box or upload a file</span>' : '';

        //if passed validation continue to the next step - ajax
        if(validation == true) {
            document.getElementById('help').style.display = 'none';
            submitGenesExp();
        } else {
            $('#loadingGif').css('display', 'none');
        }
    }


    function submitGenesExp(){
        var form = document.getElementById('geneInput');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'Assets/AJAX/heatmap.php');
        xhr.onreadystatechange = (function() {
            if(xhr.status == 200 && xhr.readyState == 4) {
                var response = JSON.parse(xhr.responseText);

                if(response.exp.length == 0) {
                    $('#information').html('<span style="color:red;">No experiments were found with the given genes and filters</span>');
                } else {
                    $('#testingMaterial').css('display', 'block');
                    var maximum = 300;
                    for(var i = 0; i < response.exp.length; i++) {
                        if($('#experimentNameLength').html(response.exp[i]).width() > maximum) {
                            maximum = $('#experimentNameLength').html(response.exp[i]).width();
                        }
                    }
                    $('#testingMaterial').css('display', 'none');


                    createHeatMap(response.exp, response.genes, maximum + 10);
                    $("#chart").css("display","block");
                }
                $('#loadingGif').css('display', 'none');
                request = false;
            }
        });
        //xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        xhr.send(new FormData(form));
    }


    function createHeatMap(experiments, genes, left){
        // - margin.top - margin.bottom,
        //gridSize = Math.floor(width / 24),

        //hcrow = [49,11,30,4,18,6,12,20,19,33,32,26,44,35,38,3,23,41,22,10,2,15,16,36,8,25,29,7,27,34,48,31,45,43,14,9,39,1,37,47,42,21,40,5,28,46,50,17,24,13], // change to gene name or probe id
        //hccol = [6,5,41,12,42,21,58,56,14,16,43,15,17,46,47,48,54,49,37,38,25,22,7,8,2,45,9,20,24,44,23,19,13,40,11,1,39,53,10,52,3,26,27,60,50,51,59,18,31,32,30,4,55,28,29,57,36,34,33,35], // change to gene name or probe id
        //rowLabel = ['1759080_s_at','1759302_s_at','1759502_s_at','1759540_s_at','1759781_s_at','1759828_s_at','1759829_s_at','1759906_s_at','1760088_s_at','1760164_s_at','1760453_s_at','1760516_s_at','1760594_s_at','1760894_s_at','1760951_s_at','1761030_s_at','1761128_at','1761145_s_at','1761160_s_at','1761189_s_at','1761222_s_at','1761245_s_at','1761277_s_at','1761434_s_at','1761553_s_at','1761620_s_at','1761873_s_at','1761884_s_at','1761944_s_at','1762105_s_at','1762118_s_at','1762151_s_at','1762388_s_at','1762401_s_at','1762633_s_at','1762701_s_at','1762787_s_at','1762819_s_at','1762880_s_at','1762945_s_at','1762983_s_at','1763132_s_at','1763138_s_at','1763146_s_at','1763198_s_at','1763383_at','1763410_s_at','1763426_s_at','1763490_s_at','1763491_s_at'], // change to gene name or probe id
        //colLabel = ['con1027','con1028','con1029','con103','con1030','con1031','con1032','con1033','con1034','con1035','con1036','con1037','con1038','con1039','con1040','con1041','con108','con109','con110','con111','con112','con125','con126','con127','con128','con129','con130','con131','con132','con133','con134','con135','con136','con137','con138','con139','con14','con15','con150','con151','con152','con153','con16','con17','con174','con184','con185','con186','con187','con188','con189','con191','con192','con193','con194','con199','con2','con200','con201','con21']; // change to contrast name
        //hccol = [9,1,4,8,6,2,3,5,10,7],
        //create hcol array
        hccol = [];
        for(var i = 1; i <= genes.length; i++) {
            hccol.push(i);
        }
        hcrow = [];
        for(var i = 1; i <= experiments.length; i++) {
            hcrow.push(i);
        }
        var colLabel = genes,
            rowLabel = experiments;
            //colLabel = ["Mfap3l", "Tcerg1l", "1600021P15Rik", "Strap", "Lman1", "Slc6a13", "Pramef8","Rftn2","Elac2","Mafa"],
            //rowLabel = ["Microarray_Mouse_BMDM_PolyICLPS_1h", "Microarray_Mouse_BMDM_PolyICLPS_4h", "Microarray_Mouse_BMDM_PolyICLPS_8h", "Microarray_Mouse_BMDM_PolyICLPS_3h"];
        var margin = { top: 150, right: 10, bottom: 50, left: left };//min
        var col_number=hccol.length;
        var row_number=hcrow.length;

        if((1125-margin.left)/hccol.length>20 ){
            if((1125-margin.left)/hccol.length>30){
                var cellSize=30;
            }else{
                var cellSize= (1125-margin.left)/hccol.length;
            }

        }else{
            var cellSize = 20;
        }
        var width = cellSize*col_number, // - margin.left - margin.right,
            height = cellSize*row_number ;
        var legendElementWidth = cellSize*2.5,
            colorBuckets = 21,
            colors = ['#005824','#1A693B','#347B53','#4F8D6B','#699F83','#83B09B','#9EC2B3','#B8D4CB','#D2E6E3','#EDF8FB','#FFFFFF','#F1EEF6','#E6D3E1','#DBB9CD','#D19EB9','#C684A4','#BB6990','#B14F7C','#A63467','#9B1A53','#91003F'];
        d3.csv("Assets/AJAX/heatmap.php?data=csv",
            function(d) {
                return {
                    row:   +d.Row,
                    col:   +d.Column,
                    value: +d.Value
                };
            },
            function(error, data) {
                var colorScale = d3.scale.linear()
                    .range(["#ffffd9", "#41b6c4", "#081d58"])
                    .domain([d3.min(data, function(d) { return d.value;}),d3.median(data, function(d) { return d.value;}), d3.max(data, function(d) { return d.value;})]);
                //  .range(colors);

                var svg = d3.select("#svgHeatMap").append("svg")
                        .attr("width", width + margin.left + margin.right)
                        .attr("height", height + margin.top + margin.bottom)
                        .append("g")
                        .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
                    ;
                var rowSortOrder=false;
                var colSortOrder=false;
                var rowLabels = svg.append("g")
                        .selectAll(".rowLabelg")
                        .data(rowLabel)
                        .enter()
                        .append("text")
                        .text(function (d) { return d; })
                        .attr("x", 0)
                        .attr("y", function (d, i) { return hcrow.indexOf(i+1) * cellSize; })
                        .style("text-anchor", "end")
                        .attr("transform", "translate(-6," + cellSize / 1.5 + ")")
                        .attr("class", function (d,i) { return "rowLabel mono r"+i;} )
                        .on("mouseover", function(d) {d3.select(this).classed("text-hover",true);})
                        .on("mouseout" , function(d) {d3.select(this).classed("text-hover",false);})
                        .on("click", function (d, i) {
                            rowSortOrder = !rowSortOrder;
                            sortbylabel("r", i, rowSortOrder);
                            //d3.select("#order").property("selectedIndex", 4).node().focus();
                        })
                    ;

                var colLabels = svg.append("g")
                        .selectAll(".colLabelg")
                        .data(colLabel)
                        .enter()
                        .append("text")
                        .text(function (d) { return d; })
                        .attr("x", 0)
                        .attr("y", function (d, i) { return hccol.indexOf(i+1) * cellSize; })
                        .style("text-anchor", "left")
                        .attr("transform", "translate("+cellSize/2 + ",-6) rotate (-90)")
                        .attr("class",  function (d,i) { return "colLabel mono c"+i;} )
                        .on("mouseover", function(d) {d3.select(this).classed("text-hover",true);})
                        .on("mouseout" , function(d) {d3.select(this).classed("text-hover",false);})
                        .on("click", function (d, i) {
                            colSortOrder = !colSortOrder;
                            sortbylabel("c", i, colSortOrder);
                            //d3.select("#order").property("selectedIndex", 4).node().focus();
                        })
                    ;

                var heatMap = svg.append("g").attr("class","g3")
                        .selectAll(".cellg")
                        .data(data,function(d){return d.row+":"+d.col;})
                        .enter()
                        .append("rect")
                        .attr("x", function(d) { return hccol.indexOf(d.col) * cellSize; })
                        .attr("y", function(d) { return hcrow.indexOf(d.row) * cellSize; })
                        .attr("class", function(d){return "cell cell-border cr"+(d.row-1)+" cc"+(d.col-1);})
                        .attr("width", cellSize)
                        .attr("height", cellSize)
                        .style("fill", function(d) { return colorScale(d.value); })
                        /* .on("click", function(d) {
                         var rowtext=d3.select(".r"+(d.row-1));
                         if(rowtext.classed("text-selected")==false){
                         rowtext.classed("text-selected",true);
                         }else{
                         rowtext.classed("text-selected",false);
                         }
                         })*/
                        .on("mouseover", function(d){
                            //highlight text
                            d3.select(this).classed("cell-hover",true);
                            d3.selectAll(".rowLabel").classed("text-highlight",function(r,ri){ return ri==(d.row-1);});
                            d3.selectAll(".colLabel").classed("text-highlight",function(c,ci){ return ci==(d.col-1);});

                            $('#testingMaterial').css('display', 'block');
                            var toolTipWidth = $('#experimentNameLength').html("Experiment Name :"+rowLabel[d.row-1]).width();
                            $('#testingMaterial').css('display', 'none');

                            d3.select("#tooltip")
                                .style("width", toolTipWidth+"px")
                                .style("left", (d3.event.pageX+10) + "px")
                                .style("top", (d3.event.pageY-10) + "px")
                                .select("#value")
                                .html("Experiment Name: "+rowLabel[d.row-1]+"<br/>Gene Name: "+colLabel[d.col-1]+"<br/>Gene Value: "+d.value);
                            //Show the tooltip
                            d3.select("#tooltip").classed("hidden", false);
                        })
                        .on("mouseout", function(){
                            d3.select(this).classed("cell-hover",false);
                            d3.selectAll(".rowLabel").classed("text-highlight",false);
                            d3.selectAll(".colLabel").classed("text-highlight",false);
                            d3.select("#tooltip").classed("hidden", true);
                        })
                    ;

                /*var legend = svg.selectAll(".legend")
                 .data([-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7,8,9,10])
                 .enter().append("g")
                 .attr("class", "legend");

                 legend.append("rect")
                 .attr("x", function(d, i) { return legendElementWidth * i; })
                 .attr("y", height+(cellSize*2))
                 .attr("width", legendElementWidth)
                 .attr("height", cellSize)
                 .style("fill", function(d, i) { return colors[i]; });

                 legend.append("text")
                 .attr("class", "mono")
                 .text(function(d) { return d; })
                 .attr("width", legendElementWidth)
                 .attr("x", function(d, i) { return legendElementWidth * i; })
                 .attr("y", height + (cellSize*4));*/
                var quantileup = Math.round(d3.max(data, function(d) { return +d.value;})*100)/100;
                var quantiledown =  Math.round(d3.min(data, function(d) { return +d.value;})*100)/100;
                var idGradient = "legendGradient";
                x1 = 50,
                    barWidth = 100,
                    y1 = 4,
                    barHeight = 25;
// Change ordering of cells
                var svgForLegendStuff = d3.select("#svgGradient").append("svg")
                    .attr("width", 300)
                    .attr("class","legend")
                    .attr("height", 24);

//create the empty gradient that we're going to populate later
                var gradient = svgForLegendStuff.append("g")
                    .append("defs")
                    .append("linearGradient")
                    .attr("id","gradient")
                    .attr("x1","0%")
                    .attr("x2","100%")
                    .attr("y1","0%")
                    .attr("spreadMethod","pad")
                    .attr("y2","0%"); // x1=0, x2=100%, y1=y2 results in a horizontal gradient
                                      // it would have been vertical if x1=x2, y1=0, y2=100%
                                      // See
                                      //      http://www.w3.org/TR/SVG/pservers.html#LinearGradients
                                      // for more details and fancier things you can do
//create the bar for the legend to go into
// the "fill" attribute hooks the gradient up to this rect
                gradient.append("svg:stop")
                    .attr("offset", "0%")
                    .attr("stop-color", "#ffffd9")
                    .attr("stop-opacity", 1);

                gradient.append("svg:stop")
                    .attr("offset", "50%")
                    .attr("stop-color", "#41b6c4")
                    .attr("stop-opacity", 1);
                gradient.append("svg:stop")
                    .attr("offset", "100%")
                    .attr("stop-color",  "#081d58")
                    .attr("stop-opacity", 1);

                svgForLegendStuff.append("rect")
                    .attr("fill","url(#gradient)")
                    .attr("x",x1)
                    .attr("y",y1)
                    .attr("width",barWidth)
                    .attr("height",barHeight)
                    .attr("rx",0)
                    .style("stroke", "white")
                    .style("fill", "url(#gradient)")
                    .attr("opacity", 1)  //rounded corners, of course!
                    .attr("ry",0);
                svgForLegendStuff.append("rect")
                    .attr("fill","url(#gradient)")
                    .attr("x",x1)
                    .attr("y",y1)
                    .attr("width",barWidth)
                    .attr("height",barHeight)
                    .attr("rx",0)
                    .style("stroke", "white")
                    .style("fill", "url(#gradient)")
                    .attr("opacity", 1)  //rounded corners, of course!
                    .attr("ry",0);

//add text on either side of the bar

                var textY = y1 + barHeight/2 + 15;
                svgForLegendStuff.append("text")
                    .attr("class","legendText")
                    .attr("text-anchor", "middle")
                    .attr("x",x1 - 30)
                    .attr("y",textY-11)
                    .attr("dy",0)
                    .text(quantiledown);

                svgForLegendStuff.append("text")
                    .attr("class","legendText")
                    .attr("text-anchor", "left")
                    .attr("x",x1 + barWidth + 15)
                    .attr("y",textY-11)
                    .attr("dy",0)
                    .text(quantileup);


                function sortbylabel(rORc,i,sortOrder){
                    var t = svg.transition().duration(3000);
                    var log2r=[];
                    var sorted; // sorted is zero-based index
                    d3.selectAll(".c"+rORc+i)
                        .filter(function(ce){
                            log2r.push(ce.value);
                        })
                    ;
                    if(rORc=="r"){ // sort log2ratio of a gene
                        sorted=d3.range(col_number).sort(function(a,b){ if(sortOrder){ return log2r[b]-log2r[a];}else{ return log2r[a]-log2r[b];}});
                        t.selectAll(".cell")
                            .attr("x", function(d) { return sorted.indexOf(d.col-1) * cellSize; })
                        ;
                        t.selectAll(".colLabel")
                            .attr("y", function (d, i) { return sorted.indexOf(i) * cellSize; })
                        ;
                    }else{ // sort log2ratio of a contrast
                        sorted=d3.range(row_number).sort(function(a,b){if(sortOrder){ return log2r[b]-log2r[a];}else{ return log2r[a]-log2r[b];}});
                        t.selectAll(".cell")
                            .attr("y", function(d) { return sorted.indexOf(d.row-1) * cellSize; })
                        ;
                        t.selectAll(".rowLabel")
                            .attr("y", function (d, i) { return sorted.indexOf(i) * cellSize; })
                        ;
                    }
                }

                d3.select("#order").on("change",function(){
                    //order(this.value);
                });

                function order(value){
                    if(value=="hclust"){
                        var t = svg.transition().duration(3000);
                        t.selectAll(".cell")
                            .attr("x", function(d) { return hccol.indexOf(d.col) * cellSize; })
                            .attr("y", function(d) { return hcrow.indexOf(d.row) * cellSize; })
                        ;

                        t.selectAll(".rowLabel")
                            .attr("y", function (d, i) { return hcrow.indexOf(i+1) * cellSize; })
                        ;

                        t.selectAll(".colLabel")
                            .attr("y", function (d, i) { return hccol.indexOf(i+1) * cellSize; })
                        ;

                    }else if (value=="probecontrast"){
                        var t = svg.transition().duration(3000);
                        t.selectAll(".cell")
                            .attr("x", function(d) { return (d.col - 1) * cellSize; })
                            .attr("y", function(d) { return (d.row - 1) * cellSize; })
                        ;

                        t.selectAll(".rowLabel")
                            .attr("y", function (d, i) { return i * cellSize; })
                        ;

                        t.selectAll(".colLabel")
                            .attr("y", function (d, i) { return i * cellSize; })
                        ;

                    }else if (value=="probe"){
                        var t = svg.transition().duration(3000);
                        t.selectAll(".cell")
                            .attr("y", function(d) { return (d.row - 1) * cellSize; })
                        ;

                        t.selectAll(".rowLabel")
                            .attr("y", function (d, i) { return i * cellSize; })
                        ;
                    }else if (value=="contrast"){
                        var t = svg.transition().duration(3000);
                        t.selectAll(".cell")
                            .attr("x", function(d) { return (d.col - 1) * cellSize; })
                        ;
                        t.selectAll(".colLabel")
                            .attr("y", function (d, i) { return i * cellSize; })
                        ;
                    }
                }
                //
                var sa=d3.select(".g3")
                        .on("mousedown", function() {
                            if( !d3.event.altKey) {
                                d3.selectAll(".cell-selected").classed("cell-selected",false);
                                d3.selectAll(".rowLabel").classed("text-selected",false);
                                d3.selectAll(".colLabel").classed("text-selected",false);
                            }
                            var p = d3.mouse(this);
                            sa.append("rect")
                                .attr({
                                    rx      : 0,
                                    ry      : 0,
                                    class   : "selection",
                                    x       : p[0],
                                    y       : p[1],
                                    width   : 1,
                                    height  : 1
                                })
                        })
                        .on("mousemove", function() {
                            var s = sa.select("rect.selection");

                            if(!s.empty()) {
                                var p = d3.mouse(this),
                                    d = {
                                        x       : parseInt(s.attr("x"), 10),
                                        y       : parseInt(s.attr("y"), 10),
                                        width   : parseInt(s.attr("width"), 10),
                                        height  : parseInt(s.attr("height"), 10)
                                    },
                                    move = {
                                        x : p[0] - d.x,
                                        y : p[1] - d.y
                                    }
                                    ;

                                if(move.x < 1 || (move.x*2<d.width)) {
                                    d.x = p[0];
                                    d.width -= move.x;
                                } else {
                                    d.width = move.x;
                                }

                                if(move.y < 1 || (move.y*2<d.height)) {
                                    d.y = p[1];
                                    d.height -= move.y;
                                } else {
                                    d.height = move.y;
                                }
                                s.attr(d);

                                // deselect all temporary selected state objects
                                d3.selectAll('.cell-selection.cell-selected').classed("cell-selected", false);
                                d3.selectAll(".text-selection.text-selected").classed("text-selected",false);

                                d3.selectAll('.cell').filter(function(cell_d, i) {
                                    if(
                                        !d3.select(this).classed("cell-selected") &&
                                            // inner circle inside selection frame
                                        (this.x.baseVal.value)+cellSize >= d.x && (this.x.baseVal.value)<=d.x+d.width &&
                                        (this.y.baseVal.value)+cellSize >= d.y && (this.y.baseVal.value)<=d.y+d.height
                                    ) {

                                        d3.select(this)
                                            .classed("cell-selection", true)
                                            .classed("cell-selected", true);

                                        d3.select(".r"+(cell_d.row-1))
                                            .classed("text-selection",true)
                                            .classed("text-selected",true);

                                        d3.select(".c"+(cell_d.col-1))
                                            .classed("text-selection",true)
                                            .classed("text-selected",true);
                                    }
                                });
                            }
                        })
                        .on("mouseup", function() {
                            // remove selection frame
                            sa.selectAll("rect.selection").remove();

                            // remove temporary selection marker class
                            d3.selectAll('.cell-selection').classed("cell-selection", false);
                            d3.selectAll(".text-selection").classed("text-selection",false);
                        })
                        .on("mouseout", function() {
                            if(d3.event.relatedTarget.tagName=='html') {
                                // remove selection frame
                                sa.selectAll("rect.selection").remove();
                                // remove temporary selection marker class
                                d3.selectAll('.cell-selection').classed("cell-selection", false);
                                d3.selectAll(".rowLabel").classed("text-selected",false);
                                d3.selectAll(".colLabel").classed("text-selected",false);
                            }
                        })
                    ;
            });
    }

</script>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-97388395-1', 'auto');
        ga('send', 'pageview');

    </script>