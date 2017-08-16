<?php
if (!isset($_GET['ranges']) || !isset($_GET['filter']) || !isset($_GET['tabs']) || !isset($_GET['operation'])) {
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="style.css" rel="stylesheet"/>
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, minimal-ui">
    <title>Pie style</title>
    <script src="../JS/jquery.min.js"></script>
    <script src="../JS/cytoscape.min.js"></script>
    <!-- The modified D3 file -->
    <script src="../JS/d3_2.js"></script>


</head>
<body>
<div class="center" style="display:block" id="loadingGif">
    <img src="../Images/loading.gif" alt="Loading GIF" style="height:200px;padding-right:5px;"/>
</div>
<div id="cy"></div>
    <script>
        var tabs = '<?= htmlspecialchars($_GET["tabs"]); ?>';
        var ranges = '<?= htmlspecialchars($_GET["ranges"]); ?>';
        var filterType = '<?= htmlspecialchars($_GET["filter"]); ?>';
        var pathwayOptions = '<?= htmlspecialchars($_GET["pathwayOptions"]); ?>';
        var geneOperation = '<?= htmlspecialchars($_GET['operation']); ?>';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../AJAX/data.php?type=pathway&tabs=' + tabs + '&ranges=' + ranges + '&filter=' + filterType + '&pathwayOptions=' + pathwayOptions + '&operation=' + geneOperation);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                window.parent.gradient('gradient' + window.frameElement.id, response.quantile);
                if(response.Valid == true) {
                    createPathway(response.data, pathwayOptions);
                } else {
                    document.getElementById('cy').innerHTML = response.Message;
                }
                $("#loadingGif").css('display', 'none');
            }
        };
        xhr.send(null);
        //createPathway();
        function createPathway(response, pathwayOptions) {
            var positionX = 0;
            var cy = cytoscape({container: $("#cy")[0],

                style: cytoscape.stylesheet()
                    .selector('node')
                    .css({
                        'shape': 'rectangle',
                        'width': function(node) {return node.data().width;},
                        'height': function(node) {return node.data().height;},
                        'content': 'data(name)',
                        'color'  : 'white',
                        'text-valign': 'center',
                        'text-halign': 'center',
                        'font-size': 'data(fontSize)',
                        "word-wrap": "break-word",
                        "text-wrap" :"wrap",
                        'font-weight' : 'data(fontWeight)',
                        'background-image': '../AJAX/data.php?type=pathwayImage&pathwayOptions='+pathwayOptions,
                        'background-position-y': 'data(y)',
                        'background-position-x': '0px',
                        'background-repeat' : 'no-repeat'
                    })
                    .selector('edge')
                    .css({
                        'width': 4,
                        'target-arrow-shape': 'triangle',
                        'target-arrow-color': '#ccc',
                        'line-color': '#ccc',
                        'opacity': 1
                    })
                    .selector(':selected')
                    .css({
                        'background-color': 'black',
                        'line-color': 'black',
                        'target-arrow-color': 'black',
                        'source-arrow-color': 'black',
                        'opacity': 1
                    })
                    .selector('.faded')
                    .css({
                        'opacity': 0.25,
                        'text-opacity': 0
                    }),

                elements: response,

                layout: {
                    name: 'preset'
                },

                ready: function () {
                    window.cy = this;
                }
            });
            cy.on("tap", "node", function () {
                try {
                    window.open(this.data("href"));
                } catch (e) {
                    window.location.href = this.data("href");
                }

            });
            var colorScale = d3.scale.linear()
                .range(["red", "white", "blue"])
                .domain([-1.0, 0, 1]);

        }
    </script>
</body>
</html>
