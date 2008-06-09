<?php
/**
 * 
 * Partial template for local navigation layout in the Bookmarks app.
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
<?php
    // always show the authentication box
    echo $this->partial('_auth.php');
    
    // should we show the "order" and "tags" lists?
    if ($this->action == 'user' || $this->action == 'tag'):
?>

<div id="bookmarks-local">
    
    <?php if (Solar_Registry::get('user')->auth->isValid()): ?>
    
        <!-- Add a new bookmark -->
        <p><?php
            echo $this->action("bookmarks/add", 'ACTION_ADD');
        ?></p>
    
        <!-- QuickMark link -->
        <p><?php
            $uri = Solar::factory('Solar_Uri_Action');
            $uri->set('bookmarks/quick');
            $href = $uri->get(true);
            $js = "javascript:location.href='$href?uri='+encodeURIComponent(location.href)+'&subj='+encodeURIComponent(document.title)";
            echo $this->getText('DRAG_THIS') . ': ';
            echo $this->anchor($js, 'ACTION_QUICK');
        ?></p>
        
    <?php endif ?>
    
    <h3><?php echo $this->getText('HEADING_ORDER') ?></h3>
    <ul>
    <?php
        $tmp = array(
            'created'      => 'ORDER_CREATED',
            'created_desc' => 'ORDER_CREATED_DESC',
            'pos'          => 'ORDER_POS',
            'pos_desc'     => 'ORDER_POS_DESC',
            'subj'         => 'ORDER_SUBJ',
            'subj_desc'    => 'ORDER_SUBJ_DESC',
        );
    
        // an action uri processor
        $uri = Solar::factory('Solar_Uri_Action');
    
        // add links
        foreach ($tmp as $key => $val) {
            if ($this->order == $key) {
                echo "<li class=\"active\">"
                   . $this->getText($val)
                   . "</li>\n";
            } else {
                $uri->query['order'] = $key;
                echo "<li>" . $this->action($uri, $val) . "</li>\n";
            }
        }
    ?>
    </ul>
    
    <h3><?php echo $this->getText('HEADING_TAGLIST') ?></h3>
    <ul>
    <?php
        if ($this->action == 'user') {
            $action = "bookmarks/user/{$this->owner_handle}";
        } else {
            $action = "bookmarks/tag";
        }
    
        // build a series of links to tags
        $uri = Solar::factory('Solar_Uri_Action');
        unset($uri->query['page']);
        $tmp = array();
        foreach ($this->tags_in_use as $tag) {
            $uri->setPath("$action/{$tag->name}");
            $tmp[] = "<li>" . $this->action($uri, $tag->name) . " ({$tag->count})</li>";
        }
        echo implode("\n", $tmp);
    ?>
    </ul>
</div>

<?php endif; ?>