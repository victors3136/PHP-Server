<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

if (!isset($_GET['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Username not provided'
    ]);
    return;
}
$username = $_GET['username'];

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
    'select d.ID         as ID,
                   d.Name      as Name,
                   d.Extension as Extension
            from document d
                     inner join author a
                                on d.AuthorID = a.ID
            where a.Name = ? ;');
$statement->bind_param('s', $username);
$statement->execute();

$result = $statement->get_result();
if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Executing statement failed']);
    return;
}

$documents = [];
while ($row = $result->fetch_assoc()) {
    $item['ID'] = $row['ID'];
    $item['Name'] = $row['Name'];
    $item['Extension'] = $row['Extension'];
    $documents[] = $item;
}
echo json_encode([
    'success' => true,
    'documents' => $documents]);
$statement->close();
$connection->close();
