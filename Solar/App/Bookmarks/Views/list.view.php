<?php
/**
 * 
 * Savant3 template for lists of bookmarks (in XHTML).
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

/**
 * Include the header file.
 */
include $this->template('header.php');

// a basic link object
$link = Solar::factory('Solar_Uri');
?>
<div>
    <!-- the list of tags in use -->
    <div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
        <h2><?php $this->eprint($this->locale('TAG_LIST')) ?></h2>
        <table border="0" cellspacing="2" cellpadding="0"><?php
            
            // get a clean link
            $link->import();
            
            // clear out pathinfo, but reset the page to 1
            $link->clearInfo();
            $link->setQuery('page', 1);
            
            // set up baseline path info for a 'user' or 'tag' request?
            if ($this->owner_handle) {
                $baseInfo = "bookmarks/user/{$this->owner_handle}";
            } else {
                $baseInfo = "bookmarks/tag";
            }
            
            // build a series of table rows as links to tags
            $tmp = array();
            foreach ($this->tags_in_use as $tag => $count) {
                $link->setInfoString("$baseInfo/$tag");
                $tmp[] = "<tr><td align=\"right\">$count</td><td>" . $this->ahref($link->export(), $tag) . "</td></tr>";
            }
            echo implode("\n", $tmp);
        ?></table>
    </div>
    
    <!-- ordering -->
    <div style="float: right; margin: 12px; padding: 8px; border: 1px solid gray; background: #eee;">
        <h2><?php $this->eprint($this->locale('ORDERED_BY')) ?></h2>
        <p><?php
            $tmp = array(
                'created'      => $this->locale('ORDER_CREATED'),
                'created_desc' => $this->locale('ORDER_CREATED_DESC'),
                'rank'         => $this->locale('ORDER_RANK'),
                'rank_desc'    => $this->locale('ORDER_RANK_DESC'),
                'tags'         => $this->locale('ORDER_TAGS'),
                'tags_desc'    => $this->locale('ORDER_TAGS_DESC'),
                'subj'         => $this->locale('ORDER_SUBJ'),
                'subj_desc'    => $this->locale('ORDER_SUBJ_DESC'),
            );
            
            // refresh the base link
            $link->import();
            
            // add links
            foreach ($tmp as $key => $val) {
                if (Solar::get('order', 'created_desc') == $key) {
                    echo "<strong>";
                    $this->eprint($val);
                    echo "</strong><br />\n";
                } else {
                    $link->setQuery('order', $key);
                    echo $this->ahref($link->export(), $val) . "<br />\n";
                }
            }
        ?></p>
    </div>
    
    <!-- results -->
    <div style="float: left;">
        <!-- output the owner_handle and tag-search, if any -->
        <?php if ($this->owner_handle || $this->tags): ?>
            <h2><?php
                if ($this->owner_handle) $this->eprint($this->locale('USER') . ': ' . $this->owner_handle);
                if ($this->owner_handle && $this->tags) echo "<br />\n";
                if ($this->tags) $this->eprint($this->locale('TAGS') . ': ' . $this->tags);
            ?></h2>
        <?php endif ?>
        
        <!-- output the list of results -->
        <?php if (count($this->list)): ?>
            <?php foreach ($this->list as $item): ?>
            
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
                        $link->clearInfo();
                        $link->clearQuery();
                        $link->setInfoString("bookmarks/user/{$item['owner_handle']}");
                        echo $this->ahref($link->export(), $item['owner_handle']);
                    ?></span>
                    
                    <!-- tags and edit link -->
                    <br /><?php
                    
                        // tags
                        $this->eprint($this->locale('TAGGED'));
                        $tags = explode(' ', $item['tags']);
                        foreach ($tags as $tag) {
                            echo '&nbsp;';
                            $link->clearInfo();
                            $link->clearQuery();
                            $link->setInfoString("bookmarks/tag/$tag");
                            echo $this->ahref($link->export(), $tag);
                        }
                        
                        // edit link
                        if (Solar::shared('user')->auth->username == $item['owner_handle']) {
                            $back_info = Solar::server('PATH_INFO');
                            $back_qstr = Solar::server('QUERY_STRING');
                            $link->clearInfo();
                            $link->clearQuery();
                            $link->setInfoString("bookmarks/edit/{$item['id']}");
                            $link->setQuery('info', $back_info);
                            $link->setQuery('qstr', $back_qstr);
                            
                            echo '&nbsp;...&nbsp;' . $this->ahref($link->export(), 'edit');
                        }
                    ?>
                </p>
            <?php endforeach ?>
            
            <!-- previous / page-count / next -->
            <hr />
            <p><strong>[ <?php
                $link->import();
                $tmp = Solar::get('page', 1);
                $link->setQuery('page', $tmp - 1);
                $prev = $link->export();
                $link->setQuery('page', $tmp + 1);
                $next = $link->export();
                if ($this->page > 1) echo $this->ahref($prev, $this->locale('Solar::OP_PREVIOUS')) . ' | ';
                $this->eprint("Page {$this->page} of {$this->pages}");
                if ($this->page < $this->pages) echo ' | ' . $this->ahref($next, $this->locale('Solar::OP_NEXT'));
            ?> ]</strong></p>
            
        <?php else: ?>
            <p><?php $this->eprint($this->locale('NO_BOOKMARKS_FOUND')) ?></p>
        <?php endif ?>
        
        <?php if (Solar::shared('user')->auth->status_code == 'VALID'): ?>
            <hr />
            <p><?php
                $link->clearInfo();
                $link->clearQuery();
                $link->setInfoString("bookmarks/edit");
                $link->setQuery('id', '0');
                echo $this->ahref($link->export(), $this->locale('ADD_NEW_BOOKMARK'))
            ?></p>
            
            <p><?php
                $scheme = $link->scheme;
                $host = $link->host;
                $path = $link->path;
                $js = "javascript:location.href='$scheme://$host$path/bookmarks/edit?id=0&uri='+encodeURIComponent(location.href)+'&subj='+encodeURIComponent(document.title)";
                $this->eprint($this->locale('DRAG_THIS') . ': ');
                echo $this->ahref($js, $this->locale('QUICKMARK'));
            ?></p>
        <?php endif ?>
    </div>
</div>
<?php include $this->template('footer.php') ?>