<div style="color: red;">
<?php while ($err = $this->error->pop()): ?>
	<p><?php echo $this->scrub($err['class::code'] . ' -- ' . $err['text']) ?></p>
<?php endwhile; ?>
</div>