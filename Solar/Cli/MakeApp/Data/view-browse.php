<h2><?php echo $this->getText('HEADING_BROWSE'); ?></h2>

<?php if (! $this->list): ?>

    <?php echo $this->getText('ERR_NO_RECORDS'); ?>

<?php else: ?>
    
    <?php
        $pager = $this->pager($this->list->getPagerInfo());
        echo $pager . "<br />";
    ?>

    <ol>
    <?php foreach ($this->list as $item): ?>
        <li><ul>
        <?php
            foreach ($item as $key => $val) {
                
                $label = $this->escape($key);
                
                if (is_scalar($val)) {
                    $value = $this->escape($val);
                } elseif (is_object($val)) {
                    $value = get_class($val);
                } else {
                    $value = gettype($val);
                }
                
                echo "<li>$label: $value</li>\n";
                
            }
            
            $id = $item->getPrimaryVal();
            
            echo "<li>"
               . $this->action("{$this->controller}/read/$id", 'ACTION_READ')
               . "</li>\n";
        ?>
        </ul></li>
    <?php endforeach; ?>
    </ol>

    <?php echo $pager . "<br />"; ?>

<?php endif; ?>

<p><?php echo $this->action("{$this->controller}/add", 'ACTION_ADD'); ?>
