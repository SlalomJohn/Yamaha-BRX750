<?php
# Created by DoC. E-Mail: ruslan_smirnoff@mail.ru

function random_string($length) {
    $str = random_bytes($length);
    $str = base64_encode($str);
    $str = str_replace(["+", "/", "="], "", $str);
    $str = substr($str, 0, $length);
    return $str;
}

$sfile="/var/www/html/setupapp/yamaha/asp/BrowseFML/_list";
if (isset($_POST["SText"])) {
$sso=$_POST["SText"];
$sso = str_replace(array("\r\n", "\r", "\n"), "\n", $sso);
$rnd=random_string(4);
$date   = new DateTime();
$rdate = $date->format('Y-m-d_H-i-s');
rename($sfile,$sfile."_".$rdate."_".$rnd);
echo "File saved!</br></br>";
file_put_contents($sfile,$sso);
}

$homepage = file_get_contents($sfile);

echo "
<form action='index.php' method='post'>
<textarea name='SText' cols='133' rows='65'>$homepage</textarea><br>
<input type='submit' value='Save list' />
</form><br><br>
<form action='/setupapp/yamaha/asp/BrowseFML/_getl.php'>
    <input type='submit' value='Refresh XML file' />
</form>
";

?>