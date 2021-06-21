<?php
require __DIR__.'/utility.php';
use \Aws\DynamoDb\Marshaler;
echo "<div>
    <h2>Register</h2>
    <form method='POST'>
        <label for='email'>Email</label><br>
        <input type='email' name='email' required><br>
        <label for='user_name' >UserName</label><br>
        <input type='text' name='uname' required><br>
        <label for='passwd'>Password</label><br>
        <input type='password' name='passwd' required><br>
        <input type='submit' name='register' value='Register'>
    </form>
</div>";
$tableName ='login';
if(isset($_POST['register']) && !empty($dynamoSDK)){
    $client= $dynamoSDK->createDynamoDb();
    $key =(new Marshaler())->marshalJson('{
        "email": "'.$_POST['email'] . '"
    }');
    $result = $client->getItem(
        array(
            'ConsistentRead' => true,
            'TableName' => 'login',
            'Key'       =>$key
        ));
    if($result['Item'])
        echo "<p style='color: darkred'>The email already exists</p>";
    else{
        $newUser = json_encode([
            'email' => $_POST['email'],
            'user_name'=>$_POST['uname'],
            'password'=>$_POST['passwd']
        ]);
        $client->putItem(array(
            'TableName' => $tableName,
            'Item'=>(new Marshaler())->marshalJson($newUser)
        ));
        echo "<script>window.location.replace('/index.php');</script>";
    }
}
