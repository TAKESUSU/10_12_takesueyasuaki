<?php
// var_dump($_POST);
// exit();
// 関数ファイル読み込み
include('functions.php');

//入力チェック(受信確認処理追加)
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


//DB接続します(エラー処理追加)
$pdo = db_conn();

//データ登録SQL作成
$sql = 'UPDATE gs_bm_table SET name=:a1, url=:a2, comment=:a3,image=:image WHERE id=:id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':a1', $name, PDO::PARAM_STR);
$stmt->bindValue(':a2', $url, PDO::PARAM_STR);
$stmt->bindValue(':a3', $comment, PDO::PARAM_STR);
// :imageを$file_nameで追加！
$stmt->bindValue(':image', $fileNameToSave, PDO::PARAM_STR);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

//4．データ登録処理後
if ($status == false) {
    errorMsg($stmt);
} else {
    header('Location:select.php');
    exit;
}
