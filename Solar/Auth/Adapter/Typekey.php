<?php
/**
 * 
 * Authentication adapter for TypeKey.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Daiji Hirata <hirata@uva.ne.jp>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Htpasswd.php 1449 2006-07-09 01:28:55Z pmjones $
 * 
 */

/**
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 *
 * Authentication adapter for TypeKey.
 * 
 * Requires that PHP have been compiled using "--enable-bcmath" or
 * '--with-gmp'.
 * 
 * Based largely on PEAR Auth_Typekey proposal by Daiji Hirata, in
 * particular the DSA signature verification methods.  See the original
 * code at <http://www.uva.ne.jp/Auth_TypeKey/Auth_TypeKey.phps>.
 * 
 * Developed for, and then donated by, Mashery.com <http://mashery.com>.
 * 
 * For more info on TypeKey, see:
 * 
 * * http://www.sixapart.com/typekey/api
 * 
 * * http://www.sixapart.com/movabletype/docs/tk-apps
 * 
 * @category Solar
 *
 * @package Solar_Auth
 * 
 * @author Daiji Hirata <hirata@uva.ne.jp>
 * 
 * @author Paul M. Jones <pmjones@mashery.com>
 *
 */
class Solar_Auth_Adapter_Typekey extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-defined configuration.
     * 
     * Keys are:
     * 
     * : \\token\\ : (string) The TypeKey "site token" id against which
     *   authentication requests will be made.
     * 
     * : \\window\\ : (int) The signature should have been generated
     *   within this many seconds of "now". Default is 10 seconds, to
     *   allow for long network latency periods.
     * 
     */
    protected $_Solar_Auth_Adapter_Typekey = array(
        'token'  => null,
        'window' => 10,
    );
    
    /**
     * 
     * A reconstructed "message" to be verified for validity.
     * 
     * @var string
     * 
     * @see Solar_Auth_Adapter_Typekey::isLoginValid()
     * 
     * @see Solar_Auth_Adapter_Typekey::_verify()
     * 
     */
    protected $_msg;
    
    /**
     * 
     * Public key as fetched from TypeKey server.
     * 
     * @var string
     * 
     * @see Solar_Auth_Adapter_Typekey::_fetchKeyData()
     * 
     */
    protected $_key;
    
    /**
     * 
     * DSA signature extracted from login attempt $_GET vars.
     * 
     * @var string
     * 
     * @see Solar_Auth_Adapter_Typekey::isLoginValid()
     * 
     * @see Solar_Auth_Adapter_Typekey::_verify()
     * 
     */
    protected $_sig;
    
    /**
     * 
     * Use bcmath or gmp extension to verify signatures?
     * 
     * @var string
     * 
     */
    protected $_ext;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config)
    {
        if (extension_loaded('gmp')) {
            $this->_ext = 'gmp';
        } elseif (extension_loaded('bcmath')) {
            $this->_ext = 'bc';
        } else {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => '(bcmath || gmp)')
            );
        }
        
        parent::__construct($config);
    }
    
    /**
     * 
     * Is the current page-load a login request?
     * 
     * We can tell because there will be certain GET params in place:
     * 
     * <code>
     * &ts=1149633028
     * &email=clay%40mashery.com
     * &name=mashery
     * &nick=Solar
     * &sig=PBG7mN48V9f83hOX5Ao+X9GbmUU=:maoKWgIZpcF1qVFUHf8GbFooAFc=
     * </code>
     * 
     * @return bool
     * 
     */
    public function isLoginRequest()
    {
        return ! empty($_GET['email']) &&
               ! empty($_GET['name']) &&
               ! empty($_GET['nick']) &&
               ! empty($_GET['ts']) &&
               ! empty($_GET['sig']);
    }
    
    /**
     * 
     * Fetches the public key data from TypeKey.
     * 
     * The URI used is "http://www.typekey.com/extras/regkeys.txt".
     * 
     * @return array An array with keys 'p', 'q', 'g', and 'pub_key'
     * as extracted from the fetched key string.
     * 
     * @todo We should cache the results of this so we don't hit
     * their server all the time.
     * 
     */
    protected protected function _fetchKeyData()
    {
        $src = file_get_contents('http://www.typekey.com/extras/regkeys.txt');
        $lines = explode(' ', trim($src));
        foreach ($lines as $line) {
            $val = explode('=', $line);
            $info[$val[0]] = $val[1];
        }
        return $info;
    }
    
    /**
     * 
     * Is the current login attempt valid?
     * 
     * The signature must pass DSA verification, and the timestamp of
     * the signature must be within the time-window (this is to avoid
     * replay attacks).
     * 
     * If the login is valid, this populates the handle, email, and
     * name properties of this adapter.
     * 
     * @return bool
     * 
     */
    public function isLoginValid()
    {
        // get data from the login.
        $email = $_GET['email'];
        $name  = $_GET['name'];
        $nick  = $_GET['nick'];
        $ts    = $_GET['ts'];
        
        // get the signature values from the login. note that the sig
        // values need to have pluses converted to spaces because
        // urldecode() doesn't do that for us. thus, we have to re-
        // encode, the raw-decode it.
        $this->_sig = rawurldecode(urlencode($_GET['sig']));
        
        // re-create the message for signature comparison.
        // <email>::<name>::<nick>::<ts>
        $this->_msg = "$email::$name::$nick::$ts";

        // get the TypeKey public key data
        $this->_key = $this->_fetchKeyData();
        
        // verify credentials and time-window
        $window = time() - $ts <= $this->_config['window'];
        $verify = $this->_verify();
        
        // are both conditions matched?
        $result = $window && $verify;
        if ($result) {
            // save user data
            $this->_handle  = $name;  // username
            $this->_email   = $email; // email
            $this->_moniker = $nick;  // display name
            $this->_uri     = null;   // not supported by TypeKey
        } else {
            // clear out user data
            $this->reset();
        }
        
        // done!
        return $result;
    }
    
    /**
     * 
     * Verifies login using either GMP or bcmath functions.
     * 
     * @return bool True if the message signature is verified using the
     * DSA public key.
     * 
     */
    protected function _verify()
    {
        $method = '_verify_' . $this->_ext;
        return $this->$method();
    }
    
    /**
     * 
     * DSA verification using GMP.
     * 
     * Uses $this->_msg, $this->_key, and $this->_sig as the data
     * sources.
     * 
     * @return bool True if the message signature is verified using the
     * DSA public key.
     * 
     */
    protected function _verify_gmp()
    {
        $msg = $this->_msg;
        $key = $this->_key;
        $sig = $this->_sig;
        
        list($r_sig, $s_sig) = explode(":", $sig );
        $r_sig = base64_decode($r_sig);
        $s_sig = base64_decode($s_sig);
        
        foreach ($key as $i => $v) {
            $key[$i] = gmp_init($v);
        }
        
        $s1 = gmp_init($this->_gmp_bindec($r_sig));
        $s2 = gmp_init($this->_gmp_bindec($s_sig));
        
        $w = gmp_invert($s2, $key['q']);
        
        $hash_m = gmp_init('0x' . sha1($msg));

        $u1 = gmp_mod(gmp_mul($hash_m, $w), $key['q']);
        $u2 = gmp_mod(gmp_mul($s1, $w), $key['q']);
        
        $v = gmp_mod( 
                gmp_mod( 
                    gmp_mul(
                        gmp_powm($key['g'], $u1, $key['p']), 
                        gmp_powm($key['pub_key'], $u2, $key['p'])
                    ), 
                    $key['p']
                ), 
             $key['q']
        );
        
        if (gmp_cmp($v, $s1) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * Converts a binary to a decimal value using GMP functions.
     * 
     * @param string $bin The original binary value string.
     *
     * @return string Decimal value string converted from $bin.
     *
     */
    protected function _gmp_bindec($bin) 
    {
        $dec = gmp_init(0);
        while (strlen($bin)) {
            $i = ord(substr($bin, 0, 1));
            $dec = gmp_add(gmp_mul($dec, 256), $i);
            $bin = substr($bin, 1);
        }
        return gmp_strval($dec);
    }

    /**
     * 
     * DSA verification using bcmath.
     * 
     * Uses $this->_msg, $this->_key, and $this->_sig as the data
     * sources.
     * 
     * @return bool True if the message signature is verified using the
     * DSA public key.
     * 
     */
    protected function _verify_bc()
    {
        $msg = $this->_msg;
        $key = $this->_key;
        $sig = $this->_sig;
        
        list($r_sig, $s_sig) = explode(':', $sig);

        $r_sig = base64_decode($r_sig);
        $s_sig = base64_decode($s_sig);

        $s1 = $this->_bc_bindec($r_sig);
        $s2 = $this->_bc_bindec($s_sig);

        $w = $this->_bc_invert($s2, $key['q']);
        $hash_m = $this->_bc_hexdec(sha1($msg));

        $u1 = bcmod(bcmul($hash_m, $w), $key['q']);
        $u2 = bcmod(bcmul($s1, $w), $key['q']);

        $v = bcmod( 
                 bcmod( 
                     bcmul(
                         bcmod(bcpowmod($key['g'], $u1, $key['p']), $key['p']),
                         bcmod(bcpowmod($key['pub_key'], $u2, $key['p']), $key['p'])),
                     $key['p']),
                 $key['q']);

        return (bool) bccomp($v, $s1) == 0;
    }

    /**
     * 
     * Converts a hex value string to a decimal value string using
     * bcmath functions.
     * 
     * @param string $hex The original hex value string.
     *
     * @return string Decimal string converted from $hex.
     *
     */
    protected function _bc_hexdec($hex)
    {
        $dec = '0';
        while (strlen($hex)) {
            $i = hexdec(substr($hex, 0, 4));
            $dec = bcadd(bcmul($dec, 65536), $i);
            $hex = substr($hex, 4);
        }
        return $dec;
    }

    /**
     * 
     * Converts a binary value string to a decimal value string using
     * bcmath functions.
     * 
     * @param string $bin The original binary value string.
     *
     * @return string Decimal value string converted from $bin.
     *
     */
    protected function _bc_bindec($bin)
    {
        $dec = '0';
        while (strlen($bin)) {
            $i = ord(substr($bin, 0, 1));
            $dec = bcadd(bcmul($dec, 256), $i);
            $bin = substr($bin, 1);
        }
        return $dec;
    }

    /**
     * 
     * Inverts two values using bcmath functions.
     * 
     * @param string $x
     * 
     * @param string $y
     *
     * @return string The inverse of $x and $y.
     *
     */
    protected function _bc_invert ($x, $y) 
    {
        while (bccomp($x, 0)<0) { 
            $x = bcadd($x, $y);
        }
        $r = $this->_bc_exgcd($x, $y);
        if ($r[2] == 1) {
            $a = $r[0];
            while (bccomp($a, 0)<0) {
                $a = bcadd($a, $y);
            }
            return $a;
        } else {
            return false;
        }
    }

    /**
     *
     * Finds the extended greatest-common-denominator of two values
     * using bcmath functions.
     * 
     * @param string $x
     * 
     * @param string $y
     *
     * @return array Extended GCD of $x and $y.
     *
     */
    protected function _bc_exgcd ($x, $y) 
    {
        $a0 = 1; $a1 = 0;
        
        $b0 = 0; $b1 = 1;
        
        $c = 0;
        
        while($y > 0) {
            $q = bcdiv($x, $y, 0);
            $r = bcmod($x, $y);
            
            $x = $y; $y = $r;
            
            $a2 = bcsub($a0, bcmul($q, $a1));
            $b2 = bcsub($b0, bcmul($q, $b1));
            
            $a0 = $a1; $a1 = $a2;
            
            $b0 = $b1; $b1 = $b2;
        }
        
        return array($a0, $b0, $x);
    }
}
?>