<?php
/* Jsp pk mais ça fonctionne (il semblerait que ce soit du au système de proxy avec <app-id>.discordsays.com */
ini_set('session.cookie_secure', "1");
ini_set('session.cookie_httponly', "1");
ini_set('session.cookie_samesite','None');
session_start();

// Vérifier si la méthode de requête est GET et que le code d'autorisation est présent
if (isset($_REQUEST['code'])) {
    // Récupérer le code d'autorisation à partir de la requête
    $code = htmlspecialchars($_REQUEST['code']);

    // Construire les données de la requête pour obtenir le jeton d'accès
    $data = array(
        'client_id' => "app-id",
        'client_secret' => "secret-client",
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'https://url.truc/',
        'code' => $code
    );

    // Initialiser cURL
    $ch = curl_init();

    // Configurer les options de la requête cURL pour obtenir le jeton d'accès
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded'
    ));

    // Exécuter la requête cURL pour obtenir le jeton d'accès
    $response = curl_exec($ch);

    // Vérifier les erreurs cURL
    if ($response === false) {
        // Gérer les erreurs
        $error_message = curl_error($ch);
        echo json_encode(array('error' => 'Erreur cURL: ' . $error_message));
    } else {
        // Analyser la réponse JSON pour obtenir le jeton d'accès
        $responseData = json_decode($response, true);

        // Vérifier si le jeton d'accès est présent dans la réponse
        if (isset($responseData['access_token'])) {
        // Pour résumer on ne peut jamais faire confiance au client ainsi on vé récuperer l'id et le username côté serveur (sans passer par le client) on profite d'avoir l'access token ici

          
            // Récupérer les informations de l'utilisateur à partir de l'API Discord
            $accessToken = $responseData['access_token'];
            $userInfoResponse = file_get_contents("https://discord.com/api/users/@me", false, stream_context_create([
                "http" => [
                    // accesstoken précedement généré
                    "header" => "Authorization: Bearer " . $accessToken
                ]
            ]));
            // les informations se limite aux autorisation que vous aurez donné avec votre application OAuth (les scopes de l'apk)
            $userInfo = json_decode($userInfoResponse, true);

            // Vérifier si les informations de l'utilisateur ont été récupérées avec succès
           if (isset($userInfo['id'])) {
                // Enregistrer les informations de l'utilisateur en session
                $_SESSION['v2userId'] = $userInfo['id'];
                $_SESSION['v2username'] = $userInfo['username'];
		$_SESSION['v2avatar'] = $userInfo['avatar'];
		$_SESSION['v2tokenaccess'] = $accessToken;

                // Renvoyer le jeton d'accès
                echo json_encode(array('access_token' => $accessToken));
            } else {
                // Gérer l'erreur si les informations de l'utilisateur n'ont pas été récupérées
                echo json_encode(array('error' => 'Erreur lors de la récupération des informations de l\'utilisateur depuis l\'API Discord.'));
            }
        } else {
            // Gérer l'erreur si le jeton d'accès n'est pas renvoyé
            echo json_encode(array('error' => 'Erreur lors de la récupération du jeton d\'accès.'));
        }
    }

    // Fermer la session cURL
    curl_close($ch);
} else {
    // Gérer l'erreur si la méthode de requête n'est pas GET ou si le code d'autorisation est manquant
    http_response_code(400);
    echo json_encode(array('error' => 'Requête invalide ou code d\'autorisation manquant.'));
}
?>
