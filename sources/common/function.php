<?php
/*!
@file function.php
@brief ユーティリティクラス
@copyright Copyright (c) 2017 Yamanoi Yasushi,Shimojima Ryo.
*/

//定数の読み込み
require_once("config.php");

////////////////////////////////////
//クラスブロック

//--------------------------------------------------------------------------------------
///	ユーティリティ関数群(スタティック呼出をする)
//--------------------------------------------------------------------------------------
class cutil {
	//--------------------------------------------------------------------------------------
	/*!
	@brief	指定したURLにリダイレクトして終了
	@param[in]	$url	リダイレクトするURL
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function redirect_exit($url){
		$str = "Location: ".  $url;
		header($str);
		//リダイレクトしたのでexit
		exit();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて数字かどうかを判別
	@param[in]	$value 判別する文字列
	@return	true　数字　false数字以外が混じってる
	*/
	//--------------------------------------------------------------------------------------
	public static function is_number($value){
		if($value == '')return false;
		if(preg_match('/^[0-9]+$/',$value)){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて数字とハイフンかを判別
	@param[in]	$value 判別する文字列
	@return	true　数字かハイフン　false数字以外が混じってる
	*/
	//--------------------------------------------------------------------------------------
	public static function is_num_hyphen($value){
		if($value == '')return false;
		if(preg_match('/^[-0-9]+$/',$value)){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて半角かどうかを判別
	@param[in]	$value 判別する文字列
	@return	true　半角　false半角以外が混じってる
	*/
	//--------------------------------------------------------------------------------------
	public static function is_hankaku($value){
		$strlen = strlen($value);
		$mbstrlen = mb_strlen($value,PHP_CHARSET);
		if($strlen == $mbstrlen){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて数字で、かつ範囲に入ってるか判別
	@param[in]	$value 判別する文字列
	@param[in]	$from 範囲の始まり
	@param[in]	$to 範囲の終わり
	@return	条件に合えばtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function number_range($value,$from,$to){
		if(cutil::is_number($value) &&
			$value >= $from && $value <= $to){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	文字列のサイズ（マルチバイト）が範囲に入ってるか判別
	@param[in]	$str 判別する文字列
	@param[in]	$from 範囲の始まり
	@param[in]	$to 範囲の終わり
	@return	条件に合えばtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chk_mb_strlen_size($str,$from,$to){
		$len = mb_strlen($str,PHP_CHARSET);
		if($len >= $from && $len <= $to){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	文字列のサイズ（シングルバイト）が範囲に入ってるか判別
	@param[in]	$str 判別する文字列
	@param[in]	$from 範囲の始まり
	@param[in]	$to 範囲の終わり
	@return	条件に合えばtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chk_strlen_size($str,$from,$to){
		$len = strlen($str);
		if($len >= $from && $len <= $to){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	特殊文字をhtmlエンティティに変換
	@param[in]	$value 変換するする文字列
	@return	変換後の文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function escape($value){
		return htmlspecialchars($value,ENT_QUOTES,PHP_CHARSET);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	改行を<br />に変換
	@param[in]	$str 変換するする文字列
	@return	変換後の文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function ret2br($str){
		$order = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, $str);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	改行を<br />に変換して出力
	@param[in]	$str 変換するする文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function echo_ret2br($str){
		echo cutil::ret2br($str);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パスワード暗号化
	@param[in]	$password 暗号化する文字列
	@return	暗号化した文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function pw_encode($password){
		$seed = null;
		for ($i = 1; $i <= 8; $i++){
			$seed .= substr('0123456789abcdef',rand(0,15),1);
		}
		return hash("md5",$seed . $password) . $seed;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パスワードチェック
	@param[in]	$password 暗号化しないパスワード
	@param[in]	$stored_value 暗号化したパスワード
	@return	成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function pw_check($password,$stored_value){
		$stored_seed = substr($stored_value,32,8);
		if(hash("md5",$stored_seed . $password) . $stored_seed == $stored_value){
			return true;
		}
		else{
			return false;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	メールフォーマットチェック（フォーマットのみチェックする）
	@param[in]	$mail メールアドレス
	@return	成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chek_mail_format($mail){
		if(preg_match('/^[-a-zA-Z0-9_\.]+@([-a-zA-Z0-9_\.]+\.[-a-zA-Z0-9_]+$)/',$mail)){
			return true;
		}
		else{
			return false;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	mb_send_mailをデバッグする
	@param[in]	$To 送信先
	@param[in]	$Subject タイトル
	@param[in]	$Message 本文
	@param[in]	$Headers ヘッダ
	@param[in]	$brflg 改行をbrにするかどうか
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function mb_send_mail_chk($To, $Subject, $Message, $Headers,$brflg = false){
		$retcode = '';
		if($brflg){
			$retcode = '<br />';
		}
		$str =<<<END_BLOCK
{$retcode}
--start-----mb_send_mail_chk--------------{$retcode}
To: {$To}{$retcode}
Subject: {$Subject}{$retcode}
Headers: {$Headers}{$retcode}
Massage:{$retcode}
{$Message}{$retcode}
--end-------mb_send_mail_chk--------------{$retcode}
END_BLOCK;
		echo $str;
	}
}

?>
