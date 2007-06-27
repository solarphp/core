<?php
/**
 * 
 * Generic application view for displaying major server errors (that is,
 * uncaught exceptions).
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
?>
<html>

    <head>
        <title>Server Error</title>
    </head>
    
    <body>
        
        <h1>Server Error</h1>

        <p>The application could not complete your request.  This is our
            fault, not yours.</p>
        
        <p>See below for more information about the application error, and
            please inform the system administrator so we can fix it.</p>
        
        <ul>
        <?php
            foreach ((array) $this->errors as $err) {
                echo "<li>";
                if ($err instanceof Exception) {
                    echo "<pre>";
                    echo $err;
                    echo "</pre>";
                } else {
                    echo $this->getText($err);
                }
                echo "</li>\n";
            }
        ?>
        </ul>
    
        <p>Thank you for your patience while we work this out.</p>
        
    </body>
    
</html>