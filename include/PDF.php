<?php

require ('fpdf.php');
class PDF extends FPDF
{
function Header()
{
    
    //Titre
    $this->SetFont('Arial','',18);
    $this->Cell(0,6,'Populations mondiales',0,1,'C');
    $this->Ln(10);
    //Imprime l'en-tête du tableau si nécessaire
    parent::Header();
}
}

//Connexion à la base
$db = mysql_connect('localhost','root','mysql','gsbapplifrais');
$idPersonne = $_REQUEST['idPersonne'];
$mois = $_REQUEST['mois'];
$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Helvetica','',11);
$pdf->SetTextColor(0);

$req = "select  FraisForfait.libelle as libelle, 
		LigneFraisForfait.quantite as quantite, 
                LigneFraisForfait.montant as montant from LigneFraisForfait inner join FraisForfait 
		on FraisForfait.id = LigneFraisForfait.idFraisForfait
		where LigneFraisForfait.idPersonne ='.$idPersonne.' and LigneFraisForfait.mois='.$mois.' 
		order by LigneFraisForfait.idFraisForfait";
$rep = mysqli_query($db, $req);
$row = mysqli_fetch_array($rep);

while ($row = mysqli_fetch_array($rep)) {
    $pdf->SetY($position_detail);
    $pdf->SetX(8);
    $pdf->MultiCell(158,8,utf8_decode($row['libelle']),1,'L');
    $pdf->SetY($position_detail);
    $pdf->SetX(166);
    $pdf->MultiCell(10,8,$row['quantite'],1,'C');
    $pdf->SetY($position_detail);
    $pdf->SetX(176);
    $pdf->MultiCell(24,8,$row['montant'],1,'R');
    $position_detail += 8;

$pdf->Text(8,38,'libelle : '.$row['libelle']);
$pdf->Text(8,43,'quantite : '.$row['quantite']);
$pdf->Text(8,48,'montant : '.$row['montant']);

$pdf->AddPage();

$req2->Table("select * from LigneFraisHorsForfait where LigneFraisHorsForfait.idPersonne ='$idPersonne' 
		and LigneFraisHorsForfait.mois = '$mois' ");
$rep2 = mysqli_query($db, $req2);
$row2 = mysqli_fetch_array($rep2);

while ($row = mysqli_fetch_array($rep)) {
    $pdf->SetY($position_detail);
    $pdf->SetX(8);
    $pdf->MultiCell(158,8,utf8_decode($row2['date']),1,'L');
    $pdf->SetY($position_detail);
    $pdf->SetX(166);
    $pdf->MultiCell(10,8,$row2['libelle'],1,'C');
    $pdf->SetY($position_detail);
    $pdf->SetX(176);
    $pdf->MultiCell(24,8,$row['montant'],1,'R');
    $position_detail += 8;

$pdf->Text(8,38,'libelle : '.$row['date']);
$pdf->Text(8,43,'quantite : '.$row['libelle']);
$pdf->Text(8,48,'montant : '.$row['montant']);

$pdf->AddPage();

$pdf->Close();
$pdf->Output("FicheFrais.pdf","F");
?>