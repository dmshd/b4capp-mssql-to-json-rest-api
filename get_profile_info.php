<?php
// Headers requis pour les requêtre HTTP (principe de l'API qui va nous renvoyer du JSON)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Dépendances : tools contient des functions custom et connect la connection pdo
require_once "tools.php";
require_once "connect.php";

if (
  isset($_GET['idpph']) and filter_var($_GET['idpph'], FILTER_SANITIZE_STRING)
) {
  $gsmQuery = $conn->prepare(
    "SELECT [Num] FROM _censored_to_avoid_eventual_hacking_problem WHERE [Type]=2 AND [IDPPH]=:idpph"
  );
  $gsmQuery->bindValue(':idpph', $_GET['idpph'], PDO::PARAM_INT);
  if ($gsmQuery->execute()) {
    $gsm = $gsmQuery->fetch(PDO::FETCH_ASSOC);
    // var_dump($gsm);
    // array(1) {
    //   ["Num"]=>
    //   string(16) "+32 475 44 36 20"
    // }
    $gsm = $gsm["Num"];
    // var_dump($gsm);
  } else {
    $gsm = " ";
  }

  $emailQuery = $conn->prepare(
    "SELECT [Num] FROM _censored_to_avoid_eventual_hacking_problem WHERE [Type]=4 AND [IDPPH]=:idpph"
  );
  $emailQuery->bindValue(':idpph', $_GET['idpph'], PDO::PARAM_INT);
  if ($emailQuery->execute()) {
    $email = $emailQuery->fetch(PDO::FETCH_ASSOC);
    $email = $email["Num"];
    // var_dump($email);
  } else {
    $email = " ";
  }

  $detailsQuery = $conn->prepare("SELECT P.FctOrg, P.FctEnt, T.IDTIE,T.Libelle,T.Nom_Comm  FROM  _censored_ P 
    JOIN _censored_ I ON P.IDPPH = I.IDPPH 
    JOIN _censored_ T ON T.IDTIE = I.IDTIE
    WHERE P.Passif=0 AND P.IdLibType IN(1,2,3,12) AND I.Principal=1 AND P.IDPPH=:idpph");

  $detailsQuery->bindValue(':idpph', $_GET['idpph'], PDO::PARAM_INT);
  if ($detailsQuery->execute()) {
    $details = $detailsQuery->fetch(PDO::FETCH_ASSOC);
    extract($details);
    $response = array(
      "email" => utf8_encode($email),
      "gsm" => utf8_encode($gsm),
      "IDTIE" => utf8_encode($IDTIE),
      "FctOrg" => utf8_encode($FctOrg),
      "FctEnt" => utf8_encode($FctEnt),
      "LibelleEntreprise" => utf8_encode($Libelle),
    );
  } else {
    $response = array(
      "email" => utf8_encode($email),
      "gsm" => utf8_encode($gsm)
    );
  }

  http_response_code(200);
  $jsonArr = json_encode(
    $response,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
  );
  echo $jsonArr;
} else {
  echo "L'application a rencontré une erreur. Nous nous excusons pour ce désagrément.";
}
