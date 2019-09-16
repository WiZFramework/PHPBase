<?php
/*!
@file contents_func.php
@brief 他コンテンツ系ユーティリティ
@copyright Copyright (c) 2017 Yamanoi Yasushi,Shimojima Ryo.
*/
////////////////////////////////////
//クラスブロック

//--------------------------------------------------------------------------------------
///	ローカルユーティリティ関数群(スタティック呼出をする)
//--------------------------------------------------------------------------------------
class ccontentsutil  {
	//--------------------------------------------------------------------------------------
	/*!
	@brief	テキストボックスの取得
	@param[in]	$name 変数名
	@param[in]	$ext  テキストボックスの属性
	@param[in]	$err_array エラー配列
	@return	テキストボックス文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function get_textbox($name,$ext,$err_array){
		$retstr = "";
		if(!isset($_POST[$name]))$_POST[$name] = '';
		$tgt = new ctextbox($name,$_POST[$name],$ext);
		if(isset($err_array[$name])){
			$retstr = '<br /><span class="error">※' 
			. cutil::ret2br($err_array[$name]) 
			. '</span><br />';
		}
		$retstr .= $tgt->get($_POST['func'] == 'conf');
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	配列型テキストボックスの取得
	@param[in]	$name 変数名
	@param[in]	$id インデックス
	@param[in]	$ext  テキストボックスの属性
	@param[in]	$err_array エラー配列
	@return	テキストボックス文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function get_textbox_arr($name,$id,$ext,$err_array){
		$retstr = "";
		if(!isset($_POST[$name][$id]))$_POST[$name][$id] = '';
		$tgt = new ctextbox($name . '[' . $id . ']',$_POST[$name][$id],$ext);
		if(isset($err_array[$name][$id])){
			$retstr = '<br /><span class="error">※' 
			. cutil::ret2br($err_array[$name][$id]) 
			. '</span><br />';
		}
		$retstr .= $tgt->get($_POST['func'] == 'conf');
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	テキストエリアの取得
	@param[in]	$name 変数名
	@param[in]	$ext  テキストエリアの属性
	@param[in]	$err_array エラー配列
	@return	テキストエリア文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function get_textarea($name,$ext,$err_array){
		$retstr = "";
		if(!isset($_POST[$name]))$_POST[$name] = '';
		$tgt = new ctextarea($name,$_POST[$name],$ext);
		if(isset($err_array[$name])){
			$retstr = '<br /><span class="error">※' 
			. cutil::ret2br($err_array[$name]) 
			. '</span>';
		}
		$retstr .= $tgt->get($_POST['func'] == 'conf');
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	配列型テキストエリアの取得
	@param[in]	$name 変数名
	@param[in]	$id インデックス
	@param[in]	$ext  テキストエリアの属性
	@param[in]	$err_array エラー配列
	@return	テキストエリア文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function get_textarea_arr($name,$id,$ext,$err_array){
		$retstr = "";
		if(!isset($_POST[$name][$id]))$_POST[$name][$id] = '';
		$tgt = new ctextarea($name . '[' . $id . ']',$_POST[$name][$id],$ext);
		if(isset($err_array[$name][$id])){
			$retstr = '<br /><span class="error">※' 
			. cutil::ret2br($err_array[$name][$id]) 
			. '</span><br />';
		}
		$retstr .= $tgt->get($_POST['func'] == 'conf');
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パスワードフォーマットチェック（アルファベットか数字）
	@param[in]	$value チェックする文字列
	@return	フォーマットに合っていればtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chek_pass_format($value){
		if(preg_match('/^[a-zA-Z0-9]+$/',$value)){
			return true;
		}
		else {
			return false;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	$_PSOT入力エラーをチェックし、エラー配列にセット
	@param[in]	$err_array エラー配列
	@param[in]	$name POST変数名
	@param[in]	$field ユーザーにわかる変数名
	@param[in]	$chkid チェック方法
	@param[in]	$para1 パラメータ１（オプション）
	@param[in]	$para2 パラメータ２（オプション）
	@return	フォーマットにあってなければtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chkset_err_field(&$err_array,$name,$field,$chkid,$para1='',$para2=''){
		switch($chkid){
			case 'isset':
				//存在チェックのみ
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
			break;
			case 'isset_nl':
				//存在と空白チェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif($_POST[$name] == '' ){
					$err_array[$name] = "「{$field}」は必須項目です";
					return true;
				}
			break;
			case 'isset_nl_strlen':
				//存在と空白チェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif($_POST[$name] == '' ){
					$err_array[$name] = "「{$field}」は必須項目です";
					return true;
				}
				elseif(mb_strlen($_POST[$name],PHP_CHARSET) > $para1){
					$err_array[$name] = "「{$field}」は、{$para1}文字以内です";
					return true;
				}
			break;
			case 'isset_num':
				//存在と数字チェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif(!cutil::is_number($_POST[$name])){
					$err_array[$name] = "「{$field}」は数字を記入して下さい";
					return true;
				}
			break;
			case 'isset_num_more0':
				//存在と数字チェック(数字は0以上。プルダウン用)
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif(!cutil::is_number($_POST[$name])
					|| $_POST[$name] < 0 ){
					$err_array[$name] = "「{$field}」を選択して下さい";
					return true;
				}
			break;
			case 'isset_num_more1':
				//存在と数字チェック(数字は1以上。プルダウン用)
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif(!cutil::is_number($_POST[$name])
					|| $_POST[$name] <= 0 ){
					$err_array[$name] = "「{$field}」を選択して下さい";
					return true;
				}
			break;
			case 'isset_num_range':
				//存在と数字チェック(数字は1以上。プルダウン用)
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif(!cutil::number_range($_POST[$name],$para1,$para2)){
					$err_array[$name] = "「{$field}」を選択して下さい";
					return true;
				}
			break;
			case 'isset_tel':
				//存在と電話番号記入時のチェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif($_POST[$name] != '' 
					&& !cutil::is_num_hyphen($_POST[$name])){
					//未記入でも構わないが書いた場合は書式チェック
					$err_array[$name] = "「{$field}」は半角数字かハイフンで記入下さい";
					return true;
				}
			break;
			case 'isset_nul_tel':
				//存在と電話番号チェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif($_POST[$name] == '' 
					|| !cutil::is_num_hyphen($_POST[$name])){
					$err_array[$name] = "「{$field}」は必須項目です。半角数字かハイフンで記入下さい";
					return true;
				}
			break;
			case 'isset_mail':
				//存在とEメール書式チェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif($_POST[$name] == ""){
					$err_array[$name] = "「{$field}」は必須項目です";
					return true;
				}
				elseif(!cutil::chek_mail_format($_POST[$name])){
					$err_array[$name] = "「{$field}」が書式にあってません";
					return true;
				}
			break;
			case 'isset_pass':
				//存在とパスワードチェック
				if(!isset($_POST[$name])){
					$err_array[$name] = "「{$field}」が見つかりません";
					return true;
				}
				elseif($_POST[$name] == ""){
					$err_array[$name] = "「{$field}」は必須項目です";
					return true;
				}
				elseif(!ccontentsutil::chek_pass_format($_POST[$name])){
					$err_array[$name] = "「{$field}」が書式にあってません";
					return true;
				}
			break;
		}
		return false;
	}
}
?>
