<?php
/**
 *
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 */


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <?php echo $this->Html->charset(); ?>
    <title>事業目的を検索</title>
    <?php
	echo $this->Html->css('style');	
        
	echo $this->Html->script('rollover.js');
	
        echo $this->Html->script('jquery.min');
        echo $this->Html->script('jquery-1.11.1.min');
        
	echo $this->fetch('css');
	echo $this->fetch('script');
        echo $this->Html->script('jquery-ui.min');

    ?>
	
</head>
<body> 
    <?php echo $this->fetch('content'); ?>

</body>
</html>
