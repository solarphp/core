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
                <!-- NEW ITEM -->
                <p>
                    <!-- title -->
                    <span style="font-size: 120%; font-weight: bold;"><?php
                        echo $this->anchor($uri, $subj);
                    ?></span>
                    
                    <!-- description -->
                    <?php if (trim($summ) != ''): ?>
                    
                    <br /><?php echo nl2br(wordwrap($this->escape($summ), 72)) ?>
                    <?php endif ?>
                    
                    <!-- pos and uri -->
                    <br /><span style="font-size: 90%;"><?php
                    
                        // pos
                        echo $this->getText('LABEL_POS') . ' ' . $this->escape($pos);
                        
                        // from uri
                        echo ' ' . $this->getText('LABEL_URI') . ' ';
                        $cut = $uri;
                        if (strlen($cut) > 72) {
                            // if longer than 72 chars, only show 64 chars, cut in the middle
                            $cut = substr($cut, 0, 48) . '...' . substr($cut, -16);
                        }
                        echo $this->escape($cut);
                    ?>
                    
                    <!-- date added by user -->
                    <br /><?php
                        echo $this->getText('LABEL_CREATED') . ' ' . $this->escape($this->timestamp($created) . ' ');
                        echo $this->getText('LABEL_OWNER_HANDLE') . ' ';
                        echo $this->action("bookmarks/user/$owner_handle", $owner_handle);
                    ?></span>
                    
                    <!-- tags and edit link -->
                    <br /><?php
                    
                        // tags
                        echo $this->getText('LABEL_TAGS');
                        $tags = explode(' ', $tags);
                        foreach ($tags as $tag) {
                            echo '&nbsp;' . $this->action("bookmarks/tag/$tag", $tag);
                        }
                        
                        // edit link
                        if (Solar::registry('user')->auth->handle == $owner_handle) {
                            echo '&nbsp;...&nbsp;' . $this->action("bookmarks/edit/$id", 'SUBMIT_EDIT');
                        }
                    ?>
                </p>
