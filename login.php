<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Username not provided']);
    return;
}
$username = $data['username'];

$connection = new mysqli(
    getenv('web_prog_lab_host'),
    getenv('web_prog_lab_username'),
    '',
    getenv('web_prog_lab_database'));

if ($connection->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $connection->connect_error]);
    return;
}

$statement = $connection->prepare(
    'select * 
            from author 
            where Name = ? ;');
$statement->bind_param('s', $username);
$statement->execute();
$result = $statement->get_result();

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $connection->connect_error]);
    $statement->close();
    $connection->close();
    return;
}

if ($result->num_rows > 0) {
    echo json_encode(['success' => true]);
    $statement->close();
    $connection->close();
    return;
}

$statement = $connection->prepare(
    'insert into author (Name) 
                        values (?)');

$statement->bind_param('s', $username);

echo $statement->execute()
    ? json_encode(['success' => true])
    : json_encode([
        'success' => false,
        'message' => 'Error inserting username']);
$statement->close();
$connection->close();