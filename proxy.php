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
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    curl_close($ch);

    // Strip or modify security-related headers
    $header_lines = explode("\r\n", $headers);
    foreach ($header_lines as $header) {
        // Remove or skip over headers that might prevent embedding
        if (stripos($header, 'X-Frame-Options') === false && stripos($header, 'Content-Security-Policy') === false) {
            header($header);
        }
    }

    return $body;
}

// Fetch and display the content
$content = fetch_content($url);
echo $content;

?>
