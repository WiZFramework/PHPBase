<?php
/*!
@file member_detail.php
@brief メンバー詳細
@copyright Copyright (c) 2017 Yamanoi Yasushi.
*/

/////////////////////////////////////////////////////////////////
/// 実行ブロック
/////////////////////////////////////////////////////////////////

//ライブラリをインクルード
require_once("inc_base.php");
require_once($CMS_COMMON_INCLUDE_DIR . "libs.php");


$member_id = 0;
$err_array = array();
$err_flag = 0;

$page = 0;
if(isset($_GET['page']) 
	&& cutil::is_number($_GET['page'])
	&& $_GET['page'] > 0){
	$page = $_GET['page'];
}

if(isset($_GET['mid']) 
//cutilクラスのメンバ関数をスタティック呼出
	&& cutil::is_number($_GET['mid'])
	&& $_GET['mid'] > 0){
	$member_id = $_GET['mid'];
}
//$_POST優先
if(isset($_POST['member_id']) 
//cutilクラスのメンバ関数をスタティック呼出
	&& cutil::is_number($_POST['member_id'])
	&& $_POST['member_id'] > 0){
	$member_id = $_POST['member_id'];
}
//メンバークラスを構築
$member_obj = new cmember();
//配列にメンバーを$_POSTに取り出す
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
	if($member_id > 0){
		if(($_POST = $member_obj->get_tgt(false,$member_id)) === false){
			$_POST['func'] = 'new';
		}
		else{
			$_POST['fruits'] = $member_obj->get_all_fruits_match(false,$member_id);
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
	/// メンバー名の存在と空白チェック
	if(ccontentsutil::chkset_err_field($err_array,'member_name','メンバー名','isset_nl')){
		$retflg = false;
	}
	/// メンバーの都道府県チェック
	if(ccontentsutil::chkset_err_field($err_array,'prefecture_id','都道府県','isset_num_range',1,47)){
		$retflg = false;
	}
	/// メンバー住所の存在と空白チェック
	if(ccontentsutil::chkset_err_field($err_array,'member_address','市区郡町村以下','isset_nl')){
		$retflg = false;
	}
	/// メンバーの性別チェック
	if(ccontentsutil::chkset_err_field($err_array,'member_gender','性別','isset_num_range',1,2)){
		$retflg = false;
	}

	return $retflg;
}

//--------------------------------------------------------------------------------------
/*!
@brief	フルーツデータの追加／更新
@return	なし
*/
//--------------------------------------------------------------------------------------
function regist_fruits($member_id){
	$chenge = new cchange_ex();
	$chenge->delete("fruits_match","member_id=" . $member_id);
	foreach($_POST['fruits'] as $key => $val){
		$dataarr = array();
		$dataarr['member_id'] = (int)$member_id;
		$dataarr['fruits_id'] = (int)$val;
		$chenge->insert('fruits_match',$dataarr);
	}
}


//--------------------------------------------------------------------------------------
/*!
@brief	メンバーデータの追加／更新。保存後自分自身を再読み込みする。
@return	なし
*/
//--------------------------------------------------------------------------------------
function regist(){
	global $member_id;
	$dataarr = array();
	$dataarr['member_name'] = (string)$_POST['member_name'];
	$dataarr['prefecture_id'] = (int)$_POST['prefecture_id'];
	$dataarr['member_address'] = (string)$_POST['member_address'];
	$dataarr['member_gender'] = (int)$_POST['member_gender'];
	$dataarr['member_comment'] = (string)$_POST['member_comment'];
	$chenge = new cchange_ex();
	if($member_id > 0){
		$chenge->update('member',$dataarr,'member_id=' . $member_id);
		regist_fruits($member_id);
		cutil::redirect_exit($_SERVER['PHP_SELF'] . '?mid=' . $member_id);
	}
	else{
		$mid = $chenge->insert('member',$dataarr);
		regist_fruits($mid);
		cutil::redirect_exit($_SERVER['PHP_SELF'] . '?mid=' . $mid);
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
@brief	メンバーIDの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_member_id(){
	global $member_id;
	echo $member_id;
}

//--------------------------------------------------------------------------------------
/*!
@brief	メンバーIDの出力(新規の場合は「新規」)
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_member_id_txt(){
	global $member_id;
	if($member_id <= 0){
		echo '新規';
	}
	else{
		echo $member_id;
	}
}
//--------------------------------------------------------------------------------------
/*!
@brief	メンバー名の出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_member_name(){
	global $err_array;
	if(!isset($_POST['member_name']))$_POST['member_name'] = '';
	$tgt = new ctextbox('member_name',$_POST['member_name'],'size="50"');
	$tgt->show($_POST['func'] == 'conf');
	if(isset($err_array['member_name'])){
		echo '<br /><span class="red">' 
		. cutil::ret2br($err_array['member_name']) 
		. '</span>';
	}
}

//--------------------------------------------------------------------------------------
/*!
@brief	都道府県プルダウンの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_prefecture_select(){
	global $err_array;
	if(!isset($_POST['prefecture_id']))$_POST['prefecture_id'] = 0;
	//都道府県の一覧を取得
	$prefecture_obj = new cprefecture();
	$allcount = $prefecture_obj->get_all_count(false);
	$prefecture_rows = $prefecture_obj->get_all(false,0,$allcount);
	$tgt = new cselect('prefecture_id');
	$tgt->add(0,'選択してください',$_POST['prefecture_id'] == 0);
	foreach($prefecture_rows as $key => $val){
		$tgt->add($val['prefecture_id'],$val['prefecture_name'],$val['prefecture_id'] == $_POST['prefecture_id']);
	}
	$tgt->show($_POST['func'] == 'conf');
	if(isset($err_array['prefecture_id'])){
		echo '<br /><span class="red">' 
		. cutil::ret2br($err_array['prefecture_id']) 
		. '</span>';
	}
}

//--------------------------------------------------------------------------------------
/*!
@brief	メンバー住所の出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_member_address(){
	global $err_array;
	if(!isset($_POST['member_address']))$_POST['member_address'] = '';
	$tgt = new ctextbox('member_address',$_POST['member_address'],'size="80"');
	$tgt->show($_POST['func'] == 'conf');
	if(isset($err_array['member_address'])){
		echo '<br /><span class="red">' 
		. cutil::ret2br($err_array['member_address']) 
		. '</span>';
	}
}


//--------------------------------------------------------------------------------------
/*!
@brief	メンバー性別ラジオボタンの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_member_gender_radio(){
	global $err_array;
	if(!isset($_POST['member_gender']))$_POST['member_gender'] = 0;
	//メンバー性別のラジオボタンを作成
	$tgt = new cradio('member_gender');
	$tgt->add(1,'男性',$_POST['member_gender'] == 1);
	$tgt->add(2,'女性',$_POST['member_gender'] == 2);
	$tgt->show($_POST['func'] == 'conf','&nbsp;');
	if(isset($err_array['member_gender'])){
		echo '<br /><span class="red">' 
		. cutil::ret2br($err_array['member_gender']) 
		. '</span>';
	}
}

//--------------------------------------------------------------------------------------
/*!
@brief	好きな果物チェックボックスの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_fruits_match_check(){
	global $err_array;
	global $member_id;
	//フルーツの一覧を取得
	$fruits_obj = new cfruits();
	$fruits_rows = $fruits_obj->get_all(false);
	//果物のチェックボックスを作成
	$tgt = new cchkbox('fruits[]');
	if(!isset($_POST['fruits']))$_POST['fruits'] = array();
	foreach($fruits_rows as $key => $val){
		$check = false;
		foreach($_POST['fruits'] as $key2 => $val2){
			if($val2 == $val['fruits_id']){
				$check = true;
				break;
			}
		}
		$tgt->add($val['fruits_id'],$val['fruits_name'],$check);
	}
	$tgt->show($_POST['func'] == 'conf','&nbsp;');
}

//--------------------------------------------------------------------------------------
/*!
@brief	メンバーコメントの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_member_comment(){
	global $err_array;
	if(!isset($_POST['member_comment']))$_POST['member_comment'] = '';
	$tgt = new ctextarea('member_comment',$_POST['member_comment'],'cols="70" rows="10"');
	$tgt->show($_POST['func'] == 'conf');
}



//--------------------------------------------------------------------------------------
/*!
@brief	操作ボタンの出力
@return	なし
*/
//--------------------------------------------------------------------------------------
function echo_switch(){
	global $member_id;
	if($_POST['func'] == 'conf'){
		$button = '更新';
		if($member_id <= 0){
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
<title>メンバー詳細</title>
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
<h2>メンバー詳細</h2>
</div>
<!-- コンテンツ　-->
<div id="inquiry">
<?php echo_err_flag(); ?>

<form name="form1" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" >
<a href="member_list.php<?php echo_page(); ?>">一覧に戻る</a><br />
<span class="red">＊</span>は必須項目
<table>
<tr>
<th>ID</th>
<td width="70%"><?php echo_member_id_txt(); ?></td>
</tr>
<tr>
<th>メンバー名<span class="red">＊</span></th>
<td width="70%"><?php echo_member_name(); ?></td>
</tr>
<tr>
<th>都道府県<span class="red">＊</span></th>
<td width="70%"><?php echo_prefecture_select(); ?></td>
</tr>
<tr>
<th>市区郡町村以下<span class="red">＊</span></th>
<td width="70%"><?php echo_member_address(); ?></td>
</tr>
<tr>
<th>性別<span class="red">＊</span></th>
<td width="70%"><?php echo_member_gender_radio(); ?></td>
</tr>
<tr>
<th>好きな果物</th>
<td width="70%"><?php echo_fruits_match_check(); ?></td>
</tr>
<tr>
<th class="bobottom">コメント</th>
<td width="70%" class="bobottom"><?php echo_member_comment(); ?></td>
</tr>
</table>
<input type="hidden" name="func" value="" />
<input type="hidden" name="param" value="" />
<input type="hidden" name="member_id" value="<?php echo_member_id(); ?>" />
<p class="center"><?php echo_switch(); ?></p>
</form>
</div>
<!-- /コンテンツ　-->
</div>
<!-- /全体コンテナ　-->
</body>
</html>
