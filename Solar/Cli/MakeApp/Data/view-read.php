<h2><?php echo $this->getText('HEADING_READ'); ?></h2>

<p>[ <?php echo $this->action("/{$this->controller}", 'ACTION_BROWSE');?> ]</p>

<?php echo $this->partial('_record', $this->item); ?>

<p><?php echo $this->action(
    "/{$this->controller}/edit/{$this->item->getPrimaryVal()}",
    'ACTION_EDIT'
); ?></p>
