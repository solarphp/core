<?php
/**
 * 
 * Partial template for a the list of "order" links.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_Bookmarks
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
?>
        <h3><?php echo $this->getText('ORDERED_BY') ?></h3>
        <ul>
        <?php
            $tmp = array(
                'created'      => 'ORDER_CREATED',
                'created_desc' => 'ORDER_CREATED_DESC',
                'pos'         => 'ORDER_POS',
                'pos_desc'    => 'ORDER_POS_DESC',
                'tags'         => 'ORDER_TAGS',
                'tags_desc'    => 'ORDER_TAGS_DESC',
                'subj'         => 'ORDER_SUBJ',
                'subj_desc'    => 'ORDER_SUBJ_DESC',
            );
            
            // an action uri processor
            $uri = Solar::factory('Solar_Uri_Action');
            
            // add links
            foreach ($tmp as $key => $val) {
                if ($this->order == $key) {
                    echo "<li class=\"selected\"><strong>"
                       . $this->getText($val)
                       . "</strong></li>\n";
                } else {
                    $uri->query['order'] = $key;
                    echo "<li>" . $this->action($uri, $val) . "</li>\n";
                }
            }
        ?>
        </ul>
