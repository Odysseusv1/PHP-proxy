<?php
// Include functions file
require_once __DIR__ . '/functions.php';

// Check if URL is provided
if (!isset($_GET['url']) || empty($_GET['url'])) {
    redirect_with_error('Please provide a URL to proxy.');
}

$url = $_GET['url'];

// Basic URL validation
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    redirect_with_error('Invalid URL format. Please enter a valid URL.');
}

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for simplicity
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_ENCODING, ''); // Handle compressed responses
curl_setopt($ch, CURLOPT_AUTOREFERER, true); // Set referer on redirect
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Add cookies support
curl_setopt($ch, CURLOPT_COOKIEFILE, ""); // Use in-memory cookie jar
curl_setopt($ch, CURLOPT_COOKIEJAR, "");  // Save cookies

// Pass the request method, POST data, etc. if this is coming from a form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Pass POST data if available
    if (!empty($_POST)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
    }
}

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    redirect_with_error("Error fetching URL: " . $error);
}

// Get response info
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

// Close cURL session
curl_close($ch);

// Split header and body
$header = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

// Check if request was successful
if ($httpCode >= 400) {
    redirect_with_error("The requested URL returned error: $httpCode");
}

// Extract specific headers we want to pass through
$headersArray = parse_headers($header);

// Handle content type appropriately
if (isset($contentType)) {
    // For HTML content, process links
    if (strpos($contentType, 'text/html') !== false) {
        // Process HTML content
        $body = process_html($body, $url);
        
        // Output the processed HTML
        header('Content-Type: text/html; charset=UTF-8');
        echo $body;
    } 
    // For CSS, process URLs
    elseif (strpos($contentType, 'text/css') !== false) {
        $body = process_css($body, $url);
        header('Content-Type: text/css');
        echo $body;
    }
    // For JavaScript
    elseif (strpos($contentType, 'javascript') !== false || 
            strpos($contentType, 'application/js') !== false || 
            strpos($contentType, 'application/javascript') !== false || 
            strpos($contentType, 'text/javascript') !== false) {
        // Process JavaScript to rewrite URLs if the function exists
        if (function_exists('process_javascript')) {
            $body = process_javascript($body, $url);
        }
        header('Content-Type: ' . $contentType);
        echo $body;
    }
    // For images, PDFs, and other binary content
    elseif (strpos($contentType, 'image/') !== false || 
            strpos($contentType, 'application/pdf') !== false ||
            strpos($contentType, 'font/') !== false ||
            strpos($contentType, 'audio/') !== false ||
            strpos($contentType, 'video/') !== false ||
            strpos($contentType, 'application/octet-stream') !== false ||
            strpos($contentType, 'application/x-font') !== false ||
            strpos($contentType, 'application/vnd.ms-fontobject') !== false ||
            strpos($contentType, 'application/font-woff') !== false ||
            strpos($contentType, 'application/font-woff2') !== false ||
            strpos($contentType, 'application/x-font-ttf') !== false) {
        // Pass through the content type
        header('Content-Type: ' . $contentType);
        echo $body;
    }
    // For all other content types
    else {
        header('Content-Type: ' . $contentType);
        echo $body;
    }
} else {
    // Default content type if none is specified
    header('Content-Type: text/plain');
    echo $body;
}