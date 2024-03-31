<?php 
ini_set('session.cookie_secure', "1");
ini_set('session.cookie_httponly', "1");
ini_set('session.cookie_samesite','None');
session_start();
//frame_id est un élement qui est tjrs présent en cas de lancement
if(isset($_REQUEST['frame_id'])){
require_once('util/sdk.php');
}
else{
//ça signifie qu'il vient pas de discord ou alors qu'il en est sortie
  //logique -->
}

?>
