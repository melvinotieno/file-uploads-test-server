<?php

// Set error reporting.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition');

// Configure upload settings.
$uploads_dir = __DIR__ . '/uploads';
$max_file_size = 100 * 1024 * 1024; // 100MB max file size.

// Create uploads directory if it doesn't exist.
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Handle file uploads.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();

    if (!empty($_FILES)) {
        foreach ($_FILES as $file) {
            if (is_array($file['name'])) {
                // Handle multiple file uploads.
                for ($i = 0; $i < count($file['name']); $i++) {
                    $result = handleRegularUpload([
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i]
                    ]);
                    $response[] = $result;
                }
            } else {
                // Handle single file upload.
                $response[] = handleRegularUpload($file);
            }
        }
    } else {
        echo "No file uploaded.";
    }
} else {
    // Return 405 Method Not Allowed for non-POST requests.
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

function handleRegularUpload($file) {
    global $uploads_dir, $max_file_size;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array(
            'success' => false,
            'error' => 'Upload error: ' . $file['error']
        );
    }

    if ($file['size'] > $max_file_size) {
        return array(
            'success' => false,
            'error' => 'File too large'
        );
    }

    $filename = sanitizeFilename($file['name']);
    $destination = $uploads_dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return array(
            'success' => true,
            'filename' => $filename,
            'size' => $file['size']
        );
    }

    return array(
        'success' => false,
        'error' => 'Failed to move uploaded file'
    );
}

function sanitizeFilename($filename) {
    // Remove any path components.
    $filename = basename($filename);

    // Replace any non-alphanumeric characters except dots and dashes.
    $filename = preg_replace('/[^a-zA-Z0-9\-\.]/', '_', $filename);

    // Ensure unique filename.
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $counter = 1;
    
    while (file_exists($GLOBALS['uploads_dir'] . '/' . $filename)) {
        $filename = $base . '_' . $counter . '.' . $ext;
        $counter++;
    }
    
    return $filename;
}

?>
