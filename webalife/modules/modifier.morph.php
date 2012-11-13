<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     price_parse<br>
 * Date:     Nov 08, 2006
 * Purpose:  catenate a value to a variable
 * Input:    string to parse
 * Example:  {$var|price_parse}
 * @author   Ivan Soloviev
 * @version 1.0
 * @param decimal
 * @return string
 */
function smarty_modifier_morph($n, $f1, $f2, $f5)
{
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}

/* vim: set expandtab: */

?>
