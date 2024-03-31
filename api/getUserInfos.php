<?php
ini_set('session.cookie_secure', "1");
ini_set('session.cookie_httponly', "1");
ini_set('session.cookie_samesite','None');
session_start();

// Vérifier si le token d'accès est présent dans la session
if (!isset($_SESSION['v2tokenaccess'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(array('error' => 'Token d\'accès non trouvé dans la session.'));
    exit;
}

// Récupérer le token d'accès depuis la session
$access_token = $_SESSION['v2tokenaccess'];

// Construire l'URL de l'API Discord
$discord_api_url = 'https://discord.com/api/users/@me';

// Initialiser la requête cURL
$ch = curl_init($discord_api_url);

// Configurer les options de la requête cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $access_token
));

// Exécuter la requête cURL
$response = curl_exec($ch);

// Vérifier les erreurs de la requête cURL
if ($response === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Erreur lors de la requête à l\'API Discord: ' . curl_error($ch)));
    exit;
}

// Fermer la session cURL
curl_close($ch);

// Vérifier si la réponse est au format JSON
$user_data = json_decode($response, true);
if ($user_data === null) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Réponse invalide de l\'API Discord.'));
    exit;
}

// Renvoyer les données de l'utilisateur au format JSON
http_response_code(200); // OK
header('Content-Type: application/json');
echo json_encode($user_data);
?>
