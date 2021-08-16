<?php

use Google\Service\YouTube\LiveBroadcastSnippet;
use Google\Service\YouTube\LiveBroadcastStatus;

require __DIR__ . '/vendor/autoload.php';

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Docs API PHP Quickstart');
    $client->setScopes([Google_Service_YouTube::YOUTUBE]);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory('token.json');
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path)
{
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google\Service\YouTube($client);
$liveBroadcast = new Google\Service\YouTube\LiveBroadcast();
$thumbnail = new Google\Service\YouTube\Thumbnail();
$thumbnail->setUrl('https://site.coop/image.jpg');
$thumbnails = new Google\Service\YouTube\ThumbnailDetails();
$thumbnails->setDefault($thumbnail);
$snippet = new LiveBroadcastSnippet();
$snippet->setTitle('teste');
$snippet->setDescription('Descrição aqui');
$snippet->setThumbnails($thumbnails);
$snippet->setScheduledStartTime('2021-08-16T10:30-03:00');
$snippet->setChannelId('AQUIVAIOCHANNELID');
$status = new LiveBroadcastStatus();
$status->setPrivacyStatus('unlisted');
$liveBroadcast->setStatus($status);
$liveBroadcast->setSnippet($snippet);
$oi = $service->liveBroadcasts->insert(
    'id,snippet,contentDetails,status',
    $liveBroadcast
);
