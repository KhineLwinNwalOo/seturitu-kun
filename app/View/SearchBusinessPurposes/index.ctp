<script type="text/javascript" src="js/rollover.js"></script>

<div id="plan">
<h2>事業目的を検索</h2>
</div>

<div class = "search_top">
    事業目的別に下記カテゴリから検索できます
</div>

<div class="search_cont" id="searchForm">
    <table>
	<tbody>
	    <tr>
		<th>大カテゴリ</th>
		<td>
                    <select name="big_id">
			<option value="">▼選択してください</option>
		    </select>
                </td>
		<th>フリーワード</th>
		<td>
                    <input type="text" name="free_word" />
		</td>
	    </tr>
	    <tr>
		<th>中カテゴリ</th>
		<td>
		   <select name="mid_id">
			<option value="">▼選択してください</option>
		    </select>
		</td>
		<td class="allBusinesses" colspan="2">
		    <span class="txt10">
			※半角スペースで複数の検索用語が検索可能
			<br>
			                    ※「-」で除外検索用語が追加可能
		    </span>
		</td>
	    </tr>
	    <tr>
		<th>小カテゴリ</th>
		<td>
		    <select name="min_id">
			<option value="">▼選択してください</option>
		    </select>
		</td>
		<td colspan="2">
		    <a href="#">
			<img src="img/top/btn_search.png" alt="検索"  id="searchdata" name="searchdata">
		    </a>
		</td>
	    </tr>
	</tbody>
    </table>
</div>
<div id="search_catego"></div>

<div class="search_cont" id="category_list">
    <table>
        <tbody>
            <tr>
                <td colspan="4" class="caption_all">オリジナル事業目的一覧</td>
            </tr>
            <tr height="40px">
                <td class="caption_S">並び替え</td>
                <td class="caption_S"><img src="img/top/btn_item_delete.png" id="delete" alt="一括削除" class="hoverImg"></a></td>
                <td valign="top" class="caption_L">事業目的</td>
                <td valign="top" class="caption_M"><img src="img/top/btn_item_add.png" alt="項目を追加する" id="btn_add"></a></td>
            </tr>
        </tbody>
        <tbody id="selectTbody">
        </tbody>
    </table>
    
    <div id="scrolldiv" style="display: none;">
        <div class="dragdiv1" id="navcontainer" style="vertical-align:top;width:698px;overflow:hidden;float: left;">
            <ul id = "sortable" class="ui-sortable" style="padding:0px;list-style-type:none;width:690px;vertical-align:top;float:left;">
            </ul>
        </div>
        <div id="clickbutton" style="width:694px;align:center;">
            <table>
                <tr>
                    <td align="center">
                        <input type="checkbox" name="confirm" value="その他前各号に附帯する一切の業務」を追加する" checked="checked" />
                        <span id="comfirmtext" style="color:#4C3A26;font-size:10pt;">「その他前各号に附帯する一切の業務」を追加する。</span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <img src="img/top/btn_confirm_ov.png" alt="目的生成" name="btn_confirm"  border="0" id="btn_confirm" />
                    </th>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="search_cont_lineBold" id="finaldataBox" style="display: none;">
    <div class="ttl_completeBar">会社目的一覧</div>
    <ol class="comfrimul-class"></ol>
</div>



<script>(function($) {
    var elBigCat = '#searchForm :input[name=big_id]';
    var elMidCat = '#searchForm :input[name=mid_id]';
    var elMinCat = '#searchForm :input[name=min_id]';
    var search_btn = '#searchdata';
    var resultBox = '#search_catego';
    var elFreeWord ='#searchForm :input[name=free_word]';
    var resultBox = '#search_catego';
    var elSelectTbody = '#selectTbody';
    var elBtnAdd = "#btn_add";
    var elBtnDelete = "#delete";
    var elScrolldiv = "#scrolldiv";
    var elBtnConfirm = '#btn_confirm';
    var elFinaldataBox = '#finaldataBox';
    var elClickButtonBox = '#clickbutton';
    
    $(window).ready(function(){
        var url = '/business_purpose/SearchBusinessPurposes/big_category/';
        $(elBigCat).html('<option value="">検索中 ... </option>');
        $(elBigCat).load(url);
    });
    
    $(elBigCat).change(function(){
        var Bigid = $(this).val();
        if (Bigid) {
            var url = '/business_purpose/SearchBusinessPurposes/mid_category/' + escape(Bigid);
            $(elMidCat).html('<option value="">検索中 ... </option>');
            $(elMidCat).load(url);
            $(elMinCat).html('<option value="">▼選択してください</option>');
        } else {
            $(elMidCat).html('<option value="">▼選択してください</option>');
            $(elMinCat).html('<option value="">▼選択してください</option>');
        }	
    });
    $(elBigCat).change();
    
    $(elMidCat).change(function(){
        var Midid = $(this).val();
        if (Midid) {
            var url = '/business_purpose/SearchBusinessPurposes/min_category/' + escape(Midid);
            $(elMinCat).html('<option value="">検索中 ... </option>');
            $(elMinCat).load(url);
        } else {
            $(elMinCat).html('<option value="">▼選択してください</option>');
        }	
    });
    $(elMidCat).change();
    
    $(search_btn).click(function(){
        var freeWord = $(elFreeWord).val();
        var big_id = $(elBigCat).val();
        var mid_id = $(elMidCat).val();
        var min_id = $(elMinCat).val();
        
        var url = '';
        if (freeWord) {
            url = '/business_purpose/SearchBusinessPurposes/search_category?free_word=' + encodeURIComponent(freeWord);
        } else {
            url = '/business_purpose/SearchBusinessPurposes/search_category'
                + '?big_id=' + escape(big_id)
                + '&mid_id=' + escape(mid_id)
                + '&min_id=' + escape(min_id);
        }
        
        var callback = function(d, t, x) {
            // 再起ロードイベント設定
            $(resultBox).find('a').each(function(e, i) {
               $(this).click(function(){
                    var url = $(this).attr('href');
                    $(resultBox).load(url, callback);
                    return false;
                });
            });
            // チェック
            $(resultBox).find('tr').each(function(e, i) {
                $(this).find('td').click(function(e, o){
                    if ($(this).parent('tr').find(':input[name=confirm]').prop('checked')) {
                        $(this).parent('tr').find(':input[name=confirm]').prop('checked', false);
                    } else {
                        $(this).parent('tr').find(':input[name=confirm]').prop('checked', true);
                    }
                });
            });
            $(elSelectTbody).find(':input[name=confirm]').each(function(i, e){
                var checkVal = $(this).val();
                $(resultBox).find(':input[name=confirm]').each(function(i, e) {
                    var val = $(this).val();
                    if (checkVal === val) {
                        $(this).parents('tr').hide();
                    }
                });
            });
        };
        
	$(resultBox).load(url, callback);
    });
    
    $(elBtnAdd).click(function(){
        var tpl = '<tr>'
            + '<th><img src="img/top/icon_sort.png" alt="" /></th>'
            + '<th><input type="checkbox" checked="checked" name="confirm" value="{:label}" /></th>'
            + '<td colspan="2" class="resultData">{:label}</td>'
        + '</tr>';
        
        $(resultBox).find(':input[name=confirm]').each(function(i, e) {
            if (!$(this).prop('checked')) {
                return;
            }
            
            $(this).parents('tr').hide();
            $(this).prop('checked', false);
            
            var val = $(this).val();
            var tr = tpl.replace(/{:label}/g, val);
            $(elSelectTbody).append(tr);
            
            if ($(elSelectTbody).find('tr')) {
                $(elScrolldiv).show();
            } else {
                $(elScrolldiv).hide();
            }
            
            $(elSelectTbody).sortable({
                placeholder: "ui-sortable-placeholder"
            });
        });
    });
    
    $(elBtnDelete).click(function() {
        $(elSelectTbody).find(':input[name=confirm]').each(function(i, e){
            var flag = $(this).prop('checked');
            if (!flag) {
                return;
            }
            $(this).parents('tr').remove();
            
            var checkVal = $(this).val();
            $(resultBox).find(':input[name=confirm]').each(function(i, e) {
                var val = $(this).val();
                if (checkVal === val) {
                    $(this).parents('tr').show();
                }
            });
        });
    });
    
    $(elBtnConfirm).click(function() {
        var tpl = '<li style="list-style:number;font-size:16px;font-weight: bold;line-height: 1.5;text-align:left;">{:label}</li>';
        $(elFinaldataBox).find('ol').html('');
        $(elSelectTbody).find(':input[name=confirm]').each(function(i, e){
            var flag = $(this).prop('checked');
            if (!flag) {
                return;
            }
            var val = $(this).val();
            var html = tpl.replace(/{:label}/g, val);
            
            $(elFinaldataBox).find('ol').append(html);
        });
        $(elClickButtonBox).find(':input[name=confirm]').each(function(i, e){
            var flag = $(this).prop('checked');
            if (!flag) {
                return;
            }
            var val = $(this).val();
            var html = tpl.replace(/{:label}/g, val);
            
            $(elFinaldataBox).find('ol').append(html);
        });
        
        if ($(elFinaldataBox).find('li').length) {
            $(elFinaldataBox).show();
        } else {
            $(elFinaldataBox).hide();
        }
    });
})(jQuery);
</script>