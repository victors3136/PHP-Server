<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

if (!isset($data['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Username not provided']);
    return;
}
$username = $data['username'];

if (!isset($data['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Id for post to delete not provided']);
    return;
}
$id = $data['id'];

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
        where AuthorID = ? and ID = ?;');

$statement->bind_param('ii', $username, $id);
$statement->execute();
$statement->bind_result($result);
$statement->fetch();

if ($result == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'That is not your document >:-(']);
    $statement->close();
    $connection->close();
    return;
}

$statement = $connection->prepare(
    'delete from document 
            where Id=?');
$statement->bind_param('i', $id);

echo $statement->execute()
    ? json_encode(['success' => true])
    : json_encode(['success' => false,
        'message' => 'Could not delete the document']);

$statement->close();
$connection->close();