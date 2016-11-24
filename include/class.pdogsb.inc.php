<?php

/** 
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */

class PdoGsb{   		
      	private static $serveur='mysql:host=localhost';
      	private static $bdd='dbname=gsbapplifrais';   		
      	private static $user='root' ;    		
      	private static $mdp='mysql' ;	
		private static $monPdo;
		private static $monPdoGsb=null;
		
/**
 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	private function __construct(){
    	PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp); 
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
/**
 * Fonction statique qui crée l'unique instance de la classe
 
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 
 * @return l'unique objet de la classe PdoGsb
 */
	public  static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
/**
 * Retourne les informations d'un Personne
 
 * @param $login 
 * @param $mdp
 * @return l'id, le type,le nom, le prénom et l'id de profil sous la forme d'un tableau associatif 
*/
	public function getInfosPersonne($login, $mdp){
		$req = "select Personne.id as id, Personne.nom as nom, Personne.prenom as prenom, Personne.idProfil as idProfil from Personne 
		where Personne.login='$login' and Personne.mdp=SHA1('$mdp')";
		$rs = PdoGsb::$monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	}
/**
 * Retourne le profil d'une Personne

 * @param $idProfil
 * @return le libelle du profil sous la forme d'un tableau associatif 
*/
        public function getProfil($idProfil){
            $req = "select Profil.libelle as libelle from Profil 
		where Profil.id='$idProfil'";
            $rs = PdoGsb::$monPdo->query($req);
            $ligne = $rs->fetch();
            return $ligne;
        }

	
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
 * concernées par les deux arguments
 
 * La boucle foreach ne peut être utilisée ici car on procède
 * à une modification de la structure itérée - transformation du champ date-
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
*/
	public function getLesFraisHorsForfait($idPersonne,$mois){
	    $req = "select * from LigneFraisHorsForfait where LigneFraisHorsForfait.idPersonne ='$idPersonne' 
		and LigneFraisHorsForfait.mois = '$mois' order by date DESC ";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++){
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
/**
 * Retourne le nombre de justificatif d'un Personne pour un mois donné
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @return le nombre entier de justificatifs 
*/
	public function getNbjustificatifs($idPersonne, $mois){
		$req = "select FicheFrais.nbJustificatifs as nb from  FicheFrais where FicheFrais.idPersonne ='$idPersonne' and FicheFrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne['nb'];
	}
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfaitMois($idPersonne, $mois){
		$req = "select FraisForfait.id as idFrais, FraisForfait.libelle as libelle, 
		LigneFraisForfait.quantite as quantite, 
                LigneFraisForfait.montant as montant from LigneFraisForfait inner join FraisForfait 
		on FraisForfait.id = LigneFraisForfait.idFraisForfait
		where LigneFraisForfait.idPersonne ='$idPersonne' and LigneFraisForfait.mois='$mois' 
		order by LigneFraisForfait.idFraisForfait";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait
 
 * @return un tableau associatif 
*/
	public function getLesIdFrais(){
		$req = "select FraisForfait.id as idFrais from FraisForfait order by FraisForfait.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table LigneFraisForfait
 
 * Met à jour la table LigneFraisForfait pour un Personne et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return un tableau associatif 
*/
	public function majFraisForfait($idPersonne, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update LigneFraisForfait set LigneFraisForfait.quantite = $qte
			where LigneFraisForfait.idPersonne = '$idPersonne' and LigneFraisForfait.mois = '$mois'
			and LigneFraisForfait.idFraisForfait = '$unIdFrais'";
			PdoGsb::$monPdo->exec($req);
		}
		
	}
/**
 * met à jour le nombre de justificatifs de la table FicheFrais
 * pour le mois et le Personne concerné
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @param $nbJustificatifs
*/
	public function majNbJustificatifs($idPersonne, $mois, $nbJustificatifs){
		$req = "update FicheFrais set nbJustificatifs = $nbJustificatifs 
		where FicheFrais.idPersonne = '$idPersonne' and FicheFrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);	
	}
/**
 * Teste si un Personne possède une fiche de frais pour le mois passé en argument
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idPersonne,$mois)
	{
		$ok = false;
		$req = "select count(*) as nbLignesFrais from FicheFrais 
		where FicheFrais.mois = '$mois' and FicheFrais.idPersonne = '$idPersonne'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nbLignesFrais'] == 0){
			$ok = true;
		}
		return $ok;
	}
/**
 * Retourne le dernier mois en cours d'un Personne
 
 * @param $idPersonne 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idPersonne){
		$req = "select max(mois) as dernierMois from FicheFrais where FicheFrais.idPersonne = '$idPersonne'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un Personne et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idPersonne,$mois){
		$dernierMois = $this->dernierMoisSaisi($idPersonne);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idPersonne,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idPersonne, $dernierMois,'CL');
				
		}
		$req = "insert into FicheFrais(idPersonne,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values('$idPersonne','$mois',0,0,now(),'CR')";
                
		PdoGsb::$monPdo->exec($req);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idFrais'];
			$req = "insert into LigneFraisForfait(idPersonne,mois,idFraisForfait,quantite,date) 
			values('$idPersonne','$mois','$unIdFrais',0,now())";
			PdoGsb::$monPdo->exec($req);
		 }
	}
        
       

/**
 * Crée un nouveau frais hors forfait pour un Personne et un mois donné
 * à partir des informations fournies en paramètre
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
	public function creeNouveauFraisHorsForfait($idPersonne,$mois,$libelle,$date,$montant){
		$dateFr = dateFrancaisVersAnglais($date);
		$req = "insert into LigneFraisHorsForfait 
		values(DEFAULT,'$idPersonne','$mois','$libelle','$dateFr','$montant')";
		PdoGsb::$monPdo->exec($req);
	}
	

/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 
 * @param $idFrais 
*/
	public function supprimerFraisHorsForfait($idFrais){
		$req = "delete from LigneFraisHorsForfait where LigneFraisHorsForfait.id =$idFrais ";
		PdoGsb::$monPdo->exec($req);
	}
/**
 * Retourne les mois pour lesquels un Personne a une fiche de frais
 
 * @param $idPersonne 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
	public function getLesMoisDisponibles($idPersonne){
		$req = "select FicheFrais.mois as mois from  FicheFrais where FicheFrais.idPersonne ='$idPersonne' and FicheFrais.idEtat in ('CR', 'VA')
		order by FicheFrais.mois desc ";
		$res = PdoGsb::$monPdo->query($req);
		$lesMois =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$numAnnee =substr( $mois,0,4);
			$numMois =substr( $mois,4,2);
			$lesMois["$mois"]=array(
		    "mois"=>"$mois",
		    "numAnnee"  => "$numAnnee",
			"numMois"  => "$numMois"
             );
			$laLigne = $res->fetch(); 		
		}
		return $lesMois;
	}
	

/**
 * Retourne les informations d'une fiche de frais d'un Personne pour un mois donné
 
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idPersonne,$mois){
		$req = "select FicheFrais.idEtat as idEtat, FicheFrais.dateModif as dateModif, FicheFrais.nbJustificatifs as nbJustificatifs, 
			FicheFrais.montantValide as montantValide, Etat.libelle as libEtat from  FicheFrais inner join Etat on FicheFrais.idEtat = Etat.id 
			where FicheFrais.idPersonne ='$idPersonne' and FicheFrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
/**
 * Modifie l'état et la date de modification d'une fiche de frais
 
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idPersonne 
 * @param $mois sous la forme aaaamm
 */
 
	public function majEtatFicheFrais($idPersonne,$mois,$etat){
		$req = "update FicheFrais set idEtat = '$etat', dateModif = now() 
		where FicheFrais.idPersonne ='$idPersonne' and FicheFrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}
	
	
}
?>