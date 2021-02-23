<?php
/*!
@file prefecture_list_json.php
@brief 都道府県一覧(JSON版)
@copyright Copyright (c) 2021 Yamanoi Yasushi.
*/

/////////////////////////////////////////////////////////////////
/// 実行ブロック
/////////////////////////////////////////////////////////////////

//ライブラリをインクルード
require_once("inc_base.php");
require_once($CMS_COMMON_INCLUDE_DIR . "libs.php");

//ページの設定
//デフォルトは1
$page = 1;
//もしページが指定されていたら
if(isset($_GET['page']) 
    //なおかつ、数字だったら
    && cutil::is_number($_GET['page'])
    //なおかつ、0より大きかったら
    && $_GET['page'] > 0){
    //パラメータを設定
    $page = $_GET['page'];
}

//1ページのリミット
$limit = 20;
$rows = array();
//データの読み込み
readdata();
//一覧データをjsonで返す
echo json_encode($rows);


/////////////////////////////////////////////////////////////////
/// 関数ブロック
/////////////////////////////////////////////////////////////////

//--------------------------------------------------------------------------------------
/*!
@brief	データ読み込み
@return	なし
*/
//--------------------------------------------------------------------------------------
function readdata(){
	global $limit;
	global $rows;
	global $page;
	$obj = new cprefecture();
	$allcount = $obj->get_all_count(false);
	$rows['allcount'] = $allcount;
	$rows['limit'] = $limit;
	$rows['page'] = $page;
	$from = ($page - 1) * $limit;
	$rows['rowdata'] = $obj->get_all(false,$from,$limit);
}



?>
