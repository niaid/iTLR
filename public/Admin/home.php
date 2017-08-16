<?php
require '../../app/includes.php';

if (!isset($_SESSION['User']['isAuth']) || $_SESSION['User']['isAuth'] != 1) {
    header('Location: experiments.php');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Management</title>
    <style type="text/css">
        .button {
            padding: 2px 5px 2px 5px;
            border-radius: 10px;
            background-color: white;
            text-decoration: none;
            font-family: "Comic Sans MS";
            font-weight: bold;
            margin-left: -3px;
            cursor: pointer;
            margin-top: 10px;
            border: 3px solid #3498db;
            color: #3498db;
        }

        .management {
            padding: 15px;
            border-radius: 15px 15px 15px 15px;
            box-shadow: -1px 1px 1px rgba(0, 0, 0, 0.15);
            background: #fff;
            border: 2px solid #3498db;
            margin: 0 auto;
            width: 960px;
            /*width: 277px;*/
            /*height: 232px;*/
        }

        body {
            background-color: gainsboro;
            font-family: "Comic Sans MS";
            width: 100%;
        }

        /*.button1 {
            padding: 2px 5px 2px 5px;
            border: 3px solid #1abc9c;
            border-radius: 10px;
            background-color: white;
            color: #1abc9c;
            text-decoration: none;
            font-family: "Comic Sans MS";
            font-weight: bold;
            margin-left: -3px;
            cursor: pointer;
        }*/

        .instructions {
            color:blue;
        }

        .instructions:visited {
            color: blue;
        }

        .instructionsDialog {
            font-size:13px;
        }

    </style>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

</head>
<body>

<div class="management">
    <div id="blankData">
        <table border="1">
            <tr>
                <th>AltID</th>
                <th>GeneSymbol</th>
                <th>Data1</th>
                <th>Data2</th>
                <th>...</th>
                <th>Data(c)</th>
            </tr>
            <tr>
                <td>1053_at</td>
                <td>RFC2</td>
                <td>0.675</td>
                <td>0.375</td>
                <td>...</td>
                <td>Data(c)(1)</td>
            </tr>
            <tr>
                <td>117_at</td>
                <td>HSPA6</td>
                <td>-0.22</td>
                <td>-0.31</td>
                <td>...</td>
                <td>Data(c)(2)</td>
            </tr>
            <tr>
                <td>...</td>
                <td>...</td>
                <td>...</td>
                <td>...</td>
                <td>...</td>
                <td>...</td>
            </tr>
            <tr>
                <td>AltID(r)</td>
                <td>GeneSymbol(r)</td>
                <td>Data1(r)</td>
                <td>Data2(r)</td>
                <td>...</td>
                <td>Data(c)(r)</td>
            </tr>
        </table>

        <form action="actions/uploadDataFile2.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <span>Upload Experiment File:</span><br/>
            <!--<input type="file" name="file" id="uploadExpFile">-->

            <input type="file" name="file1" id="file1" style="display:none;" onchange="pressed(1)"/>
            <input type="text" id="txtFakeText1" size="22" readonly="true" value="Upload Your File"/>
            <input type="button" class="button" onclick="HandleFileButtonClick(1);" value="Upload File"/>
            <input type="submit" class="button" name="btn_submit" value="Submit"/>
        </form>
    </div>
    <span>-----------------------------------------------------------------------------------------</span>
    <div id="dataWithInfoInstrDialog" class="instructionsDialog" title="Data Files with an Information File Uploading Instructions">
        <p>The file <strong>must</strong> be in comma-separated values format (<strong>CSV</strong>). TODO: Incorporate more delimiters</p>
        <span>In order to open your file in excel without values being replaced by dates and other weird stuff:</span>
        <ul>
            <li>Replace any file extensions to .txt</li>
            <li>Open the Excel program</li>
            <li>File->Open</li>
            <li>Make sure to select "All Files" from the dropdown on the bottom right corner when browsing for the txt file</li>
            <li>Browse your file and press Open</li>
            <li>"Choose the file type that best decribes your data" should be Delimited then press next</li>
            <li>Set your current delimiter and then press next</li>
            <li>Select the columns in the data preview that can be potentially altered when imported into excel. Make sure to choose "TEXT" as the Column data format.</li>
            <li>Finish</li>
        </ul>
        <span>The experiment information file can store up to all of these fields. Any other field in the csv file will be disregarded</span>
        <table border="1">
            <thead>
                <tr>
                    <th>Data_Name</th><th>Name</th><th>DataType</th><th>Platform</th><th>Citation</th><th>Readout</th><th>Organism</th><th>CellType</th><th>Receptor</th><th>Stimulation</th><th>Concentration</th><th>TimePoint</th><th>Replicate</th><th>Experimentalist</th><th>Public</th><th>Experimentalist</th><th>Public</th><th>Protocol</th>
                </tr>
            </thead>
        </table>
    </div>


    <div>
        <a href="#" class="instructions" id="dataWithInfoInstr">Data Files with an Information File Uploading Instructions</a>
        <form action="actions/uploadDataFileWithInfo.php" method="POST" accept-charset="utf-8"
              enctype="multipart/form-data">
            <span>Select Experiment File(s):</span>
            <input type="file" name="Data[]" id="file3" style="display:none;" multiple onchange="pressed(3)"/>
            <input type="text" id="txtFakeText3" size="22" readonly="true" value="Upload Your File(s)"/>
            <input type="button" class="button" onclick="HandleFileButtonClick(3);" value="Upload File(s)"/>
            <br/>
            <span>Select Experiment Information File:</span>
            <input type="file" name="Info" id="file4" style="display:none;" onchange="pressed(4)"/>
            <input type="text" id="txtFakeText4" size="22" readonly="true" value="Upload Your File"/>
            <input type="button" class="button" onclick="HandleFileButtonClick(4);" value="Upload File"/>
            <br/>
            <input type="submit" class="button" name="btn_submit" value="Submit"/>

        </form>
    </div>
    <span>------------------------------------------------------------------------------------------</span><br/>

    <div>
    <span
        style="font-size:10px;">File Format: TYPE_ADDITIONALINFO.csv. Only TYPE will be saved and showed to users.</span><br/>
        <span style="font-size:10px;">CSV Format:"EntrezID_B","EntrezID_A","Gene_A","Gene_B"</span><br/>
        <span style="font-size:10px;">2nd line:111111,222222,"Agr1","Agr2"</span>

        <form action="actions/uploadNetwork.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">

            <span>Upload Network File:</span><br/>

            <input type="file" name="file2" id="file2" style="display:none;" onchange="pressed(2)">
            <input type="text" id="txtFakeText2" size="22" readonly="true" value="Upload Your File">
            <input type="button" class="button" onclick="HandleFileButtonClick(2);" value="Upload File">
            <input type="submit" class="button" name="btn_submit" value="Submit"/>
        </form>
    </div>
    <span>--------------------------------------------------------------------------------------------</span><br/>
    <span>Erase all experiments and their related information</span>

    <form action="actions/deleteExperiments.php" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
        <input type="submit" name="Delete" value="Delete"/>
    </form>


</div>
<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
<script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script type="text/javascript">
    function HandleFileButtonClick(id) {
        document.getElementById("file" + id).click();
    }
    function pressed(id) {
        document.getElementById("txtFakeText" + id).value = document.getElementById("file" + id).value;
    }

    $(function() {
        $( "#dataWithInfoInstrDialog" ).dialog({
            autoOpen: false,
            position: { my : "center top+50", at : "center top", of: ".management"},
            height: 500,
            width: "auto",
            show: {
                effect: "blind",
                duration: 1000
            },
            hide: {
                effect: "explode",
                duration: 1000
            }
        });

        $( "#dataWithInfoInstr" ).click(function() {
            $( "#dataWithInfoInstrDialog" ).dialog( "open" );
        });
    });


</script>
</body>
</html>                          