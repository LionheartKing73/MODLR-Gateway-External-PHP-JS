<?php

// class encapsulating server-side data validation functions
class validator {

// define properties
    private $_errorList;

    // define methods
    // constructor
    public function __construct() {
        $this->resetErrorList();
    }

    // initialize error list
    private function resetErrorList() {
        $this->_errorList = array();
    }

    // check whether input is empty
    public static function isEmpty($value) {
        return (!isset($value) || trim($value) == '') ? true : false;
    }

    // check valid length
	public static function isValidLength($value, $minLength, $maxLength = 0) {
	    if (strlen(trim($value)) >= $minLength) {
	        if ($maxLength != 0) {
	            if (strlen($value) <= $maxLength) {
	                return true;
	            }
	            return false;
	        }
	        return true;
	    }
	    return false;
	}

    // check whether input is a string
    public static function isString($value) {
        return is_string($value);
    }

    // check whether input is a number
    public static function isNumber($value) {
        return is_numeric($value);
    }

    // check whether input is an integer
    public static function isInteger($value) {
        return (intval($value) == $value) ? true : false;
    }

    // check whether input is alphabetic
    public static function isAlpha($value) {
        return preg_match('/^[a-zA-Z]+$/', $value);
    }

    // check whether input is within a numeric range
    public static function isWithinRange($value, $min, $max) {
        return (is_numeric($value) && $value >= $min && $value <= $max) ? true : false;
    }

    // check whether input is a valid email address
    public static function isEmailAddress($value) {
        return eregi('^([a-z0-9])+([\.a-z0-9_-])*@([a-z0-9_-])+(\.[a-z0-9_-]+)*\.([a-z]{2,6})$', $value);
    }

    // check if a value exists in an array
    public static function isInArray($array, $value) {
        return in_array($value, $array);
    }

    // add an error to the error list
    public function addError($field, $message) {
        $this->_errorList[] = array('field' => $field, 'message' => $message);
    }

    // check if errors exist in the error list
    public function hasError() {
        return (sizeof($this->_errorList) > 0) ? true : false;
    }

    // return the error list to the caller
    public function getErrorList() {
        return $this->_errorList;
    }

    // destructor
    // de-initialize error list
    public function __destruct() {
        unset($this->_errorList);
    }

}