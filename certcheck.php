<?php
//検査したいサーバ名とFQDNを配列に羅列する
$servers = [
    'www.soumu.go.jp'       => 'www.soumu.go.jp',
];
$mail_to = "yourmail@example.com";      //通知先
$mail_subject = "SSL cert expiration";  //メールのタイトル
$mail_headers = 'From: root@example.com' . "\r\n" .  //送信者メールアドレス
    'Reply-To: yourmail@example.com' . "\r\n" .      //返信先メールアドレス
    'X-Mailer: PHP/' . phpversion();                 //X-Mailerヘッダ
 
$nowtime  = time();
$message  = "SSL cert expiration\n\n";    //本文
 
foreach ($servers as $name => $fqdn) {
    $statement = "openssl s_client -connect $fqdn:443 < /dev/null 2> /dev/null | openssl x509 -text | grep 'Not' | grep 'After'";
    $res = shell_exec($statement);
    $ans = explode(' : ', $res);
    $time = strtotime(trim($ans[1]));
    $date = date('Y/m/d H:i:s', $time);
    $message .= sprintf("%-32s : %s", $fqdn, $date);
    if (($time - $nowtime) < 60*24*3600) {  //期限切れまで60日以下の場合は*を一つ付ける
        $message .= " *";
    }
    if (($time - $nowtime) < 30*24*3600) {  //期限切れまで30日以下の場合はもう一つ*を付ける
        $message .= "*";
    }
    $message .= "\n";
}
//echo $message;
mail($mail_to, $mail_subject, $message, $mail_headers);
