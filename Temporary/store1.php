<?php
$servername="localhost";
$username="root";
$password="";
$dbname="store";

$conn=new mysqli($servername,$username,$password,$dbname);
if ($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}
$stmt=$conn->prepare("INSERT INTO try (name,age,city) VALUES (?,?,?)");

$stmt->bind_param("sss",$_POST['name'],$_POST['age'],$_POST['city']);

if($stmt->execute()){
    echo"Data Stored successfully";
}
else{
    echo"Error: ".$stmt->error;
}
$stmt->close();
$conn->close();
?>