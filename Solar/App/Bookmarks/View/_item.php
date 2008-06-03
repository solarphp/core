<?php
/**
 * 
 * Partial template for a single bookmark item.
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
 * @var Solar_Model_Bookmarks_Record $item
 * 
 */
?>
            
            <li class="bookmark-item">
                <span><?php
                    $title = $item->uri;
                    if (strlen($title) > 72) {
                        // if longer than 72 chars, only show 64 chars, cut in the middle
                        $title = substr($title, 0, 48) . ' ... ' . substr($title, -16);
                    }
                    echo $this->anchor($item->uri, $item->subj, array('title' => $title));
                ?></span>
                <ul><?php if (trim($item->summ) != ''): ?>
                    
                    <li class="summ"><?php
                        echo nl2br(wordwrap($this->escape($item->summ), 72));
                    ?></li>
                    <?php endif ?>
                    
                    <li class="pos"><span><?php
                            echo $this->getText('LABEL_POS');
                        ?></span> <?php echo $this->escape($item->pos);
                    ?></li>
    
                    <li class="created">
                        <span><?php
                            echo $this->getText('LABEL_CREATED');
                        ?></span> <?php echo $this->timestamp($item->created); ?>
            
                        <span><?php
                            echo $this->getText('LABEL_OWNER_HANDLE');
                        ?></span> <?php echo $this->action(
                            "bookmarks/user/{$item->owner_handle}",
                            $item->owner_handle);
                        ?>
            
                    </li>
                    
                    <li class="tags">
                        <span><?php echo $this->getText('LABEL_TAGS'); ?></span>
                        <?php if ($item->tags) {
                            foreach ($item->tags as $tag) {
                                echo $this->action("bookmarks/tag/{$tag->name}", $tag->name);
                                echo "                            \n";
                            }
                        } ?>
                        
                    </li>
                        
                    <?php if (Solar_Registry::get('user')->auth->handle == $item->owner_handle): ?>
                    <li class="edit">
                        <?php echo $this->action("bookmarks/edit/{$item->id}", 'PROCESS_EDIT'); ?>
                    </li>
                    <?php endif; ?>
                    
                </ul>
            </li>