<?php

// Below are meta functions, that can be replaced when converting to other language (Java, C#)

use Bula\Objects\TString;

const /* TString */ DIV = "|";

function /* void */ STOP(/* TString */ $str) {
    die($str instanceof TString ? $str->getValue() : $str);
}

function /* void */ PR(/* TString */ $str) {
    print $str;
}

// Common functions
function /* Boolean */ NUL(/* Object */ $value) {
    return $value == null && !isset($value);
}

function /* Boolean */ YES(/* Object */ $value) {
    return !NUL($value) && ($value == 1 || $value == true);
}

/**
 * Convert Object to Integer.
 * @param Object $value Input value.
 * @return Integer Resulting Integer.
 */
function /* Integer */ INT(/* Object */ $value) {
    if (NUL($value))
        return 0;
    if ($value instanceof TString)
        return intval($value->getValue());
    return intval($value);
}

function /* Float */ FLOAT(/* Object */ $value) {
    if (NUL($value))
        return 0;
    if ($value instanceof TString)
        return floatval($value->getValue());
    return floatval($value);
}

function /* TString */ STR(/* Object */ $value) {
    if (NUL($value))
        return null;
    if ($value instanceof TString)
        return $value;
    else if ($value === true)
        return "True";
    else if ($value === false)
        return "False";
    return CAT($value);
}

function /* Boolean */ EQ($value1, $value2) {
    if ($value1 instanceof TString)
        $value1 = $value1->getValue();
    if ($value2 instanceof TString)
        $value2 = $value2->getValue();
    return $value1 === $value2;
}

// TString functions
function /* Boolean */ BLANK($arg) {
    if ($arg == null || !isset($arg))
        return true;
    if ($arg instanceof TString)
        return $arg->isEmpty();
    return EQ($arg, "");
}

function /* Integer */ LEN(/* TString */ $str) {
    return BLANK($str) ? 0 : strlen($str);
}

function /* TString */ CAT(/* ... */) {
    $args = func_get_args();
    /* TString */ $result = "";
    foreach ($args as $arg) {
        if (is_object($arg) && !($arg instanceof TString))
            debug_print_backtrace();
        $arg = ($arg instanceof TString) ? $arg->getValue() : "" . $arg; // make it string
        if ($arg != "")
            $result .= $arg;
    }
    return $result;
}

function /* Integer */ IXOF($str, $what, $off = 0) {
    $pos = strpos($str, $what, $off);
    return $pos === false ? -1 : $pos;
}

function /* Integer */ RIXOF($str, $what, $off = 0) {
    $pos = strrpos($str, $what, $off);
    return $pos === false ? -1 : $pos;
}

//function /* void */ PR($str) {
//    echo $str instanceof TString ? $str->getValue() : $str;
//}

function /* Object[] */ ARR(/* ... */) {
    return func_get_args();
    //$result = array();
    //foreach ($args as $arg)
    //    $result[] = $arg;
    //return $result;
}

function /* Object[] */ ADD(/* ... */) {
    $num_args = func_num_args();
    $arr = func_get_arg(0);
    for ($n = 1; $n < $num_args; $n++) {
        $arg = func_get_arg($n);
        if (is_array($arg))
            foreach ($arg as $item)
                $arr = ADD($arr, $item);
        else
            $arr[] = $arg instanceof TString ? $arg->getValue() : $arg;
    }
    return $arr;
}

function /* Integer */ SIZE(/* Object */ $val) {
    if ($val == null)
        return 0;
    if (is_array($val))
        return sizeof($val);
    if (is_string($val))
        return strlen($val);
    return 0;
}

function /* Object */ CALL(/* Object */ $Object, /* TString */ $Method, /* Object[] */ $Args) {
    //$Object->$Method($Args);
    return call_user_func_array(array($Object, $Method), $Args);
}

