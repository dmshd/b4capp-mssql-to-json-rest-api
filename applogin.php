<?php
// Headers requis pour les requêtre HTTP (principe de l'API qui va nous renvoyer du JSON)

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "tools.php";
require_once "connect.php";

$json = [];

// pp = idendifiant, p = mdp
//
//

if (
  isset($_GET['pp']) and
  !empty($_GET['pp']) and
  isset($_GET['p']) and
  !empty($_GET['p'])
) {
  $pp = filter_var($_GET['pp'], FILTER_SANITIZE_STRING);
  $p = filter_var($_GET['p'], FILTER_SANITIZE_STRING);
  // Check correspondance type 7 = premium pass
  $response = $conn->prepare(
    'SELECT IDPPH,Num From censored_table_path_to_avoid_problems WHERE Num=:identifiant AND Type=7'
  );
  $response->bindValue('identifiant', $pp, PDO::PARAM_STR);
  $response->execute();

  if ($userpp = $response->fetch(PDO::FETCH_ASSOC)) {
    $json["pp"] = true;
  } else {
    // Pas premium pass alors check correspondance type 4 = email
    $response = $conn->prepare(
      'SELECT IDPPH,Num From censored_table_path_to_avoid_problems WHERE Num=:identifiant AND Type=4'
    );
    $response->bindValue('identifiant', $pp, PDO::PARAM_STR);
    $response->execute();
    if ($usermail = $response->fetch(PDO::FETCH_ASSOC)) {
      $json["pp"] = true;
    } else {
      $json["pp"] = false;
      $json["p"] = false;
    }
  }
  // Si l'identifiant est correct on vérifie la correspondance du mdp
  if ($json["pp"]) {
    // ternary condition pour récupérer le IDPPH en fonction de si l'user s'est connecté avec son premium pass ou son mail
    $json["IDPPH"] = empty($userpp['IDPPH'])
      ? $usermail['IDPPH']
      : $userpp['IDPPH'];
    // requête type 6 = mdp de l IDPPH (user)
    $response = $conn->query(
      'SELECT Num From censored_table_path_to_avoid_problems WHERE IDPPH=' .
        $json['IDPPH'] .
        ' AND Type=6'
    );
    $response->execute();
    $data = $response->fetch();
    if (isset($data['Num']) and $data['Num'] == $p) {
      $json["p"] = true;
      $response = $conn->prepare(
        'SELECT  Pre FROM censored_table_path_to_avoid_problems WHERE IDPPH=1'
      );
      $response->bindValue('identifiant', $pp, PDO::PARAM_STR);
      $response->execute();
      $data = $response->fetch();

      $json["Pre"] = isset($data["Pre"])
        ? $data["Pre"]
        : " ";
    } else {
      $json["p"] = false;
    }
  } else {
    $json["p"] = false;
  }

  $json = json_encode($json);
  echo $json;
} else {
  echo "invalid request";
}
