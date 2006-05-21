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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
?>
<?php if (Solar::registry('user')->auth->status == 'VALID'): ?>
    <p>
        <?php echo $this->getText('Solar::TEXT_AUTH_USERNAME') ?><br />
        <strong><?php echo $this->escape(Solar::registry('user')->auth->handle) ?></strong>
    </p>
    <?php
        echo $this->form()
                  ->submit(array(
                        'name'    => 'submit',
                        'value'   => $this->getTextRaw('SUBMIT_LOGOUT')
                  ))
                  ->fetch();
    ?>
<?php else: ?>
    <?php
        echo $this->form()
                  ->hidden(array(
                        'name'    => 'submit',
                        'value'   => $this->getTextRaw('SUBMIT_LOGIN')
                  ))
                  ->text(array(
                        'name'    => 'handle',
                        'label'   => $this->getTextRaw('LABEL_HANDLE'),
                        'attribs' => array('size' => 10),
                  ))
                  ->password(array(
                        'name'    => 'passwd',
                        'label'   => $this->getTextRaw('LABEL_PASSWD'),
                        'attribs' => array('size' => 10)
                  ))
                  ->submit(array(
                        'name'    => 'submit',
                        'value'   => $this->getTextRaw('SUBMIT_LOGIN')
                  ))
                  ->fetch();
    ?>
<?php endif; ?>
<p><?php
    echo nl2br(wordwrap($this->escape(Solar::registry('user')->auth->getFlash('status_text')), 20));
?></p>
