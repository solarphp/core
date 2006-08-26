<?php
/**
 *
 * JsPrototype Ajax helper class.
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
 * The parent JsPrototype class
 */
Solar::loadClass('Solar_View_Helper_JsPrototype');

/**
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Clay Loveless <clay@killersoft.com>
 *
 */
class Solar_View_Helper_JsPrototype_Ajax extends Solar_View_Helper_JsPrototype {

    /**
     *
     * Reference name for the type of JavaScript object this class produces
     *
     * @var string
     *
     */
    protected $_type = 'JsPrototype_Ajax';


    /**
     *
     * Keys of values which should be dequoted by Solar_Json
     *
     * @var array
     *
     */
    protected $_dequote;

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
     * Method interface
     *
     * @return object Solar_View_Helper_JsPrototype_Ajax
     *
     */
    public function ajax()
    {
        return $this;
    }

    /**
     *
     * Fetch method called by Solar_View_Helper_Js. Feeds generated JavaScript
     * back into a single block of JavaScript to be inserted into a page
     * header.
     *
     * @param string $selector Object or CSS Selector to generate scripts for
     *
     * @param array $action Action details array created by a JsPrototype_Ajax
     * method.
     *
     * @param bool $object Whether or not method is being called for an object.
     * Result affects how (and if) selector is used in generated script.
     *
     * @return string Generated JavaScript.
     *
     * @todo Determine how/if the fetch method should be used in this class
     *
     */
    public function fetch($selector, $action, $object = false)
    {

        $out = '';
        /*
        switch ($action['method']) {

            case 'Updater':

                break;

            case 'PeriodicalUpdater':

                break;

            default:
                break;

        }
        */
        return $out;
    }

    /**
     *
     * Creates a string of JavaScript which, when executed, will create one
     * instance of Prototype's Ajax.Request object. That object will call
     * the given $url using the given $options. Note: when executed, a JavaScript
     * 'onCreate' event is triggered.
     *
     * It is worth noting that the given $url is subject to the browser's
     * security settings. In many cases, that means the browser **will not**
     * fetch the URL if it is not from the same host (domain) of the current
     * page. It may make sense for you to request only local URLs to avoid
     * security issues related to the user's browser configuration.
     *
     * The supported $options array may have the following key/value pairs:
     *
     * `method`
     * :_(string)_ Method of the HTTP request. Default `post`
     *
     * `parameters`
     * :_(string)_ The URL-formatted list of values appended to the URL,
     * such as `foo=bar&baz=dib&zim=gaz`. Default `''`
     *
     * `asynchronous`
     * :_(bool)_ Indicates if the request will be made asyncronously.
     * Default `true`
     *
     * `postBody`
     * :_(string)_ URL-formatted content submitted with a post-method request.
     * Default `null`
     *
     * `requestHeaders`
     * :_(array)_ Associative array of HTTP headers to be sent along with
     * the request. Example:
     *
     *       array(
     *         'X-Foo' => 'Bar',
     *         'X-Request-Baz' => 'dib'
     *       );
     *
     * Default `null`
     *
     * `on[XXXX]`
     * :_(string)_ Custom JavaScript function to be called when the respective
     * event/status is reached during the Ajax call. __Note:__ Prototype's
     * `Ajax.Request` class defines five events that are rasied during an Ajax
     * operation: 'Uninitialized', 'Loading', 'Loaded', 'Interactive', and
     * 'Complete'. Any of those events, or any HTTP status code, such as
     * `on403`, can be defined. Default `undefined`
     *
     * `onSuccess`
     * :_(string)_ Custom function to be called with the Ajax call completes
     * successfully. The function used should accept two parameters. The first
     * will contain the `XMLHttpRequest` object that is carrying the Ajax operation,
     * and the second will carry the evaluated `X-JSON` response HTTP header, if
     * any was present in the response. Default `undefined`
     *
     * `onFailure`
     * :_(string)_ Custom function to be called with the Ajax call completes
     * with an error. The function used should accept two parameters. The first
     * will contain the `XMLHttpRequest` object that is carrying the Ajax operation,
     * and the second will carry the evaluated `X-JSON` response HTTP header, if
     * any was present in the error response. Default `undefined`
     *
     * `onException`
     * :_(string)_ Custom function to be called when an exceptional condition
     * happens on the client side of the Ajax call, such as an invalid response
     * or invalid arguments. The function used should accept two parameters. The
     * first will contain the `Ajax.Request` object that wraps the Ajax operation,
     * and the second will contain the exception object. Default `undefined`
     *
     * `_deQuote`
     * :_(array)_ Internal option. An array of keys whose values should
     * not be quoted in the returned JavaScript string. See Solar_Json::encode()
     * for more details.
     *
     * @param string $url URL to call using the given options. The JavaScript
     * `onCreate` event will be raised during the constructor.
     *
     * @param array $options Array of Ajax options to configure the request.
     *
     * @return string Generated JavaScript
     *
     */
    public function request($url, $options = array())
    {
        $json = Solar::factory('Solar_Json');

        $options = $this->_tweakAjaxOptions($options);

        $js = "new Ajax.Request('{$url}',";
        $js .= $json->encode($options, $this->_dequote);
        $js .= ');';

        return $js;
    }

    /**
     *
     * Use this method when the requested URL returns HTML that you want to
     * inject directly into a specific element on the page. You can also
     * use this method when the URL returns `<script>` blocks that should
     * be evaluated upon arrival. __Note:__ You must set the `evalScripts`
     * option to `true` for returned `<script>` blocks to be evaluated.
     *
     * For complete description of available options, see the request()
     * documentation. Options available only to the `Ajax.Updater` method are:
     *
     * `insertion`
     * :_(string)_ A class that will determine how the new content will be
     * inserted. It can be `Insertion.Before`, `Insertion.Top`,
     * `Insertion.Bottom`, or `Insertion.After`. Default `undefined`.
     * __Applies only to `Ajax.Updater` objects__.
     *
     * `evalScripts`
     * :_(boolean)_ Determines if script blocks in the response, if any,
     * will be evaluated when the response arrives. __Applies only to
     * `Ajax.Updater` objects__. Default `false`
     *
     * @param string $selector CSS Selector of block to update with results
     *
     * @param string $url URL to call using the given options. The JavaScript
     * `onCreate` event will be raised during the constructor.
     *
     * @param array $options Array of Ajax options to configure the request.
     *
     * @return string Generated JavaScript
     *
     */
    public function updater($selector, $url, $options = array())
    {

        $json = Solar::factory('Solar_Json');

        $options = $this->_tweakAjaxOptions($options);

        // passing a new Selector().params.id lets us make sure Ajax.Updater
        // has something to work with. Ajax.Updater takes the first argument and
        // passes to $()
        $js = "new Ajax.Updater(new Selector('{$selector}').params.id, '{$url}',";
        $js .= $json->encode($options, $this->_dequote);
        $js .= ');';

        return $js;

    }

    /**
     *
     * Tweak Ajax.* options to make them more JSON-ready. Remove internal options,
     * convert assoc arrays to lists as needed, etc.
     *
     * @param array $options Array of Ajax.* options (see request() method)
     *
     * @return array Adjusted options.
     *
     */
    protected function _tweakAjaxOptions($options)
    {
        // Massage options for Protaculous
        $this->_dequote = array();
        if (isset($options['_deQuote'])) {
            $this->_dequote = $options['_deQuote'];
            unset($options['_deQuote']);
        }

        if (isset($options['requestHeaders'])) {
            // convert from user-friendly assoc array to clunky odd-even list
            $rh = array();
            foreach ($options['requestHeaders'] as $header => $val) {
                $rh[] = $header;
                $rh[] = $val;
            }
            $options['requestHeaders'] = $rh;
        }

        return $options;
    }

}
?>