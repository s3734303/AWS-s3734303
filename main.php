<?php
session_start();
use Aws\DynamoDb\Marshaler;
require __DIR__.'/utility.php';
echo "<h3>{$_SESSION['user_name']}</h3>";

$client= $dynamoSDK->createDynamoDb();
$subs = $client->query([
    'TableName'=>'subscription',
    'KeyConditionExpression'=> "email=:email",
//    'ExpressionAttributeNames'=> [ '#email' => 'email' ],
    'ExpressionAttributeValues'=>(new Marshaler())->marshalJson('{":email": "'.$_SESSION['email'].'"}')
]);
echo "<div>
    <h3>Subscription</h3>
    <table style='width:70%'>
        <tr>
            <th>title</th>
            <th>artist</th> 
            <th>year</th>
            <th>artist image</th>
            <th>remove</th>
        </tr>
";

if(isset($_POST['deleteItem'])){
    $client->deleteItem(
        array(
            'TableName' => 'subscription',
            'Key'=> (new Marshaler())->marshalJson('{"title": "'.$_POST['delete'].'","email": "'.$_SESSION['email'].'"}')
        )
    );
    header("Refresh:0");
}
if(isset($_POST['putItem'])){
    print_r($_SESSION['email']);
    print_r($_POST['addItem']);
    $item = (new Marshaler())->marshalJson(
        '{
        "email": "'.$_SESSION['email'].'", 
        "title": "'. $_POST['addItem'].'"
    }'
    );
    $client->putItem([
            'TableName' => 'subscription',
            'Item'=> $item
        ]
    );
    header("Refresh:0");
}


if($subs['Items'])
    foreach ($subs['Items'] as $sub){
        $key =(new Marshaler())->marshalJson('{"title": "'.$sub['title']['S'].'"}');
        $result_music = $client->getItem(
            array(
                'ConsistentRead' => true,
                'TableName' => 'music',
                'Key'       =>(new Marshaler())->marshalJson('{"title": "' . $sub['title']['S'] . '"}')
            ));
        $src = "https://s3734303-assignment2.s3-ap-southeast-2.amazonaws.com/img/".str_replace(' ','',$result_music['Item']['artist']['S']).".jpg";
        echo "<tr>
                    <th>{$result_music['Item']['title']['S']}</th>
                    <th>{$result_music['Item']['artist']['S']}</th> 
                    <th>{$result_music['Item']['year']['N']}</th>
                    <th><img src='{$src}'></th>
                    <th>
                    <form method='post'>";
        echo                '<input type="hidden" name="delete" value="'.$result_music['Item']['title']['S'].'">';
        echo                "<input type='submit' name='deleteItem' value='remove'>
                            </form>
                        </th>
                </tr>";
    }
echo "</table></div>";
echo "
<div>
    <h2>Query</h2>
    <div>
        <form method='post'>
        <label for='query_title'>Title</label>
        <input type='text' name='query_title'>
        <label for='query_artist'>Artist</label>
        <input type='text' name='query_artist'>
        <label for='query_year'>Year</label>
        <input type='number' size='4' name='query_year'>
        <input type='submit' name='query' value='Query'>
        </form>
    </div>
</div>";
if(isset($_POST['query'])){
    $filterExpression='';
    $value_encode=array_filter(array(
        ":title"=>$_POST['query_title'],
        ":artist"=>$_POST['query_artist'],
        ":year"=>(int)$_POST['query_year']
    ));

    if(array_key_exists(':title',$value_encode) || array_key_exists(':artist',$value_encode) ||array_key_exists(':year',$value_encode)){
        if(isset($value_encode[':title'])){
            $filterExpression.="title=:title";
            if(isset($value_encode[':artist']) ||isset($value_encode[':year']))
                $filterExpression .= ' and ';
        }

        if(isset($value_encode[':artist'])){
            $filterExpression.="artist=:artist";
            if(isset($value_encode[':year'])) {
                $filterExpression .= ' and ';
            }
        }
        if(isset($value_encode[':year'])){
            $filterExpression.="#year=:year";
        }
        $value_encode=array_filter($value_encode);

        if(!empty($value_encode)){
            $eav = (new Marshaler())->marshalJson(json_encode($value_encode));
            $query_parameter =array_filter([
                'TableName'=>'music',
                'FilterExpression'=>$filterExpression,
                'ProjectionExpression'=>"title,artist,#year",
                'ExpressionAttributeNames'=>['#year'=>'year'],
                'ExpressionAttributeValues'=>$eav]);
        }
        else
            $query_parameter = array('TableName'=>'music');
        $query_result = $client->scan($query_parameter);
        if(empty($query_result['Items']))
            echo "<p style='color: darkred'>No result is retrieved. Please query again</p>";
        else{
            echo "<div>
    <table style='width:70%'>
        <tr>
            <th>title</th>
            <th>artist</th> 
            <th>year</th>
            <th>artist image</th>
            <th>remove</th>
        </tr>
";

            foreach ($query_result['Items'] as $i){
                $src = "https://s3734303-assignment2.s3-ap-southeast-2.amazonaws.com/img/".str_replace(' ','',$i['artist']['S']).".jpg";
                echo "<tr>
                    <th>{$i['title']['S']}</th>
                    <th>{$i['artist']['S']}</th> 
                    <th>{$i['year']['N']}</th>
                    <th><img src='$src'></th>
                    <th>
                    <form method='post'>";
                echo                '<input type="hidden" name="addItem" value="'.$i['title']['S'].'">';
                echo                "<input type='submit' name='putItem' value='Subscribe'>
                            </form>
                        </th>
                </tr>";
            }
        }
    }



}



echo "<a href ='/logout.php'>Logout</a>";
