<h2><?php echo $this->getText('HEADING_EDIT'); ?></h2>

<p>[ <?php echo $this->action(
    "/{$this->controller}/read/{$this->item->getPrimaryVal()}",
    'ACTION_READ');
?> ]</p>

<?php echo $this->form()
                ->auto($this->form)
                ->addProcessGroup(array(
                    'save',
                    'cancel',
                    'delete',
                ))
                ->fetch();
?>
