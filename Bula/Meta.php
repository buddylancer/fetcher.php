<?php

use Bula\Objects\TString;

const DIV = "|";

/**
 * Stop executing.
 * @param string $str
 */
function STOP($str)
{
    die(CAT($str));
}

function /* void */ PR(/* TString */ $str)
{
    print $str;
}

// Common functions
/**
 * Check whether an object is null.
 * @param object $value
 * @return boolean
 */
function NUL($value)
{
    return $value == null && !isset($value);
}

/**
 * Get integer value of any object.
 * @param object $value Input object.
 * @return integer Integer result.
 */
function INT($value)
{
    if (NUL($value))
        return 0;
    if ($value instanceof TString)
        return intval($value->getValue());
    return intval($value);
}

/**
 * Get float value of any object.
 * @param object $value Input object.
 * @return double Float result.
 */
function FLOAT($value)
{
    if (NUL($value))
        return 0;
    if ($value instanceof TString)
        return floatval($value->getValue());
    return floatval($value);
}

/**
 * Get string value of any object.
 * @param object $value Input object.
 * @return string String result.
 */
function STR($value)
{
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

/**
 * Check whether 2 object are equal.
 * @param object $value1 First object.
 * @param object $value2 Second object.
 * @return boolean
 */
function EQ($value1, $value2)
{
    if ($value1 instanceof TString)
        $value1 = $value1->getValue();
    if ($value2 instanceof TString)
        $value2 = $value2->getValue();
    return $value1 === $value2;
}

// TString functions
/**
 * Check whether an object is empty.
 * @param object $arg Input object.
 * @return boolean
 */
function BLANK($arg)
{
    if ($arg == null || !isset($arg))
        return true;
    if ($arg instanceof TString)
        return $arg->isEmpty();
    return EQ($arg, "");
}

/**
 * Get the length of an object (processed as string).
 * @param object $str Input object.
 * @return integer Length of resulting string
 */
function LEN($str)
{
    return BLANK($str) ? 0 : strlen($str);
}

/**
 * Concatenate any number of objects as string.
 * @param array $args Variable length array of parameters.
 * @return string Resulting string
 */
function CAT(/* ... */)
{
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

/**
 * Instantiate array of objects.
 * @param array $args Variable length array of parameters.
 * @return array
 */
function ARR(/* ... */)
{
    return func_get_args();
}

/**
 * Merge arrays.
 * @param array $args Variable length array of parameters.
 * 1st parameter - original array.
 * 2nd+ parameter - object(s) to merge into original array.
 * @return array Merged array
 */
function /* Object[] */ ADD(/* ... */)
{
    $numArgs = func_num_args();
    $arr = func_get_arg(0);
    for ($n = 1; $n < $numArgs; $n++) {
        $arg = func_get_arg($n);
        if (is_array($arg))
            foreach ($arg as $item)
                $arr = ADD($arr, $item);
        else
            $arr[] = $arg instanceof TString ? $arg->getValue() : $arg;
    }
    return $arr;
}

/**
 * Identify the size of any object.
 * @param object $val Input object.
 * @return integer Resulting size.
 */
function SIZE($val)
{
    if ($val == null)
        return 0;
    if (is_array($val))
        return sizeof($val);
    if (is_string($val))
        return strlen($val);
    return 0;
}

/**
 * Call obj.method(args) and return its result.
 * @param string $object Object instance.
 * @param string $method Method to call.
 * @param array $args Array of parameters.
 * @return object Result of method calling.
 */
function CALL($object, $method, $args)
{
    return call_user_func_array(array($object, $method), $args);
}
