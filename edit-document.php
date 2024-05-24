<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', $uri);
$documentID = end($uriParts);

if (!is_numeric($documentID)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid post id'
    ]);
    return;
}

$documentID = intval($documentID);

$input = file_get_contents('php://input');

$body = json_decode($input, true);
if(!isset($body['user'])){
    echo json_encode([
        'success' => false,
        'message' => 'Missing username '. $input
    ]);
    return;
}
$username = $body['user'];

if (!isset($body['document']['Name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing document name '. $input
    ]);
    return;
}

$documentName = $body['document']['Name'];

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

$statement = $connection->prepare('
        select count(*)
        from document d inner join author a on d.AuthorID = a.ID
        where a.Name=? and d.ID=? ;');

$statement->bind_param('si', $username, $documentID);
$statement->execute();
$statement->bind_result($result);
$statement->fetch();
$statement->close();
if ($result == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not find document']);
    $connection->close();
    return;
}

$statement = $connection->prepare(
    'update document 
            set Name=?
            where Id=? ;');
$statement->bind_param('si', $documentName, $documentID);

echo $statement->execute()
    ? json_encode(['success' => true])
    : json_encode(['success' => false,
        'message' => 'Could not update the document']);

$statement->close();
$connection->close();