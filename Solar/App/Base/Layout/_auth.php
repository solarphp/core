<?php
/**
 * 
 * Partial layout template for the user authentication div.
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
<?php if (Solar_Registry::exists('user')): ?>
    <div id="auth">
        <?php if (Solar_Registry::get('user')->auth->isValid()): ?>
            <p>
                <?php echo $this->getText('TEXT_AUTH_USERNAME'); ?><br />
                <strong><?php
                    echo $this->escape(Solar_Registry::get('user')->auth->handle);
                ?></strong>
            </p>
            <?php
                echo $this->form()
                          ->addProcess('logout', array('id' => 'logout-process'))
                          ->fetch();
            ?>
        <?php else: ?>
            <?php
                echo $this->form()
                          ->text(array(
                                'name'    => 'handle',
                                'label'   => 'LABEL_HANDLE',
                                'attribs' => array('size' => 10, 'id' => 'login-handle'),
                          ))
                          ->password(array(
                                'name'    => 'passwd',
                                'label'   => 'LABEL_PASSWD',
                                'attribs' => array('size' => 10, 'id' => 'login-password')
                          ))
                          ->addProcess('login', array('id' => 'login-process'))
                          ->fetch();
            ?>
        <?php endif; ?>
        <p><?php
            $status = Solar_Registry::get('user')->auth->getStatusText();
            echo nl2br(wordwrap($this->escape($status), 20));
        ?></p>
    </div>
<?php endif; ?>