<?php
require 'vendor/autoload.php';

use MongoDB\Client as MongoClient;

// MongoDB에 연결
$mongoClient = new MongoClient("mongodb://3.37.205.195:27017");
$mongoDb = $mongoClient->selectDatabase('Music');
$mongoCollection = $mongoDb->selectCollection('Merge');
$documents = $mongoCollection->find();

// HTML에서 이미지 태그 작성
echo "<table>";
echo "<tr><th>Rank</th><th>Title</th><th>Singer</th><th>Image</th></tr>";

foreach ($documents as $document) {
    // 이미지 태그 생성
    $img_tag = "<img src='".$document->imgurl."' alt='".$document->title."'>";

    // 테이블에 데이터 출력
    echo "<tr>";
    echo "<td>".$img_tag."</td>";
    echo "<td>".$document->rank."</td>";
    echo "<td>".$document->title."</td>";
    echo "<td>".$document->singer."</td>";
    echo "</tr>";
}

echo "</table>";
?>
