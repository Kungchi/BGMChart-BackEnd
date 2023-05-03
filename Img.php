<?php
require 'vendor/autoload.php';

use Goutte\Client;
use MongoDB\Client as MongoClient;

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

while(true) {
  // MongoDB에 연결
  $mongoCollection = $mongoClient->Music->Merge;
  $documents = $mongoCollection->find();

  if (!extension_loaded('apcu')) {
    die('APCU 확장 모듈이 로드되어 있지 않습니다.');
  }

  foreach ($documents as $document) {
      $client = new Client();
      // HTTP 요청 헤더 설정
      $header = [
          'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
      ];

      $title = urlencode(trim(preg_replace('/\s+(FEAT|TWIN|WITH)\b.*$/i', '', $document->title)));
      $singer = urlencode(trim(preg_replace('/(,|\().*/', '', $document->singer)));

      $cacheKey = "$title|$singer";
      $src = apcu_fetch($cacheKey);

      $url = "https://music.bugs.co.kr/search/integrated?q=$title%20$singer";
      
      if ($src === false) {
        $crawler = $client->request('GET', $url);
        $Img_node = $crawler->filterXPath('//*[@id="DEFAULT0"]/table/tbody/tr[1]/td[2]/a/img');

        if ($Img_node->count() > 0) {
            $src = $Img_node->attr('src'); // src 속성 값 가져오기
            // $src 변수에 이미지 주소가 저장됨

            // 캐시 데이터 저장
            apcu_store($cacheKey, $src);
        } else {
            error_log('href not found for document ' . $document->singer, 0);
        }
    }
        
      
      $mongoCollection->updateOne(
          ['_id' => $document->_id],
          ['$set' => ['imgurl' => $src]]
      );
  }
  sleep(3600);
}
?>
