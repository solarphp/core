<?php
/**
 * 
 * Partial layout template for the "local" (local navigation) div.
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

<?php echo $this->partial('_auth.php'); ?>

<h2 class="accessibility">Local</h2>
<ul class="clearfix">
    <?php
        foreach ((array) $this->layout_local as $key => $val) {
            echo "<li";
            if ($this->action == $key) {
                echo ' class="active"';
            }
            echo '>';
            echo $this->action("{$this->controller}/$key", $val);
            echo "</li>\n";
        }
    ?>
</ul>
