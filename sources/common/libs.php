<?php
/*!
@file libs.php
@brief ライブラリをすべてインクルード。各ページはこのファイルをインクルードする
@copyright Copyright (c) 2021 Yamanoi Yasushi.
*/
//ユーティリティクラス他
require_once("function.php");
//コントロールクラス他
require_once("controls.php");
//コントロールクラスエクストラ（オプション）
require_once("controls_ex.php");
//他ユーティリティ
require_once("contents_func.php");
//このコンテンツ独自の
//DBの継承クラスの定義
require_once("contents_db.php");
