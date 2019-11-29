<?php

/**
 * Builds an array with all the provided variables, use named parameters to make an associative array
 * <pre>
 *  * rest : any number of variables, strings or anything that you want to store in the array
 * </pre>
 * Example :
 * <code>
 * {array(a, b, c)} results in array(0=>'a', 1=>'b', 2=>'c')
 * {array(a=foo, b=5, c=array(4,5))} results in array('a'=>'foo', 'b'=>5, 'c'=>array(0=>4, 1=>5))
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    1.2.3
 * @date       2016-10-15
 * @package    Dwoo
 * @param Dwoo_Compiler $compiler
 * @param array         $rest
 * @return string
 */
function Dwoo_Plugin_array_compile(Dwoo_Compiler $compiler, array $rest=array())
{
	$out = array();
	foreach ($rest as $key => $value) {
		if (!is_numeric($key) && !strstr($key, '$this->scope')) {
			$key = "'".$key."'";
		}
		$out[] = $key.'=>'.$value;
	}
	return 'array('.implode(', ', $out).')';
}