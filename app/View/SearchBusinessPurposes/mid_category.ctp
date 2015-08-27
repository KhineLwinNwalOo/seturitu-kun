<?php

if (!isset($midData)) throw new RuntimeException(__DIR__ . ':' . __FILE__ . ':' . __LINE__);

?>
<option value="">▼選択してください</option> 
<?php foreach ($midData as $value => $label) { ?>
<option value="<?php echo h($value) ?>"><?php echo h($label) ?></option> 
<?php } ?>