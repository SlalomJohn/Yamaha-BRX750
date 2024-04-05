# Yamaha-BRX750
<h1 align="center">Emulator WEB-based interface for radio station's Yamaha BRX-750</h1>

- This is a self hosted replacement for the vTuner internet radio service which older AVRs use (e.g. Yamaha BRX-750/MCR-750/MCR-755).
- It emulates a vTuner backend to provide your AVR with the necessary information to play self defined categorized internet radio stations and listen to Radio stations.

> [!NOTE]
> Supported AVR:
> + Yamaha BRX-750

<h3>Installation (Ubuntu/Debian based system):</h3>

1. install **Apache2, php, bind9**
2. Set in **/etc/php**
```
	max_execution_time = 120
	max_input_time = 120
```
3. Enable and restart `bind9` and `apache`
```
	systemctl enable bind9 apache2
	systemctl restart bind9 apache2
```
4. Copy/modify files to **/etc/bind** from **/etc/bind** in this repo
5. Copy files to **/var/www** from **/var/www** in this repo
6. Edit file **/var/www/html/setupapp/yamaha/asp/BrowseFML/_getl.php** and set **$SIP** to your server IP, e.g. 192.168.1.1 (in internal LAN), or real IP for VPS
7. Run
```
	chmod -R www-data:www-data /var/www
```
8. Allow in firewall ports: **80/tcp, 53/udp**
9. Set on `Yamaha` in `LAN settings` DNS to your server IP

`Enjoy!`

<h3>For editing station list:</h3>

1. Open in browser **http://_your_server_ip**
2. Edit/add lines as format (delimeter as symbol **|**):
```
	URL|StationName|StationGenre|StationLogo
```
   where
```
	URL		full url for audio stream in audio/mpeg format (e.g. Shoutcast), ACC/OGG not supported!
	StationName	Optional station name, exmaple - Rock station forever
	StationGenre	Optional station genres, exmaple - hard Rock, Rock, Ambient
	StationLogo	Optional station logo filename in /var/www/html/logos , exmaple - LogoStation.jpg, support JPG/PNG files, don't use big size (slow loading) and long length (cropping) images, don't use spaces in file name!
```
3. New stations logos put in **/var/www/html/logos**
4. If logo not set - used file **/var/www/html/logos.jpg**

> [!NOTE]
> One station - one line, if value not set - use empty value - example:
>```
>	URL|||
>```

5. After end editing - press button **"Save list"**
6. For renew XML list - press button **"Refresh XML file"** and wait (~20-40 sec, script is freshing online parameters data from URL's - e.g. Station name, Genres, Bitrate)
