<?php 
$db = new PDO('mysql:host=localhost;dbname=morenzo;charset=utf8', 'root', '');
error_reporting(E_ALL && ~E_WARNING && ~E_STRICT);
$db->Query('SET NAMES "utf8" COLLATE "utf8_unicode_ci"');
if($_POST['act_z'])
{
	$tablic = json_decode($_POST['act_json']);
	if($_POST['ignore'] != "tak")
	{
	foreach($tablic as $t)
	{
		$item = $db->prepare("UPDATE magazyn_tbl SET ilosc = ilosc - :ilosc WHERE kod = :kod");
		$item->execute(array(":ilosc"=>$t->ilosc,":kod"=>$t->kod));
		echo "Zdjęto z bazy ".$t->kod." - ".$t->ilosc." szt<br />";
	}
	}
	if($_POST['vatt'])
	{
	$vat= json_decode($_POST['vat']);
	genVat($tablic,$vat);
	}
}
else 
{
	$productid=$_POST['pid'];
	$productc=(int)$_POST['prc'];
	if(!empty($productid))
	{
		$pid=$db->prepare("SELECT COUNT(*),model,grupa,ilosc,kod,hurt_EUR,hurt_PLN FROM magazyn_tbl WHERE kod = :productid LIMIT 1");
		$pid->execute(array(":productid"=>$productid));
		$item=$pid->fetch(PDO::FETCH_ASSOC);
		if($item['COUNT(*)'] < 1) 
		{ 
		$item['v_kod'] =$productid;
		$item['error'] ="ERR_1";
		echo json_encode($item);
		}
		else if($item['ilosc'] < ($productc+1) && $_POST['ignore'] != "tak") 
		{ 
		$item['error'] ="ERR_2";
		echo json_encode($item);  
		}
		else {
		
		$item['cena_eur'] = $item['hurt_EUR'] * ($productc+1);
		$item['cena_pln'] = $item['hurt_PLN'] * ($productc+1);
		echo json_encode($item);
		}
	}
}
function genVat($tablica,$dane)
{
global $db;
//Tablice
$tsposob	 = array("","Przelew");
$tstransport = array("","DPD");
$tzamowienie = array("","Internet","Osobiste","Telefon","Inne");
$twaluta	 = array("","EURO €","PLN");
$typyfaktur  = array("","Faktura Nr / Invoice VAT","Faktura Nr / Invoice Proforma");
$numerfaktury=$dane->numer_faktury;
	if(empty($numerfaktury)) $numerfaktury="empty";
$odbiorca=explode("\n",$dane->odbiorca);
if(empty($dane->termin_zaplaty)) $dane->termin_zaplaty = "-";
if(empty($dane->data_dostaw)) $dane->data_dostaw= "-";

$zamiany = array(
	"O1" => $dane->dzisiejsza_data,
	"O3" => $tsposob[$dane->sposob_zaplaty],
	"O4" => $tzamowienie[$dane->zamowienie],
	"O5" => $tstransport[$dane->srodek_transportu],
	"O6" => $dane->termin_zaplaty,
	"O7" => $dane->data_dostawy,
	"O10" => $twaluta[$dane->waluta],
	"F29" => $twaluta[$dane->waluta],
	"A10" => $odbiorca[0],
	"A11" => $odbiorca[1],
	"A12" => $odbiorca[2],
	"A13" => $odbiorca[3],
	"A31" => $dane->uwagi,
	"J3" =>  $numerfaktury. "/".date("Y",time()),
	"F3" => $typyfaktur[$dane->typ_faktury]
);
//
$page = 0;
$i=1;
require_once('inc/PHPExcel/IOFactory.php');
$excel = new PHPExcel();
		$inputFileType = PHPExcel_IOFactory::identify('template.xlsx');
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);  
	$phpexcel = $objReader->load('template.xlsx');
	$phpexcel->setActiveSheetIndex(0);
if($dane->typ_faktury == 1)
{
	$filename="Fvat_".$numerfaktury;
} else { $filename="FProforma_".$numerfaktury;}
foreach($zamiany as $key=>$val)
{
	$phpexcel->getActiveSheet()->SetCellValue($key,$val);
}
for($x=28;$x <= 33;$x++) 
{
	$phpexcel->getActiveSheet()->getStyle('L'.$x)->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('N'.$x)->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('O'.$x)->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('P'.$x)->getNumberFormat()->setFormatCode("#,##0.00");
}
$phpexcel->getActiveSheet()->getStyle('C29')->getNumberFormat()->setFormatCode("#,##0.00");
$phpexcel->getActiveSheet()->getStyle('D29')->getNumberFormat()->setFormatCode("#,##0.00");
$phpexcel->getActiveSheet()->getStyle('E29')->getNumberFormat()->setFormatCode("#,##0.00");
foreach($tablica as $t)
{
	$itemname=$tablica[$i+($page*11)-1];
	//Edycja
	$przedmiot=$db->prepare("SELECT * FROM magazyn_tbl WHERE kod = :kod");
	$przedmiot->execute(array(":kod"=>$itemname->kod));
	$przedm = $przedmiot->fetch(PDO::FETCH_ASSOC);
	
	$phpexcel->getActiveSheet()->SetCellValue('A'.(16+$i),$i.".");
	$phpexcel->getActiveSheet()->SetCellValue('B'.(16+$i),$przedm['model']);
	$phpexcel->getActiveSheet()->SetCellValue('G'.(16+$i),$itemname->ilosc);
	$phpexcel->getActiveSheet()->SetCellValue('K'.(16+$i),(int)$itemname->rabat);
	$phpexcel->getActiveSheet()->SetCellValue('H'.(16+$i),'szt. / pcs');
		$phpexcel->getActiveSheet()->SetCellValue('M'.(16+$i),$itemname->podatek);
	if($dane->waluta == 1) {
		$phpexcel->getActiveSheet()->SetCellValue('I'.(16+$i),$przedm['hurt_EUR']);
	}
	else {
		$phpexcel->getActiveSheet()->SetCellValue('I'.(16+$i),$przedm['hurt_PLN']);
	}
	$phpexcel->getActiveSheet()->getStyle('I'.(16+$i))->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('J'.(16+$i))->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('O'.(16+$i))->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('L'.(16+$i))->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('N'.(16+$i))->getNumberFormat()->setFormatCode("#,##0.00");
	$phpexcel->getActiveSheet()->getStyle('P'.(16+$i))->getNumberFormat()->setFormatCode("#,##0.00");
	
	//Zapis jeśli ostatnie
	if(($i+$page*11) == count($tablica))
	{
		$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
		$objWriter->save("faktury/".$filename.'.xls');
		echo "<a href='faktury/".$filename.".xls'>Kliknij tutaj aby pobrać fakture str. ".($page+1)."</a><br />";
	}
	//Zapis
	else if($i == 11) {
		$objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
		$objWriter->save("faktury/".$filename.'.xls');
		echo "<a href='faktury/".$filename.".xls'>Kliknij tutaj aby pobrać fakture str. ".($page+1)."</a><br />";
		//Czyszczenie
		for($x=16;$x <= 27;$x++)
		{
			$phpexcel->getActiveSheet()->SetCellValue('A'.$x,'');
			$phpexcel->getActiveSheet()->SetCellValue('B'.$x,'');
			$phpexcel->getActiveSheet()->SetCellValue('G'.$x,'');
			$phpexcel->getActiveSheet()->SetCellValue('M'.$x,'');
			$phpexcel->getActiveSheet()->SetCellValue('K'.$x,'');
			$phpexcel->getActiveSheet()->SetCellValue('I'.$x,'');
		}
		$i=0; $page++;
		$numerfaktury++;
		if($dane->typ_faktury == 1){
		$filename="Fvat_".$numerfaktury;} 
		else { $filename="FProforma_".$numerfaktury;}
		$phpexcel->getActiveSheet()->SetCellValue('J3',$numerfaktury."/".date("Y",time()));
	}
	$i++;
}
}
function randomStr($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>