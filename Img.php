<?php
require 'vendor/autoload.php';

use MongoDB\Client as MongoClient;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$clientId = $_ENV['SPOTIFY_CLIENT_ID'];
$clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];

$session = new SpotifyWebAPI\Session(
    $clientId,
    $clientSecret
);

$session->requestCredentialsToken();
$accessToken = $session->getAccessToken();

$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($accessToken);

// MongoDB에 연결
$mongoClient = new MongoDB\Client($_ENV['MONGO_CONNECTION_STRING']);

while(true) {
    $mongoCollection = $mongoClient->Music->test;
    $documents = $mongoCollection->find();

    foreach ($documents as $document) 
    {
        $title =  $document->title;
        $singer =  $document->singer;

        $cacheKey = "$title|$singer";
        $artworkUrl = apcu_fetch($cacheKey);

        if($artworkUrl == false) 
        {
            $searchResults = $api->search($title . ' ' . $singer, 'track', ['limit' => 1]);
            $artworkUrl = '';
            if (!empty($searchResults->tracks->items)) 
            {
                $closestImage = getClosestImageSize($searchResults->tracks->items[0]->album->images, 50, 50);
                $artworkUrl = $closestImage->url;
                apcu_store($cacheKey, $artworkUrl);
            }
        }
        $mongoCollection->updateOne(
            ['_id' => $document->_id],
            ['$set' => ['imgurl' => $artworkUrl]]
        );
    }
    $mongoClient->Music->Melon->drop();
    $mongoClient->Music->Bugs->drop();
    $mongoClient->Music->Genie->drop();
    $mongoClient->Music->Merge->drop();

    $testCollectionData = $mongoClient->Music->test->find()->toArray();
    $mongoClient->Music->Merge->insertMany($testCollectionData);
    $mongoClient->Music->test->drop();
    sleep(3600);
}
    // 가장 근접한 이미지 크기를 찾는 함수
function getClosestImageSize($images, $desiredWidth, $desiredHeight) {
    $closestImage = null;
    $closestSizeDifference = null;

    foreach ($images as $image) {
        $sizeDifference = abs($image->width - $desiredWidth) + abs($image->height - $desiredHeight);

        if ($closestSizeDifference === null || $sizeDifference < $closestSizeDifference) {
             $closestImage = $image;
             $closestSizeDifference = $sizeDifference;
        }
    }

    return $closestImage;

}
?>
