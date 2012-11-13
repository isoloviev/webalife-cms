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
function smarty_modifier_price_parse($string)
{
	// check for decimal value
	if(!strpos($string, '.')) {
		$dec = intval($string);
	} else {
		$dec = substr($string,0,strpos($string, '.'));
		$cat = substr($string,strpos($string, '.'));
	}
	$repeat = 0; $i = 1;
	while($i <= strlen($dec)) 
	{
		if($repeat == 3) { $sp = ' '; $repeat = 1; } else { $sp = ''; $repeat++; }
		$nd = substr($dec, (strlen($dec) - $i), 1);
		$ndec = $nd.$sp.$ndec;
		$i++; 
	}
    return $ndec.$cat;
}

/* vim: set expandtab: */

?>
