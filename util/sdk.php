<script type='module' async>
const { DiscordSDK } = await import('https://unpkg.com/@discord/embedded-app-sdk@1.4.3/output/index.mjs');
  const discordSdk = new DiscordSDK('app-id');

  console.log('Waiting for DiscordSDK to be ready...');
  async function setup() {
  await discordSdk.ready();
  // The rest of your app logic
async function getCodeAndAuthenticate() {
  try {
    // Récupérer le code d'autorisation
    const authoresp = await discordSdk.commands.authorize({
      client_id: 'app-id',
      response_type: 'code',
      state: '',
      prompt: 'none',
      scope: [
        'identify',
        'guilds',
      ],
    });

    const code = authoresp.code;
    //ne pas oublier de l'enlever
    console.log('Code d\'autorisation :', code);

    // Notre serveur d'auth en php
    const tokenResponse = await fetch(`/.proxy/token.php?code=${encodeURIComponent(code)}`);
    if (!tokenResponse.ok) {
      throw new Error('Erreur lors de la récupération du jeton d\'accès');
    }

    // Récupérer le jeton d'accès à partir de la réponse
    const { access_token } = await tokenResponse.json();
    //console.log('Access Token :', access_token);

    // Authentifier avec Discord en utilisant l'access token
    const infouser = await discordSdk.commands.authenticate({
      access_token: access_token
    });
	  //exemple d'application
    console.log('Authentification réussie avec l\'access token.');
	  await console.log(discordSdk.commands.getInstanceConnectedParticipants());
  } catch (error) {
    console.error('Erreur lors de la récupération du code ou de l\'authentification avec l\'access token :', error);
  }
}

// Appel de la fonction pour récupérer le code et s'authentifier avec l'access token
getCodeAndAuthenticate();

}
	setup()


</script>
