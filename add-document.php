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
if (!isset($data['name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Name for file not provided']);
    return;
}
$name = $data['name'];
if (!isset($data['extension'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Extension for file not provided']);
    return;
}
$extension = $data['extension'];
if (!in_array($extension, [
    'txt', 'json', 'html', 'css', 'svg', 'scss', 'xml', 'xaml', 'yaml', 'fxml',
    'c', 'cpp', 'cs', 'java', 'js', 'jsx', 'ts', 'tsx',
    'php', 'jsp', 'asp', 'aspx',
    'py', 'ipynb',
    'sql'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unaccepted file extension']);
    return;
}
if (!isset($_FILES['file'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Content for file not provided']);
    return;
}
$file = $_FILES['file'];

$real_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($real_extension != $extension) {
    echo json_encode([
        'success' => false,
        'message' => 'Specified extension does not match actual extension']);
    return;
}


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
    'select ID
            from author 
            where Name=? ;');
$statement->bind_param('s', $username);
$statement->execute();

$result = $statement->get_result();
if ($result->num_rows < 1) {
    echo json_encode(['success' => false]);
    $statement->close();
    $connection->close();
    return;
}

$author_id = $result->fetch_assoc()['ID'];
$statement = $connection->prepare(
    'insert into document (Name, Extension, AuthorID, Document) 
                        values (?,?,?,?)');

$statement->bind_param('ssis', $name, $extension, $author_id, $file_content);

$file_content = file_get_contents($file['tmp_name']);
echo $statement->execute()
    ? json_encode(['success' => true])
    : json_encode(['success' => false,
        'message' => 'Failed to insert']);

$statement->close();
$connection->close();