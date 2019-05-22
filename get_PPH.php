<?php
// Headers requis pour les requêtre HTTP (principe de l'API qui va nous renvoyer du JSON)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


// Dépendances : tools contient des functions custom et connect la connection pdo
require_once "tools.php";
require_once "connect.php";

// bouléen utilisé dans la condition ci dessous
$ok = false;
// on vérifie qu'on injecte pas de code et si la valeut GET de act = all alors on envoie une
// requête SQL qui va nous renvoyer les activités dont la valeur de la colonne IdLibCatAct est entre 1 et 3
// 1 = activite club - 2 = act culturelle - 3 = act administrateur (ca, ce)
// si qqun essaie d'injecter du code on renvoie un message
if (isset($_GET['p']) AND filter_var($_GET['p'], FILTER_SANITIZE_STRING)) {
  if ($_GET['p'] === "1") {
    $response=$conn->prepare("SELECT  P.IDPPH, P.Photo_Chemin, P.Nom, P.Pre, T.IDTIE,T.Libelle,T.Nom_Comm  FROM  censored_table_path P 
    JOIN censored_table_path I ON P.IDPPH = I.IDPPH 
    JOIN censored_table_path T ON T.IDTIE = I.IDTIE
    WHERE P.Passif=0 AND P.IdLibType IN(1,2,3,12) AND I.Principal=1
    ORDER BY P.Nom ASC");

    $ok = true;
  } else {
    api_error();
  }
  if ($ok) {
    $membres_arr=array();
    $membres_arr["membres"]=array();
    $response->execute();
    
    while ($row = $response->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $FullName = $Pre . " " . $Nom;
            // userpic check
            if (empty(trim($Photo_Chemin))) {
              $Photo_Chemin = "https://_censored_/b4c/membres/b4c-app_nopic.png";
            }else {
              $Photo_Chemin = "https://_censored_/b4c/membres/" . trim($Photo_Chemin);
            }
            
            // nom commercial entreprise check
            if (empty(trim($Nom_Comm))) {
              $LibelleEntreprise = $Libelle;
            } else {
              $LibelleEntreprise = $Nom_Comm;
            }

            $activite_item=array(
                    "IDPPH" => utf8_encode($IDPPH),
                    "Nom" => utf8_encode($Nom),
                    "Pre" => utf8_encode($Pre),
                    "IDTIE" => utf8_encode($IDTIE),
                    "LibelleEntreprise" => utf8_encode(htmlspecialchars_decode($LibelleEntreprise)),
                    "Photo_Chemin" => utf8_encode($Photo_Chemin),
                );
            array_push($membres_arr["membres"], $activite_item);
    };
  
    http_response_code(200);
    // print_r($membres_arr);
    $jsonArr = json_encode($membres_arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    echo $jsonArr;
      } else {
        api_error();
      }
} elseif ( isset($_GET['idpph']) AND filter_var($_GET['idpph'], FILTER_SANITIZE_STRING) AND ctype_digit(strval($_GET['idpph'])) ) {
  header("Content-Type: text/html; charset=utf-8");
  echo "ok";
} else {
  api_error();
}