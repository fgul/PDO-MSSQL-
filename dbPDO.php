<?
class dbMSSQLPDO {

	private $sayfaUstYazi;
	private $sayfaAltYazi;
	private $sayfaIlk;
	private $sayfaSon;
	private $sayfaAdet;
	private $sayfa;	
	public $dbPDO;
		
	public function __construct() {
			
	}
	
	public function dbBaglan(){ 
		$HOST 	= "127.0.0.1";
		$DB 	= "DBNAME";
		$USR	= "KULLANICI";
		$PSW	= "SIFRE";
			
		try {
			$dbPDO = new PDO(
							'dblib:host='.$HOST.';port=3306;dbname='.$DB.';', 
							$USR, 
							$PSW,
							array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
							);
            //$dbPDO->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
			//$dbPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            //$dbh = new PDO('mysql:unix_socket=/tmp/mysql.sock;dbname='.DB.';', USR, PSW); 
            $dbPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); //error açılması
            $dbPDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ); // default obje olarak belirlenmesi
		    
		    $this->dbPDO = $dbPDO;
		    return $dbPDO;
		    
		} catch (PDOException $e) {
		    print "Hata!: " . $e->getMessage() . "<br/>";
		    die();
		    
		}
		
	}
	
	public function getdbPDO(){
		return $this->dbPDO;
	}
	
	
	public function hataMail($dbPDO, $stmt, $sql, $filtre=array(), $kime='fatihgulgs@gmail.com', $ekran="1") { 
		global $cMail;
		
	   	$icerik_arr = array();
	   	$sql_hata	= array();
	   	
	   	$sql_hata	= $stmt->errorInfo();
	   	$adresarr 	= explode(".",$_SERVER['SERVER_NAME']);
	   	$adres 		= $adresarr[0].".".$adresarr[1];  
	   	$sorgu		= $this->getSQL($sql, $filtre);
	    		    
	    $konu	= "SQL HATASI - ".$sql_hata[2];
	    $icerik_arr[]	= "Beklenmeyen bir hata oluştu! Bu hatanın oluştuğuna dair bilgi yetkililere iletildi ve en kısa sürede çözülecektir.";
	    $icerik_arr[]	= "Username: ".$_SESSION['kullanici'];
	    $icerik_arr[]	= "IP: ".$_SERVER['REMOTE_ADDR'];
	    $icerik_arr[]	= "Adres: ".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
	    $icerik_arr[]	= "Geldigi Sayfa: ".$_SERVER['HTTP_REFERER'];
	    $icerik_arr[]	= "Sorgu: ".$sorgu;
	    $icerik_arr[]	= "Filtre: ".json_encode($filtre);
	    $icerik_arr[]	= "Sql: ".$sql;
	    $icerik_arr[]	= "Hata: ".$sql_hata[2];
	    $icerik			= implode("\n", $icerik_arr); 
	    mail($kime, $konu, $icerik,"From:HATA - $adres<fatihgulgs@gmail.com>\nMIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n");
	    $cMail->Gonder($kime,$konu,$icerik);
	    
	    if($ekran){
		    $icerik_arr[0]	= "<b>Beklenmeyen bir hata oluştu! Bu hatanın oluştuğuna dair bilgi yetkililere iletildi ve en kısa sürede çözülecektir.</b>";
		    $icerik			= implode('<br>', $icerik_arr);	    
		   	//$stmt->debugDumpParams();
		   	echo   "<div class='col-md-12'> 
		   				<div class='alert alert-danger' role='alert'> 
		   					<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>
		   					$icerik 
		   				</div> 
		   			</div>";
	   	}
	   	
	   	die(); 
	
	} 
	
	public function getSQL($sql, $filtre){
		if(count($filtre)==0) return $sql;
		
		$sql_echo = $sql;
		foreach($filtre as $key => $value){
			//$sql_echo = str_replace($key, "'$value'", $sql_echo);
			$sql_echo = preg_replace('/'.$key.'\b/', "'$value'", $sql_echo);
		}
		return $sql_echo;
		
	}
	
	public function SQL($sql, $filtre){
		
		if(dbg()) {
			if(count($filtre)==0) { 
				echo "
					<div>
						<img src='../images/sql-icon.png' onclick='$(\"#dbg\").toggle();' style='cursor: pointer' width='25' height='25' >
						<div id='dbg' style='display: none;'>
							$sql
						</div>
					</div>	
					";
				
			}else{
				
				$sql_echo = $sql;
				foreach($filtre as $key => $value){
					$sql_echo = preg_replace('/'.$key.'\b/', "'$value'", $sql_echo);
				}
				
				echo "
					<div>
						<img src='../images/sql-icon.png' onclick='$(\"#dbg\").toggle();' style='cursor: pointer' width='25' height='25' >
						<div id='dbg' style='display: none;'>
							$sql_echo
						</div>
					</div>	
					";
			}
		}
		
	}
	
	public function setSayfalama($sayfa, $sayfaToplam, $sayfaAdet) { 

		//Sayfalama için ekledim.
		if(intval($sayfa)<=0 || intval($sayfaToplam)<($sayfa-1)*$sayfaAdet) $sayfa = 1;
		if(intval($sayfaAdet)<=0) $sayfaAdet = 20;
		$sayfaIlk 	= ($sayfa-1) * $sayfaAdet;
		$sayfaSon 	= $sayfa * $sayfaAdet;
		
		$sayfaSayisi = ceil($sayfaToplam/$sayfaAdet);
		$sayfaAltYazi = "";
		for($i = 1; $i <= $sayfaSayisi; $i++){
			if($i==$sayfa) 
				$sayfaAltYazi .= "<a href=\"javascript:fsubmit('form',$i,'')\"><font color='red'> $i </font></a>";	
			else 
				$sayfaAltYazi .= "<a href=\"javascript:fsubmit('form',$i,'')\"> $i </a>";	
			
			if($i%50 == 0) $sayfaAltYazi .= "<br>";
		}
		if($sayfaToplam>0) {
			$sayfaUstYazi = $sayfaToplam . " Sonuç içinde " . ($sayfaIlk+1) . " - " . (($sayfaToplam>$sayfaSon)?$sayfaSon:$sayfaToplam) . " arası sonuçlar"; 
			$sayfaOnceki  = $sayfa - 1;
			$sayfaSonraki = $sayfa + 1; 
			if($sayfa == 1){
				$sayfaAltYazi = "<a href=\"javascript:fsubmit('form',1,'')\"> <i class='glyphicon glyphicon-backward'></i> </a>" . $sayfaAltYazi;	
			} else{
				$sayfaAltYazi = "<a href=\"javascript:fsubmit('form',$sayfaOnceki,'')\"> <i class='glyphicon glyphicon-backward'></i> </a>" . $sayfaAltYazi;
			}
			if($sayfa == $sayfaSayisi){
				$sayfaAltYazi = $sayfaAltYazi . "<a href=\"javascript:fsubmit('form',$sayfaSayisi,'')\"> <i class='glyphicon glyphicon-forward'></i> </a>";
			} else{
				$sayfaAltYazi = $sayfaAltYazi . "<a href=\"javascript:fsubmit('form',$sayfaSonraki,'')\"> <i class='glyphicon glyphicon-forward'></i> </a>";
			}			
			$sayfaAltYazi = "<a href=\"javascript:fsubmit('form',1,'')\"> <i class='glyphicon glyphicon-fast-backward'></i> </a>" . $sayfaAltYazi;
			$sayfaAltYazi = $sayfaAltYazi . "<a href=\"javascript:fsubmit('form',$sayfaSayisi,'')\"> <i class='glyphicon glyphicon-fast-forward'></i> </a>";
			
		} else {
			$sayfaUstYazi = "0 Kayıt Bulundu... ";		
			
		}
		
		$this->sayfaUstYazi = $sayfaUstYazi;
		$this->sayfaAltYazi = $sayfaAltYazi;
		$this->sayfaIlk = $sayfaIlk;
		$this->sayfaSon = $sayfaSon;
		$this->sayfaAdet = $sayfaAdet;
		$this->sayfa = $sayfa;
		
		
	}
	
	public function getSayfaUstYazi() { 
		return $this->sayfaUstYazi;
	}
	
	public function getSayfaAltYazi() { 
		return $this->sayfaAltYazi;
	}
	
	public function getSayfaIlk() { 
		return $this->sayfaIlk;
	}
	
	public function getSayfaSon() { 
		return $this->sayfaSon;
	}
	
	public function getSayfaAdet() { 
		return $this->sayfaAdet;
	}
	
	public function getSayfa() { 
		return $this->sayfa;
	}
	
	public function row($sql, $filtre){
		$stmt = $this->dbPDO->prepare($sql);
		if (!$stmt->execute($filtre)) { $this->hataMail($this->dbPDO, $stmt, $sql, $filtre);}	
		return $stmt->fetchObject();
		
	}
	
	public function rows($sql, $filtre){
		$stmt = $this->dbPDO->prepare($sql);
		if (!$stmt->execute($filtre)) { $this->hataMail($this->dbPDO, $stmt, $sql, $filtre);}	
		return $stmt->fetchAll();
		
	}
	
	public function rowsCount($sql, $filtre){
		$stmt = $this->dbPDO->prepare($sql);
		if (!$stmt->execute($filtre)) { $this->hataMail($this->dbPDO, $stmt, $sql, $filtre);}	
		return $stmt->rowCount();
		
	}
	
	public function lastInsertId($sql, $filtre){
		$stmt = $this->dbPDO->prepare($sql);
		if (!$stmt->execute($filtre)) { $this->hataMail($this->dbPDO, $stmt, $sql, $filtre);}	
		return $this->dbPDO->lastInsertId();
		
	}
	
}
?>