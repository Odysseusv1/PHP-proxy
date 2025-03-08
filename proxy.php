<?php
// Simple Web Proxy in PHP

// Get the URL passed via the GET request
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Validate the URL
if ($url) {
    // Remove any unwanted characters from the URL
    $url = filter_var($url, FILTER_SANITIZE_URL);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
    curl_setopt($ch, CURLOPT_HEADER, false);  // Don't include the header in the output

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }

    // Close the cURL session
    curl_close($ch);

    // Output the fetched content
    echo $response;
} else {
    echo "Please provide a valid URL!";
}
?>
