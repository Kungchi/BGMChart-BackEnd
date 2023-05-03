<?php
require 'vendor/autoload.php';

use Goutte\Client;
use MongoDB\Client as MongoClient;

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

while(true) {
    // MongoDB에 연결
    $mongoCollection = $mongoClient->Music->Genie;

    // Goutte Client 생성
    $client = new Client();

    // HTTP 요청 헤더 설정
    $header = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
    ];

    // Goutte Client의 HTTP 요청 헤더 설정
    $client->setServerParameter('HTTP_USER_AGENT', $header['User-Agent']);

    for($i = 1; $i <= 2; $i++) {
        // 크롤링할 페이지 URL
        $url = "https://www.genie.co.kr/chart/top200?ditc=D&ymd=20230418&hh=23&rtm=Y&pg=" . $i;

        // HTML 페이지 가져오기
        $crawler = $client->request('GET', $url);

        // 크롤링한 데이터를 MongoDB에 저장
        $rank_nodes = $crawler->filter('td.number');
        $title_nodes = $crawler->filter('a.title.ellipsis');
        $singer_nodes = $crawler->filter('td.info > a:nth-child(2)');
        $pattern = '/[^\p{Hangul}\p{Latin}\p{Nd}\s\'\x{2019}&]+/u';

        $documents = array();
        $rank_nodes->each(function ($node, $index) use ($title_nodes, $singer_nodes, &$documents, $pattern) {
            $title_text = $title_nodes->eq($index)->text();
            $title_filtered = preg_replace($pattern, '', $title_text);
            $title_filtered = strtoupper($title_filtered);
            $title_filtered = str_replace('PROD BY', 'PROD', $title_filtered);
            $title_filtered = str_replace('’', '\'', $title_filtered);
            $title_filtered = trim($title_filtered);

            $documents[] = array(
                'rank' => (int) $node->text(),
                'title' => $title_filtered,
                'singer' => $singer_nodes->eq($index)->text(),
            );
        });

        $mongoCollection->insertMany($documents);
    }
    $documents = $mongoCollection->find();

    // HTML 테이블 생성
    echo "<table>";
    foreach ($documents as $document) {
        echo "<tr>";
        echo "<td>" . $document['rank'] . "</td>";
        echo "<td>" . $document['title'] . "</td>";
        echo "<td>" . $document['singer'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    sleep(3600);
}
?>