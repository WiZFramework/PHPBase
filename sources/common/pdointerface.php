<?php
/*!
@file pdointerface.php
@brief PDOのインターフェイス
@copyright Copyright (c) 2021 Yamanoi Yasushi.
*/

////////////////////////////////////
//実行ブロック
//DB接続
$DB_PDO = new cpdo();

////////////////////////////////////
//クラスブロック

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
			$engine = DB_RDBMS;
			$host = DB_HOST;
			$database = DB_NAME;
			$charset = DB_CHARSET;
			$user = DB_USER;
			$pass = DB_PASS;
			//接続
			$dsn = "{$engine}:host={$host};dbname={$database};charset={$charset}";
			parent::__construct($dsn,$user,$pass);
			$this->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if(DB_MYSQL_SET_NAMES == 1){
				$this->beginTransaction ();
				$this->exec("SET NAMES {$charset}");
				$this->commit();
			}
			if(ini_get('display_errors')){
				$this->m_display_errors = true;
			}
			$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		} catch (PDOException $e){
			if($this->m_display_errors){
				echo '接続できません: ' . $e->getMessage();
			}
			exit();
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
	//結果リソースの保持
	public $res = null;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		$this->retarr = array();
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
	@brief crecord系のSQLを実行する
	@param[in]  $values  SQLにはバインド用の値が入っている前提(SQLで「?」に対応する、1次元配列)
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	protected function crecord_core_pres($values = array()){
		global $DB_PDO;
		try{
			$DB_PDO->beginTransaction ();
			$this->res = $DB_PDO->prepare($this->retarr['sql']);
			if(is_array($values) && count($values) > 0){
				foreach($values as $key => $val){
					if(is_int($val)){
						$param = PDO::PARAM_INT;
					}
					elseif(is_bool($val)){
						$param = PDO::PARAM_BOOL;
					}
					elseif(is_null($val)){
						$param = PDO::PARAM_NULL;
					}
					elseif(is_string($val)){
						$param = PDO::PARAM_STR;
					}
					else{
						$param = FALSE;
					}
					if($param !== false){
						$this->res->bindValue($key + 1, $val, $param);
					}
				}
			}
			$this->res->execute();
			$DB_PDO->commit();
			return true;
		} 
		catch (PDOException $e){
			$DB_PDO->rollback();
			if($DB_PDO->is_display_errors()){
				echo 'SELECT文が実行できません: ' . $e->getMessage();
			}
			exit();
		}
		return false;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief cchange系のSQLを実行する
	@param[in]  $values  SQLにはバインド用の値が入っている前提
	@param[in]  $is_insert  インサートかどうか
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	protected function cchange_core($values = array(),$is_insert = false){
		global $DB_PDO;
		try{
			$DB_PDO->beginTransaction ();
			$this->res = $DB_PDO->prepare($this->retarr['sql']);
			if(is_array($values) && count($values) > 0){
				foreach($values as $key => $val){
					if(is_int($val)){
						$param = PDO::PARAM_INT;
					}
					elseif(is_bool($val)){
						$param = PDO::PARAM_BOOL;
					}
					elseif(is_null($val)){
						$param = PDO::PARAM_NULL;
					}
					elseif(is_string($val)){
						$param = PDO::PARAM_STR;
					}
					else{
						$param = FALSE;
					}
					if($param !== false){
						$this->res->bindValue($key, $val, $param);
					}
				}
			}
			$this->res->execute();
			if($is_insert){
				$this->retarr['lastinsert'] = $DB_PDO->lastInsertId();
			}
			$DB_PDO->commit();
			return true;
		}
		catch (PDOException $e){
			$DB_PDO->rollback();
			if($DB_PDO->is_display_errors()){
				echo '更新系のSQLが実行できません: ' . $e->getMessage();
			}
			exit();
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief cchange系のSQLを実行する
	@param[in]  $values   SQLにはバインド用の値が入っている前提(SQLで「?」に対応する、1次元配列)
	@param[in]  $is_insert  インサートかどうか
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	protected function cchange_core_pres($values = array(),$is_insert = false){
		global $DB_PDO;
		try{
			$DB_PDO->beginTransaction ();
			$this->res = $DB_PDO->prepare($this->retarr['sql']);
			if(is_array($values) && count($values) > 0){
				foreach($values as $key => $val){
					if(is_int($val)){
						$param = PDO::PARAM_INT;
					}
					elseif(is_bool($val)){
						$param = PDO::PARAM_BOOL;
					}
					elseif(is_null($val)){
						$param = PDO::PARAM_NULL;
					}
					elseif(is_string($val)){
						$param = PDO::PARAM_STR;
					}
					else{
						$param = FALSE;
					}
					if($param !== false){
						$this->res->bindValue($key + 1, $val, $param);
					}
				}
			}
			$this->res->execute();
			if($is_insert){
				$this->retarr['lastinsert'] = $DB_PDO->lastInsertId();
			}
			$DB_PDO->commit();
			return true;
		}
		catch (PDOException $e){
			$DB_PDO->rollback();
			if($DB_PDO->is_display_errors()){
				echo '更新バインド系のSQLが実行できません: ' . $e->getMessage();
			}
			exit();
		}
		return false;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief  1行分の取り出し
	@return 1行分の配列。空や失敗場合はfalse。リソースが無効の場合は例外
	*/
	//--------------------------------------------------------------------------------------
	public function fetch_assoc(){
		global $DB_PDO;
		if($this->res){
			return $this->res->fetch(PDO::FETCH_ASSOC);
		}
		else{
			if($DB_PDO->is_display_errors()){
				echo 'リソースがありません';
			}
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
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	protected function __destruct() {
		$this->free_res();
	}
}

//--------------------------------------------------------------------------------------
/// レコードクラス
//--------------------------------------------------------------------------------------
class crecord extends csqlcore {
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
	@param[in]  $values SQLにはバインド用の値が入っている前提(SQLで「?」に対応する、1次元配列)
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function select($debug,$columns,$table,$where = '1',$orderby = '',$limit = '',$values = array()){
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
		//親クラスのcrecord_core_pres関数を呼ぶ
		$ret =  $this->crecord_core_pres($values);
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  select文のクエリ実行(select関数では書けない場合、利用)
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $sql SQL文
	@param[in]  $values SQLにはバインド用の値が入っている前提(SQLで「?」に対応する、1次元配列)
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function select_query($debug,$sql,$values = array()){
		global $DB_PDO;
		$this->free_res();
		$this->retarr['sql'] = $sql;
		//親クラスのcrecord_core_pres関数を呼ぶ
		$ret =  $this->crecord_core_pres($values);
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		return $ret;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief  定型化されたget_all_count()関数（テーブルのすべての個数を得る）
	@param[in]	$debug クエリを出力するかどうか
	@param[in]	$table_name
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function get_all_count_simple($debug,$table_name){
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
	public function get_all_simple($debug,$from,$limit,$table_name,$sort_id_name){
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
	public function get_alltable_simple($debug,$table_name,$sort_id_name){
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
	public function get_tgt_simple($debug,$id,$table_name,$id_name){
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
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $arr 追加する項目の配列
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 成功すれば追加されたID
	*/
	//--------------------------------------------------------------------------------------
	public function insert($debug,$table,&$arr,$chk = false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || !is_array($arr) || count($arr) < 1){
			if($DB_PDO->is_display_errors()){
				echo '追加すべきデータがありません。';
			}
			exit();
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
		//プレイスフォルダの作成
		foreach($arr as $i => $value){
			$sqlarr .= ":" . $i;
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
		//データの作成
		$values = array();
		foreach($arr as $key => $val){
			$values[":" . $key] = $val;
		}
		if($chk){
			echo 'SQL: ' . $this->retarr['sql'] . '<br>';
			echo 'DATA: <br>';
			print_r($values);
			exit();
		}
		//親クラスのexcute_core関数を呼ぶ
		$ret = 0;
		if($this->cchange_core($values,true)){
			$ret = $this->retarr['lastinsert'];
		}
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		//追加したID
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  アップデート
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $arr 更新する項目の配列
	@param[in]  $where 条件
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 影響を受けた行数
	*/
	//--------------------------------------------------------------------------------------
	public function update($debug,$table,&$arr,$where,$chk=false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || !is_array($arr) || count($arr) < 1
			|| $where == ''){
			if($DB_PDO->is_display_errors()){
				echo '更新すべきデータがありません。';
			}
			exit();
		}
		$size = count($arr);
		$count = 1;
		$sqlarr = '';
		//プレイスフォルダの作成
		foreach($arr as $i => $value){
			$sqlarr .=  $i . " = " . ":" . $i;
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$this->retarr['sql'] = <<< END_BLOCK
update
{$table}
set
{$sqlarr}
where
{$where}
END_BLOCK;
		//データの作成
		$values = array();
		foreach($arr as $key => $val){
			$values[":" . $key] = $val;
		}
		if($chk){
			echo 'SQL: ' . $this->retarr['sql'] . '<br>';
			echo 'DATA: <br>';
			print_r($values);
			exit();
		}
		//親クラスのcchange_core関数を呼ぶ
		$ret = 0;
		if($this->cchange_core($values)){
			$ret = $this->res->rowCount();
		}
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  削除
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $where 条件
	@param[in]  $values プレイスフォルダを使う場合のデータ
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 影響を受けた行数
	*/
	//--------------------------------------------------------------------------------------
	public function delete($debug,$table,$where,$values = array(),$chk = false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || $where == ''){
			if($DB_PDO->is_display_errors()){
				echo '削除すべきデータがありません。';
			}
			exit();
		}
		$this->retarr['sql'] =<<< END_BLOCK
delete
from
{$table}
where
{$where}
END_BLOCK;
		if($chk){
			echo 'SQL: ' . $this->retarr['sql'] . '<br>';
			echo 'DATA: <br>';
			print_r($values);
			exit();
		}
		//親クラスのcchange_core関数を呼ぶ
		$ret =  0;
		if($this->cchange_core_pres($values)){
			$ret = $this->res->rowCount();
		}
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  テーブルの中身を全削除
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 影響を受けた行数
	*/
	//--------------------------------------------------------------------------------------
	public function delete_table($debug,$table,$chk = false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == ''){
			if($DB_PDO->is_display_errors()){
				echo '削除すべきテーブルがありません。';
			}
			exit();
		}
		$this->retarr['sql'] =<<< END_BLOCK
delete
from
{$table}
END_BLOCK;
		if($chk){
			echo 'SQL: ' . $this->retarr['sql'] . '<br>';
			echo 'DATA: <br>';
			print_r($values);
			exit();
		}
		//親クラスのcchange_core関数を呼ぶ
		$ret =  0;
		if($this->cchange_core(array())){
			$ret = $this->res->rowCount();
		}
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		return $ret;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief  select文のクエリ実行(select関数では書けない場合、利用)
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $sql SQL文
	@param[in]  $values 値が入った配列（条件式等がプレースフォルダの場合）
	@param[in]  $is_insert  インサートかどうか
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 影響を受けた行数と場合によってはインサートID
	*/
	//--------------------------------------------------------------------------------------
	public function change_query($debug,$sql,$values = array(),$is_insert = false,$chk = false){
		global $DB_PDO;
		$this->free_res();
		$this->retarr['sql'] = $sql;
		if($chk){
			echo 'SQL: ' . $this->retarr['sql'] . '<br>';
			echo 'DATA: <br>';
			print_r($values);
			exit();
		}
		//親クラスのcchange_core_pres関数を呼ぶ
		$ret =  array();
		if($this->cchange_core_pres($values)){
			$ret['rowCount'] = $this->res->rowCount();
			if($is_insert){
				$ret['lastInsert']  = $this->retarr['lastinsert'];
			}
		}
		if($debug){
			echo '<br />[sql debug]<br />';
			$this->res->debugDumpParams();
			echo '<br />[/sql debug]<br />';
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct() {
		//親クラスのデストラクタを呼ぶ
		parent::__destruct();
	}
}



//▲▲▲▲▲▲クラスブロック▲▲▲▲▲▲

