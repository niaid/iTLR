<?php namespace iTLR\Visualizations\Support;

use iTLR\Helpers\Prints;

/**
 * Class NodeImage
 *
 * Creates the image needed to display the data values according to a color scale
 * Creates an image composed of images every height of node
 * Stylesheet reads the image once breaks down the height, width, position x start and position y start
 * Referred as CSS sprites
 */
class NodeImage {

    protected $im; //image resource
    protected $max; //maximum Value or 99th quartile
    protected $min; //minimum value or 1st quartile
    protected $heightImage; //height of the image so far
    protected $subSectionNo; //number of sub section in the node (split amount)

    protected $from = '0000FF'; //from this hex
    protected $to   = 'EE3B3B'; //to this hex

    /**
     * NodeImage constructor.
     *
     * Constructs a transparent image and sets all the necessary variables
     *
     * @param $count
     * @param $max
     * @param $min
     * @param $width
     * @param $height
     * @param $subSectionNo
     */
    public function __construct($count, $max, $min, $totalWidth, $totalHeight, $subSectionNo)
    {
        //initialize image
        $this->im = imagecreatetruecolor($totalWidth, $totalHeight);
        imagesavealpha($this->im, true);

        //allows for png transparency
        $trans_colour = imagecolorallocatealpha($this->im, 0, 0, 0, 127);
        imagefill($this->im, 0, 0, $trans_colour);

        //assign variables
        $this->max = $max;
        $this->min = $min;
        $this->subSectionNo = $subSectionNo;
        $this->count = $count;
        $this->heightImage = 0;
    }

    public function addNodeToImage(array $values, $width, $height) {
        $nodeColors = array();
        $verticalLines = false;

        for($i = 0; $i < $this->subSectionNo; $i++) {
            if(isset($values[$i]) && is_numeric($values[$i])) {
                $nodeColors[] = $this->blend_hex($values[$i]);
                $verticalLines = true;
            } else {
                $nodeColors[] = $this->blend_hex();
            }
        }

        //print_modified_r($nodeColors);
        $this->addNodeImageToImage($nodeColors, $width, $height, $verticalLines);
    }

    private function addNodeImageToImage(array $values, $width, $height, $verticalLines) {
        $debugMode = array(0);

        $subSection = (int) $width/$this->subSectionNo;
        $yStart = $this->heightImage;
        $this->heightImage += $height;
        $yEnd   = $this->heightImage;


        if(in_array(1, $debugMode)) {
            echo '<br/>Information about filling the rectangle<br/>';
            echo 'Subsection pixels:'.$subSection.'<br/>';
            echo 'Start y:'.$yStart.'<br/>';
            echo 'End y:'.$yEnd.'<br/>';
        }

        for($i = 0; $i < count($values); $i++) {
            $xStart = $i * $subSection;
            $xEnd = ($i + 1) * $subSection;

            if (in_array(1, $debugMode)) {
                echo '----' . $i . '----<br/>';
                echo 'Start x:' . $xStart . '<br/>';
                echo 'End x:' . $xEnd . '<br/>';
                echo 'Start y:' . $yStart . '<br/>';
                echo 'End y:' . $yEnd . '<br/>';
                echo '----' . $i . '----<br/>';
                Prints::array($values[$i]);
            }

            imagefilledrectangle($this->im, $xStart, $yStart, $xEnd, $yEnd, imagecolorallocate($this->im, $values[$i]['Red'], $values[$i]['Green'], $values[$i]['Blue']));
            if ($verticalLines && $i != count($values) - 1) {
                imagefilledrectangle($this->im, $xEnd - 1, $yStart, $xEnd, $yEnd, imagecolorallocate($this->im, 0, 0, 0));
            }
        }

        //imagepng($this->im, 'tmp.png');
    }

    public function getImage() {
        //header('Content-Type: image/png');
        $fileName = 'tmp'.time().''.rand(0,999999).'.png';
        imagepng($this->im, $fileName);
        $contents =  file_get_contents($fileName);
        usleep(500000);
        unlink($fileName);
        imagedestroy($this->im);
        return $contents;
    }



    /**
     * Blend two hexadecimal colours
     * specifying the fractional position.
     *
     * Contribution to: Salathe@php.net
     *
     * Example:
     *     // 10% along the gradient between #66cc00 and #cc2200
     * $this->from
     * $this->to
     *
     * blend_hex(0.1); // "70bb00"
     */
    private function blend_hex($value = 'N/A')
    {
        // If no value was given or value does not exist
        if($value == 'N/A') {
            return array('Red' => 112, 'Green' => 128, 'Blue' => 144);
        }

        // Else
        // Convert value within the min and max between 0 and 1. 0 wil be the minimum value and 1 will be the maximum value
        $value = ($value < $this->min) ? $this->min : $value; // can be 1st  quartile
        $value = ($value > $this->max) ? $this->max : $value; // can be 99th quartile
        $value = abs(($value-$this->min)/($this->max-$this->min));

        // 1. Grab RGB from each colour
        list($fr, $fg, $fb) = sscanf($this->from, '%2x%2x%2x');
        list($tr, $tg, $tb) = sscanf($this->to, '%2x%2x%2x');

        // 2. Calculate colour based on fractional position
        $r = (int) ($fr - (($fr - $tr) * $value));
        $g = (int) ($fg - (($fg - $tg) * $value));
        $b = (int) ($fb - (($fb - $tb) * $value));

        // 3. Format to 6-char HEX colour string
        return array('Red' => $r, 'Green' => $g, 'Blue' => $b);
    }
}

?>
