<?php
/*!
@file pdointerface.php
@brief PDOのインターフェイス
@copyright Copyright (c) 2017 Yamanoi Yasushi,Shimojima Ryo.
*/

////////////////////////////////////
//実行ブロック
//DB接続
$DB_PDO = new cpdo();

////////////////////////////////////
//クラスブロック
//--------------------------------------------------------------------------------------
/// PDOエラークラス
//--------------------------------------------------------------------------------------
class cpdo_err {
	//--------------------------------------------------------------------------------------
	/*!
	@brief  エラーの表示
	@param[in]  $mess   エラーメッセージ
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public static function err($mess){
		if(DB_DEBUG_MODE == 1){
			print_r($mess);
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  エラーを表示して強制終了
	@param[in]  $mess   エラーメッセージ
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public static function err_exit($mess){
		if(DB_DEBUG_MODE == 1){
			print_r($mess);
			exit();
		}
		else{
			$str = "Location: ". DB_ERR_REDIRECT_URL;
			header($str);
			//リダイレクトしたのでexit
			exit();
		}
	}
}

//--------------------------------------------------------------------------------------
/// PDOクラス
//--------------------------------------------------------------------------------------
class cpdo extends PDO{
	private $m_display_errors = false;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		try {
			$x = create_function('$x', 'return $x;');
			//接続
			$dsn = "{$x(DB_RDBMS)}:host={$x(DB_HOST)};dbname={$x(DB_NAME)};charset={$x(DB_CHARSET)}";
			parent::__construct($dsn,DB_USER,DB_PASS);
			if(DB_MYSQL_SET_NAMES == 1){
				$this->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->beginTransaction ();
				$this->exec("SET NAMES " . DB_CHARSET);
				$this->commit();
			}
			if(ini_get('display_errors')){
				$this->m_display_errors = true;
			}
			$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		} catch (PDOException $e){
			cpdo_err::err_exit('接続できません: ' . $e->getMessage());
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  display_errorsかどうかをチェックする
	@return display_errorsならtrue
	*/
	//--------------------------------------------------------------------------------------
	public function is_display_errors(){
		return $this->m_display_errors;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct(){
	}
}

//--------------------------------------------------------------------------------------
/// sqlのコアクラス
//--------------------------------------------------------------------------------------
class csqlcore {
	//エラーメッセージ等の情報配列
	public $retarr;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		$retarr = array();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  型を調べ、クエリに含める変数を的確に装飾する
	@param[in]  $value  装飾する文字列
	@return 装飾後の文字列
	*/
	//--------------------------------------------------------------------------------------
	public function make_safe_sqlstr($value){
		global $DB_PDO;
		$retstr = "";
		if(is_string($value)){
			$retstr = $DB_PDO->quote($value);
			return $retstr;
		}
		else if(is_int($value)){
			$retstr = $value;
			return $retstr;
		}
		else if(is_bool($value)){
			if($value){
				$retstr = "1";
			}
			else{
				$retstr = "0";
			}
			return $retstr;
		}
		else if(is_float($value)){
			$retstr = "'" . $value . "'";
			return $retstr;
		}
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  安全なLIKE文を作成する
	@param[in]  $value  文字列
	@return 装飾後の文字列
	*/
	//--------------------------------------------------------------------------------------
	public function make_safe_likestr($value){
		global $DB_PDO;
		$retstr = "";
		switch(gettype($value)){
			case "string":
				$retstr = $DB_PDO->quote("%{$value}%");
			break;
			default:
			break;
		}
		return $retstr;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief excute系のSQLを実行する
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	protected function excute_core(){
		global $DB_PDO;
		if(DB_DEBUG_MODE == 1){
			if($DB_PDO->is_display_errors()){
				//PHPエラー出力あり
				//PDO::ERRMODE_EXCEPTIONで例外処理
				try{
					$DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$DB_PDO->beginTransaction ();
					$this->res = $DB_PDO->prepare($this->retarr['sql']);
					$this->res->execute();
					$DB_PDO->commit();
					return true;
				}
				catch(Exception $e){
					$DB_PDO->rollBack();
					$this->retarr['error'] = $e->getMessage();
					$this->retarr['mess'] = 'クエリが実行できません。';
					cpdo_err::err_exit($this->retarr);
				}
				return false;
			}
		}
		//上記以外はPDO::ERRMODE_SILENT
		$DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$DB_PDO->beginTransaction();
		$this->res = $DB_PDO->prepare($this->retarr['sql']);
		$this->res->execute();
		// PDOStatement::errorCode()の返り値が'00000'以外の値はエラー
		if($this->res->errorCode() != '00000') {
			// 失敗
			$DB_PDO->rollBack();
			$err_arr = $this->res->errorInfo();
			$this->retarr['SQLSTATE_errorCode'] = $err_arr[0];
			$this->retarr['unique_errorCode'] = $err_arr[1];
			$this->retarr['mess'] = $err_arr[2];
			cpdo_err::err_exit($this->retarr);
			return false;
		}
		// 成功
		$DB_PDO->commit();
		return true;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief exec系のSQLを実行する
	@param[in]  $insert_flg  インサートかどうか
	@return インサートの場合は挿入されたPK、それ以外は影響したレコードの数
	*/
	//--------------------------------------------------------------------------------------
	protected function exec_core($insert_flg){
		global $DB_PDO;
		if(DB_DEBUG_MODE == 1){
			if($DB_PDO->is_display_errors()){
				try{
					$DB_PDO->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$DB_PDO->beginTransaction ();
					$retcode = $DB_PDO->exec($this->retarr['sql']);
					if($insert_flg){
						$insertid = $DB_PDO->lastInsertId();
						$DB_PDO->commit();
						return $insertid;
					}
					else{
						$DB_PDO->commit();
						return $retcode;
					}
				}
				catch(Exception $e){
					$DB_PDO->rollBack();
					$this->retarr['error'] = $e->getMessage();
					$this->retarr['mess'] = 'クエリが実行できません。';
					cpdo_err::err_exit($this->retarr);
				}
				return 0;
			}
		}
		//上記以外はPDO::ERRMODE_SILENT
		$DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$DB_PDO->beginTransaction();
		$retcode = $DB_PDO->exec($this->retarr['sql']);
		if($retcode === false) {
			// 失敗
			$DB_PDO->rollBack();
			$err_arr = $DB_PDO->errorInfo();
			$this->retarr['SQLSTATE_errorCode'] = $err_arr[0];
			$this->retarr['unique_errorCode'] = $err_arr[1];
			$this->retarr['mess'] = $err_arr[2];
			cpdo_err::err_exit($this->retarr);
			return 0;
		}
		// 成功
		if($insert_flg){
			$insertid = $DB_PDO->lastInsertId();
			$DB_PDO->commit();
			return $insertid;
		}
		else{
			$DB_PDO->commit();
			return $retcode;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief bind付excute系のSQLを実行する
	@param[in]  $values  SQLにはバインド用の値が入っている前提
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	protected function excute_bind_core($values){
		global $DB_PDO;
		if(DB_DEBUG_MODE == 1){
			if($DB_PDO->is_display_errors()){
				//PHPエラー出力あり
				//PDO::ERRMODE_EXCEPTIONで例外処理
				try{
					$DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$DB_PDO->beginTransaction ();
					//ここで、$this->retarr['sql']には「:id」のようにバインドされた PHP 変数が入っている
					$this->res = $DB_PDO->prepare($this->retarr['sql']);
					foreach($values as $key => $val){
						if(is_bool($val)){
							$this->res->bindValue($key, (bool)$val, PDO::PARAM_BOOL);
						}
						else if(is_int($val)){
							$this->res->bindValue($key, (int)$val, PDO::PARAM_INT);
						}
						else if(is_string($val)){
							$this->res->bindValue($key, $val, PDO::PARAM_STR);
						}
						else{
							$this->res->bindValue($key, $val, PDO::PARAM_STR);
						}
					}
					$this->res->execute();
					$DB_PDO->commit();
					return true;
				}
				catch(Exception $e){
					$DB_PDO->rollBack();
					$this->retarr['error'] = $e->getMessage();
					$this->retarr['mess'] = 'クエリが実行できません。';
					cpdo_err::err_exit($this->retarr);
				}
				return false;
			}
		}
		//上記以外はPDO::ERRMODE_SILENT
		$DB_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$DB_PDO->beginTransaction();
		$this->res = $DB_PDO->prepare($this->retarr['sql']);
		foreach($values as $key => $val){
			if(is_bool($val)){
				$this->res->bindValue($key, (bool)$val, PDO::PARAM_BOOL);
			}
			else if(is_int($val)){
				$this->res->bindValue($key, (int)$val, PDO::PARAM_INT);
			}
			else if(is_string($val)){
				$this->res->bindValue($key, $val, PDO::PARAM_STR);
			}
			else{
				$this->res->bindValue($key, $val, PDO::PARAM_STR);
			}
		}
		$this->res->execute();
		// PDOStatement::errorCode()の返り値が'00000'以外の値はエラー
		if($this->res->errorCode() != '00000') {
			// 失敗
			$DB_PDO->rollBack();
			$err_arr = $this->res->errorInfo();
			$this->retarr['SQLSTATE_errorCode'] = $err_arr[0];
			$this->retarr['unique_errorCode'] = $err_arr[1];
			$this->retarr['mess'] = $err_arr[2];
			cpdo_err::err_exit($this->retarr);
			return false;
		}
		// 成功
		$DB_PDO->commit();
		return true;
	}


	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct() {
	}
}

//--------------------------------------------------------------------------------------
/// レコードクラス
//--------------------------------------------------------------------------------------
class crecord extends csqlcore {
	//結果リソースの保持
	public $res = null;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		$this->res = null;
		parent::__construct();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  select文の実行
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $columns 取得するカラム
	@param[in]  $table 取得するテーブル
	@param[in]  $where 条件文（省略可）
	@param[in]  $orderby ならび順（省略可）
	@param[in]  $limit 抽出範囲（省略可）
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function select($debug,$columns,$table,$where = '1',$orderby = '',$limit = ''){
		global $DB_PDO;
		$this->free_res();
		if($orderby != ""){
			$orderby = "order by " . $orderby;
		}
		$this->retarr['sql'] =<<< END_BLOCK
select
{$columns} 
from
{$table}
where
{$where}
{$orderby}
{$limit}
END_BLOCK;
		if($debug){
			echo '<br />[sql debug]<br />';
			echo $this->retarr['sql'];
			echo '<br />[/sql debug]<br />';
		}
		//親クラスのexcute系関数を呼ぶ
		return $this->excute_core();
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief  プレースフォルダ付SELECT文の実行
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $bind_sql プレースホルダが入ったSQL文
	@param[in]  $values 値が入った配列
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function excute_bind($debug,$bind_sql,$values){
		global $DB_PDO;
		$this->free_res();
		//プレースホルダが入ったSQL文
		$this->retarr['sql'] = $bind_sql;
		if($debug){
			echo '<br />[sql debug]<br />';
			echo $this->retarr['sql'];
			echo '<br />[/sql debug]<br />';
		}
		//親クラスのexcute_bind_core関数を呼ぶ
		return  $this->excute_bind_core($values);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  select文の実行
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $values 値が入った配列
	@param[in]  $columns 取得するカラム
	@param[in]  $table 取得するテーブル
	@param[in]  $bind_where プレースフォルダつき条件文
	@param[in]  $orderby ならび順（省略可）
	@param[in]  $limit 抽出範囲（省略可）
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function select_bind($debug,$values,$columns,$table,$bind_where,$orderby = '',$limit = ''){
		if($orderby != ""){
			$orderby = "order by " . $orderby;
		}
		$bind_sql =<<< END_BLOCK
select
{$columns} 
from
{$table}
where
{$bind_where}
{$orderby}
{$limit}
END_BLOCK;
		//excute_bind関数を呼ぶ
		return $this->excute_bind($debug,$bind_sql,$values);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  select文のクエリ実行
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $sql SQL文
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function query($debug,$sql){
		global $DB_PDO;
		$this->free_res();
		$this->retarr['sql'] = $sql;
		if($debug){
			echo '<br />[sql debug]<br />';
			echo $this->retarr['sql'];
			echo '<br />[/sql debug]<br />';
		}
		//親クラスのexcute系関数を呼ぶ
		return $this->excute_core();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  1行分の取り出し
	@return 1行分の配列。空や失敗場合はfalse。リソースが無効の場合は例外
	*/
	//--------------------------------------------------------------------------------------
	public function fetch_assoc(){
		if($this->res){
			return $this->res->fetch(PDO::FETCH_ASSOC);
		}
		else{
			$this->retarr['mess'] = 'リソースがありません';
			cpdo_err::err_exit($this->retarr);
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  リソースと情報配列の解放
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function free_res(){
		//空の配列を代入
		$this->retarr = array();
		if($this->res){
			$this->res->closeCursor();
			$this->res = null;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  定型化されたget_all_count()関数（テーブルのすべての個数を得る）
	@param[in]	$debug クエリを出力するかどうか
	@param[in]	$table_name
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function get_all_count_core($debug,$table_name){
		//select()メンバ関数を呼ぶ
		if($this->select(
			$debug,					//デバッグ文字を出力するかどうか
			"count(*)",				//取得するカラム
			"{$table_name}",			//取得するテーブル
			"1"					//条件
		)){
			if($row = $this->fetch_assoc()){
				//取得した個数を返す
				return $row['count(*)'];
			}
			else{
				return 0;
			}
		}
		return 0;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  定型化されたget_all()関数（テーブルの指定範囲の一覧を得る）
	@param[in]  $debug クエリを出力するかどうか
	@param[in]	$from	抽出開始行
	@param[in]	$limit	抽出数
	@param[in]	$table_name テーブル名
	@param[in]	$sort_id_name ソートするID名
	@return	配列（2次元配列になる）
	*/
	//--------------------------------------------------------------------------------------
	public function get_all_core($debug,$from,$limit,$table_name,$sort_id_name){
		$arr = array();
		//select()メンバ関数を呼ぶ
		if($this->select(
			$debug,			//デバッグ表示するかどうか
			"*",			//取得するカラム
			"{$table_name}",	//取得するテーブル
			"1",			//条件
			"{$sort_id_name} asc",	//並び替え
			"limit " . $from . "," . $limit		//抽出開始行と抽出数
		)){
			while($row = $this->fetch_assoc()){
				$arr[] = $row;
			}
		}
		//順次取り出す
		//取得した配列を返す
		return $arr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  定型化されたget_all()関数（テーブルのすべての一覧を得る）
	@param[in]  $debug クエリを出力するかどうか
	@param[in]	$table_name テーブル名
	@param[in]	$sort_id_name ソートするID名
	@return	配列（2次元配列になる）
	*/
	//--------------------------------------------------------------------------------------
	public function get_alltable_core($debug,$table_name,$sort_id_name){
		$arr = array();
		//select()メンバ関数を呼ぶ
		if($this->select(
			$debug,			//デバッグ表示するかどうか
			"*",			//取得するカラム
			"{$table_name}",	//取得するテーブル
			"1",			//条件
			"{$sort_id_name} asc"	//並び替え
		)){
			while($row = $this->fetch_assoc()){
				$arr[] = $row;
			}
		}
		//順次取り出す
		//取得した配列を返す
		return $arr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	定型化されたget_tgt()関数。指定テーブルのIDの配列を得る
	@param[in]	$debug	デバッグ出力をするかどうか
	@param[in]	$id		ID
	@param[in] 	$table_name テーブル名
	@param[in]	$id_name 取得するID名
	@return	配列（1次元配列になる）空の場合はfalse
	*/
	//--------------------------------------------------------------------------------------
	public function get_tgt_core($debug,$id,$table_name,$id_name){
		if(!cutil::is_number($id)
		||  $id < 1){
			//falseを返す
			return false;
		}
		//select()メンバ関数を呼ぶ
		if($this->select(
			$debug,			//デバッグ表示するかどうか
			"*",			//取得するカラム
			"{$table_name}",	//取得するテーブル
			"{$id_name}=" . $id	//条件
		)){
			return $this->fetch_assoc();
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct(){
		$this->free_res();
		//親クラスのデストラクタを呼ぶ
		parent::__destruct();
	}
}

//--------------------------------------------------------------------------------------
/// データ変更クラス
//--------------------------------------------------------------------------------------
class cchange_ex extends csqlcore {
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		parent::__construct();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  インサート
	@param[in]  $table テーブル名
	@param[in]  $arr 追加する項目の配列
	@param[in]  $pkname プライマリキー名。指定するとこのフィールドの追加された値が返る。
	@return 成功すれば追加されたID。失敗は例外
	*/
	//--------------------------------------------------------------------------------------
	public function insert($table,&$arr){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || !is_array($arr) || count($arr) < 1){
			$this->retarr['mess'] = '追加すべきデータがありません。';
			cpdo_err::err_exit($this->retarr);
		}
		//追加するsql文を動的につくり出す
		$sqlarr = "(";
		$size = count($arr);
		$count = 1;
		foreach($arr as $i => $value){
			$sqlarr .=  $i;
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$sqlarr .= ") values (";
		$count = 1;
		foreach($arr as $i => $value){
			$sqlarr .=  $this->make_safe_sqlstr($value);
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$sqlarr .= ")";
		$this->retarr['sql'] =<<< END_BLOCK
insert into
{$table}
{$sqlarr}
END_BLOCK;
		//親クラスのexec系関数を呼ぶ
		return $this->exec_core(true);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  インサートをチェックする
	@param[in]  $table テーブル名
	@param[in]  $arr 追加する項目の配列
	@return なし（SQLを表示して終了）
	*/
	//--------------------------------------------------------------------------------------
	public function insert_chk($table,&$arr){
		if($table == '' || !is_array($arr) || count($arr) < 1){
			$this->retarr['mess'] = 'インサートチェックすべきデータが不定です。';
			cpdo_err::err_exit($this->retarr);
		}
		//追加するsql文を動的につくり出す
		$sqlarr = "(";
		$size = count($arr);
		$count = 1;
		foreach($arr as $i => $value){
			$sqlarr .=  $i;
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$sqlarr .= ") values (";
		$count = 1;
		foreach($arr as $i => $value){
			$sqlarr .=  $this->make_safe_sqlstr($value);
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$sqlarr .= ")";
		$sql =<<< END_BLOCK
insert into
{$table}
{$sqlarr}
END_BLOCK;
		echo $sql;
		exit;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  アップデート
	@param[in]  $table テーブル名
	@param[in]  $arr 更新する項目の配列
	@param[in]  $where 条件
	@return 成功すれば更新したレコード数。失敗は例外
	*/
	//--------------------------------------------------------------------------------------
	public function update($table,&$arr,$where){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || !is_array($arr) || count($arr) < 1
			|| $where == ''){
			$this->retarr['mess'] = '更新すべきデータがありません。';
			cpdo_err::err_exit($this->retarr);
		}
		$size = count($arr);
		$count = 1;
		$sqlarr = '';
		foreach($arr as $i => $value){
			$sqlarr .=  $i . " = " . $this->make_safe_sqlstr($value);
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$this->retarr['sql'] =<<< END_BLOCK
update
{$table}
set
{$sqlarr}
where
{$where}
END_BLOCK;
		//親クラスのexec系関数を呼ぶ
		return $this->exec_core(false);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  アップデートをチェックする
	@param[in]  $table テーブル名
	@param[in]  $arr 更新する項目の配列
	@param[in]  $where 条件
	@return なし（SQLを表示して終了）
	*/
	//--------------------------------------------------------------------------------------
	public function update_chk($table,&$arr,$where){
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || !is_array($arr) || count($arr) < 1
			|| $where == ''){
			$this->retarr['mess'] = '更新すべきデータがありません。';
			cpdo_err::err_exit($this->retarr);
		}
		$size = count($arr);
		$count = 1;
		$sqlarr = '';
		foreach($arr as $i => $value){
			$sqlarr .=  $i . " = " . $this->make_safe_sqlstr($value);
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$sql =<<< END_BLOCK
update
{$table}
set
{$sqlarr}
where
{$where}
END_BLOCK;
		echo $sql;
		exit;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  削除
	@param[in]  $table テーブル名
	@param[in]  $where 条件
	@return 成功すれば削除したレコード数。失敗は例外
	*/
	//--------------------------------------------------------------------------------------
	public function delete($table,$where){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || $where == ''){
			$this->retarr['mess'] = '削除すべきデータがありません。';
			cpdo_err::err_exit($this->retarr);
			return 0;
		}
		$this->retarr['sql'] =<<< END_BLOCK
delete
from
{$table}
where
{$where}
END_BLOCK;
		//親クラスのexec系関数を呼ぶ
		return $this->exec_core(false);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  削除チェック
	@param[in]  $table テーブル名
	@param[in]  $where 条件
	@return なし（SQLを表示して終了）
	*/
	//--------------------------------------------------------------------------------------
	public function delete_chk($table,$where){
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || $where == ''){
			$this->retarr['mess'] = '削除すべきデータがありません。';
			cpdo_err::err_exit($this->retarr);
		}
		$sql =<<< END_BLOCK
delete
from
{$table}
where
{$where}
END_BLOCK;
		echo $sql;
		exit;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  テーブルの中身を全削除
	@param[in]  $table テーブル名
	@return なし。失敗は例外
	*/
	//--------------------------------------------------------------------------------------
	public function delete_table($table){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == ''){
			$this->retarr['mess'] = '削除すべきテーブルがありません。';
			cpdo_err::err_exit($this->retarr);
			return 0;
		}
		$this->retarr['sql'] =<<< END_BLOCK
delete
from
{$table}
END_BLOCK;
		//親クラスのexec系関数を呼ぶ
		return $this->exec_core(false);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  テーブルの削除のチェック
	@param[in]  $table テーブル名
	@return なし（SQLを表示して終了）
	*/
	//--------------------------------------------------------------------------------------
	public function delete_table_chk($table){
		//空の配列を代入
		$this->retarr = array();
		if($table == ''){
			$this->retarr['mess'] = '削除すべきテーブルがありません。';
			cpdo_err::err_exit($this->retarr);
		}
		$sql =<<< END_BLOCK
delete
from
{$table}
END_BLOCK;
		echo $sql;
		exit;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  更新系SQLの発行
	@param[in]  $sql SQL文
	@return execの戻り値。失敗は例外
	*/
	//--------------------------------------------------------------------------------------
	public function exec($sql){
		global $DB_PDO;
		$this->retarr['sql'] = $sql;
		//親クラスのexec系関数を呼ぶ
		return $this->exec_core(false);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  更新系SQLの発行のチェック
	@param[in]  $sql SQL文
	@return なし（SQLを表示して終了）
	*/
	//--------------------------------------------------------------------------------------
	public function exec_chk($sql){
		//空の配列を代入
		$this->retarr = array();
		if($sql == ''){
			$this->retarr['mess'] = '実行すべきSQLがありません。';
			cpdo_err::err_exit($this->retarr);
		}
		echo $sql;
		exit;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	function __destruct() {
		//親クラスのデストラクタを呼ぶ
		parent::__destruct();
	}
}



//▲▲▲▲▲▲クラスブロック▲▲▲▲▲▲

?>
