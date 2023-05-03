<?php
require 'vendor/autoload.php';

function normalizeTitle($title) {
    $title = mb_strtolower($title, 'UTF-8');
    $title = preg_replace('/\s+/', '', $title);
    $title = preg_replace('/[^a-z가-힣0-9]+/u', '', $title);
    return $title;
}

function levenshteinDistance($str1, $str2) {
    return levenshtein($str1, $str2);
}


$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

while(true) {
    $testCol = $mongoClient->Music->Merge;
    $testCol->deleteMany([]);

    $pipeline = array(
        array(
            '$project' => array(
                'rank' => 1,
                'title' => 1,
                'singer' => 1,
                'source' => array('$literal' => 'melon')
            )
        ),
        array(
            '$unionWith' => array(
                'coll' => 'Bugs',
                'pipeline' => array(
                    array(
                        '$project' => array(
                            'rank' => 1,
                            'title' => 1,
                            'singer' => 1,
                            'source' => array('$literal' => 'bugs')
                        )
                    )
                )
            )
        ),
        array(
            '$unionWith' => array(
                'coll' => 'Genie',
                'pipeline' => array(
                    array(
                        '$project' => array(
                            'rank' => 1,
                            'title' => 1,
                            'singer' => 1,
                            'source' => array('$literal' => 'genie')
                        )
                    )
                )
            )
        ),
        array(
            '$group' => array(
                '_id' => '$title',
                'rank' => array('$sum' => '$rank'),
                'singer' => array('$first' => '$singer'),
                'source' => array('$push' => '$source')
            )
        ),
        array(
            '$project' => array(
                '_id' => 0,
                'title' => '$_id',
                'rank' => array('$divide' => array('$rank', 3)),
                'singer' => 1,
            )
        ),
        array(
            '$sort' => array('rank' => 1)
        ),
        array(
            '$out' => 'Merge'
        )
    );

    $mongoClient->selectCollection('Music', 'Melon')->aggregate($pipeline, ['allowDiskUse' => true]);

    $mergedData = $testCol->find()->toArray();

    foreach ($mergedData as $key => $value) {
        $mergedData[$key]['normalizedTitle'] = normalizeTitle($value['title']);
    }

    $mergedLength = count($mergedData);
    for ($i = 0; $i < $mergedLength - 1; $i++) {
        for ($j = $i + 1; $j < $mergedLength; $j++) {
            $levenshteinDist = levenshteinDistance($mergedData[$i]['normalizedTitle'], $mergedData[$j]['normalizedTitle']);
            $threshold = 1;

            if ($levenshteinDist <= $threshold) {
                $mergedData[$i]['rank'] = ($mergedData[$i]['rank'] + $mergedData[$j]['rank']) / 2;
                array_splice($mergedData, $j, 1);
                $mergedLength--;
                $j--;
            }
        }
    }

    usort($mergedData, function ($a, $b) {
        return $a['rank'] <=> $b['rank'];
    });
    $mergedData = array_values($mergedData);

    $testCol->deleteMany([]);
    $testCol->insertMany($mergedData);
    sleep(3600);
}
?>