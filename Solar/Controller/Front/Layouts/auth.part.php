<?php
/**
 * 
 * Solar_View partial template for the user authentication form.
 * 
 * @category Solar
 * 
 * @package Solar_Layout
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */
?>
<?php if (Solar::registry('user')->auth->status_code == 'VALID'): ?>
    <p>
        <?php echo $this->getText('Solar::TEXT_AUTH_USERNAME') ?><br />
        <strong><?php echo $this->escape(Solar::registry('user')->auth->username) ?></strong>
    </p>
    <?php
        echo $this->form()
                  ->hidden(array('name' => 'op', 'value' => 'logout'))
                  ->submit(array('name' => 'nil', 'value' => $this->getTextRaw('TEXT_LOGOUT')))
                  ->fetch();
    ?>
<?php else: ?>
    <?php
        echo $this->form()
                  ->hidden(array(
                        'name' => 'op',
                        'value' => 'login',
                  ))
                  ->text(array(
                        'name' => 'username',
                        'label' => $this->getTextRaw('LABEL_USERNAME'),
                        'attribs' => array('size' => 10),
                  ))
                  ->password(array(
                        'name' => 'password',
                        'label' => $this->getTextRaw('LABEL_PASSWORD'),
                        'attribs' => array('size' => 10)
                  ))
                  ->submit(array('name' => 'nil', 'value' => $this->getTextRaw('TEXT_LOGIN')))
                  ->fetch();
    ?>
<?php endif; ?>
<p><?php
    echo nl2br(wordwrap($this->escape(Solar::registry('user')->auth->getFlash('status_text')), 20));
?></p>
