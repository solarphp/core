<?php
/**
 *
 * Helper for building JavaScript-powered applications.
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 * The abstract JsLibrary class
 */
Solar::loadClass('Solar_View_Helper_JsLibrary');

/**
 *
 * Helper for building JavaScript-powered applications.
 *
 * This is a fluent class; all method calls except fetch() return
 * $this, which means you can chain method calls for easier readability.
 *
 * @category Solar
 *
 * @package Solar_View
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
     * Array of JavaScript files needed to provide specified functionality
     *
     * @var array
     *
     */
    public $files;

    /**
     *
     * Array of inline JavaScript needed to provide specified functionality
     *
     * @var array
     *
     */
    public $scripts;

    /**
     *
     * Array of CSS selectors and their corresponding rules
     *
     * @var array
     *
     */
    public $selectors;

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
    }

    /**
     *
     * Build and return JavaScript for page header
     *
     * @return string Block of JavaScript with <script src ...> and inline script
     * chunks as needed.
     *
     */
    public function fetch()
    {
        $js = '';

        if (!empty($this->files)) {
            foreach ($this->files as $file) {
                $js .= '    ' . $this->_view->script($file) . "\n";
            }
        }

        // Loop through selectors for registered actions
        $load = '';
        $f = '';
        if (!empty($this->selectors)) {
            foreach ($this->selectors as $selector => $actions) {
                foreach ($actions as $a) {
                    switch ($a['type']) {
                        case 'effect':
                            $f .= "    \$\$('$selector').each(function(li){new Effect.{$a['name']}(li";
                            switch ($a['name']) {
                                case 'Scale':
                                    $f .= ", {$a['percent']}";
                                    break;

                                case 'MoveBy':
                                    $f .= ", {$a['y']}, {$a['x']}";
                                    break;

                                case 'Toggle':
                                    $f .= ", {$a['effect']}";
                                    break;

                                default:
                                    break;
                            }
                            if (!empty($a['options'])) {
                                $f .= ', ' . $this->_optionsForJs($a['options']);
                            }
                            $f .= ")});";
                            break;

                        default:

                            break;
                    }
                }
            }

            if ($f != '') {
                $load = "Event.observe(window, 'load', function() {\n";
                $load .= $f;
                $load .= "\n});\n";
                if ($this->scripts === null) {
                    $this->scripts = array();
                }
                $this->scripts[] = $load;
            }
        }




        if (!empty($this->scripts)) {
            $scripts = implode("\n\n", $this->scripts);
            $scripts = trim($scripts);
            $js .= $this->_view->inlineScript($scripts);
        }

        return $js;

    }

    /**
     *
     * Method interface
     *
     * @return Solar_View_Helper_Js
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
     * @param string $file Name of .js file to add to the header of the page.
     *
     */
    public function addFile($file)
    {
        if ($this->files === null) {
            $this->files = array();
        }
        if ($file !== null && !in_array($file, $this->files, true)) {
            $this->files[] = $file;
        }
        return $this;
    }

    /**
     *
     * Resets the helper entirely.
     *
     * @return Solar_View_Helper_Js
     *
     */
    public function reset()
    {
        $this->selectors = array();
        $this->files = array();
        $this->scripts = array();

        return $this;
    }



}
?>