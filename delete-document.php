<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', $uri);
$id = end($uriParts);

if (!is_numeric($id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid post id'
    ]);
    return;
}

$id = intval($id);

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
        from document 
        where ID = ?;');

$statement->bind_param('i', $id);
$statement->execute();
$statement->bind_result($result);
$statement->fetch();
$statement->close();
if ($result == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No such document']);
    $connection->close();
    return;
}
$statement = $connection->prepare(
    'delete from document 
            where ID=?');

$statement->bind_param('i', $id);

echo $statement->execute()
    ? json_encode(['success' => true])
    : json_encode(['success' => false,
        'message' => 'Could not delete the document']);

$statement->close();
$connection->close();