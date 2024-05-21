<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

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
    'select d.ID        as ID,
                   d.Name      as Name,
                   d.Extension as Extension,
                   a.Name      as Author
            from document d
                     inner join author a
                                on d.AuthorID = a.ID ;'
);

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
    $item['Author'] = $row['Author'];
    $documents[] = $item;
}

echo json_encode([
    'success' => true,
    'documents' => $documents]);
$statement->close();
$connection->close();
