<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

include_once dirname(__FILE__) . '/../common/include.ini';

_LogDelete();

_DB_Open();

$condition = array(
    'usr_cmp_rel_user_id' => NOLOGIN_USER_ID,
);

$relations = _DB_GetList('tbl_user_company_relation', $condition);
$expiry = strtotime('-3 day');
_Log('expiry = ' . date('Y/m/d H:i:s', $expiry));

foreach ($relations as $relation) {
    _Log(print_r($relation, true));

    $ts = strtotime($relation['usr_cmp_rel_create_date']);
    if ($ts > $expiry) {
        _Log('期限切れ前なので削除しない');
        continue;
    }
    _Log('期限切れなので削除する');

    // 期限切れとなった各テーブルの会社情報レコードを削除する

    // tbl_user_company_relation
    $condition = array(
        'usr_cmp_rel_user_id' => NOLOGIN_USER_ID,
        'usr_cmp_rel_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_user_company_relation', $condition);

    // tbl_company
    $condition = array(
        'cmp_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_company', $condition);

    // tbl_company_board
    $condition = array(
        'cmp_bod_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_company_board', $condition);

    // tbl_company_promoter
    $condition = array(
        'cmp_prm_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_company_promoter', $condition);

    // tbl_company_promoter_corporation
    $condition = array(
        'cmp_prm_cop_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_company_promoter_corporation', $condition);

    // tbl_company_promoter_investment
    $condition = array(
        'cmp_prm_inv_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_company_promoter_investment', $condition);

    // tbl_company_purpose
    $condition = array(
        'cmp_pps_company_id' => $relation['usr_cmp_rel_company_id'],
    );
    _DB_DeleteInfo('tbl_company_purpose', $condition);
}
