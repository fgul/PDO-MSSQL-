<?
	require_once ($_SERVER['DOCUMENT_ROOT'] . '/dbPDO.php');
	error_reporting(1);
	header('Content-Type: text/html; charset=utf-8');
	// MSSQL DB bağlantısı
	$cdbMSQLPDO 	= new dbMSSQLPDO();
	$dbMSSQLPDO 	= $cdbMSQLPDO->dbBaglan();
	
	$rows_urun = $cdbPDO->rows("SELECT URUN_KOD, URUN, BARKOD FROM URUN WHERE 1 = 1", array());
	var_dump($rows_urun);
?>