<?php

$host = "localhost";
$user = "kimacces_dbuser";
$pass = "L3tsG0$@";
$db1 = "kimacces_magento";
$db2 = "kimacces_import";
$conn1 = mysqli_connect($host, $user, $pass, $db1) or die('niente');
$conn2 = mysqli_connect($host, $user, $pass, $db2) or die('niente');

function mysqli_result($ris,$row,$field=0) {
	if ($ris==false) return false;
	if ($row>=mysqli_num_rows($ris)) return false;
	if (is_string($field)) && !(strpos($field,".")===false)) {
		$t_field=explode('.',$field);
		$field=-1;
		$t_fields=mysqli_fetch_fields($ris);
		for ($id=0;$id<mysqli_num_fields($ris);$id++) {
			if ($t_fields[$id]->table==$t_field[0] && $t_fields[$id]->name==$t_field[1]) {
				$field=$id;
				break;
			}
		}
		if ($field==-1) return false;
	}
	mysqli_data_seek($ris,$row);
	$line = mysqli_fetch_array($ris);
	return isset($line[$field])?$line[$field]:false;
}

//crea e aggiorna file di log
$log = fopen("log.txt","w");
fwrite($log, date("d/m/Y H:i")."\n\n");

//cerca gli articoli nei DB
$conf = mysqli_query($conn1, "SELECT * FROM catalog_product_entity WHERE type_id='configurable'");
$simple = mysqli_query($conn1, "SELECT * FROM catalog_product_entity WHERE type_id='simple'");
$cont = 0;

while ($arrc = mysqli_fetch_array($simple)) {
	$sku = $arrc['sku']; //cod prodotto testuale univoco
	$id = $arrc['entity_id']; //cod prodotto numerico
	$qqta=mysqli_query($conn1,"SELECT qty FROM cataloginventory_stock_item WHERE product_id=$id");
	$rqta=mysqli_fetch_array($qqta);
	//quantità db remoto
	$mageQta=$rqta['qty'];

	//scomponi SKU
	$expsku = explode("_", $sku);
	$expSku=explode("_",$sku);
	//fornitore
	$Fornitore = strtoupper($expSku[0]);
	//articolo
	$Articolo = strtoupper($expSku[1]);	
	//colore testuale
	$DescrizioneColore = strtoupper($expSku[2]);
	//taglia
	$Taglia = $expSku[3];

	$qColore = mysqli_query($conn2,"SELECT CodiceColore FROM Colori_Desc_Art WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND DescrizioneColore='$DescrizioneColore'");
	//converto colore testuale in colore numerico
	$CodiceColore = mysqli_result($qColore,0,"CodiceColore");
	if(empty($CodiceColore)){
		$cercaColore = str_replace("-", " ", $DescrizioneColore);
		$qColore2 = mysqli_query($conn2,"SELECT CodiceColore FROM Colori_Desc_Art WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND DescrizioneColore='$cercaColore'");
		$CodiceColore = mysqli_result($qColore2,0,"CodiceColore");
	} else {
		$qSizeQt=mysqli_query($conn2,"SELECT * FROM Tot_Esi WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND Colore='$CodiceColore'");
		for($flag=1;$flag<=30;$flag++){
				$campo = "Tag".$flag;
				$campoQt = "Qta".$flag;
				$nTaglia = mysqli_result($qSizeQt,0,$campo);
				if($nTaglia==$Taglia){
					//quantità
					$quant = mysqli_result($qSizeQt,0,$campoQt);
					//paragona le quantità, se sono diverse, aggiorna il db remoto usando quello locale
					if(intval($mageQta)!=$quant){
						$nuovaQta = number_format($quant,4,".",",");
						mysqli_query($conn1,"UPDATE cataloginventory_stock_item SET qty=$nuovaQta WHERE product_id=$id ");
						$stringa = $sku." qt. ".$mageQta." - sizeQt. ".$nuovaQta." \n";
						fwrite($file,$stringa);
						
					}
				}
			}
		}
	$cont++;
	}

}

fwrite($log,"\n\n");
fclose($log);
?>