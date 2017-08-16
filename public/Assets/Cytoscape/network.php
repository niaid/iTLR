<?php
  if(!isset($_GET['ranges']) || !isset($_GET['filter']) || !isset($_GET['tabs']) || !isset($_GET['operation'])) {
    exit();
  }
?>

<!DOCTYPE html>
<html>
<head>
  <title></title>
  <link href="style.css" rel="stylesheet"/>
</head>
<body>
<div class="center" style="display:block" id="loadingGif">
    <img src="../Images/loading.gif" alt="Loading GIF" style="height:200px;padding-right:5px;"/>
</div>
<div id="cy"></div>
<script src="../JS/jquery.min.js"></script>
<script src="../JS/cytoscape.min.js"></script>

<script>
var tabs = '<?= htmlspecialchars($_GET["tabs"]); ?>';
var ranges = '<?= htmlspecialchars($_GET["ranges"]); ?>';
var filterType = '<?= htmlspecialchars($_GET["filter"]); ?>';
var networkChoice = '<?= htmlspecialchars($_GET["networkChoice"]); ?>';
var geneOperation = '<?= htmlspecialchars($_GET['operation']); ?>';
var geneNo        = '<?= htmlspecialchars($_GET['geneNo']); ?>';

var colors=["'rgb(255, 118, 142)'", "'#3498db'", "'red'", "'gold'", "'#7CFC00'", "'#ff6600'", "'#7B68EE'", "'#FF4500'", "'#7FB5DA'", "'#9400D3'"];
var string = "";
var m=1;
var tabs2 = tabs.split(",");
for(var u=0; u<tabs2.length; u++){
  tabs2[u] = parseFloat(tabs2[u]);
}
for(var d=0; d<tabs2.length; d++){

  string += "'pie-"+m+"-background-color': "+ colors[d+1]+",";
  if(d != tabs.length-1){
      string += "'pie-"+m+"-background-size': 'mapData(data"+tabs2[d]+", 0, 10, 0, 100)',";

  }else{
    string += "'pie-"+m+"-background-size': 'mapData(data"+tabs2[d]+", 0, 10, 0, 100)'";
  }
  m++;
}

console.log(string);
var dataDistribution = null; //= "[{ data: { id: 'a', data1: 3, data2: 5, data3 : 2 } },{ data: { id: 'b', data1: 6, data2: 1, data3: 3 } },{ data: { id: 'c', data1: 2, data2: 3, data3: 5 } },{ data: { id: 'd', data1: 7, data2: 1, data3: 2 } },{ data: { id: 'e', data1: 2, data2: 3, data3: 5 } }]";
var networkConnection = null; //"[{ data: { id: 'ae', weight: 1, source: 'a', target: 'e' } },{ data: { id: 'ab', weight: 3, source: 'a', target: 'b' } },{ data: { id: 'be', weight: 4, source: 'b', target: 'e' } },{ data: { id: 'bc', weight: 5, source: 'b', target: 'c' } },{ data: { id: 'ce', weight: 6, source: 'c', target: 'e' } },{ data: { id: 'cd', weight: 2, source: 'c', target: 'd' } },{ data: { id: 'de', weight: 7, source: 'd', target: 'e' } }]";

var xhr = new XMLHttpRequest();
xhr.open('GET', '../AJAX/data.php?type=network&tabs='+tabs+'&ranges='+ranges+'&filter='+filterType+'&operation='+geneOperation+'&geneNo='+geneNo);
xhr.onreadystatechange = function() {
  if (xhr.readyState == 4) {
      if (xhr.status == 200) {
          var response = JSON.parse(xhr.responseText);
          var nodes = JSON.stringify(response.dataDist);
          var edges = JSON.stringify(response.edges);
          var message = response.Message;
          console.log('Message:' + message);
          console.log(message);
          if (message != '' && self != top) {
              message = message.replace(/"/g, "");
              window.parent.document.getElementById('results3Message').innerHTML = '<span style="color:red">' + message + '</span><br/>Network Gene Number: <input type="number" name="networkGeneNo" id="networkGeneNo"/><input type="button" class="button" style="border: 3px solid #3498db; color: #3498db; margin-left:10px;" value="Submit" onclick="makeNetwork(\'circle\')"/>';
              $("#loadingGif").css('display', 'none');
              window.parent.document.getElementById('networkChoice').style.display = 'none';
              window.parent.document.getElementById('results3iframe').style.display = 'none';
          }
          else
          {
              window.parent.document.getElementById('networkChoice').style.display = 'block';
              window.parent.document.getElementById('results3iframe').style.display = 'block';
          }
          eval("$('#cy').cytoscape({textureOnViewport: true, pixelRatio : 1,motionBlur : true,style: cytoscape.stylesheet().selector('node').css({'width': '60px','height': '60px','content': 'data(id)','pie-size': '80%'," + string + "}).selector('edge').css({'width': 4,'target-arrow-shape': 'triangle','opacity': 0.5}).selector(':selected').css({'background-color': 'black','line-color': 'black','target-arrow-color': 'black','source-arrow-color': 'black','opacity': 1}).selector('.faded').css({'opacity': 0.25,'text-opacity': 0}),elements: {nodes: " + nodes + ", edges: " + edges + " },layout: {name: '" + networkChoice + "', padding: 10},ready: function(){window.cy = this;}});");
          $("#loadingGif").css('display', 'none');
          
      }
  }
}
xhr.send(null);





</script>
</body>
</html>