<?php
require_once("inc_base.php");
require_once($CMS_COMMON_INCLUDE_DIR . "libs.php");

?>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/main.css" rel="stylesheet" type="text/css">
<title>メインメニュー</title>
<script type="text/javascript">
<!--


// -->
</script>
</head>
<body>
<!-- 全体コンテナ　-->
<div id="container">
<?php require_once("gmenu.php"); ?>
<div id="headTitle">
<h2>メインメニュー</h2>
</div>
<!-- コンテンツ　-->
<div id="inquiry">
<br />
<table >
<tr>
<td class="nobottom"><a href="prefecture_list.php">都道府県管理</a></td>
</tr>
</table>
<p>&nbsp;</p>
</div>
<!-- /コンテンツ　-->
</div>
<!-- /全体コンテナ　-->
</body>
</html>

