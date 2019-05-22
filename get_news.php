<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");

//Dép
require_once "tools.php";
require_once "connect.php";

//Var
$ok = false;
$alert = "WTF dude ?";
$data = [];

//si acttype = all alors on prépare la requête
//elle va envoyer les activités dont la valeur de la colonne IdLibCatAct est entre 1 et 3
// 1 = activite club - 2 = act cult - 3 = act admin
if (isset($_GET['acttype']) AND filter_var($_GET['acttype'], FILTER_SANITIZE_STRING)) {

    //Creation du timestamp pour hier qui sera utilisé dans la requête préparée
    $yesterday = new DateTime(); 
    $yesterday->modify('-1 day'); 
    $yesterday = $yesterday->format('Y-m-d');

    if (($_GET['acttype'] === "all")) {
        // $response=$conn->prepare("SELECT IDACT, Photo_Chemin, IdLibCatAct, Date, Lib FROM ID143953_bccopy.dbo.SQL_ACT WHERE Date > :date AND IdLibCatAct BETWEEN 1 AND 3 ORDER BY Date DESC");
        $response=$conn->prepare("SELECT IDACT, Photo_Chemin, IdLibCatAct, Date, DatMax, Lib FROM ID143953_b4c.dbo.SQL_ACT WHERE Date >= :date OR DatMax >= :date AND IdLibCatAct BETWEEN 1 AND 2 ORDER BY IDACT");
        $ok = true;

    } elseif (filter_input(INPUT_GET, "acttype", FILTER_VALIDATE_INT)) {
        // $response=$conn->prepare("SELECT IDACT, Photo_Chemin, IdLibCatAct, Date, Lib FROM ID143953_bccopy.dbo.SQL_ACT WHERE Date > :date AND IdLibCatAct = :acttype ORDER BY Date DESC");
        $response=$conn->prepare("SELECT IDACT, Photo_Chemin, IdLibCatAct, Date, Lib FROM ID143953_b4c.dbo.SQL_ACT WHERE Date >= :date AND IdLibCatAct = :acttype ORDER BY IDACT");
        $response->bindValue(':acttype', $_GET['acttype'], PDO::PARAM_STR);
        $ok = true;
    }
    //Bind et exec requête
    if ($ok) {
        $response->bindValue(':date', $yesterday, PDO::PARAM_STR);
        $activites_arr=array();
        $activites_arr["activites"]=array();
        $response->execute();

        //Extraction de chaque ligne de résultat et stockages dans un tableau
        while ($row = $response->fetch(PDO::FETCH_ASSOC)) {
              extract($row); // voir doc php
              $activite_item=array(
                      "IDACT" => utf8_encode($IDACT),
                      "IdLibCatAct" => utf8_encode($IdLibCatAct),
                      "Photo_Chemin" => utf8_encode($Photo_Chemin),
                      "Date" => utf8_encode($Date),
                      "Lib" => mb_strtoupper(utf8_encode(stripslashes($Lib)), 'UTF-8')
                      );
              array_push($activites_arr["activites"], $activite_item);
      };

      //Ok -> json avec params qu'on veut
      http_response_code(200);
      $jsonArr = json_encode($activites_arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
      echo $jsonArr;

      } else {
        echo $alert;
      }

} elseif (isset($_GET['actid'])) {

  if (filter_input(INPUT_GET, "actid", FILTER_VALIDATE_INT)) {

      //requête de bind de input get puis extract crée des variables pour chaque elem du tableau
      // $response=$conn->prepare("SELECT DescriptionWeb, Nom_Lieu, Rue, Cop, Loc FROM ID143953_bccopy.dbo.SQL_ACT WHERE IDACT = :id");
      $response=$conn->prepare("SELECT Lib, Description, Date, DatMax, TypRem, NbMaxInvit, Rue, Loc, Cop, DateInscription, DescriptionWeb, Nom_Lieu,Photo_Chemin, PrixAbsent, rsv_mem, rsv_adm, NbMaxInscript, IdLibCatAct FROM ID143953_b4c.dbo.SQL_ACT WHERE IDACT = :id");
      $response->bindValue(':id', $_GET['actid'], PDO::PARAM_INT);
      $response->execute();
      $act = $response->fetch(PDO::FETCH_ASSOC);
      extract($act);
      // var_dump($act);


      // IdLibType=1  -> membre
      $prix1 = $conn->prepare("SELECT Prix FROM ID143953_b4c.dbo.SQL_TAR WHERE IDACT = :id AND IdLibType=1 ");
      $prix1->bindValue(':id', $_GET['actid'], PDO::PARAM_INT);
      $prix1->execute();
      $prixmembre = $prix1->fetch(PDO::FETCH_ASSOC);
      // var_dump($prixmembre);
      // array(1) {
      //   ["Prix"]=>
      //   string(2) "75"
      // }
      $prixmembre = $prixmembre["Prix"];
      
      

      // IdLibType=1  -> Invité
      $prix2 = $conn->prepare("SELECT Prix FROM ID143953_b4c.dbo.SQL_TAR WHERE IDACT = :id AND IdLibType=4 ");
      $prix2->bindValue(':id', $_GET['actid'], PDO::PARAM_INT);
      $prix2->execute();
      $prixinvite = $prix2->fetch(PDO::FETCH_ASSOC);
      $prixinvite = $prixinvite["Prix"];
      
      // Nettoyage du la chaine de char DescriptionWeb pour afficher dans l'app
      $patterns = "/([a-zA-Z])\?([a-zA-Z])/";
      $replacements = "$1'$2";
      $DescriptionWeb = preg_replace($patterns, $replacements, html_entity_decode(stripslashes(str_replace(array("\'", "\\"), array("'", ""), $DescriptionWeb))));
      //enlelerles backsashes en trop
      $Rue = utf8_encode(stripslashes(str_replace(array("\'", "\\"), array("'", ""), $Rue)));

      $adessMapQueryStr = $Rue . " " . $Cop . " " . $Loc;

      $act_details=array(
        "Date" => utf8_encode($Date),
        "DatMax" => utf8_encode($DatMax),
        // "IdLibTypeAct" => utf8_encode($IdLibTypeAct),
        "TypRem" => utf8_encode($TypRem),
        "NbMaxInvit" => utf8_encode($NbMaxInvit),
        "DateInscription" => utf8_encode($DateInscription),
        "PrixAbsent" => utf8_encode($PrixAbsent),
        "rsv_mem" => utf8_encode($rsv_mem),
        "rsv_adm" => utf8_encode($rsv_adm),
        "IdLibCatAct" => utf8_encode($IdLibCatAct),
        "NbMaxInscript" => utf8_encode($NbMaxInscript),
        "DescriptionWeb" => $DescriptionWeb,
        "Nom_Lieu" => utf8_encode(stripslashes($Nom_Lieu)),
        "Rue" => $Rue,
        "Cop" => utf8_encode($Cop),
        "Loc" => utf8_encode($Loc),
        "adressMapQueryStr" => $adessMapQueryStr,
        "prixinvite" => utf8_encode($prixinvite),
        "prixmembre" => utf8_encode($prixmembre)
      );
      //
      // $data[] = $act_details;
      //
      http_response_code(200);
      // // // JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
      //
      $jsonArr = json_encode($act_details, JSON_UNESCAPED_UNICODE);

      // // // var_dump($jsonArr);
      // // // $error = json_last_error();
      // // // var_dump($jsonArr, $error === JSON_ERROR_UTF8);
      // // echo $error;
      echo $jsonArr;

  } else {
    echo $alert;
  }

} else {
  echo $alert;
}
