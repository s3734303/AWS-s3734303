<?php
require 'vendor/autoload.php';

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
$bucket = 's3734303-assignment2';
$client = new S3Client([
    'region'  => 'ap-southeast-2',
    'version' => '2006-03-01',
    'credentials' => [
        'key'    => 'AKIAR7IZKHOUEPKTCSVB',
        'secret' => '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'
    ],

]);
$marshaler = new Marshaler();
$dynamoSDK = new \Aws\Sdk([
    'region'  => 'ap-southeast-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => 'AKIAR7IZKHOUEPKTCSVB',
        'secret' => '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'
    ],
]);
$TableName ='music';
$dynamoDb =$dynamoSDK->createDynamoDb();
    try{
        $client->createBucket([
            'Bucket' => $bucket,
        ]);
    }catch (AwsException $e){
        echo "Bucket Exists\n";
    }
try{
    $dynamoDb->createTable([
            'TableName' => $TableName,
            'KeySchema' => [
                [
                    'AttributeName' => 'title',
                    'KeyType' => 'HASH'
                ],
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'title',
                    'AttributeType' => 'S'
                ],
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 1,
                'WriteCapacityUnits' => 1
            ]
        ]);
}catch (DynamoDbException $e){
        echo "table exists\n";
}

$songs = json_decode(file_get_contents('a2.json'),true)['songs'];

    foreach ($songs as $song){
        $title = $song['title'];
        $artist = $song['artist'];
        $year =$song['year'];
        $web_url = $song['web_url'];
        $img_url = $song['img_url'];
        $json = json_encode([
            'title'=>$song['title'],
            'artist' => $song['artist'],
            'year' =>(int)$song['year'],
            'web_url' => $song['web_url'],
            'img_url' => $song['img_url']
            ]);
        try{
            echo "adding ".$song['title']."\n";
            $dynamoDb->putItem([
                'TableName' =>$TableName,
                'Item' =>$marshaler->marshalJson($json)

            ]);
        }catch (DynamoDbException $e){

        }
        try{
            $client->putObject([
                'Bucket' => $bucket,
                'Key' => "img/".str_replace(' ','',$song['artist']).".jpg",
                'Body' => (new GuzzleHttp\Client())->get($song['img_url'])->getBody(),
                'ACL'    => 'public-read'
            ]);
        }catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }
        catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo 'Huh?';
        }

    }
echo "done.";



