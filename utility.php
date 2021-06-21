<?php
require 'vendor/autoload.php';

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\DynamoDb\Exception\DynamoDbException;

use Aws\Sdk;
$bucket = 's3734303-assignment2';
$region = 'ap-southeast-2';
$credential =[
    'key'    => 'AKIAR7IZKHOUEPKTCSVB',
    'secret' => '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'
];
$s3client = new S3Client([
    'region'  => $region,
    'version' => '2006-03-01',
    'credentials' => $credential,
]);
$dynamoSDK = new Sdk([
    'region'  => $region,
    'version' => 'latest',
    'credentials' => $credential,
]);
