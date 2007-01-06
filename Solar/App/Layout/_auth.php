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
<?php if (Solar::registry('user')->auth->isValid()): ?>
    <p>
        <?php echo $this->getText('TEXT_AUTH_USERNAME') ?><br />
        <strong><?php echo $this->escape(Solar::registry('user')->auth->handle) ?></strong>
    </p>
    <?php
        echo $this->form()
                  ->submit(array(
                        'name'    => 'process',
                        'value'   => $this->getTextRaw('PROCESS_LOGOUT')
                  ))
                  ->fetch();
    ?>
<?php else: ?>
    <?php
        echo $this->form()
                  ->hidden(array(
                        'name'    => 'process',
                        'value'   => $this->getTextRaw('PROCESS_LOGIN')
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
                        'name'    => 'process',
                        'value'   => $this->getTextRaw('PROCESS_LOGIN')
                  ))
                  ->fetch();
    ?>
<?php endif; ?>
<p><?php
    $status = Solar::registry('user')->auth->getFlash('status_text');
    echo nl2br(wordwrap($this->escape($status), 20));
?></p>
