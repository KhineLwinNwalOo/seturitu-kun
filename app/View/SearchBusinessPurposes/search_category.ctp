

<style>
    .txtGray {color:#999;}
    
    .paging .current { font-size: 12px; font-weight: bold; color: #fff; border: 1px solid #00A073; background-color: #00A073; }
    .paging .current { padding: 3px 6px; text-align: center; }

    .disabled { font-size: 12px; color: #67B221; text-decoration: none; border: 1px solid #00A073; background-color: #FFF; }
    .disabled { padding: 3px 6px; text-align: center; }

</style>
<?php
$ctlHelper = $this->SearchBusinessPurpose;

if (!isset($dataPaginate))                              throw new RuntimeException(__DIR__ . ':' . __FILE__ . ':' . __LINE__);
if (!$ctlHelper instanceof SearchBusinessPurposeHelper)	throw new RuntimeException(__DIR__ . ':' . __FILE__ . ':' . __LINE__);

$ctlHelper->setDataPaginate($dataPaginate);

$paginatorCounter   = $ctlHelper->getPaginatorCounter();
// ページ遷移リンク
$paginatorLinks     = $ctlHelper->getPaginatorLinks();

?>
<div class="pageList">
    <div class="paging"><?php echo $paginatorLinks; ?>&nbsp;<?php echo $paginatorCounter; ?></div>
</div>
<div class="search_cont">
    <table id="second_table">
        <tbody>
            <tr>
                <td class="require_l">チェック項目</td>
                <td colspan="2" class="require_r">検索用語該当データ</td>
            </tr>
            <?php for ($i = 0, $cnt = $ctlHelper->getDataPaginateCount(); $i < $cnt; ++$i) {
                $textContent    = $ctlHelper->getTextContent($i);
                $textParentNavi = $ctlHelper->getTextParentNavi($i);
            ?>
            <tr>
                <th><input type="checkbox" name="confirm" value="<?php echo $textContent?>" /></th>
                <td class="resultData"><?php echo $textContent?></td>
                <td class="category"><?php echo $textParentNavi; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>