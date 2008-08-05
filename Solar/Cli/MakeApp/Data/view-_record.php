<?php
/**
 * 
 * Generic record display.
 * 
 * @var $record {:model_class}_Record from the calling code.
 * 
 */

// the name of the model this record came from
$model_name = $record->getModel()->model_name;

// the CSS class to use for the <dl> tag
$css_class = $this->escape($model_name) . '-record';

// the CSS id to use for the <dl> tag
$css_id = $this->escape(
    $model_name . "-record-" . $record->getPrimaryVal()
);

// the table cols to show
$cols = array_keys($record->getModel()->table_cols);
?>
<dl id="<?php echo $css_id ?>" class="<?php echo $css_class ?>"><?php

    // output each table column and value as a dt/dd pair
    foreach ($cols as $col) {
        
        // localized label
        $locale_key = strtoupper("LABEL_$col");
        echo "\n    <dt>" . $this->getText($locale_key) . "</dt>\n";
        
        // value
        $val = $record->$col;
        if ($val === null) {
            echo "    <dd><em>null</em></dd>\n";
        } else {
            echo "    <dd>" . $this->escape($val) . "</dd>\n";
        }
    }
    
?></dl>
