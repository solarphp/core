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
 * @license LGPL
 * 
 * @version $Id$
 * 
 */
?>
                <!-- NEW ITEM -->
                <p>
                    <!-- title -->
                    <span style="font-size: 120%; font-weight: bold;"><?php
                        echo $this->anchor($item['uri'], $item['subj']);
                    ?></span>
                    
                    <!-- description -->
                    <?php if (trim($item['summ']) != ''): ?>
                    
                    <br /><?php echo nl2br(wordwrap($this->escape($item['summ']), 72)) ?>
                    <?php endif ?>
                    
                    <!-- rank and uri -->
                    <br /><span style="font-size: 90%;"><?php
                    
                        // rank
                        echo $this->getText('RANK') . ' ' . $this->escape($item['rank']);
                        
                        // from uri
                        echo ' ' . $this->getText('FROM') . ' ';
                        $cut = $item['uri'];
                        if (strlen($cut) > 72) {
                            // if longer than 72 chars, only show 64 chars, cut in the middle
                            $cut = substr($cut, 0, 48) . '...' . substr($cut, -16);
                        }
                        echo $this->escape($cut);
                    ?>
                    
                    <!-- date added by user -->
                    <br /><?php
                        echo $this->getText('ON') . ' ' . $this->escape($this->timestamp($item['created']) . ' ');
                        echo $this->getText('BY') . ' ';
                        echo $this->action("bookmarks/user/{$item['owner_handle']}", $item['owner_handle']);
                    ?></span>
                    
                    <!-- tags and edit link -->
                    <br /><?php
                    
                        // tags
                        echo $this->getText('TAGGED');
                        $tags = explode(' ', $item['tags']);
                        foreach ($tags as $tag) {
                            echo '&nbsp;' . $this->action("bookmarks/tag/$tag", $tag);
                        }
                        
                        // edit link
                        if (Solar::registry('user')->auth->handle == $item['owner_handle']) {
                            echo '&nbsp;...&nbsp;' . $this->action("bookmarks/edit/{$item['id']}", 'OP_EDIT');
                        }
                    ?>
                </p>
