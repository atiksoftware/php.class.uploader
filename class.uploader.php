<?php

	class Uploader
	{
		public $buffer_size = "32768";
		
		function __construct(){
			$this->boundary = sha1(time());
			$this->crlf     = "\r\n";
			$this->elements = [
				"vals"  => [],
				"files" => []
			];
			$this->sizes    = [
				"vals"  => 0,
				"files" => 0,
				"total" => 0
			];
		}

		function Upload(
			$url,
			$headers = [],
			$vals = [],
			$files = []
		){
			$url_host = parse_url($url, PHP_URL_HOST);
			$url_path = parse_url($url, PHP_URL_PATH);

			$this->enc_vals($vals);
			$this->enc_files($files);

			$this->sizes["total"] += strlen("--".$this->boundary.'--');
			$response = '';
			if($fp = fsockopen($url_host, 80, $errno, $errstr, 20)){
				$write = "POST $url_path HTTP/1.1\r\n"
					."Host: $url_host\r\n"
					."Content-type: multipart/form-data; boundary=".$this->boundary."\r\n"
					."Content-Length: ".($this->sizes["total"])."\r\n"
					.implode("\r\n",$headers)."\r\n"
					."Connection: Close\r\n\r\n";
				fwrite($fp, $write);
				foreach($this->elements["vals"] as $val){
					fwrite($fp, $val["head"]);
					fwrite($fp, $val["body"]);
					fwrite($fp, $this->crlf);
				}
				foreach($this->elements["files"] as $file){
					fwrite($fp, $file["head"]);
					/* File Read Start */
					$dt = fopen($file["body"], "rb");
					while (!feof($dt)) {
						//$icerik = fread($dt, 8192);
						$icerik = fread($dt, $this->buffer_size);
						echo $icerik;
						fwrite($fp, $icerik);
					}
					fclose($dt);
					/* File Read End */
					fwrite($fp, $this->crlf);
				}
				fwrite($fp, "--".$this->boundary.'--');

				while($line = fgets($fp)){
					if($line !== false){
						$response .= $line;
					}
				}
				fclose($fp);
			}
			else{

			}

		}

		function enc_vals($vals){
			foreach($vals as $key => $value){
				$head = '--'.$this->boundary.$this->crlf
					.'Content-Disposition: form-data; name="'.$key.'"'.$this->crlf
					.'Content-Length: '.strlen($value).$this->crlf.$this->crlf;
				$this->elements["vals"][] = [
					"head" =>  $head,
					"body" =>  $value,
				];
				$size = strlen($head.$value) + 2;
				$this->sizes["vals"]  += ($size);
				$this->sizes["total"] += ($size);
			}
		}
		function enc_files($files){
			foreach($files as $key => $file){
				$head = '--'.$this->boundary.$this->crlf
					.'Content-Disposition: form-data; name="'.$key.'"; filename="'.basename($file).'"'.$this->crlf
					.'Content-Type: '.mime_content_type($file).$this->crlf
					.'Content-Length: '.filesize($file).$this->crlf
					.'Content-Type: application/octet-stream'.$this->crlf.$this->crlf;
					$this->elements["files"][] = [
						"head" =>  $head,
						"body" =>  $file,
					];
					$size = strlen($head)+filesize($file) + 2;
					$this->sizes["files"] += ($size);
					$this->sizes["total"] += ($size);
			}
		}

	}


	$up = new Uploader();

	$up->upload(
		"http://127.0.0.1/sock/up.php",
		[
			"Cache-Control: max-age=0",
			"Upgrade-Insecure-Requests: 1",
			"User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
			"Accept-Encoding: gzip, deflate, br",
			"Accept-Language: tr-TR,tr;q=0.8,en-US;q=0.6,en;q=0.4,de;q=0.2"
		],
		["fname" => "bbb.mp4","xname" => "bbb.mp4"],
		["file" => "a.png"]
	);




















	/**/
