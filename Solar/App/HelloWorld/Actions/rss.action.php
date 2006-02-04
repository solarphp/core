<?php
// get the locale code, default is en_US
$this->code = $this->_query('code', 'en_US');

// reset the locale strings to the new code
Solar::shared('locale')->reset($this->code);

// set the translated text, and we're done
$this->text = $this->locale('TEXT_HELLO_WORLD');
?>