<html>
    <head>
        <title>Solar: Hello World</title>
    </head>
    <body>
        <p><?php $this->eprint($this->text) ?></p>
        <p><?php $this->eprint($this->code) ?></p>
        <ul>
            <?php foreach ($this->list as $code): ?>
            <li>
                <?php echo $this->actionLink("hello/main?code=$code", $code) ?>
                (<?php echo $this->actionLink("hello/rss?code=$code", 'RSS') ?>)
            </li>
            <?php endforeach ?>
        </ul>
    </body>
</html>