<?php
require 'vendor/autoload.php';

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
            '$limit' => 100
        ),
        array(
            '$out' => 'Merge'
        )
    );

    $mongoClient->selectCollection('Music', 'Melon')->aggregate($pipeline, ['allowDiskUse' => true]);
    sleep(3600);
}
?>
