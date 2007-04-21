<?php
/**
 * 
 * Layout template with site navigation at the top, local navigation on the
 * right, and one column of main content.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<?php
    // put these styles at the top of the stack
    $style = array(
        'Solar/styles/cssfw/tools.css',
        'Solar/styles/cssfw/typo.css',
        'Solar/styles/cssfw/forms.css',
        'Solar/styles/cssfw/layout-navtop-localright.css',
        'Solar/styles/typo.css',
        'Solar/styles/forms.css',
        'Solar/styles/app/' . $this->controller . '.css',
    );
    
    // merge with overrides
    $this->layout_head['style'] = array_merge(
        (array) $style,
        (array) $this->layout_head['style']
    );
    
    // generate the <head>
    include $this->template('_head.php');
    
    // generate the <body>
    include $this->template('_body.php')
?>

</html>