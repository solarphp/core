<?php
/**
 *
 * Helper for building JavaScript-powered applications.
 *
 * @category Solar
 *
 * @package Solar_View_Helper_Js
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 *
 * Helper for building JavaScript-powered applications.
 *
 * This is a fluent class; all method calls except fetch() return
 * $this, which means you can chain method calls for easier readability.
 *
 * @category Solar
 *
 * @package Solar_View_Helper_Js
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Solar_View_Helper_Js extends Solar_View_Helper_JsLibrary {

    /**
     *
     * User-provided configuration values.
     *
     * @var array
     *
     */
    protected $_Solar_View_Helper_Js = array(
        'attribs' => array(),
    );

    /**
     *
     * Array of JavaScript files needed to provide specified functionality.
     *
     * @var array
     *
     */
    public $files;

    /**
     *
     * Array of CSS files required by a JavaScript class.
     *
     * @var array
     *
     */
    public $styles;

    /**
     *
     * Array of inline JavaScript needed to provide specified functionality.
     *
     * @var array
     *
     */
    public $scripts;

    /**
     *
     * Array of CSS selectors and their corresponding rules.
     *
     * @var array
     *
     */
    public $selectors;

    /**
     *
     * Array of JavaScript objects and their corresponding rules.
     *
     * @var array
     *
     */
    public $objects;

    /**
     *
     * Constructor.
     *
     * @param array $config User-provided configuration values.
     *
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->reset();
    }

    /**
     *
     * Build and return JavaScript for page header.
     *
     * @return string Block of JavaScript with <script src ...> for view-defined
     * script requirements.
     *
     */
    public function fetchFiles()
    {
        $js = '';

        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $js .= '    ' . $this->_view->script($file) . "\n";
            }
        }

        return $js;
    }

    /**
     *
     * Build and return list of CSS files for page header.
     *
     * @return string Block of HTML with <style> tags for JavaScript-defined
     * style requirements.
     *
     */
    public function fetchStyles()
    {
        $str = '';

        if (!empty($this->styles)) {
            foreach ($this->styles as $style) {
                $str .= '    ' . $this->_view->style($style) . "\n";
            }
        }

        return $str;
    }

    /**
     *
     * Returns all defined inline scripts. This is a separate fetch method
     * so that any/all external (standalone JS file) scripts required by the
     * App or the View that the inline scripts depend on can be loaded prior to
     * the output of the inline script.
     *
     * @return string All inline JavaScripts
     *
     */
    public function fetchInline()
    {
        $js = '';

        // Loop through selectors for registered actions
        $f = '';
        if (!empty($this->selectors)) {

            foreach ($this->selectors as $selector => $actions) {

                // Wrap in selector loop
                $f .= "    \$\$('$selector').each(function(el){\n";

                foreach ($actions as $a) {
                    // add in loop with indent for easy reading
                    $f .= '        '
                       . trim($this->_view->getHelper($a['type'])->fetch($selector, $a))
                       . "\n";
                }

                // Close off selector loop wrapper
                $f .= "    });\n";

            }

            // Register window onload event to process CSS selector actions
            if ($f != '') {
                $f = "function() {\n" . rtrim($f) . "\n}";
                $this->_view->JsPrototype()->event->observeObject('window', 'load', $f);
            }
        }

        // Loop through registered object actions/observers
        if (!empty($this->objects)) {
            foreach ($this->objects as $object => $actions) {
                foreach ($actions as $a) {
                    $this->scripts[] = $this->_view->getHelper($a['type'])->fetch($object, $a, true);
                }
            }
        }

        // Gather all registered scripts for output
        if (!empty($this->scripts)) {
            $scripts = implode("\n\n", $this->scripts);
            $scripts = trim($scripts);
            $js = $this->_view->inlineScript($scripts);
        }

        return $js;
    }

    /**
     *
     * Fluent interface.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function js()
    {
        return $this;
    }

    /**
     *
     * Add the specified JavaScript file to the Helper_Js file list
     * if it's not already present.
     *
     * Paths should be releative to the 'path' configuration value for the
     * corresponding Solar_View_Helper class.
     *
     * @param mixed $file Name of .js file to add to the header of the page, or
     * (optionally) an array of files to add.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function addFile($file)
    {
        if ($this->files === null) {
            $this->files = array();
        }

        if (is_array($file)) {
            $this->files = array_merge($this->files, $file);
        } elseif ($file !== null && !in_array($file, $this->files, true)) {
            $this->files[] = $file;
        }

        return $this;
    }

    /**
     *
     * Add the specified CSS file to the Helper_Js styles list
     * if it's not already present.
     *
     * Paths should be releative to the 'styles' configuration value for the
     * corresponding Solar_View_Helper class.
     *
     * @param mixed $file Name of .css file to add to the header of the page, or
     * (optionally) an array of files to add.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function addStyle($file)
    {
        if ($this->files === null) {
            $this->files = array();
        }

        if (is_array($file)) {
            $this->styles = array_merge($this->styles, $file);
        } elseif ($file !== null && !in_array($file, $this->styles, true)) {
            $this->styles[] = $file;
        }

        return $this;
    }

    /**
     *
     * Add the script defined in $src to the inline scripts array.
     *
     * @param string $src A snippet of JavaScript to be inserted in the head
     * of a document.
     *
     * @return Solar_View_Helper_Js
     */
    public function addInlineScript($src)
    {
        $this->scripts[] = $src;
        return $this;
    }

    /**
     *
     * Resets the helper entirely.
     *
     * @return object Solar_View_Helper_Js
     *
     */
    public function reset()
    {
        $this->selectors = array();
        $this->objects = array();
        $this->files = array();
        $this->scripts = array();
        $this->styles = array();

        return $this;
    }


}
