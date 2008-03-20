<h2><?php echo $this->getText('HEADING_DELETE'); ?></h2>

<ul>
    <?php foreach ($this->item as $key => $val) {
        echo "<li>" . $this->escape($key) . ": "
           . $this->escape($val)
           . "</li>\n";
    } ?>
</ul>

<?php echo $this->form()
                ->addProcess('delete_confirm')
                ->fetch()
?>
