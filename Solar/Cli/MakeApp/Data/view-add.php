<h2><?php echo $this->getText('HEADING_ADD'); ?></h2>

<?php echo $this->form()
                ->auto($this->form)
                ->addProcessGroup(array(
                    'save',
                    'cancel',
                ))
                ->fetch();
?>
