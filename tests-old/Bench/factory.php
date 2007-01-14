<?php
$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$include_path = $dir . 'source';
ini_set('include_path', $include_path);

require 'Solar.php';
Solar::start(false);

$list = array(
    'Solar_Markdown',
    'Solar_Markdown_Plugin',
    'Solar_Markdown_Plugin_AmpsAngles',
    'Solar_Markdown_Plugin_BlockQuote',
    'Solar_Markdown_Plugin_Break',
    'Solar_Markdown_Plugin_CodeBlock',
    'Solar_Markdown_Plugin_CodeSpan',
    'Solar_Markdown_Plugin_EmStrong',
    'Solar_Markdown_Plugin_Encode',
    'Solar_Markdown_Plugin_Header',
    'Solar_Markdown_Plugin_HorizRule',
    'Solar_Markdown_Plugin_Html',
    'Solar_Markdown_Plugin_Image',
    'Solar_Markdown_Plugin_Link',
    'Solar_Markdown_Plugin_List',
    'Solar_Markdown_Plugin_Paragraph',
    'Solar_Markdown_Plugin_Prefilter',
    'Solar_Markdown_Plugin_StripLinkDefs',
    'Solar_Markdown_Plugin_Uri',
    'Solar_Markdown_Extra',
    'Solar_Markdown_Extra_DefList',
    'Solar_Markdown_Extra_EmStrong',
    'Solar_Markdown_Extra_Header',
    'Solar_Markdown_Extra_Table',
    'Solar_Markdown_Wiki',
    'Solar_Markdown_Wiki_ColorCodeBlock',
    'Solar_Markdown_Wiki_Escape',
    'Solar_Markdown_Wiki_Filter',
    'Solar_Markdown_Wiki_Header',
    'Solar_Markdown_Wiki_Link',
    'Solar_Markdown_Wiki_MethodSynopsis',
);

foreach ($list as $class) {
    $obj = Solar::factory($class);
}
?>