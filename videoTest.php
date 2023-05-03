<?php
require 'vendor/autoload.php';

use MongoDB\Client as MongoClient;

$mongoClient = new MongoClient("mongodb://localhost:27017");
$mongoDb = $mongoClient->selectDatabase('Music');
$mongoCollection = $mongoDb->selectCollection('test');
$documents = $mongoCollection->find([]);

// Google API 클라이언트 객체 생성
$client = new Google\Client();
$client->setApplicationName('BGM_Chart');
$client->setDeveloperKey('YOUR_DEVELOPER_KEY');

// YouTube API 클라이언트 생성
$youtube = new Google\Service\YouTube($client);

foreach($documents as $document) {
    // 캐싱된 검색 결과가 있다면 해당 결과를 사용
    $cacheKey = $document->title . '|' . $document->singer;
    $cacheValue = apc_fetch($cacheKey);
    if ($cacheValue !== false) {
        $videoId = $cacheValue;
    } else {
        // 검색 요청을 보냄
        $keyword = $document->title . ' ' . $document->singer;
        $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $keyword,
            'maxResults' => 1
        ));
        $videoId = $searchResponse['items'][0]['id']['videoId'];
        // 검색 결과를 캐싱
        apc_store($cacheKey, $videoId);
    }

    $mongoCollection->updateOne(
        ['_id' => $document->_id],
        ['$set' => ['videoId' => $videoId]]
    );
}

?>
