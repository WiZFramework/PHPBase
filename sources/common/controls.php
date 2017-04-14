<?php
/*!
@file controls.php
@brief コントロールクラス
@copyright Copyright (c) 2017 Yamanoi Yasushi.
*/
////////////////////////////////////
//クラスブロック
//--------------------------------------------------------------------------------------
///	テキストボックスをカプセル化
//--------------------------------------------------------------------------------------
class ctextbox
{
	public $m_name ;
	public $m_value;
	public $m_extstr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$name	変数名
	@param[in]	$value	値
	@param[in]	$extstr	アトリビュート
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($name,$value,$extstr = ""){
		$this->m_name  = $name;
		$this->m_value = $value;
		$this->m_extstr = $extstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	textbox文字列を返す
	@param[in]	$conf	確認画面かどうか
	@param[in]	$input_last_str	コントロールの後ろにつく文字列
	@return	textbox文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf,$input_last_str = ''){
		$spval = cutil::escape($this->m_value);
		if($conf){
			//conf
			$str =<<<END_BLOCK
<span style="font-weight: bold;">{$spval}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
END_BLOCK;
		}
		else{
$str =<<<END_BLOCK
<input type="text" name="{$this->m_name }" {$this->m_extstr} value="{$spval}" />{$input_last_str}
END_BLOCK;
		}
		return $str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	textbox文字列を出力する
	@param[in]	$conf	確認画面かどうか
	@param[in]	$input_last_str	コントロールの後ろにつく文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($conf,$input_last_str = ''){
		echo $this->get($conf,$input_last_str);
	}
}
//--------------------------------------------------------------------------------------
///	プルダウンをカプセル化
//--------------------------------------------------------------------------------------
class cselect
{
	public $m_name ;
	public $m_valueArr;
	public $m_extstr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$name	変数名
	@param[in]	$extstr	アトリビュート
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($name,$extstr = ""){
		$this->m_name  = $name;
		$this->m_extstr = $extstr;
		$this->m_valueArr = array();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	オプションを一括設定する
	@param[in]	$optarr	オプションの配列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function setoption(&$optarr){
		$this->m_valueArr = $optarr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	オプションを追加する
	@param[in]	$value	値
	@param[in]	$laststr	設定文字列
	@param[in]	$selected	選択してるかどうか
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function add($value,$laststr,$selected = false){
		$this->m_valueArr[] = array($value,$laststr,$selected);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	プルダウン文字列を返す
	@param[in]	$conf	確認画面かどうか
	@param[in]	$nullvalue	選択されないときの値
	@param[in]	$nullstr	選択されないときの文字列
	@param[in]	$nullshowstr	選択されないときの警告文
	@return	プルダウン文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf,$nullvalue = 0,$nullstr = "",$nullshowstr = ""){
		$retstr = "";
		if($conf){
			//conf
			$flag = false;
			$retbase = '';
			for($i = 0;$i < count($this->m_valueArr);$i++){
				$spval = cutil::escape($this->m_valueArr[$i][0]);
				$splaststr = cutil::escape($this->m_valueArr[$i][1]);
				if($this->m_valueArr[$i][2]){
					$retbase =<<<END_BLOCK
<span style="font-weight: bold;">{$splaststr}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
END_BLOCK;
					$flag = true;
					$retstr .= $retbase;
				}
			}
			if(!$flag){
				$nullstr = cutil::escape($nullstr);
				if($nullshowstr != ""){
					$nullstr = cutil::escape($nullshowstr);
				}
				$retstr =<<<END_BLOCK
{$nullstr}
<input type="hidden" name="{$this->m_name}" value="{$nullvalue}" />
END_BLOCK;
			}
		}
		else{
			$retstr =<<<END_BLOCK
<select name="{$this->m_name }" {$this->m_extstr} >
END_BLOCK;
			$defoption = "";
			if($nullstr != ""){
				$defoption =<<<END_BLOCK
<option value="{$nullvalue}">{$nullstr}</option>
END_BLOCK;
			}
			$retstr .= $defoption;
			for($i = 0;$i < count($this->m_valueArr);$i++){
				$spval 
				= cutil::escape($this->m_valueArr[$i][0]);
				$splaststr 
				= cutil::escape($this->m_valueArr[$i][1]);
				$selectstr = "";
				if($this->m_valueArr[$i][2]){
					$selectstr = ' selected="selected" ';
				}
				$str =<<<END_BLOCK
<option value="{$spval}" {$selectstr} >{$splaststr}</option>
END_BLOCK;
				$retstr .= $str;
			}
			$retstr .= '</select>';
		}
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	プルダウン文字列を出力する
	@param[in]	$conf	確認画面かどうか
	@param[in]	$nullvalue	選択されないときの値
	@param[in]	$nullstr	選択されないときの文字列
	@param[in]	$nullshowstr	選択されないときの警告文
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($conf,$nullvalue = 0,$nullstr = "",$nullshowstr = ""){
		echo $this->get($conf,$nullvalue,$nullstr,$nullshowstr);
	}
}
//--------------------------------------------------------------------------------------
///	テキストエリアをカプセル化
//--------------------------------------------------------------------------------------
class ctextarea
{
	public $m_name ;
	public $m_value;
	public $m_extstr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$name	変数名
	@param[in]	$value	値
	@param[in]	$extstr	アトリビュート
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($name,$value,$extstr){
		$this->m_name  = $name;
		$this->m_value = $value;
		$this->m_extstr = $extstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	テキストエリア文字列を返す
	@param[in]	$conf	確認画面かどうか
	@return	テキストエリア文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf){
		$spval = cutil::escape($this->m_value);
		$spbrval = cutil::ret2br($spval);
		if($conf){
			//conf
			$str =<<<END_BLOCK
<span style="font-weight: bold;">{$spbrval}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
END_BLOCK;
		}
		else{
			$str =<<<END_BLOCK
<textarea name="{$this->m_name }" {$this->m_extstr} >{$spval}</textarea>
END_BLOCK;
		}
		return $str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	テキストエリア文字列を出力する
	@param[in]	$conf	確認画面かどうか
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($conf){
		echo $this->get($conf);
	}
}

//--------------------------------------------------------------------------------------
///	チェックボックスをカプセル化
//--------------------------------------------------------------------------------------
class cchkbox
{
	public $m_name ;
	public $m_valueArr;
	public $m_extstr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$name	変数名
	@param[in]	$extstr	アトリビュート
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($name,$extstr = ""){
		$this->m_name  = $name;
		$this->m_extstr = $extstr;
		$this->m_valueArr = array();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	チェックボックスを一括設定する
	@param[in]	$chkarr	チェックボックスの配列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function setchk($chkarr){
		$this->m_valueArr = $chkarr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	チェックボックスを追加する
	@param[in]	$value	値
	@param[in]	$laststr	説明文
	@param[in]	$checked	チェックされているかどうか
	@param[in]	$attstr	アトリビュート文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function add($value,$laststr,$checked = false,$attstr = ""){
		$this->m_valueArr[] = array($value,$laststr,$checked,$attstr);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	チェックボックス文字列を返す
	@param[in]	$conf	確認画面かどうか
	@param[in]	$delimiter	チェックボックスの区切り文字列
	@return	チェックボックス文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf,$delimiter){
		$retstr = "";
		if($conf){
			//conf
			$flag = false;
			$retbase = '';
			$rowcount = 1;
			for($i = 0;$i < count($this->m_valueArr);$i++){
				$spval = cutil::escape($this->m_valueArr[$i][0]);
				$splaststr = cutil::escape($this->m_valueArr[$i][1]);
				if($this->m_valueArr[$i][2]){
					$retbase =<<<END_BLOCK
<span style="font-weight: bold;">{$splaststr}</span>
<input type="hidden" name="{$this->m_name}" value="{$spval}" />
END_BLOCK;
					$flag = true;
					if($rowcount != count($this->m_valueArr)){
						$retbase .= $delimiter;
					}
					$retstr .= $retbase;
				}
			}
		}
		else{
			$rowcount = 1;
			for($i = 0;$i < count($this->m_valueArr);$i++){
				$spval 
				= cutil::escape($this->m_valueArr[$i][0]);
				$splaststr 
				= cutil::escape($this->m_valueArr[$i][1]);
				$checkstr = "";
				if($this->m_valueArr[$i][2]){
					$checkstr = ' checked="checked" ';
				}
				$attstr = $this->m_valueArr[$i][3];
				$str =<<<END_BLOCK
<input type="checkbox" name="{$this->m_name}" value="{$spval}" {$attstr} {$checkstr} />{$splaststr}
END_BLOCK;
				if($rowcount != count($this->m_valueArr)){
					$str .= $delimiter;
				}
				$retstr .= $str;
				$rowcount++;
			}
		}
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	チェックボックス文字列を出力する
	@param[in]	$conf	確認画面かどうか
	@param[in]	$delimiter	チェックボックスの区切り文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($conf,$delimiter){
		echo $this->get($conf,$delimiter);
	}
}

//--------------------------------------------------------------------------------------
///	ラジオボタンをカプセル化
//--------------------------------------------------------------------------------------
class cradio
{
	public $m_name ;
	public $m_valueArr;
	public $m_extstr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$name	変数名
	@param[in]	$extstr	アトリビュート
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($name,$extstr = ""){
		$this->m_name  = $name;
		$this->m_extstr = $extstr;
		$this->m_valueArr = array();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ラジオボタンを一括設定する
	@param[in]	$chkarr	ラジオボタンの配列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function setradio($chkarr){
		$this->m_valueArr = $chkarr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ラジオボタンを追加する
	@param[in]	$value	値
	@param[in]	$laststr	説明文
	@param[in]	$checked	チェックされているかどうか
	@param[in]	$attstr	アトリビュート文字列
	@param[in]	$prevstr	ラジオボタンの前の文字列
	@param[in]	$nextstr	ラジオボタンの後の文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function add($value,$laststr,$checked = false,$attstr = "",$prevstr = "",$nextstr = ""){
		$this->m_valueArr[] = array($value,$laststr,$checked,$attstr,$prevstr,$nextstr);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ラジオボタン文字列を返す
	@param[in]	$conf	確認画面かどうか
	@param[in]	$delimiter	チェックボックスの区切り文字列
	@param[in]	$prevflg	ラジオボタンの前の文字列を表示するかどうか
	@param[in]	$nextflg	ラジオボタンの後の文字列を表示するかどうか
	@return	ラジオボタン文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf,$delimiter,$prevflg = false,$nextflg = false){
		$retstr = "";
		if($conf){
			//conf
			$flag = false;
			$retbase = '';
			$rowcount = 1;
			for($i = 0;$i < count($this->m_valueArr);$i++){
				$spval = cutil::escape($this->m_valueArr[$i][0]);
				$splaststr = cutil::escape($this->m_valueArr[$i][1]);
				$prevstr = '';
				if($prevflg){
					$prevstr = $this->m_valueArr[$i][4];
				}
				$nextstr = '';
				if($nextflg){
					$nextstr = $this->m_valueArr[$i][5];
				}
				if($this->m_valueArr[$i][2]){
					$retbase =<<<END_BLOCK
{$prevstr}
<span style="font-weight: bold;">{$splaststr}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
{$nextstr}
END_BLOCK;
					$flag = true;
					if($rowcount != count($this->m_valueArr)){
						$retbase .= $delimiter;
					}
					$retstr .= $retbase;
					//ラジオボタンは複数選択無し
					break;
				}
			}
		}
		else{
			$rowcount = 1;
			for($i = 0;$i < count($this->m_valueArr);$i++){
				$spval 
				= cutil::escape($this->m_valueArr[$i][0]);
				$splaststr 
				= cutil::escape($this->m_valueArr[$i][1]);
				$checkstr = "";
				if($this->m_valueArr[$i][2]){
					$checkstr = ' checked="checked" ';
				}
				$attstr = $this->m_valueArr[$i][3];
				$prevstr = '';
				if($prevflg){
					$prevstr = $this->m_valueArr[$i][4];
				}
				$nextstr = '';
				if($nextflg){
					$nextstr = $this->m_valueArr[$i][5];
				}
				$str =<<<END_BLOCK
{$prevstr}
<input type="radio" name="{$this->m_name }" value="{$spval}" {$attstr} {$checkstr} />{$splaststr}
{$nextstr}
END_BLOCK;
				if($rowcount != count($this->m_valueArr)){
				$str .= $delimiter;
				}
				$retstr .= $str;
				$rowcount++;
			}
		}
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ラジオボタン文字列を出力する
	@param[in]	$conf	確認画面かどうか
	@param[in]	$delimiter	チェックボックスの区切り文字列
	@param[in]	$prevflg	ラジオボタンの前の文字列を表示するかどうか
	@param[in]	$nextflg	ラジオボタンの後の文字列を表示するかどうか
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($conf,$delimiter,$prevflg = false,$nextflg = false){
		echo $this->get($conf,$delimiter,$prevflg,$nextflg);
	}
}
//--------------------------------------------------------------------------------------
///	ページリンクをカプセル化
//--------------------------------------------------------------------------------------
class cpager
{
	public $m_base_uri;
	public $m_allcount;
	public $m_limit;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$base_uri	ベースとなるURL
	@param[in]	$allcount	レコード総数
	@param[in]	$limit	1ページの行数
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($base_uri,$allcount,$limit){
		$this->m_base_uri  = $base_uri;
		if(strstr($this->m_base_uri,'?') === false){
			$this->m_base_uri .= '?';
		}
		else{
			$this->m_base_uri .= '&';
		}
		$this->m_allcount  = $allcount;
		$this->m_limit = $limit;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ページリンク文字列を返す
	@param[in]	$pageparam	ページを意味するキー
	@param[in]	$tgt_page	現在のページ
	@param[in]	$delimiter	ページリンクの区切り文字列
	@param[in]	$first_ex	ページリンク全体の前の文字列
	@param[in]	$att	Aタグのアトリビュート
	@return	ページリンク文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($pageparam,$tgt_page,$delimiter = ' > ',$first_ex = '',$last_ex = '',$att = ''){
		$str = "";
		$pagemax = (int)($this->m_allcount / $this->m_limit);
		if($this->m_allcount % $this->m_limit){
			$pagemax++;
		}
		if($pagemax <= 1){
			return "";
		}
		$str .= $first_ex;
		for($i = 1;$i <= $pagemax;$i++){
			if($i == $tgt_page){
				$str .=  $i;
			}
			else{
				$temp =<<<END_BLOCK
<a href="{$this->m_base_uri}{$pageparam}={$i}" {$att}>{$i}</a>
END_BLOCK;
				$str .= $temp;
			}
			if($i != $pagemax){
				$str .= $delimiter;
			}
		}
		$str .= $last_ex;
		return $str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ページリンク文字列を表示する
	@param[in]	$pageparam	ページを意味するキー
	@param[in]	$tgt_page	現在のページ
	@param[in]	$delimiter	ページリンクの区切り文字列
	@param[in]	$first_ex	ページリンク全体の前の文字列
	@param[in]	$att	Aタグのアトリビュート
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($pageparam,$tgt_page,$delimiter = ' > ',$first_ex = '',$last_ex = '',$att = ''){
		echo $this->get($pageparam,$tgt_page,$delimiter,$first_ex,$last_ex,$att);
	}
}
?>
