<?php

if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
    $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
} else if (isset($_SERVER['CONTENT_TYPE'])) {
    $contentType = $_SERVER['CONTENT_TYPE'];
} else if (isset($_SERVER['HTTP_ACCEPT'])) {
    $contentType = $_SERVER['HTTP_ACCEPT'];
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $data = $_GET;

        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/redirect') {
            header('Location: /?' . $_SERVER['QUERY_STRING']);
            exit;
        }

        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/bad') {
            header('Content-Type: application/json');
            echo '{"foo":"bar"';
            exit;
        }

        break;
    case 'POST':
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = $_POST;
        }

        break;
    case 'PUT':
        $data = [];
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            parse_str(file_get_contents('php://input'), $data);
        }
        break;
    case 'DELETE':
        $data = $_GET;
        break;
    default:
        $data = [];
        break;
}

header('Content-Type: application/json');
echo json_encode($data);

