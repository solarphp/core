<h2><?php echo $this->getText('HEADING_DELETE'); ?></h2>

<?php echo $this->partial('_record', $this->item); ?>

<?php echo $this->form()
                ->addProcess('delete_confirm')
                ->fetch();
?>
