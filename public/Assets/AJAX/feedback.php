<?php
    require_once '../../../Private/includes.php';





$json = array('Status' => 0);
//print_modified_r($_POST);
if(isset($_POST['pageSelection']) && isset($_POST['type']) && isset($_POST['subject']) && isset($_POST['body'])) {
    $json['ValidInput'] = 1;

    $feedback = new FeedBack($_POST['pageSelection'], $_POST['type'], $_POST['subject'], $_POST['body']);

    if(!$feedback->isValidInput()) {
        $json['ValidInput'] = 0;
        $json['Errors']     = $feedback->getErrors();
    }
}
echo json_encode($json);


class FeedBack {

    public $_pageSelectionValues   = array('Data Comparison', 'Gene Page');
    public $_typeValues            = array('Issue', 'Comment', 'Suggested Feature');

    private $_pageSelection;
    private $_type;
    private $_subject;
    private $_body;

    private $_validated = false;
    private $_errors     = array();

    public function __construct($pageSelection, $type, $subject, $body)
    {
        $this->_pageSelection   = $pageSelection;
        $this->_type            = $type;
        $this->_subject         = $subject;
        $this->_body            = $body;

        $this->validateInput();

        $this->storeInput();
    }

    public function isValidInput() {
        return $this->_validated;
    }

    public function getErrors() {
        return $this->_errors;
    }

    private function validateInput() {
        $this->_errors = array();

        //Page Selection
        if(!in_array($this->_pageSelection, $this->_pageSelectionValues)) {
            $this->_errors[] = 'Page Selection is incorrect';
        }

        //Type
        if(!in_array($this->_type, $this->_typeValues)) {
            $this->_errors[] = 'Feedback Type is incorrect';
        }

        //Subject
        if($this->_subject == '') {
            $this->_errors[] = 'Subject must be filled out';
        }

        if(strlen($this->_subject) > 40) {
            $this->_errors[] = 'Subject cannot exceed 40 characters';
        }

        //Body
        if($this->_body == '') {
            $this->_errors[] = 'Body must be filled out';
        }

        $this->_validated = (count($this->_errors) == 0) ? true : false;
    }

    private function storeInput() {
        if($this->_validated == false) return;

        global $db;

        $req = $db->prepare('INSERT INTO feedback (PageSelection, Type, Subject, Body) VALUES(:PageSelection, :Type, :Subject, :Body)');
        $req->execute(array('PageSelection' => $this->_pageSelection,
                            'Type'          => $this->_type,
                            'Subject'       => $this->_subject,
                            'Body'          => $this->_body));
    }






}