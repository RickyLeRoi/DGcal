<?php

$dbhost = "localhost";
$dbusername = "kimacces_dbuser";
$dbpassword = "L3tsG0$@";
$dbname = "kimacces_magento";
$dbname2 = "kimacces_import";
$conn = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname) or die('Could not connect');
$conn2 = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname2) or die('Could not connect');

function mysqli_result($result,$row,$field=0) {
	if ($result===false) return false;
	if ($row>=mysqli_num_rows($result)) return false;
	if (is_string($field) && !(strpos($field,".")===false)) {
		$t_field=explode(".",$field);
		$field=-1;
		$t_fields=mysqli_fetch_fields($result);
		for ($id=0;$id<mysqli_num_fields($result);$id++) {
			if ($t_fields[$id]->table==$t_field[0] && $t_fields[$id]->name==$t_field[1]) {
				$field=$id;
				break;
			}
		}
		if ($field==-1) return false;
	}
	mysqli_data_seek($result,$row);
	$line=mysqli_fetch_array($result);
	return isset($line[$field])?$line[$field]:false;
}


$file = fopen("log.txt","w");

fwrite($file,date("d/m/Y H:i")."\n\n");
$q=mysqli_query($conn,"SELECT * FROM catalog_product_entity WHERE type_id='simple' ");
$g=1;
while($r=mysqli_fetch_array($q)){
	
	$sku =$r['sku'];														// SKU SU MAGENTO
	$id = $r['entity_id'];													// ID SU MAGENTO
	if($sku!="TEST"){
			$qqta=mysqli_query($conn,"SELECT qty FROM cataloginventory_stock_item WHERE product_id=$id");
			$rqta=mysqli_fetch_array($qqta);
			$mageQta=$rqta['qty'];													// QUANTITÀ SU MAGENTO    ( in decimale con . e 4 zeri - es. 2.0000 - )
			
			// ricavare fornitore,articolo,codice colore, taglia.
			$expSku=explode("_",$sku);
			$Fornitore = strtoupper($expSku[0]);									// FORNITORE SU SIZE
			$Articolo = strtoupper($expSku[1]);										// ARTICOLO SU SIZE
			$DescrizioneColore = strtoupper($expSku[2]);
			
			//$DescrizioneColore = strtoupper(str_replace("-", " ", $expSku[2])); 	// DESCRIZIONE SU SIZE, RICAVARE IL CODICE
			$Taglia = $expSku[3];
			
			$qColore = mysqli_query($conn2,"SELECT CodiceColore FROM Colori_Desc_Art WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND DescrizioneColore='$DescrizioneColore'");
			$CodiceColore = mysqli_result($qColore,0,"CodiceColore");				// CODICE COLORE SU SIZE
			if(empty($CodiceColore)){
				$cercaColore = str_replace("-", " ", $DescrizioneColore);
				$qColore2 = mysqli_query($conn2,"SELECT CodiceColore FROM Colori_Desc_Art WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND DescrizioneColore='$cercaColore'");
				$CodiceColore = mysqli_result($qColore2,0,"CodiceColore");
				//echo "SELECT CodiceColore FROM Colori_Desc_Art WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND DescrizioneColore='$cercaColore'<br>";
			}else{
				//echo "SELECT CodiceColore FROM Colori_Desc_Art WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND DescrizioneColore='$DescrizioneColore'<br>";
			}
			
			// cercare su TOT_ESI la quantità corrispondente
			
			//echo 'Riga: '.$g.'; Taglia: '.$Taglia.'; Colore: '.$CodiceColore.'; Taglie: ';
			$qSizeQt=mysqli_query($conn2,"SELECT * FROM Tot_Esi WHERE Fornitore='$Fornitore' AND Articolo='$Articolo' AND Colore='$CodiceColore'");
			for($flag=1;$flag<=30;$flag++){
				$campo = "Tag".$flag;
				$campoQt = "Qta".$flag;
				$nTaglia = mysqli_result($qSizeQt,0,$campo);
				//echo $nTaglia." ";
				if($nTaglia==$Taglia){
					$quant = mysqli_result($qSizeQt,0,$campoQt);		// 	QUANTITA SU SIZE
					
					if(intval($mageQta)!=$quant){	// se le quantita non corrispondono
						// aggiorna la tab magento con $quant.
						$nuovaQta = number_format($quant,4,".",",");
						mysqli_query($conn,"UPDATE cataloginventory_stock_item SET qty=$nuovaQta WHERE product_id=$id ");
						$stringa = $sku." qt. ".$mageQta." - sizeQt. ".$nuovaQta." \n";
						fwrite($file,$stringa);
						
					}
					if($quant<1){
						// setta come disabilitato
						mysqli_query($conn,"UPDATE cataloginventory_stock_item SET is_in_stock=0 WHERE product_id=$id ");
						mysqli_query($conn,"UPDATE catalog_product_entity_int SET value=2 WHERE attribute_id='96' AND entity_id='$id' ");
					}else{
						// setta come abilitato
						mysqli_query($conn,"UPDATE cataloginventory_stock_item SET is_in_stock=1 WHERE product_id=$id ");
						mysqli_query($conn,"UPDATE catalog_product_entity_int SET value=1 WHERE attribute_id='96' AND entity_id='$id' ");
					}
				}
				//echo "| ";
			}
			//echo "<br><br>";
			$g++;
	}
	
	
}
fwrite($file,"\n\n");
fclose($file);

?>