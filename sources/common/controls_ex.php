<?php
/*!
@file controls_ex.php
@brief コントロールクラス（オプション）
@copyright Copyright (c) 2021 Yamanoi Yasushi.
*/
////////////////////////////////////
//クラスブロック
//--------------------------------------------------------------------------------------
///	パスワード型テキストボックスをカプセル化
//--------------------------------------------------------------------------------------
class cpasswordtextbox
{
	public $m_name ;
	public $m_value;
	public $m_conf_char;
	public $m_extstr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	@param[in]	$name	変数名
	@param[in]	$value	値
	@param[in]	$conf_char	確認モードの時の表示文字<br/>
		（例えば「*****」と表示したければ '*' を渡す）<br/>
		空白の場合は、入力文字をそのまま表示する
	@param[in]	$extstr	アトリビュート
	*/
	//--------------------------------------------------------------------------------------
	public function __construct($name,$value,$conf_char,$extstr = ""){
		$this->m_name  = $name;
		$this->m_value = $value;
		$this->m_conf_char = $conf_char;
		$this->m_extstr = $extstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	password型textbox文字列を返す
	@param[in]	$conf	確認画面かどうか
	@param[in]	$input_last_str	コントロールの後ろにつく文字列
	@return	password型textbox文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf,$input_last_str = ''){
		$spval = cutil::escape($this->m_value);
		if($conf){
			//conf
			$spval2 = $spval;
			if($this->m_conf_char != ''){
				$spval2 = '';
				$sz = mb_strlen($this->m_value);
				for($i = 0;$i < $sz;$i++){
					$spval2 .= $this->m_conf_char;
				}
			}
			$str =<<< END_BLOCK
<span style="font-weight: bold;">{$spval2}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
END_BLOCK;
		}
		else{
			$str =<<< END_BLOCK
<input type="password" name="{$this->m_name }" {$this->m_extstr} value="{$spval}" />{$input_last_str}
END_BLOCK;
		}
		return $str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	password型textbox文字列を出力する
	@param[in]	$conf	確認画面かどうか
	@param[in]	$input_last_str	コントロールの後ろにつく文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function show($conf_mode,$input_last_str = ''){
		echo $this->get($conf_mode,$input_last_str = "");
	}
}

//--------------------------------------------------------------------------------------
///	日付型テキストボックスをカプセル化
//--------------------------------------------------------------------------------------
class cinputdate
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
	@brief	コントロール作成文字列を返す
	@param[in]	$conf	確認画面かどうか
	@param[in]	$input_last_str	コントロールの後ろにつく文字列
	@return	コントロール作成文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get($conf,$input_last_str = ''){
		$spval = cutil::escape($this->m_value);
		if($conf){
			//conf
			$str =<<< END_BLOCK
<span style="font-weight: bold;">{$spval}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
END_BLOCK;
		}
		else{
$str =<<< END_BLOCK
<input type="date" name="{$this->m_name }" {$this->m_extstr} value="{$spval}" />{$input_last_str}
END_BLOCK;
		}
		return $str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コントロール作成文字列を出力する
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
///	グループ付きプルダウンをカプセル化
//--------------------------------------------------------------------------------------
class cselect_ex
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
	@brief	オプションを追加する
	@param[in]	$value	値
	@param[in]	$laststr	設定文字列
	@param[in]	$selected	選択してるかどうか
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function add_option($value,$laststr,$selected = false){
		$this->m_valueArr[] = array(0,$value,$laststr,$selected);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	グループを開始する
	@param[in]	$label	ラベル名
	@param[in]	$extstr	アトリビュート
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function start_group($label,$extstr = ''){
		$this->m_valueArr[] = array(1,$label,$extstr);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	グループを閉じる
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function end_group(){
		$this->m_valueArr[] = array(2);
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
				if($this->m_valueArr[$i][0] == 0){
					$spval = cutil::escape($this->m_valueArr[$i][1]);
					$splaststr = cutil::escape($this->m_valueArr[$i][2]);
					if($this->m_valueArr[$i][3]){
						$retbase =<<< END_BLOCK
<span style="font-weight: bold;">{$splaststr}</span>
<input type="hidden" name="{$this->m_name }" value="{$spval}" />
END_BLOCK;
						$flag = true;
						$retstr .= $retbase;
					}
				}
			}
			if(!$flag){
				$nullstr = cutil::escape($nullstr);
				if($nullshowstr != ""){
					$nullstr = cutil::escape($nullshowstr);
				}
				$retstr =<<< END_BLOCK
{$nullstr}
<input type="hidden" name="{$this->m_name}" value="{$nullvalue}" />
END_BLOCK;
				}
			}
			else{
				$retstr =<<< END_BLOCK
<select name="{$this->m_name }" {$this->m_extstr} >
END_BLOCK;
				$defoption = "";
				if($nullstr != ""){
				$defoption =<<< END_BLOCK
<option value="{$nullvalue}">{$nullstr}</option>
END_BLOCK;
			}
			$retstr .= $defoption;
			for($i = 0;$i < count($this->m_valueArr);$i++){
				if($this->m_valueArr[$i][0] == 0){
					$spval 
					= cutil::escape($this->m_valueArr[$i][1]);
					$splaststr 
					= cutil::escape($this->m_valueArr[$i][2]);
					$selectstr = "";
					if($this->m_valueArr[$i][3]){
						$selectstr = ' selected="selected" ';
					}
					$str =<<< END_BLOCK
<option value="{$spval}" {$selectstr} >{$splaststr}</option>
END_BLOCK;
					$retstr .= $str;
				}
				else if($this->m_valueArr[$i][0] == 1){
					//グループ開始
					$spgloup = cutil::escape($this->m_valueArr[$i][1]);
					$spex = cutil::escape($this->m_valueArr[$i][2]);
					$str =<<< END_BLOCK
<optgroup label="{$spgloup}" {$spex} >
END_BLOCK;
					$retstr .= $str;
				}
				else if($this->m_valueArr[$i][0] == 2){
					$str =<<< END_BLOCK
</optgroup>
END_BLOCK;
					$retstr .= $str;
				}
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


