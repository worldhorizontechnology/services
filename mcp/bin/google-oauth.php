<?php

declare(strict_types=1);

use Google\Client;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
(new Dotenv())->usePutenv(true)->bootEnv($root . '/.env');

$tokenPath = getenv('GOOGLE_WORKSPACE_TOKEN_PATH')
    ?: $root . '/var/google_calendar_token.json';

$client = new Client();
$client->setClientId((string) getenv('GOOGLE_CLIENT_ID'));
$client->setClientSecret((string) getenv('GOOGLE_CLIENT_SECRET'));
$client->setRedirectUri((string) getenv('GOOGLE_REDIRECT_URI'));
$client->setScopes([
    'https://www.googleapis.com/auth/calendar',
    'https://www.googleapis.com/auth/drive.readonly',
]);
$client->setAccessType('offline');
$client->setIncludeGrantedScopes(true);
$client->setPrompt('consent');
$client->setState('workspace-drive-consent');

$token = is_file($tokenPath)
    ? json_decode((string) file_get_contents($tokenPath), true)
    : null;

if (is_array($token) && isset($token['refresh_token'])) {
    $client->setAccessToken($token);
}

if ($client->isAccessTokenExpired() || !$client->getAccessToken()) {
    $url = $client->createAuthUrl();
    fwrite(STDOUT, "Open this URL in your browser:\n\n{$url}\n\n");
    fwrite(STDOUT, "Paste the authorization code here: ");
    $code = trim((string) fgets(STDIN));

    if ($code === '') {
        throw new RuntimeException('Authorization code cannot be empty.');
    }

    $accessToken = $client->fetchAccessTokenWithAuthCode($code);
    if (isset($accessToken['error'])) {
        throw new RuntimeException(json_encode($accessToken, JSON_THROW_ON_ERROR));
    }

    if (isset($token['refresh_token']) && !isset($accessToken['refresh_token'])) {
        $accessToken['refresh_token'] = $token['refresh_token'];
    }

    if (!is_dir(dirname($tokenPath))) {
        mkdir(dirname($tokenPath), 0775, true);
    }

    file_put_contents($tokenPath, json_encode($accessToken, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    fwrite(STDOUT, "OAuth token saved to {$tokenPath}\n");
} else {
    fwrite(STDOUT, "Existing OAuth token is available at {$tokenPath}.\n");
}
