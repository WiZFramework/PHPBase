<?php
/*!
@file member_list_smarty.php
@brief メンバー一覧(Smarty版)
@copyright Copyright (c) 2021 Yamanoi Yasushi.
*/

/////////////////////////////////////////////////////////////////
/// 実行ブロック
/////////////////////////////////////////////////////////////////

//ライブラリをインクルード
require_once("inc_base.php");
require_once($CMS_COMMON_INCLUDE_DIR . "libs.php");
require_once("inc_smarty.php");

$show_mode = '';
$ERR_STR = '';

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

if(is_func_active()){
	if(param_chk()){
		switch($_POST['func']){
			case "del":
				$show_mode = 'del';
				//削除操作
				deljob();
				//リダイレクトするページの計算
				$re_page = $page;
				$obj = new cmember();
				$allcount = $obj->get_all_count(false);
				$last_page = (int)($allcount / $limit);
				if($allcount % $limit){
					$last_page++;
				}
				if($re_page > $last_page){
					$re_page = $last_page;
				}
				//再読み込みのためにリダイレクト
				cutil::redirect_exit($_SERVER['PHP_SELF'] 
				. '?page=' . $re_page);
			break;
			default:
			break;
		}
	}
}
$show_mode = 'edit';
//データの読み込み
readdata();

/////////////////////////////////////////////////////////////////
/// 関数ブロック
/////////////////////////////////////////////////////////////////

//--------------------------------------------------------------------------------------
/*!
@brief	コマンドが渡されたかどうか
@return	渡されたらtrue
*/
//--------------------------------------------------------------------------------------
function is_func_active(){
    if(isset($_POST['func']) && $_POST['func'] != ""){
        return true;
    }
    return false;
}


//--------------------------------------------------------------------------------------
/*!
@brief	パラメータのチェック
@return	エラーがあったらfalse
*/
//--------------------------------------------------------------------------------------
function param_chk(){
	 global $ERR_STR;
	if(!isset($_POST['param']) 
	|| !cutil::is_number($_POST['param'])
	|| $_POST['param'] <= 0){
		$ERR_STR .= "パラメータを取得できませんでした\n";
		return false;
	}
	return true;
}


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
	$obj = new cmember();
	$from = ($page - 1) * $limit;
	$rows = $obj->get_all(false,$from,$limit);
}

//--------------------------------------------------------------------------------------
/*!
@brief	削除
@return	なし
*/
//--------------------------------------------------------------------------------------
function deljob(){
	$chenge = new cchange_ex();
	if($_POST['param'] > 0){
		$chenge->delete("member","member_id=" . $_POST['param']);
	}
}


//--------------------------------------------------------------------------------------
/*!
@brief	ページャーのアサイン
@return	なし
*/
//--------------------------------------------------------------------------------------
function assign_page_block(){
	//$smartyをグローバル宣言（必須）
	global $smarty;
	global $limit;
	global $page;
	$retstr = '';
	$obj = new cmember();
	$allcount = $obj->get_all_count(false);
	$ctl = new cpager($_SERVER['PHP_SELF'],$allcount,$limit);
	$smarty->assign('pager_arr',$ctl->get('page',$page));
}


//--------------------------------------------------------------------------------------
/*!
@brief	一覧のアサイン
@return	なし
*/
//--------------------------------------------------------------------------------------
function assign_member_list(){
	//$smartyをグローバル宣言（必須）
	global $smarty;
	global $rows;
	$smarty->assign('rows',$rows);
}

//--------------------------------------------------------------------------------------
/*!
@brief	URIのアサイン
@return	なし
*/
//--------------------------------------------------------------------------------------
function assign_tgt_uri(){
	//$smartyをグローバル宣言（必須）
	global $smarty;
	global $page;
	$smarty->assign('tgt_uri',$_SERVER['PHP_SELF'] . '?page=' . $page);
}


/////////////////////////////////////////////////////////////////
/// 関数呼び出しブロック
/////////////////////////////////////////////////////////////////
$smarty->assign('ERR_STR',$ERR_STR);
assign_page_block();
assign_member_list();
assign_tgt_uri();

//Smartyを使用した表示(テンプレートファイルの指定)
$smarty->display('member_list_smarty.tmpl');


?>
