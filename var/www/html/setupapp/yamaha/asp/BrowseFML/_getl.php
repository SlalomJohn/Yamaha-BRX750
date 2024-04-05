<?php
# Created by DoC. E-Mail: ruslan_smirnoff@mail.ru

$SIP="192.168.1.100";	# IP address Apache host
define('CRLF', "\r\n");

class streaminfo{
public $valid = false;
public $useragent = 'Winamp 2.81';

protected $headers = array();
protected $metadata = array();

public function __construct($location){
    $errno = $errstr = '';
    $t = parse_url($location);
if (empty($t['port'])) {$t['port']="80";}	# for empty vaules
if (empty($t['path'])) {$t['path']="";}		# for empty vaules
    $sock = fsockopen($t['host'], $t['port'], $errno, $errstr, 5);
    $path = isset($t['path'])?$t['path']:'/';
    if ($sock){
        $request = 'GET '.$path.' HTTP/1.0' . CRLF . 
            'Host: ' . $t['host'] . CRLF . 
            'Connection: Close' . CRLF . 
            'User-Agent: ' . $this->useragent . CRLF . 
            'Accept: */*' . CRLF . 
            'icy-metadata: 1'.CRLF.
            'icy-prebuffer: 65536'.CRLF.
            (isset($t['user'])?'Authorization: Basic '.base64_encode($t['user'].':'.$t['pass']).CRLF:'').
            'X-TipOfTheDay: Winamp "Classic" rulez all of them.' . CRLF . CRLF;
        if (fwrite($sock, $request)){
            $theaders = $line = '';
            while (!feof($sock)){ 
                $line = fgets($sock, 4096); 
                if('' == trim($line)){
                    break;
                }
                $theaders .= $line;
            }
            $theaders = explode(CRLF, $theaders);
            foreach ($theaders as $header){
                $t = explode(':', $header); 
                if (isset($t[0]) && trim($t[0]) != ''){
                    $name = preg_replace('/[^a-z][^a-z0-9]*/i','', strtolower(trim($t[0])));
                    array_shift($t);
                    $value = trim(implode(':', $t));
                    if ($value != ''){
                        if (is_numeric($value)){
                            $this->headers[$name] = (int)$value;
                        }else{
                            $this->headers[$name] = $value;
                        }
                    }
                }
            }
            if (!isset($this->headers['icymetaint'])){
                $data = ''; $metainterval = 512;
                while(!feof($sock)){
                    $data .= fgetc($sock);
                    if (strlen($data) >= $metainterval) break;
                }
               $this->print_data($data);
                $matches = array();
                preg_match_all('/([\x00-\xff]{2})\x0\x0([a-z]+)=/i', $data, $matches, PREG_OFFSET_CAPTURE);
               preg_match_all('/([a-z]+)=([a-z0-9\(\)\[\]., ]+)/i', $data, $matches, PREG_SPLIT_NO_EMPTY);
                $title = $artist = '';
                foreach ($matches[0] as $nr => $values){
                  $offset = $values[1];
                  $length = ord($values[0][0]) +
                            (ord($values[0][1]) * 256)+ 
                            (ord($values[0][2]) * 256*256)+ 
                            (ord($values[0][3]) * 256*256*256);
                  $info = substr($data, $offset + 4, $length);
                  $seperator = strpos($info, '=');
                  $this->metadata[substr($info, 0, $seperator)] = substr($info, $seperator + 1);
                    if (substr($info, 0, $seperator) == 'title') $title = substr($info, $seperator + 1);
                    if (substr($info, 0, $seperator) == 'artist') $artist = substr($info, $seperator + 1);
                }
                $this->metadata['streamtitle'] = $artist . ' - ' . $title;
            }else{
                $metainterval = $this->headers['icymetaint'];
                $intervals = 0;
                $metadata = '';
                while(1){
                    $data = '';
                    while(!feof($sock)){
                        $data .= fgetc($sock);
                        if (strlen($data) >= $metainterval) break;
                    }
                    $len = join(unpack('c', fgetc($sock))) * 16;
                    if ($len > 0){
                        $metadata = str_replace("\0", '', fread($sock, $len));
                        break;
                    }else{
                        $intervals++;
                        if ($intervals > 100) break;
                    }
                }
                $metarr = explode(';', $metadata);
                foreach ($metarr as $meta){
                    $t = explode('=', $meta);
                    if (isset($t[0]) && trim($t[0]) != ''){
                        $name = preg_replace('/[^a-z][^a-z0-9]*/i','', strtolower(trim($t[0])));
                        array_shift($t);
                        $value = trim(implode('=', $t));
                        if (substr($value, 0, 1) == '"' || substr($value, 0, 1) == "'"){
                            $value = substr($value, 1);
                        }
                        if (substr($value, -1) == '"' || substr($value, -1) == "'"){
                            $value = substr($value, 0, -1);
                        }
                        if ($value != ''){
                            $this->metadata[$name] = $value;
                        }
                    }
                }
            }

            fclose($sock);
            $this->valid = true;
        }else	{#echo 'unable to write.';
		}
    }else	{#echo 'no socket '.$errno.' - '.$errstr.'.';
	        }

}

public function print_data($data){
}

public function __get($name){
    if (isset($this->metadata[$name])){
        return $this->metadata[$name];
    }
    if (isset($this->headers[$name])){
        return $this->headers[$name];
    }
    return null;
 }
}

function random_string($length) {
    $str = random_bytes($length);
    $str = base64_encode($str);
    $str = str_replace(["+", "/", "="], "", $str);
    $str = substr($str, 0, $length);
    return $str;
}

$i=0;
$sl="";
$fh = fopen("_list","r");
while(!feof($fh)) {
$i++;
$bur=fgets($fh);
if ($bur!="") {
list($Surl, $Sname, $Sdesc, $Slogo) = explode('|',str_replace(array("\r", "\n"), '', $bur)); #</StationUrl>
$rr=random_string(4);
$Sid="RB_edgfaf11-43bc-4c27-872c-bf72de11".$i; #</StationId>
#$Slogo=""; #<Logo>
if ($Slogo == "")
 {
  $Slogo="http://".$SIP."/logos.jpg"; # default logo
 }
 else
  {
   $Slogo="http://".$SIP."/logos/".$Slogo; # logo from folder
  } #<Logo>
$Scountry="XX"; #<StationLocation>
$Sbandw="128"; #<StationBandWidth>
$Smime="MP3"; #<StationMime>

$t = new streaminfo($Surl); // get metadata
if (($t->icyname != "")&&($t->icyname != "no name")) {$Sname=$t->icyname;}
if ($t->icygenre != "") {$Sdesc=$t->icygenre;}
if ($t->icybr != "") {$Sbandw=$t->icybr;}
if ($t->contenttype != "") {$Smime=$t->contenttype;}

$sl=$sl."<Item>
<ItemType>Station</ItemType>
<StationId>$Sid</StationId>
<StationName>$Sname</StationName>
<StationUrl>$Surl</StationUrl>
<StationDesc>$Sdesc</StationDesc>
<Logo>$Slogo</Logo>
<StationFormat>assisi</StationFormat>
<StationLocation>$Scountry</StationLocation>
<StationBandWidth>$Sbandw</StationBandWidth>
<StationMime>$Smime</StationMime>
<Relia>3</Relia>
<Bookmark />
</Item>
";
}

}
fclose($fh);

$sl="<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>
<ListOfItems><ItemCount>".$i."</ItemCount>
$sl
</ListOfItems>";

$sfile="/var/www/html/setupapp/yamaha/asp/BrowseFML/_list.xml";
file_put_contents($sfile,$sl);

echo "List is saved.<br><br>";
?>

<form>
 <input type="button" value="Return back" onclick="history.back()">
</form>
