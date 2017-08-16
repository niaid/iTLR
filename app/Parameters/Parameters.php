<?php

namespace iTLR\Parameters;

use iTLR\Database\Database;
use iTLR\Experiment\Experiment;
use iTLR\Helpers\Prints;
use PDO;

/**
 * Class Parameters
 * @package iTLR\Parameters
 *
 * This is class is used for the parameter selection
 */
class Parameters
{
    private $start;
    /* Valid Types have to be in order */
    private $validTypes = ['dataType', 'cellType', 'stimulation', 'strain', 'timePoint', 'concentration', 'experimentalist', 'replicate'];
    private $essentialTypes = ['dataType', 'cellType', 'stimulation'];
    private $startNo;
    private $displayStatus;
    private $completed;
    private $completedExpId;

    private $newButtons;
    private $available;
    private $selected;
    private $buttonsOnDisplay;

    private $startButtons;

    private $stimulationSelectedExpIds;
    private $otherStimulationExpIds;
    private $stimulationSelectedValues;

    public function __construct()
    {
        $db = Database::getInstance();

        $index = 0;
        $result = array();
        //Retrieve all starting buttons
        $req = $db->query('SELECT DISTINCT DataType FROM experiments ORDER BY DataType');
        while ($data = $req->fetch()) {
            $result[$index]['Type'] = 'dataType';
            $result[$index]['Value'] = $data['DataType'];
            $index++;
        }

        $req = $db->query('SELECT DISTINCT CellType FROM experiments ORDER BY CellType');
        while ($data = $req->fetch()) {
            $result[$index]['Type'] = 'cellType';
            $result[$index]['Value'] = $data['CellType'];
            $index++;
        }

        $req = $db->query('SELECT DISTINCT Stimulus FROM stimulation ORDER BY Stimulus');
        while ($data = $req->fetch()) {
            $result[$index]['Type'] = 'stimulation';
            $result[$index]['Value'] = $data['Stimulus'];
            $index++;
        }

        //Save the information to start and available
        $this->start = $result;
        $this->available = $result;
        $this->buttonsOnDisplay = $result;
        $this->startButtons = true;
        $this->displayStatus = 0;
        $this->startNo = 3;
        $this->completed = 0;
        $this->completedExpId = null;

    }

    public function startingButtons()
    {
        return $this->start;
    }

    public function newButtons()
    {
        if ($this->startButtons) {
            $this->startButtons = false;
            return $this->startingButtons();
        }
        if (count($this->newButtons) > 0) {
            $new = $this->newButtons;
            $this->newButtons = array();
            return $new;
        }
        return array();
    }

    public function available()
    {
        return $this->available;
    }

    public function selected()
    {
        return $this->selected;
    }

    public function completed()
    {
        return $this->completed;
    }

    public function displayStatus()
    {
        return $this->displayStatus;
    }

    public function addSelection($type, $value)
    {
       //  echo '1';
        $type = explode('_', $type);

        if (count($type) == 2) {
            //echo '-1';
            $selectionTmp = array('Type' => $type[0], 'Value' => $value, 'Stimulant' => $type[1]);
        } else {
            //echo '0';
            $selectionTmp = array('Type' => $type[0], 'Value' => $value);
        }
        //echo '1';
        if (is_array($this->selected) && in_array($selectionTmp, $this->selected)) return;
        if (!in_array($selectionTmp, $this->available)) return;
        $this->selected[] = $selectionTmp;
        //Prints::array($this->selected);
        //echo '2';
        $this->checkDisplayStatus();
        //echo '3';
        //Prints::array($this->selected);
        $this->updateParameters();
        //echo '4';
        $this->checkNewButtons();
        //echo '5';
    }

    public function removeSelection($type, $value)
    {
        if (strpos($type, 'concentration') !== false) {
            $type = explode('_', $type);
            $selection = array('Type' => $type[0], 'Value' => $value, 'Stimulant' => $type[1]);
        } else if (in_array($type, $this->validTypes)) {
            $selection = array('Type' => $type, 'Value' => $value);
        } else {
            return;
        }

        if (!in_array($selection, $this->selected)) return; //if the selection is not in the selected array
        unset($this->selected[array_search($selection, $this->selected)]); //find item, delete
        $this->selected = array_values($this->selected); //reset the indexes

        $this->checkDisplayStatus();

        $this->updateParameters();

        $this->buttonsOnDisplay = $this->available;
    }

    /**
     * Determines if we have successfully selected an experiment.
     * False means that the user has not finished selecting all of the parameters
     * Null indicates that the user has selected all of the parameters but no Experiment was found with those parameters.
     * This will signify that there is a problem and should be logged.
     *
     * @param int $tab The current tab of the experiment
     * @return bool|Experiment|null
     */
    public function isCompleted()
    {
        if ($this->completed == 0) return false;

        $db = Database::getInstance();

        $whereQuery = $this->createWhereQuery();
        $whereValues = $this->createWhereValues();

        $req = $db->prepare('SELECT DISTINCT experiments.ID, stimulation.Stimulus AS stimulation, stimulation.Concentration AS concentration FROM experiments LEFT JOIN `experiment_stimulation` AS es ON experiments.id = es.ExperimentID LEFT JOIN stimulation ON es.StimulationID = stimulation.ID WHERE experiments.ID IN (SELECT experiments.ID FROM experiments LEFT JOIN `experiment_stimulation` AS es ON experiments.id = es.ExperimentID LEFT JOIN stimulation ON es.StimulationID = stimulation.ID ' . $whereQuery . ') ORDER BY `experiments`.`ID` ASC');
        $req->execute(array_values($whereValues));

        $data = $req->fetchAll(PDO::FETCH_ASSOC);
        $this->filterExperiments($data, true);

        $data = $this->getStimulationSelectedExperiments($data);

        if (count($data) == 0) {
            //logging
            $log = \iTLR\Log\Log::getInstance();
            $log->addError('Experiment was not found although all the fields were selected'.Prints::implodeAssociateArray($this->selected));
            return null;
        }

        $currentId = $data[0]['ID'];
        for ($i = 1; $i < count($data); $i++) {
            if ($data[$i]['ID'] != $currentId) {
                unset($data[$i]);
            }
        }

        $data = array_values($data);
        $this->completedExpId = $currentId;

        $experiment = new Experiment($data[0]['ID']);
        
        return $experiment;
    }

    /**
     * Returns the experiment id or false
     * @return bool|Experiment|null
     */
    public function getExperiment()
    {
        $result = ($this->completed == 1 && $this->completedExpId != null) ? $this->completedExpId : false;
        return ($result === false) ? $this->isCompleted() : $result;
    }

    public function reset()
    {
        $this->available = $this->start;
        $this->buttonsOnDisplay = $this->start;
        $this->startButtons = true;
        $this->displayStatus = 0;
        $this->startNo = 3;
        $this->completed = 0;
        $this->completedExpId = null;
        $this->selected = array();
    }


    private function checkNewButtons()
    {
        //Callback
        $callback = array('\iTLR\Parameters\Parameters', 'difference');
        $difference = array_udiff($this->available, $this->buttonsOnDisplay, $callback);

        if (count($difference) == 0) return;
        $difference = array_values($difference);

        for ($i = 0; $i < count($difference); $i++) {
            $this->newButtons[] = $difference[$i];
        }

        $this->buttonsOnDisplay = $this->available;
    }

    /**
     * @param $a array
     * @param $b array
     * @return int
     *
     * The callback function when using udiff
     * Compares the first array with the second array by converting it into a string and using strcmp
     */
    private function difference($a, $b)
    {
        return strcmp(implode("", $a), implode("", $b));
    }


    private function checkDisplayStatus()
    {
        $selectionTypes = array();
        $duplicityTypeStimulation = 0;
        $duplicityTypeConcentration = 0;

        for ($i = 0; $i < count($this->selected); $i++) {
            if (!in_array($this->selected[$i]['Type'], $selectionTypes)) {
                $selectionTypes[] = $this->selected[$i]['Type'];
            } else if ($this->selected[$i]['Type'] == 'concentration') {
                $duplicityTypeConcentration++;
            } else if ($this->selected[$i]['Type'] == 'stimulation') {
                $duplicityTypeStimulation++;
            }
        }

        //Prints::array($selectionTypes);

        $selectionNo = 2;
        //Use the order of the valid types in order to find what is selected
        for ($i = 2; $i < count($selectionTypes); $i++) {
            if (in_array($this->validTypes[$i], $selectionTypes)) {
                $selectionNo++;
                continue;
            }
            break;
        }
        //echo $selectionNo;
        //echo $selectionNo + $duplicityTypeStimulation + $duplicityTypeConcentration.' ';
        array_splice($this->selected, $selectionNo + $duplicityTypeStimulation + $duplicityTypeConcentration);
        //Prints::array($this->selected);
        $this->displayStatus = $selectionNo - $this->startNo + 1;
        $this->displayStatus = ($this->displayStatus < 0) ? 0 : $this->displayStatus;
        //for matching stimulation with concentration
        $this->displayStatus = ($duplicityTypeConcentration != $duplicityTypeStimulation
            && in_array('timePoint', $selectionTypes)) ? 3 : $this->displayStatus;
        //print_r($selectionTypes);
        //print_r($this->essentialTypes);
        //echo count(array_diff($selectionTypes, $this->essentialTypes));
        if (count(array_diff($this->essentialTypes, $selectionTypes)) != 0) {
            if ($this->displayStatus > 0) {
                array_splice($this->selected, 1 + $duplicityTypeStimulation);
            } /*else {
                array_splice($this->selected, 2);
            }*/
            $this->displayStatus = 0;
        }

        if (count(array_diff($this->validTypes, $selectionTypes)) == 0 && count(array_diff($selectionTypes, $this->validTypes)) == 0) {
            $this->completed = 1;
            $this->displayStatus = 5;
            $this->isCompleted();
        } else {
            $this->completed = 0;
            $this->completedExpId = null;
        }
    }

    /**
     * Find all the types in the given in the specified array
     * @param $array
     * @param $type - ('Type' or 'Value')
     * @return array
     */
    private function get($array, $type = 'Type')
    {
        $result = array();

        for ($i = 0; $i < count($array); $i++) {
            $result[] = $array[$i][$type];
        }

        return $result;
    }

    /**
     * Creates the where query clause for the parameters in order to
     * sanitize the data coming into the server - PDO version
     * @return string - Where query string
     */
    private function createWhereQuery()
    {
        if (count($this->selected) == 0) return;
        //make the where query to be pdo friendly
        $whereQuery = 'WHERE ';
        $isStimulationSet = false;
        for ($a = 0; $a < count($this->selected); $a++) {
            if ($a != 0) {
                //if its not the first or the last element
                $whereQuery .= ' AND ';
            }
            if ($this->selected[$a]['Type'] == 'stimulation' && !$isStimulationSet) {
                $whereQuery .= '(';
                for ($i = $a; isset($this->selected[$i]) && $this->selected[$i]['Type'] == 'stimulation'; $i++) {
                    if ($a != $i)
                        $whereQuery .= ' OR ';

                    $whereQuery .= '(stimulation.Stimulus = ?';
                    for ($y = 0; $y < count($this->selected); $y++) {
                        if ($this->selected[$y]['Type'] == 'concentration' && $this->selected[$y]['Stimulant'] == $this->selected[$i]['Value']) {
                            $whereQuery .= ' AND stimulation.Concentration = ?';
                        }
                    }
                    $whereQuery .= ')';
                }
                $whereQuery .= ')';
                $isStimulationSet = true;
            } else if ($this->selected[$a]['Type'] != 'stimulation' && $this->selected[$a]['Type'] != 'concentration') {
                //if its a simple where type = value
                $whereQuery .= $this->selected[$a]['Type'] . ' = ?';
            } else {
                //if already added (stimulation or concentration), remove AND
                $whereQuery = substr($whereQuery, 0, -5);
            }
        }
        return $whereQuery;
    }

    private function createWhereValues()
    {
        $values = array();
        $isStimulationSet = false;

        for ($a = 0; $a < count($this->selected); $a++) {
            if ($this->selected[$a]['Type'] == 'stimulation' && !$isStimulationSet) {
                for ($i = $a; isset($this->selected[$i]) && $this->selected[$i]['Type'] == 'stimulation'; $i++) {
                    $values[] = $this->selected[$i]['Value'];
                    for ($y = 0; $y < count($this->selected); $y++) {
                        if ($this->selected[$y]['Type'] == 'concentration' && $this->selected[$y]['Stimulant'] == $this->selected[$i]['Value']) {
                            $values[] = $this->selected[$y]['Value'];
                        }
                    }
                }
                $isStimulationSet = true;
            } else if ($this->selected[$a]['Type'] != 'stimulation' && $this->selected[$a]['Type'] != 'concentration') {
                //if its a simple where type = value
                $values[] = $this->selected[$a]['Value'];
            }
        }

        return $values;
    }

    private function updateParameters()
    {
        $debug = array(0);

        $db = Database::getInstance();

        $whereQuery = $this->createWhereQuery();
        $whereValues = $this->createWhereValues();
        if (in_array(1, $debug)) {
            Prints::array($whereQuery);
            Prints::array($whereValues);
        }

        $req = $db->prepare('SELECT DISTINCT experiments.ID, experiments.DataType AS dataType, experiments.CellType AS cellType, stimulation.Stimulus AS stimulation, experiments.Strain AS strain, experiments.TimePoint AS timePoint, stimulation.Concentration AS concentration, experiments.Experimentalist AS experimentalist, experiments.Replicate AS replicate FROM experiments LEFT JOIN `experiment_stimulation` AS es ON experiments.id = es.ExperimentID LEFT JOIN stimulation ON es.StimulationID = stimulation.ID WHERE experiments.ID IN (SELECT experiments.ID FROM experiments LEFT JOIN `experiment_stimulation` AS es ON experiments.id = es.ExperimentID LEFT JOIN stimulation ON es.StimulationID = stimulation.ID ' . $whereQuery . ') ORDER BY `experiments`.`ID` ASC');
        $req->execute(array_values($whereValues));

        $available = array();

        $data = $req->fetchAll(PDO::FETCH_ASSOC);
        if (in_array(1, $debug)) {
            Prints::array($data);
        }
        $this->filterExperiments($data);

        $otherStimulationValues = $this->getOtherStimulationPossibilities($data);
        if (in_array(1, $debug)) {
            Prints::array($otherStimulationValues);
        }

        $data = $this->getStimulationSelectedExperiments($data);
        if (in_array(1, $debug)) {
            Prints::array($data);
        }

        for ($i = 0; $i < count($data); $i++) {
            if (in_array(1, $debug)) Prints::array($data[$i]);
            $types = array_keys($data[$i]); //all of the types resulted from the array
            //echo $this->displayStatus;
            //print_modified_r($types);
            for ($a = 1; $a <= ($this->startNo + $this->displayStatus); $a++) {
                //start from the second since the first is expID
                if ($types[$a] == 'concentration' && $this->displayStatus > 1) {
                    $availableTmp = array('Type' => $types[$a], 'Value' => $data[$i][$types[$a]], 'Stimulant' => $data[$i]['stimulation']);
                } else {
                    $availableTmp = array('Type' => $types[$a], 'Value' => $data[$i][$types[$a]]);
                }
                if (array_search($availableTmp, $available) === false) {
                    $available[] = $availableTmp;
                }
            }
        }

        $this->available = array_merge($available, $otherStimulationValues);

    }

    private function getStimulationSelectedExperiments($data)
    {
        if (!in_array('stimulation', $this->get($this->selected, 'Type'))) return $data;
        //create a new array with only the stimulation selected experiments
        $newExpData = array();
        for ($i = 0; $i < count($data); $i++) {
            if (in_array($data[$i]['ID'], $this->stimulationSelectedExpIds)) {
                $newExpData[] = $data[$i];
            }
        }

        return $newExpData;
    }

    private function getOtherStimulationPossibilities($data)
    {
        if (!in_array('stimulation', $this->get($this->selected, 'Type'))) return array();

        $otherStimulationPossibilities = array();

        for ($a = 0; $a < count($data); $a++) {
            if (in_array($data[$a]['ID'], $this->otherStimulationExpIds) && !in_array($data[$a]['stimulation'], $this->stimulationSelectedValues) && !in_array($data[$a]['stimulation'], array_column($otherStimulationPossibilities, "Value"))) {
                $otherStimulationPossibilities[] = array('Type' => 'stimulation', 'Value' => $data[$a]['stimulation']);
            }
        }

        return $otherStimulationPossibilities;
    }

    /**
     * Pre-condition: stimulation has to be selected
     * Removes all experiments that do not match the stimulation selected criteria
     */
    private function filterExperiments($data, $concentration = false)
    {
        $print = new Prints(0);

        if (!in_array('stimulation', $this->get($this->selected, 'Type'))) return $data;

        $stimulationSelectedValues = array();
        $stimulationSelectedValuesTmp = array();
        $concentrationSelectedValues = array();
        $stimulationSelectedExpIds = array();
        $otherStimulationExpIds = array();

        //store the selected stimulation values
        for ($i = 0; $i < count($this->selected); $i++) {
            if ($this->selected[$i]['Type'] == 'concentration') {
                $stimulationSelectedValues[] = $this->selected[$i]['Stimulant'];
                $concentrationSelectedValues[] = $this->selected[$i]['Value'];
            } else if ($this->selected[$i]['Type'] == 'stimulation') {
                $stimulationSelectedValuesTmp[] = $this->selected[$i]['Value'];
            }
        }
        //print_modified_r($stimulationSelectedValues);
        //print_modified_r($concentrationSelectedValues);

        if (count($stimulationSelectedValuesTmp) == count($concentrationSelectedValues)) {
            $concentration = true;
        } else {
            $stimulationSelectedValues = $stimulationSelectedValuesTmp;
        }


        $print->print(1, $stimulationSelectedValues);

        //parse the each row to find which experiments matches with the stimulation requirement
        $i = 0;
        while ($i < count($data)) {
            $experimentId = $data[$i]['ID'];
            $stimulationExpValues = array($data[$i]['stimulation']);
            $concentrationExpValues = array($data[$i]['concentration']);

            while (++$i < count($data) && $data[$i]['ID'] == $experimentId) {
                $stimulationExpValues[] = $data[$i]['stimulation'];
                $concentrationExpValues[] = $data[$i]['concentration'];
            }

            $insertExpId = true;

            $print->print(1, $stimulationExpValues);


            /* Selected values must now be handled. */


            /* If the current experiment has less than the selected values, no need to go further */
            if(count($stimulationExpValues) < count($stimulationSelectedValues)) {
                $print->print(1, 1);
                continue;
            } else {
                /* Determine if the current experiment has all the stimulations selected */
                $hasAllSelectedStimulations = true;
                for ($a = 0; $a < count($stimulationSelectedValues); $a++) {
                    if (!in_array($stimulationSelectedValues[$a], $stimulationExpValues)) {
                        $print->print(1, 2);
                        $hasAllSelectedStimulations = false;
                    }
                }

                if(!$hasAllSelectedStimulations) {
                    continue;
                }
            }

            /* If there are more stimulations in the current experiment */
            if (count($stimulationSelectedValues) < count($stimulationExpValues)) {
                $print->print(1, 3);
                $otherStimulationExpIds[] = $experimentId;
                continue;
            }

            //echo $concentration.'<br/>';
            if (!$concentration) {
                if (array_diff($stimulationSelectedValues, $stimulationExpValues) === array_diff($stimulationExpValues, $stimulationSelectedValues)) {
                    $print->print(1, 4);
                    $insertExpId = true;
                } else {
                    $print->print(1, 5);
                    $insertExpId = false;
                }
            } else {
                for ($a = 0; $a < count($stimulationSelectedValues); $a++) {
                    $search = array_search($stimulationSelectedValues[$a], $stimulationExpValues);
                    if ($search === false) {
                        $insertExpId = false;
                        break;
                    } else if ($concentrationExpValues[$search] != $concentrationSelectedValues[$a]) {
                        $insertExpId = false;
                        break;
                    }
                }
            }

            if ($insertExpId) {
                $stimulationSelectedExpIds[] = $experimentId;
            } else {
                $otherStimulationExpIds[] = $experimentId;
            }
        }

        $this->stimulationSelectedExpIds = $stimulationSelectedExpIds;
        $this->otherStimulationExpIds = $otherStimulationExpIds;
        $this->stimulationSelectedValues = $stimulationSelectedValues;

        $print->print(1, $this->otherStimulationExpIds);
    }
}

?>