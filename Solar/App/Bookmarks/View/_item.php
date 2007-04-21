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
 */
?>

            <li class="bookmark-item">
                <span><?php
                    $title = $uri;
                    if (strlen($title) > 72) {
                        // if longer than 72 chars, only show 64 chars, cut in the middle
                        $title = substr($title, 0, 48) . ' ... ' . substr($title, -16);
                    }
                    echo $this->anchor($uri, $subj, array('title' => $title));
                ?></span>
                <ul><?php if (trim($summ) != ''): ?>
                    
                    <li class="summ"><?php
                        echo nl2br(wordwrap($this->escape($summ), 72));
                    ?></li>
                    <?php endif ?>
                    
                    <li class="pos"><span><?php
                            echo $this->getText('LABEL_POS');
                        ?></span> <?php echo $this->escape($pos);
                    ?></li>
    
                    <li class="created">
                        <span><?php
                            echo $this->getText('LABEL_CREATED');
                        ?></span> <?php echo $this->timestamp($created); ?>
            
                        <span><?php
                            echo $this->getText('LABEL_OWNER_HANDLE');
                        ?></span> <?php echo $this->action(
                            "bookmarks/user/$owner_handle",
                            $owner_handle);
                        ?>
            
                    </li>
    
                    <li class="tags">
                        <span><?php echo $this->getText('LABEL_TAGS');
                        ?></span><?php
                            $tags = explode(' ', $tags);
                            foreach ($tags as $tag) {
                                echo ' ' . $this->action("bookmarks/tag/$tag", $tag);
                            }
                        ?>
            
                    </li><?php
                    if (Solar::registry('user')->auth->handle == $owner_handle): ?>
                    
                    <li class="edit">
                        <?php echo $this->action("bookmarks/edit/$id", 'PROCESS_EDIT'); ?>
                    </li>
                    <?php endif; ?>
                    
                </ul>
            </li>