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
 * @license LGPL
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
                'rank'         => 'ORDER_RANK',
                'rank_desc'    => 'ORDER_RANK_DESC',
                'tags'         => 'ORDER_TAGS',
                'tags_desc'    => 'ORDER_TAGS_DESC',
                'subj'         => 'ORDER_SUBJ',
                'subj_desc'    => 'ORDER_SUBJ_DESC',
            );
            
            // refresh the base link
            $uri->import();
            
            // add links
            foreach ($tmp as $key => $val) {
                if (Solar::get('order', 'created_desc') == $key) {
                    echo "<li class=\"selected\"><strong>"
                       . $this->getText($val)
                       . "</strong></li>\n";
                } else {
                    $uri->setQuery('order', $key);
                    echo "<li>" . $this->action($uri, $val) . "</li>\n";
                }
            }
        ?>
        </ul>
