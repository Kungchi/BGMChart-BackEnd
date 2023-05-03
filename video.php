<?php
require 'vendor/autoload.php';

use Goutte\Client;
use MongoDB\Client as MongoClient;

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

while(true) {
  // MongoDB에 연결
  $mongoCollection = $mongoClient->Music->Merge;
  $documents = $mongoCollection->find([]);

  if (!extension_loaded('apcu')) {
    die('APCU 확장 모듈이 로드되어 있지 않습니다.');
  }

  foreach ($documents as $document) {
      $client = new Client();
      // HTTP 요청 헤더 설정
      $header = [
          'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
      ];

      $client->setServerParameter('HTTP_USER_AGENT', $header['User-Agent']);

      $title = urlencode(trim(preg_replace('/\s+(FEAT|TWIN|WITH)\b.*$/i', '', $document->title)));
      $singer = urlencode(trim(preg_replace('/(,|\().*/', '', $document->singer)));

      $cacheKey = "$title|$singer";
      $videoId = apcu_fetch($cacheKey);

      $url = "https://www.google.com/search?q=$title%20$singer%20youtube";

      if ($videoId === false) {
        $crawler = $client->request('GET', $url);
        $videoId_node = $crawler->filter('div.ct3b9e > div > a');

        if ($videoId_node->count() > 0) {
          $href = $videoId_node->attr('href'); // href 속성 값 가져오기
          $pos = strpos($href, '=');
          if ($pos !== false) {
            $videoId = substr($href, $pos + 1); // = 이후의 문자열 추출
            apcu_store($cacheKey, $videoId);
          } else {
            error_log('Video ID not found for document1 ' . $document->title, 0);
          }
        } else {
          error_log('Video ID not found for document2 ' . $document->title, 0);
        }
    }

      $mongoCollection->updateOne(
        ['_id' => $document->_id],
        ['$set' => ['videoId' => $videoId]]
    );
  }
  $mongoClient->Music->Melon->drop();
  $mongoClient->Music->Bugs->drop();
  $mongoClient->Music->Genie->drop();
  sleep(3600);
}
?>

