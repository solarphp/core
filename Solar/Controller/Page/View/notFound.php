<?php
/**
 * 
 * Template used for action not found exceptions.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

$this->head()->setTitle($this->getTextRaw('HEADING_NOT_FOUND'));
?>
<h1><?php echo $this->getText('HEADING_NOT_FOUND'); ?></h1>

<p><?php echo $this->getText('TEXT_NOT_FOUND'); ?></p>

<?php include $this->template('_errors'); ?>
