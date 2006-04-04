<?php

/**
 * 
 * Helper to generate an <img ... /> tag.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Helper to generate an <img ... /> tag.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */

class Solar_View_Helper_Image extends Solar_View_Helper {
	
	
	/**
	* 
	* Outputs an <img ... /> tag.
	* 
	* @param string $src The public href to the image.
	* 
	* @param array $attribs Additional attributes for the tag.
	* 
	* @return string An <img ... /> tag.
	* 
	* @todo Add automated height/width calculation?
	* 
	*/
	
	public function image($src, $attribs = array())
	{
	    unset($attribs['src']);
	    if (empty($attribs['alt'])) {
	        $attribs['alt'] = basename($src);
	    }
	    
	    return '<img src="' . $this->_view->publicHref($src) . '"'
	         . $this->_view->attribs($attribs) . ' />';
	}
}
?>