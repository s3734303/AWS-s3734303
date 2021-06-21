<?php
session_start();
require 'vendor/autoload.php';
require __DIR__.'/utility.php';
echo "
    <h2>Login</h2>
    <form method='POST'>
    <label for='email'>Email</label><br>
    <input type='email' name='email'><br>
    <label for='passWD'>Password</label><br>
    <input type='password' name='passWD'><br>
    <input type='submit' name='login'>
    </form>
    <a href ='/register.php'>Register</a>
";
if(isset($_POST['login']) && !empty($dynamoSDK)){
    $client= $dynamoSDK->createDynamoDb();
    $key =(new \Aws\DynamoDb\Marshaler())->marshalJson('{
        "email": "' . $_POST['email'] . '"
    }');
    try{
        $result = $client->getItem(
            array(
                'ConsistentRead' => true,
                'TableName' => 'login',
                'Key'       =>$key
            ));
        if($result['Item'] && (strcmp($result['Item']['password']['S'],$_POST['passWD'])==0)){
            $_SESSION['email']=$_POST['email'];
            $_SESSION['user_name']=$result['Item']['user_name']['S'];
            echo "<script>window.location.replace('/main.php');</script>";
        }
        else
            echo "<p style='color: darkred'>email or password is invalid</p>";
    }catch (DynamoDbException $e){

    }
}