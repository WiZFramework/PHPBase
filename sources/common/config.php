<?php
/*!
@file config.php
@brief DB接続変数と、エンコードの定義
@copyright Copyright (c) 2017 Yamanoi Yasushi,Shimojima Ryo.
*/
////////////////////////////////////
//実行ブロック
//データベースマネージメント
define('DB_RDBMS','mysql');
//MySQLの場合のキャラ設定にSET NAMESを使用するかどうか
define('DB_MYSQL_SET_NAMES','1');
//ホスト(ローカルの場合は'localhost'と記述)
define('DB_HOST','********');
//ユーザー
define('DB_USER','********');
//パスワード
define('DB_PASS','********');
//DB名
define('DB_NAME','********');
//DBのキャラセット
define('DB_CHARSET','utf8');
//PHPのキャラセット
define('PHP_CHARSET','UTF-8');
//デバッグかどうか
define('DB_DEBUG_MODE','1');
//サイトのルートからのパス
define('SITE_BASE_URL','/sources/');
//デバッグでない場合のエラー時のリダイレクト先
define('DB_ERR_REDIRECT_URL',SITE_BASE_URL . 'errpage.php');

?>
