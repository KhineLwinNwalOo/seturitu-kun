<?php
include_once '../../common/constant.php';
$clientMail = COMPANY_E_MAIL;
?>

<?php if ($_SERVER['REQUEST_METHOD'] == 'GET'): ?>
    <style>
        div#maincontent div.message {
            background-color: #fcffef;
            border: 1px solid #3e69df;
            color: #3e69df;
            font-weight: bold;
            margin: 0 0 15px;
            padding: 5px;
            width: 450px;
            margin-top: 5px;
        }
    </style>

    <form id="frmUpdate" name="frmUpdate" action="index.php" method="post" onsubmit="return confirm('送信します。よろしいですか？');">
        <div class="formWrapper">
            <div class="formList">
                <div id="tbl_user">
                    <h3>オプションサービス</h3>
                    <table class="independent" style="width: 700px;">
                        <colgroup class="colgroupHead"></colgroup>
                        <colgroup class="colgroupBody"></colgroup>
                        <tbody>
                        <?php foreach ($optionServices as $i => $optionService): ?>
                            <tr>
                                <td class="colHead"><?php echo htmlspecialchars($optionService['name']) ?></td>
                                <td>
                                    <?php foreach ($optionService['options'] as $j => $option): ?>
                                        <div>
                                            <input type="radio" name="option_service[<?php echo $i; ?>][option]"
                                                   value="<?php echo $j; ?>"
                                                   <?php if ($j == 0): ?>checked="checked"<?php endif; ?>/>
                                            <label><?php echo htmlspecialchars($option) ?></label>
                                            <?php if (!empty($optionService['checkbox_options'])): ?>
                                                <?php if ($optionService['name'] == '求人' && $j == 1): ?>
                                                    <table style="margin-left:10px; border: none;">
                                                        <tr>
                                                            <?php foreach ($optionService['checkbox_options'] as $k => $checkboxOption): ?>
                                                            <td style="border: none;">
                                                                <input type="checkbox"
                                                                       name="option_service[<?php echo $i; ?>][checkbox_options][]"
                                                                       value="<?php echo $k; ?>"/>
                                                                <label><?php echo htmlspecialchars($checkboxOption) ?></label>
                                                            </td>
                                                            <?php if (($k + 1) % 4 == 0): ?></tr>
                                                        <tr><?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </table>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (!empty($optionService['discount'])): ?>
                                        <div
                                            class="message errorMessage"><?php echo nl2br($optionService['discount']) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="frm_button" class="button">
                    <input class="submit" type="submit" name="submit" value="　送　信　"/>
                </div>
            </div>
        </div>
    </form>
<?php else: ?>
    <p>メールを送信しました。</p>
    メールが届かない場合は、お手数ですが&nbsp;
    <a href="mailto:<?php echo $clientMail ?>"><?php echo $clientMail ?></a>
    &nbsp;までメールでお問い合わせください。
<?php endif; ?>
