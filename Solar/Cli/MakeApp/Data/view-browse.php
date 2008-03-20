<h2><?php echo $this->getText('HEADING_BROWSE'); ?></h2>

<ol>
<?php foreach ($this->list as $item): ?>
    <li><ul>
    <?php
        foreach ($item as $key => $val) {
            echo "<li>" . $this->escape($key) . ": "
               . $this->escape($val)
               . "</li>\n";
        }
    
        $id = $item->getPrimaryVal();
        
        echo "<li>"
           . $this->action("{$this->controller}/read/$id", 'ACTION_READ')
           . "</li>\n";
    ?>
    </ul></li>
<?php endforeach; ?>
</ol>

<p><?php echo $this->action("{$this->controller}/add", 'ACTION_ADD'); ?>