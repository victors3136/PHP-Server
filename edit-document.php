<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$form_data = $data['formData'];
$name = $form_data['name'];
$id = $_SESSION['id'];

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
    'update document 
            set Name=?
            where Id=?');
$statement->bind_param('si', $name, $id);

echo $statement->execute()
    ? json_encode(['success' => true])
    : json_encode(['success' => false,
        'message' => 'Could not update the document']);

$statement->close();
$connection->close();