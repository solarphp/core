<?php
/**
 *
 * Simple "hello world" application with actions, views, and localization ...
 * all with a little Ajax goodness
 *
 * @category Solar
 *
 * @package Solar_App
 *
 * @subpackage Solar_App_HelloAjax
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 * Application controller class.
 */
Solar::loadClass('Solar_App');

/**
 *
 * Simple "hello world" application with actions, views, and localization ...
 * all with a little Ajax goodness
 *
 * @category Solar
 *
 * @package Solar_App
 *
 * @subpackage Solar_App_HelloAjax
 *
 */
class Solar_App_HelloAjax extends Solar_App {

    /**
     *
     * The default controller action.
     *
     * @var string
     *
     */
    protected $_action_default = 'main';

    /**
     *
     * The list of available locale codes.
     *
     * @var array
     *
     */
    public $list = array('en_US', 'es_ES', 'fr_FR');

    /**
     *
     * The requested locale code.
     *
     * @var string
     *
     */
    public $code;


    /**
     *
     * The translated text.
     *
     * @var string
     *
     */
    public $text;

    /**
     *
     * Overrides the general Solar_App setup so that we don't need a
     * database connection. This is because we want the simplest
     * possible hello-world example.
     *
     * Thanks, Clay Loveless, for suggesting this.
     *
     * @return void
     *
     */
    protected function _setup()
    {
        // register a Solar_User object if not already.
        // this will trigger the authentication process.
        if (! Solar::isRegistered('user')) {
            Solar::register('user', Solar::factory('Solar_User'));
        }

        // set the layout title
        $this->layout_title = get_class($this);
    }

    /**
     *
     * Resets to the requested locale code and shows translated output
     * as an HTML file.
     *
     * @param string $code The requested locale code.
     *
     * @return void
     *
     */
    public function actionMain($code = 'en_US')
    {
        // set the code from input
        $this->code = $code;

        // reset the locale strings to the new code
        Solar::setLocale($this->code);

        // set the translated text
        $this->text = $this->locale('TEXT_HELLO_WORLD');

        // tell the site layout what title to use
        $this->layout_title = 'Solar: Hello Ajax!';
    }

    /**
     *
     * Resets to the requested locale code and shows translated output
     * as an RSS file.
     *
     * @param string $code The requested locale code.
     *
     * @return void
     *
     */
    public function actionRss($code = 'en_US')
    {
        // set the code from input
        $this->code = $code;

        // reset the locale strings to the new code
        Solar::setLocale($this->code);

        // set the translated text
        $this->text = $this->locale('TEXT_HELLO_WORLD');

        // turn off the site layout so RSS is not mangled
        $this->_layout = false;
    }
}
