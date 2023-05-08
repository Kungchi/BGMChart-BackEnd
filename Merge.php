<?php
require 'vendor/autoload.php';

function normalizeTitle($title) {
    $title = mb_strtolower($title, 'UTF-8');
    $title = preg_replace('/\s+/', '', $title);
    $title = preg_replace('/[^a-z가-힣0-9]+/u', '', $title);
    return $title;
}

function normalizeSinger($singer) {
    // 괄호 안의 정보 제거
    $singer = preg_replace('/\([^)]*\)/', '', $singer);
    // 소문자로 변환
    $singer = mb_strtolower($singer, 'UTF-8');
    // 공백 제거
    $singer = preg_replace('/\s+/', '', $singer);
    // 알파벳과 한글, 숫자 이외의 문자 제거
    $singer = preg_replace('/[^a-z가-힣0-9]+/u', '', $singer);
    // 정규화된 가수 이름 반환
    return $singer;
}

function levenshteinDistance($str1, $str2) {
    return levenshtein($str1, $str2);
}

function jaroWinkler($str1, $str2, $prefixScale = 0.1) {
    $jaroDistance = jaro($str1, $str2);
    $prefixLength = commonPrefixLength($str1, $str2);

    return $jaroDistance + ($prefixLength * $prefixScale * (1 - $jaroDistance));
}

function jaro($str1, $str2) {
    $str1_len = mb_strlen($str1);
    $str2_len = mb_strlen($str2);

    if ($str1_len === 0 || $str2_len === 0) {
        return 0.0;
    }

    $match_distance = intval(max($str1_len, $str2_len) / 2) - 1;
    $str1_matches = array();
    $str2_matches = array();

    $matches = 0;
    $transpositions = 0;

    for ($i = 0; $i < $str1_len; $i++) {
        $start = max(0, $i - $match_distance);
        $end = min($i + $match_distance + 1, $str2_len);

        for ($j = $start; $j < $end; $j++) {
            if (isset($str2_matches[$j]) && $str2_matches[$j]) {
                continue;
            }

            if ($str1[$i] === $str2[$j]) {
                $str1_matches[$i] = $str2_matches[$j] = true;
                $matches++;
                break;
            }
        }
    }

    if ($matches === 0) {
        return 0.0;
    }

    $k = 0;
    for ($i = 0; $i < $str1_len; $i++) {
        if (!isset($str1_matches[$i]) || !$str1_matches[$i]) {
            continue;
        }

        while (!isset($str2_matches[$k]) || !$str2_matches[$k]) {
            $k++;
        }

        if ($str1[$i] !== $str2[$k]) {
            $transpositions++;
        }

        $k++;
    }

    return (($matches / $str1_len) + ($matches / $str2_len) + (($matches - intval($transpositions / 2)) / $matches)) / 3;
}

function commonPrefixLength($str1, $str2, $max = 4) {
    $n = min(min(mb_strlen($str1), mb_strlen($str2)), $max);
    for ($i = 0; $i < $n; $i++) {
        if ($str1[$i] !== $str2[$i]) {
            return $i;
        }
    }

    return $n;
}


$mongoClient = new MongoDB\Client("mongodb://localhost:27017");

while(true) {
    $testCol = $mongoClient->Music->test;
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
            '$sort' => array('rank' => 1)
        ),
        array(
            '$out' => 'test'
        )
    );
    
    $mongoClient->selectCollection('Music', 'Melon')->aggregate($pipeline, ['allowDiskUse' => true]);

    $mergedData = $testCol->find()->toArray();

    foreach ($mergedData as $key => $value) {
        $mergedData[$key]['normalizedTitle'] = normalizeTitle($value['title']);
        $mergedData[$key]['normalizedSinger'] = normalizeSinger($value['singer']);
    }

    $threshold = 0.1;
    $groupedData = [];

    foreach ($mergedData as $value) {
        $isGrouped = false;

        foreach ($groupedData as $key => $group) {
            // 가수 이름이 같고 Jaro-Winkler 거리가 임계값 이상인 경우 그룹화
            if ($value['normalizedSinger'] === $group['normalizedSinger'] &&
                jaroWinkler($value['normalizedTitle'], $group['normalizedTitle']) >= $threshold &&
                substr($value['normalizedTitle'], 2, 5) === substr($group['normalizedTitle'], 2, 5)) {
                $groupedData[$key]['values'][] = $value;
                $isGrouped = true;
                break;
            }
        }
        

        if (!$isGrouped) {
            $groupedData[] = [
                'values' => [$value],
                'normalizedTitle' => $value['normalizedTitle'],
                'normalizedSinger' => $value['normalizedSinger']
            ];
        }
    }

    // 각 그룹의 평균 순위 계산
    foreach ($groupedData as $key => $group) {
        $totalRank = 0;
        $totalCount = count($group['values']);

        foreach ($group['values'] as $value) {
            $totalRank += $value['rank'];
        }

        $groupedData[$key]['averageRank'] = $totalRank / $totalCount;
    }

    // 3. 그룹화된 데이터를 다시 펼쳐서 정렬합니다.
    $finalData = [];
    foreach ($groupedData as $group) {
        $finalData[] = [
            'title' => $group['values'][0]['title'],
            'singer' => $group['values'][0]['singer'],
            'rank' => intval($group['averageRank'])
        ];
    }

    usort($finalData, function ($a, $b) {
        return $a['rank'] <=> $b['rank'];
    });
    $finalData = array_values($finalData);

    $testCol->deleteMany([]);
    $testCol->insertMany(array_slice($finalData, 0, 100));
    // 제안한 코드 추가 끝

    sleep(3600);
}
?>