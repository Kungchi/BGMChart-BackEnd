<?php
require 'Composer/vendor/autoload.php';

 use Goutte\Client;
 use MongoDB\Client as MongoClient;

// MongoDB에 연결
$mongoClient = new MongoClient("mongodb://3.39.229.193:27017");
$mongoDb = $mongoClient->selectDatabase('Music');
$mongoCollection = $mongoDb->selectCollection('Bugs');
$mongoCollection->deleteMany([]);

// Goutte Client 생성
$client = new Client();

// HTTP 요청 헤더 설정
$header = [
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
];

// Goutte Client의 HTTP 요청 헤더 설정
$client->setServerParameter('HTTP_USER_AGENT', $header['User-Agent']);

// 크롤링할 페이지 URL
$url = "https://music.bugs.co.kr/chart";

// HTML 페이지 가져오기
$crawler = $client->request('GET', $url);

// 크롤링한 데이터를 MongoDB에 저장
$rank_nodes = $crawler->filter('div.ranking > strong');
$title_nodes = $crawler->filter('p.title > a');
$singer_nodes = $crawler->filter('td.left > p.artist > a:nth-child(1)');

$documents = array();
$rank_nodes->each(function ($node, $index) use ($title_nodes, $singer_nodes, &$documents) {
    $documents[] = array(
        'rank' => (int) $node->text(),
        'title' => $title_nodes->eq($index)->text(),
        'singer' => $singer_nodes->eq($index)->text(),
    );
});

$mongoCollection->insertMany($documents);

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
?>
