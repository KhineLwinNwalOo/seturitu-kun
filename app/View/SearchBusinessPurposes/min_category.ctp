<?php

if (!isset($minData)) throw new RuntimeException(__DIR__ . ':' . __FILE__ . ':' . __LINE__);

?>
<option value="">▼選択してください</option> 
<?php foreach ($minData as $value => $label) {  ?>
<option value="<?php echo h($value) ?>"><?php echo h($label) ?></option> 
<?php } ?>