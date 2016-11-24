<?php

date_default_timezone_set("UTC");
include("vues/v_sommaire.php");
$idPersonne = $_SESSION['idPersonne'];
$mois = getMois(date("d/m/Y"));
$numAnnee =substr( $mois,0,4);
$numMois =substr( $mois,4,2);
$action = $_REQUEST['action'];
$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idPersonne,$mois);
$lesFraisForfait= $pdo->getLesFraisForfaitMois($idPersonne,$mois);
$lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idPersonne,$mois);
switch($action){
	case 'saisirFraisForfait':{
            echo "ok";
		if($pdo->estPremierFraisMois($idPersonne,$mois)){
			$pdo->creeNouvellesLignesFrais($idPersonne,$mois);
		}
                include("vues/v_listeFraisForfait.php");
                
		break;
	}
        case 'saisirFraisHorsForfait':{
            echo "ok";
		if($pdo->estPremierFraisMois($idPersonne,$mois)){
			$pdo->creeNouvellesLignesFrais($idPersonne,$mois);
		}
                include("vues/v_listeFraisHorsForfait.php");
		break;
	}
	case 'validerMajFraisForfait':{
                
		$lesFrais = $_REQUEST['lesFrais'];
		if(lesQteFraisValides($lesFrais)){
	  	 	$pdo->majFraisForfait($idPersonne,$mois,$lesFrais);
		}
		else{
			ajouterErreur("Les valeurs des frais doivent �tre num�riques");
			include("vues/v_erreurs.php");
		}
	  break;
	}
	case 'validerCreationFraisHorsForfait':{
		$dateFrais = $_REQUEST['dateFrais'];
		$libelle = $_REQUEST['libelle'];
		$montant = $_REQUEST['montant'];
		valideInfosFrais($dateFrais,$libelle,$montant);
		if (nbErreurs() != 0 ){
			include("vues/v_erreurs.php");
		}
		else{
			$pdo->creeNouveauFraisHorsForfait($idPersonne,$mois,$libelle,$dateFrais,$montant);
		}
		break;
	}
	case 'supprimerFrais':{
		$idFrais = $_REQUEST['idFrais'];
	    $pdo->supprimerFraisHorsForfait($idFrais);
		break;
	}
}




?>