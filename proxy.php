
<?php

// Sanitize and validate URL
$url = filter_var($_GET['url'], FILTER_VALIDATE_URL);
if (!$url) {
    echo "Invalid URL.";
    exit;
}

/**
 * Fetches content from a specified URL using cURL.
 *
 * @param string $url The URL to fetch content from.
 * @return string The fetched content with headers removed or modified.
 */
function fetch_content($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL certificates
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    curl_close($ch);

    // Strip or modify security-related headers
    $header_lines = explode("\r\n", $headers);
    foreach ($header_lines as $header) {
        // Remove or skip over headers that might prevent embedding
        if (stripos($header, 'X-Frame-Options') === false && stripos($header, 'Content-Security-Policy') === false && !empty($header)) {
            header($header);
        }
    }

    return $body;
}

// Start output buffering
ob_start();

// Fetch and display the content
$content = fetch_content($url);

// Get the content type from the fetched content
$content_type = 'text/html'; // Default content type
if (preg_match('/<meta[^>]+content=["\']?([^"\'>]+)["\']?[^>]*>/i', $content, $matches)) {
    $content_type = $matches[1];
}
header("Content-Type: $content_type");

// Output the content
echo $content;

// Flush the output buffer
ob_end_flush();

?>
