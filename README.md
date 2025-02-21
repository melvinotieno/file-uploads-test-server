# PHP File Upload Test Server

A versatile PHP-based file upload server that supports multiple upload methods including regular form uploads, resumable chunked uploads, and raw binary uploads.

<b>NOTE: This is meant to be used for testing and development purposes only. It is not intended for production use.</b>

## Features

- Multiple file upload methods supported:
  - Regular multipart/form-data uploads
  - Resumable chunked uploads
  - Raw binary uploads
- Support for single and multiple file uploads
- Configurable upload limits
- Filename sanitization
- Automatic file deduplication

## Setup

1. Ensure PHP is installed on your server.
2. Create an `uploads` directory in the same directory as the script.
3. Set appropriate permissions on the uploads directory:

```bash
chmod -R 777 uploads
```

4. Configure your PHP settings to allow for your desired maximum file size.

Otherwise, you can use `docker` with `docker compose` to run the server:

```bash
docker compose up -d
```

## Configuration

The server has several configurable parameters:

```php
$uploads_dir = __DIR__ . '/uploads'; // Directory to store uploaded files.
$chunk_size = 1024 * 1024; // 1MB chunks for resumable uploads.
$max_file_size = 100 * 1024 * 1024; // 100MB max file size.
```

## API Endpoints

### POST /

Handles all file upload requests. The behavior changes based on the request headers and content:

#### Regular File Upload

- Method: `POST`
- Content-Type: `multipart/form-data`
- Body: Form data containing the file(s) to upload

#### Resumable Upload

- Method: `POST`
- Headers:
  - `Content-Range: bytes <start>-<end>/<total>`
  - `X-File-Name: <filename>` (optional)
- Body: File chunk data

#### Raw Binary Upload

- Method: `POST`
- Headers:
  - `X-File-Name: <filename>`
- Body: Raw binary file data

### Response Format

All responses are in JSON format:

```json
{
  "success": true|false,
  "filename": "filename.ext",
  "size": 123456, // Not present for resumable uploads
  "error": "Error message", // Only present if success is false
  "bytesReceived": 123456, // Only present for resumable uploads
  "complete": true|false // Only present for resumable uploads
}
```

### Example usage with `curl`

#### Regular File Upload

```bash
curl -F "file=@/path/to/file.ext" http://localhost:8080/
```

#### Multiple File Upload

```bash
curl -F "files[]=@/path/to/file1.ext" -F "files[]=@/path/to/file2.ext" http://localhost:8080/
```

#### Raw Binary Upload

```bash
curl -X POST -H "X-File-Name: binary.ext" --data-binary "@/path/to/file.ext" http://localhost:8080/
```

#### Resumable Upload

```bash
curl -X POST -H "X-File-Name: file.ext" -H "Content-Range: bytes 0-1023/1024" --data-binary "@/path/to/file.ext" http://localhost:8080/
```
