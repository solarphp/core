<h2><?php echo $this->getText('HEADING_READ'); ?></h2>

<p>[ <?php echo $this->action("/{$this->controller}", 'ACTION_BROWSE');?> ]</p>

<ul>
    <?php foreach ($this->item as $key => $val) {
        echo "<li>" . $this->escape($key) . ": "
           . $this->escape($val)
           . "</li>\n";
    } ?>
</ul>

<p><?php echo $this->action(
    "/{$this->controller}/edit/{$this->item->getPrimaryVal()}",
    'ACTION_EDIT');
?></p>