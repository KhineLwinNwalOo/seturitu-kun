<?php
/**
 *
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 */
$prjHelper = $this->Project;

if (!isset($title_for_layout))				throw new RuntimeException(__DIR__ . ':' . __FILE__ . ':' . __LINE__);
if (!$prjHelper instanceof ProjectHelper)	throw new RuntimeException(__DIR__ . ':' . __FILE__ . ':' . __LINE__);

$prjHelper->setTitleForLayout($title_for_layout);

// テキスト
$textTitleName				= $prjHelper->getTextTitleName();
$textSystemName				= $prjHelper->getTextSystemName();
$textCopyright				= $prjHelper->getTextCopyright();
// システム
$authSessionFlashMessage	= $prjHelper->getAuthSessionFlashMessage();

?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $textTitleName; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1><?php echo $textSystemName; ?></h1>
		</div>
		<div id="content">
			<?php echo $authSessionFlashMessage; ?>	
			<?php echo $this->fetch('content'); ?> 
		</div>
		<div id="footer">
			<?php echo $textCopyright; ?>
		</div>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
