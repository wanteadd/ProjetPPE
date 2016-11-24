<?php
require('FPDFMysql.php');
class PDF extends FPDFMysql
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
mysql_connect('mysql:host=localhost','root','mysql');
mysql_select_db('gsbaplifrais');

$pdf=new PDF();
$pdf->Table("select FraisForfait.id as idFrais, FraisForfait.libelle as libelle, 
		LigneFraisForfait.quantite as quantite, 
                LigneFraisForfait.montant as montant from LigneFraisForfait inner join FraisForfait 
		on FraisForfait.id = LigneFraisForfait.idFraisForfait
		where LigneFraisForfait.idPersonne ='$idPersonne' and LigneFraisForfait.mois='$mois' 
		order by LigneFraisForfait.idFraisForfait");
$pdf->AddPage();
//$pdf->AddPage();
//Premier tableau : imprime toutes les colonnes de la requête
$pdf->Table("select * from LigneFraisHorsForfait where LigneFraisHorsForfait.idPersonne ='$idPersonne' 
		and LigneFraisHorsForfait.mois = '$mois' ");
$pdf->AddPage();

//Second tableau : définit 3 colonnes
//$pdf->AddCol('rank',20,'Rang','C');
//$pdf->AddCol('name',40,'Pays');
//$pdf->AddCol('pop',40,'Pop (2001)','R');
//$prop=array('HeaderColor'=>array(255,150,100),
//            'color1'=>array(210,245,255),
//            'color2'=>array(255,255,210),
//            'padding'=>2);
//$pdf->Table('select name, format(pop,0) as pop, rank from country order by rank limit 0,10',$prop);
$pdf->Output("FicheFrais.pdf","F");
?>
