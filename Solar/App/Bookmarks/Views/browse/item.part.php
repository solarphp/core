                <!-- NEW ITEM -->
                <p>
                    <!-- title -->
                    <span style="font-size: 120%; font-weight: bold;"><?php
                        echo $this->ahref($item['uri'], $item['subj']);
                    ?></span>
                    
                    <!-- description -->
                    <?php if (trim($item['summ']) != ''): ?>
                    
                    <br /><?php echo nl2br(wordwrap($this->escape($item['summ']), 72)) ?>
                    <?php endif ?>
                    
                    <!-- rank and uri -->
                    <br /><span style="font-size: 90%;"><?php
                        // rank
                        $this->eprint($this->locale('RANK') . ' ' . $item['rank']);
                        
                        // from uri
                        $this->eprint(' ' . $this->locale('FROM') . ' ');
                        $cut = $item['uri'];
                        if (strlen($cut) > 72) {
                            // if longer than 72 chars, only show 64 chars, cut in the middle
                            $cut = substr($cut, 0, 48) . '...' . substr($cut, -16);
                        }
                        $this->eprint($cut);
                    ?>
                    
                    <!-- date added by user -->
                    <br /><?php
                        $this->eprint($this->locale('ON') . ' ' . $this->date($item['created']) . ' ');
                        $this->eprint($this->locale('BY') . ' ');
                        echo $this->actionLink("bookmarks/user/{$item['owner_handle']}", $item['owner_handle']);
                    ?></span>
                    
                    <!-- tags and edit link -->
                    <br /><?php
                    
                        // tags
                        $this->eprint($this->locale('TAGGED'));
                        $tags = explode(' ', $item['tags']);
                        foreach ($tags as $tag) {
                            echo '&nbsp;';
                            echo $this->actionLink("bookmarks/tag/$tag", $tag);
                        }
                        
                        // edit link
                        if (Solar::registry('user')->auth->username == $item['owner_handle']) {
                            echo '&nbsp;...&nbsp;' . $this->actionLink("bookmarks/edit/{$item['id']}", 'edit');
                        }
                    ?>
                </p>
