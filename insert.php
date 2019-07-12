<?php
//関数を記述したファイルの読み込み
include('functions.php');

// 入力チェック
if (
    !isset($_POST['name']) || $_POST['name'] == '' ||
    !isset($_POST['url']) || $_POST['url'] == ''
) {
    exit('ParamError');
}

//POSTデータ取得
$name = $_POST['name'];
$url = $_POST['url'];
$comment = $_POST['comment'];

//Fileアップロードチェック
if (isset($_FILES['upfile']) && $_FILES['upfile']['error'] == 0) {
    // ファイルをアップロードしたときの処理
    // ①送信ファイルの情報取得
    $uploadedFileName = $_FILES['upfile']['name'];
    $tempPathName = $_FILES['upfile']['tmp_name'];
    $fileDirectoryPath = 'upload/';
    // ②File名の準備
    $extension = pathinfo($uploadedFileName, PATHINFO_EXTENSION);
    $uniqueName = date('YmdHis') . md5(session_id()) . "." . $extension;
    $fileNameToSave = $fileDirectoryPath . $uniqueName;
    // ③サーバの保存領域に移動&④表示
    if (is_uploaded_file($tempPathName)) {
        if (move_uploaded_file($tempPathName, $fileNameToSave)) {
            chmod($fileNameToSave, 0644);
        } else {
            exit('保存に失敗しました');
        }
    } else {
        exit('画像があがってないです');
    }
} else {
    // ファイルをアップしていないときの処理
    exit('画像が送信されていません');
}


//DB接続
$pdo = db_conn();
// $dbn = 'mysql:dbname=gsf_d03_db12;charset=utf8;port=3306;host=localhost';
// $user = 'root';
// $pwd = '';

// try {
//     $pdo = new PDO($dbn, $user, $pwd);
// } catch (PDOException $e) {
//     exit('dbError : ' . $e->getMessage());
// }

//データ登録SQL作成
$sql  = 'INSERT INTO gs_bm_table(id,name,url,comment,image,indate)VALUES(NULL,:a1,:a2,:a3,:image,sysdate())';

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':a1', $name, PDO::PARAM_STR);    //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':a2', $url, PDO::PARAM_STR);   //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':a3', $comment, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
// :imageを$file_nameで追加！
$stmt->bindValue(':image', $fileNameToSave, PDO::PARAM_STR);
$status = $stmt->execute();

//４．データ登録処理後
if ($status == false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit('sqlError:' . $error[2]);
} else {
    //５．index.phpへリダイレクト
    header('Location:index.php');
    exit;
}
