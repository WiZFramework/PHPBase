<?php
/*!
@file prefecture_detail.php
@brief 都道府県詳細
@copyright Copyright (c) 2021 Yamanoi Yasushi.
*/

/////////////////////////////////////////////////////////////////
/// 実行ブロック
/////////////////////////////////////////////////////////////////

//ライブラリをインクルード
require_once("inc_base.php");
require_once($CMS_COMMON_INCLUDE_DIR . "libs.php");


$prefecture_id = 0;
$err_array = array();
$err_flag = 0;

$page = 0;
if(isset($_GET['page']) 
	&& cutil::is_number($_GET['page'])
	&& $_GET['page'] > 0){
	$page = $_GET['page'];
}

if(isset($_GET['pid']) 
//cutilクラスのメンバ関数をスタティック呼出
	&& cutil::is_number($_GET['pid'])
	&& $_GET['pid'] > 0){
	$prefecture_id = $_GET['pid'];
}
//$_POST優先
if(isset($_POST['prefecture_id']) 
//cutilクラスのメンバ関数をスタティック呼出
	&& cutil::is_number($_POST['prefecture_id'])
	&& $_POST['prefecture_id'] > 0){
	$prefecture_id = $_POST['prefecture_id'];
}
//都道府県クラスを構築
$prefecture_obj = new cprefecture();
//配列に都道府県を$_POSTに取り出す
//すでにPOSTされていたら、DBからは取り出さない

if(isset($_POST['func'])){
	switch($_POST['func']){
		case 'set':
			if(!paramchk()){
				$_POST['func'] = 'edit';
				$err_flag = 1;
			}
			else{
				regist();
				//regist()内でリダイレクトするので
				//ここまで実行されればリダイレクト失敗
				$_POST['func'] = 'edit';
				//システムに問題のあるエラー
				$err_flag = 2;
			}
		case 'conf':
			if(!paramchk()){
				$_POST['func'] = 'edit';
				$err_flag = 1;
			}
		break;
		case 'edit':
			//戻るボタン。
		break;
		default:
			//通常はありえない
			echo '原因不明のエラーです。';
			exit;
		break;
	}
}
else{
	if($prefecture_id > 0){
		if(($_POST = $prefecture_obj->get_tgt(false,$prefecture_id)) === false){
			$_POST['func'] = 'new';
		}
		else{
			$_POST['func'] = 'edit';
		}
	}
	else{
		//新規の入力フォーム
		$_POST['func'] = 'new';
	}
}

/////////////////////////////////////////////////////////////////
/// 関数ブロック
/////////////////////////////////////////////////////////////////

//--------------------------------------------------------------------------------------
/*!
@brief	エラー存在の表示
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_err_flag(){
	global $err_flag;
	switch($err_flag){
		case 1:
		$str =<<<END_BLOCK

<p class="red">入力エラーがあります。各項目のエラーを確認してください。</p>
END_BLOCK;
		echo $str;
		break;
		case 2:
		$str =<<<END_BLOCK

<p class="red">更新に失敗しました。サポートを確認下さい。</p>
END_BLOCK;
		echo $str;
		break;
	}
}

//--------------------------------------------------------------------------------------
/*!
@brief	パラメータのチェック
@return	エラーの場合はfalseを返す
*/
//--------------------------------------------------------------------------------------
function paramchk(){
	global $err_array;
	$retflg = true;
	/// 都道府県名の存在と空白チェック
	if(ccontentsutil::chkset_err_field($err_array,'prefecture_name','都道府県名','isset_nl')){
		$retflg = false;
	}
	return $retflg;
}
//--------------------------------------------------------------------------------------
/*!
@brief	メンバーデータの追加／更新。保存後自分自身を再読み込みする。
@return	なし
*/
//--------------------------------------------------------------------------------------
function regist(){
	global $prefecture_id;
	$dataarr = array();
	$dataarr['prefecture_name'] = (string)$_POST['prefecture_name'];
	$chenge = new cchange_ex();
	if($prefecture_id > 0){
		$chenge->update('prefecture',$dataarr,'prefecture_id=' . $prefecture_id);
		cutil::redirect_exit($_SERVER['PHP_SELF'] . '?pid=' . $prefecture_id);
	}
	else{
		$pid = $chenge->insert('prefecture',$dataarr);
		cutil::redirect_exit($_SERVER['PHP_SELF'] . '?pid=' . $pid);
	}
}
//--------------------------------------------------------------------------------------
/*!
@brief	ページの出力(一覧に戻るリンク用)
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_page(){
	global $page;
	if($page > 0){
		echo '?page=' . $page;
	}
}

//--------------------------------------------------------------------------------------
/*!
@brief	都道府県IDの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_prefecture_id(){
	global $prefecture_id;
	echo $prefecture_id;
}

//--------------------------------------------------------------------------------------
/*!
@brief	都道府県IDの出力(新規の場合は「新規」)
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_prefecture_id_txt(){
	global $prefecture_id;
	if($prefecture_id <= 0){
		echo '新規';
	}
	else{
		echo $prefecture_id;
	}
}
//--------------------------------------------------------------------------------------
/*!
@brief	都道府県の出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_prefecture_name(){
	global $err_array;
	if(!isset($_POST['prefecture_name']))$_POST['prefecture_name'] = '';
	$tgt = new ctextbox('prefecture_name',$_POST['prefecture_name'],'size="70"');
	$tgt->show($_POST['func'] == 'conf');
	if(isset($err_array['prefecture_name'])){
		echo '<br /><span class="red">' 
		. cutil::ret2br($err_array['prefecture_name']) 
		. '</span>';
	}
}
//--------------------------------------------------------------------------------------
/*!
@brief	操作ボタンの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_switch(){
	global $prefecture_id;
	if($_POST['func'] == 'conf'){
		$button = '更新';
		if($prefecture_id <= 0){
			$button = '追加';
		}
		$str =<<<END_BLOCK
<input type="button"  value="戻る" onClick="javascript:set_func_form('edit','')"/>&nbsp;
<input type="button"  value="{$button}" onClick="javascript:set_func_form('set','')"/>
END_BLOCK;
	}
	else{
		$str =<<<END_BLOCK
<input type="button"  value="確認" onClick="javascript:set_func_form('conf','')"/>
END_BLOCK;
	}
	echo $str;
}

?>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/main.css" rel="stylesheet" type="text/css">
<title>都道府県詳細</title>
<script type="text/javascript">
<!--
function set_func_form(fn,pm){
	document.form1.target = "_self";
	document.form1.func.value = fn;
	document.form1.param.value = pm;
	document.form1.submit();
}


// -->
</script>
</head>
<body>
<!-- 全体コンテナ　-->
<div id="container">
<?php require_once("gmenu.php"); ?>
<div id="headTitle">
<h2>都道府県詳細</h2>
</div>
<!-- コンテンツ　-->
<div id="inquiry">
<?php echo_err_flag(); ?>

<form name="form1" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" >
<a href="prefecture_list.php<?php echo_page(); ?>">一覧に戻る</a>
<table>
<tr>
<th>ID</th>
<td width="70%"><?php echo_prefecture_id_txt(); ?></td>
</tr>
<tr>
<th>都道府県名</th>
<td width="70%"><?php echo_prefecture_name(); ?></td>
</tr>
</table>
<input type="hidden" name="func" value="" />
<input type="hidden" name="param" value="" />
<input type="hidden" name="prefecture_id" value="<?php echo_prefecture_id(); ?>" />
<p class="center"><?php echo_switch(); ?></p>
</form>
</div>
<!-- /コンテンツ　-->
</div>
<!-- /全体コンテナ　-->
</body>
</html>
