<?php
require 'vendor/autoload.php';

 use Goutte\Client;
 use MongoDB\Client as MongoClient;

 $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
 $dotenv->load();
 $mongoClient = new MongoDB\Client($_ENV['MONGO_CONNECTION_STRING']);

 while(true) {
    // MongoDB에 연결
    $mongoCollection = $mongoClient->Music->Bugs;
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

    $documents = $mongoCollection->find();
    sleep(3600);
}
?>
