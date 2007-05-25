<?php
/**
 * 
 * Partial layout template for the <body>.
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
<body>
    
    <div id="page">
        
        <div id="header" class="clearfix">
            <?php include $this->template('_header.php'); ?>
        </div><!-- end header -->
        
        <div id="content" class="clearfix">
            
            <div id="main">
                <?php echo $this->layout_content; ?>
                <hr />
            </div><!-- end main content -->
            
            <div id="sub">
                <?php include $this->template('_sub.php'); ?>
                <hr />
            </div><!-- end sub content -->
            
            <div id="local">
                <?php include $this->template('_local.php'); ?>
            </div><!--  end local nav -->
            
            <div id="nav">
                <?php include $this->template('_nav.php'); ?>
            </div><!-- end main nav -->
            
        </div><!-- end content -->
        
        <div id="footer" class="clearfix">
            <?php include $this->template('_footer.php'); ?>
        </div><!-- end footer -->
        
    </div><!-- end page -->
    
    <div id="extra1"><?php include $this->template('_extra1.php'); ?></div>
    <div id="extra2"><?php include $this->template('_extra2.php'); ?></div>
    
</body>
