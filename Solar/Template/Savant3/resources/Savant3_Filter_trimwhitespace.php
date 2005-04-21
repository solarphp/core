<?php

/**
* 
* Filter to remove extra white space within the text.
* 
* @package Savant3
* 
* @author Monte Ohrt <monte@ispi.net>
* 
* @author Contributions from Lars Noschinski <lars@usenet.noschinski.de>
* 
* @author Converted to a Savant3 filter by Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Filter_trimwhitespace.php,v 1.3 2005/03/07 14:49:46 pmjones Exp $
*
*/

/**
* 
* Filter to remove extra white space within the text.
* 
* @package Savant3
* 
* @author Monte Ohrt <monte@ispi.net>
* 
* @author Contributions from Lars Noschinski <lars@usenet.noschinski.de>
* 
* @author Converted to a Savant3 filter by Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Filter_trimwhitespace extends Savant3_Filter {
	
	
	/**
	* 
	* Removes extra white space within the text.
	* 
	* Trim leading white space and blank lines from template source after it
	* gets interpreted, cleaning up code and saving bandwidth. Does not
	* affect <pre>, <script>, or <textarea> blocks.
	* 
	* @access public
	* 
	* @param string $buffer The source text to be filtered.
	* 
	* @return string The filtered text.
	* 
	*/
	
	public static function filter($buffer)
	{
		// Pull out the script blocks
		preg_match_all("!<script[^>]+>.*?</script>!is", $buffer, $match);
		$_script_blocks = $match[0];
		$buffer = preg_replace(
			"!<script[^>]+>.*?</script>!is",
			'@@@SAVANT:TRIM:SCRIPT@@@',
			$buffer
		);
	
		// Pull out the pre blocks
		preg_match_all("!<pre[^>]*>.*?</pre>!is", $buffer, $match);
		$_pre_blocks = $match[0];
		$buffer = preg_replace(
			"!<pre[^>]*>.*?</pre>!is",
			'@@@SAVANT:TRIM:PRE@@@',
			$buffer
		);
	
		// Pull out the textarea blocks
		preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $buffer, $match);
		$_textarea_blocks = $match[0];
		$buffer = preg_replace(
			"!<textarea[^>]+>.*?</textarea>!is",
			'@@@SAVANT:TRIM:TEXTAREA@@@',
			$buffer
		);
	
		// remove all leading spaces, tabs and carriage returns NOT
		// preceeded by a php close tag.
		$buffer = trim(preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $buffer));
	
		// replace script blocks
		Savant3_Filter_trimwhitespace::_replace(
			"@@@SAVANT:TRIM:SCRIPT@@@",
			$_script_blocks,
			$buffer
		);
	
		// replace pre blocks
		Savant3_Filter_trimwhitespace::_replace(
			"@@@SAVANT:TRIM:PRE@@@",
			$_pre_blocks,
			$buffer
		);
	
		// replace textarea blocks
		Savant3_Filter_trimwhitespace::_replace(
			"@@@SAVANT:TRIM:TEXTAREA@@@",
			$_textarea_blocks,
			$buffer
		);
	
		return $buffer;
	}
	
	
	/**
	* 
	* Does a simple search-and-replace on the source text.
	* 
	* @access protected
	* 
	* @param string $search_str The string to search for.
	* 
	* @param string $replace Replace with this text.
	* 
	* @param string &$subject The source text.
	* 
	* @return string The text after search-and-replace.
	* 
	*/
	
	protected static function _replace($search_str, $replace, &$subject)
	{
		$_len = strlen($search_str);
		$_pos = 0;
		for ($_i=0, $_count=count($replace); $_i<$_count; $_i++) {
			if (($_pos=strpos($subject, $search_str, $_pos))!==false) {
				$subject = substr_replace($subject, $replace[$_i], $_pos, $_len);
			} else {
				break;
			}
		}
	}

}
?>