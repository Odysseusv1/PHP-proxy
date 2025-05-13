<?php
// Ensure DOMDocument class is available
use DOMDocument;
use DOMElement;

/**
 * Redirect to index page with error message
 * 
 * @param string $message Error message to display
 * @return void
 */
function redirect_with_error($message) {
    header('Location: index.php?error=' . urlencode($message));
    exit;
}

/**
 * Parse HTTP headers into an array
 * 
 * @param string $headerContent Raw header content
 * @return array Parsed headers
 */
function parse_headers($headerContent) {
    $headers = [];
    $headerLines = explode("\n", $headerContent);
    
    foreach ($headerLines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Skip status line and headers we'll set ourselves
        if (strpos($line, 'HTTP/') === 0) continue;
        if (strpos($line, 'Transfer-Encoding:') === 0) continue;
        if (strpos($line, 'Connection:') === 0) continue;
        if (strpos($line, 'Content-Length:') === 0) continue;
        
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $headers[trim($parts[0])] = trim($parts[1]);
        }
    }
    
    return $headers;
}

/**
 * Process HTML content to rewrite links and resources
 * 
 * @param string $html HTML content to process
 * @param string $baseUrl Base URL of the original content
 * @return string Processed HTML
 */
function process_html($html, $baseUrl) {
    // Parse the base URL
    $parsedUrl = parse_url($baseUrl);
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $basePath = isset($parsedUrl['path']) ? dirname($parsedUrl['path']) : '/';
    if ($basePath !== '/') {
        $basePath .= '/';
    }
    
    // The base domain with scheme
    $baseDomain = $scheme . '://' . $host;
    
    // Don't add base tag as it can interfere with our proxy rewriting
    // Instead we'll handle all URL rewrites ourselves
    
    // Process URLs in various attributes
    $attributesToRewrite = [
        'a' => 'href',
        'img' => 'src',
        'link' => 'href',
        'script' => 'src',
        'form' => 'action',
        'iframe' => 'src',
        'source' => 'src',
        'audio' => 'src',
        'video' => 'src',
        'embed' => 'src',
        'input' => 'src',
        'track' => 'src',
        'area' => 'href',
        'base' => 'href',
        'frame' => 'src',
        'button' => 'formaction',
        'object' => 'data'
    ];
    
    // Load HTML as DOM
    $dom = new DOMDocument();
    
    // Suppress warnings for malformed HTML
    libxml_use_internal_errors(true);
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    
    // Process each element type
    foreach ($attributesToRewrite as $tag => $attr) {
        $elements = $dom->getElementsByTagName($tag);
        
        for ($i = 0; $i < $elements->length; $i++) {
            $element = $elements->item($i);
            
            // Make sure element is a DOMElement
            if (!($element instanceof DOMElement)) {
                continue;
            }
            
            if ($element->hasAttribute($attr)) {
                $url = $element->getAttribute($attr);
                
                // Skip URLs that are already proxied, JavaScript, or anchors
                if (strpos($url, 'proxy.php') === 0 || 
                    strpos($url, 'javascript:') === 0 || 
                    strpos($url, '#') === 0 ||
                    $url === '') {
                    continue;
                }
                
                // Process the URL to make it absolute if it's relative
                if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
                    // Handle different kinds of relative URLs
                    if (strpos($url, '//') === 0) {
                        // Protocol-relative URL
                        $url = $scheme . ':' . $url;
                    } elseif (strpos($url, '/') === 0) {
                        // Absolute path relative to domain root
                        $url = $baseDomain . $url;
                    } else {
                        // Relative path
                        $url = $baseDomain . $basePath . $url;
                    }
                }
                
                // Rewrite URL to go through the proxy
                $newUrl = 'proxy.php?url=' . urlencode($url);
                $element->setAttribute($attr, $newUrl);
            }
        }
    }
    
    // Handle inline styles
    $elementsWithStyle = $dom->getElementsByTagName('*');
    for ($i = 0; $i < $elementsWithStyle->length; $i++) {
        $element = $elementsWithStyle->item($i);
        
        // Make sure element is a DOMElement
        if (!($element instanceof DOMElement)) {
            continue;
        }
        
        if ($element->hasAttribute('style')) {
            $styleAttr = $element->getAttribute('style');
            $processedStyle = process_css($styleAttr, $baseUrl);
            $element->setAttribute('style', $processedStyle);
        }
    }
    
    // Handle style tags
    $styleTags = $dom->getElementsByTagName('style');
    for ($i = 0; $i < $styleTags->length; $i++) {
        $styleTag = $styleTags->item($i);
        
        // Make sure element is a DOMElement
        if (!($styleTag instanceof DOMElement)) {
            continue;
        }
        
        $css = $styleTag->nodeValue;
        $processedCss = process_css($css, $baseUrl);
        $styleTag->nodeValue = $processedCss;
    }
    
    // Convert back to HTML string
    $html = $dom->saveHTML();
    
    // Add a proxy message at the top of the page with improved styling
    $proxyMessage = "
    <div style='position:fixed; top:0; left:0; width:100%; background-color:rgba(37, 99, 235, 0.95); color:white; 
                 text-align:center; padding:12px; z-index:999999; font-family:system-ui,-apple-system,\"Segoe UI\",Roboto,sans-serif;
                 font-size:14px; backdrop-filter:blur(5px); box-shadow:0 2px 10px rgba(0,0,0,0.2);'>
        <div style='display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto; padding:0 20px;'>
            <div>
                <span style='font-weight:bold; margin-right:8px;'>WebVoyager Proxy:</span>
                Viewing <a href='{$baseUrl}' style='color:white; text-decoration:underline; font-weight:600;' target='_blank'>{$baseUrl}</a>
            </div>
            <div>
                <a href='index.php' style='color:white; background-color:rgba(255,255,255,0.2); padding:6px 12px; 
                   border-radius:4px; text-decoration:none; font-weight:500; display:inline-block;'>Back to Proxy</a>
            </div>
        </div>
    </div>
    <div style='height:50px;'></div>
    ";
    
    // Insert after the body tag
    $html = preg_replace('/<body[^>]*>/i', '$0' . $proxyMessage, $html, 1);
    
    return $html;
}

/**
 * Process CSS content to rewrite URLs
 * 
 * @param string $css CSS content to process
 * @param string $baseUrl Base URL of the original content
 * @return string Processed CSS
 */
function process_css($css, $baseUrl) {
    // Parse the base URL
    $parsedUrl = parse_url($baseUrl);
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $basePath = isset($parsedUrl['path']) ? dirname($parsedUrl['path']) : '/';
    if ($basePath !== '/') {
        $basePath .= '/';
    }
    
    // The base domain with scheme
    $baseDomain = $scheme . '://' . $host;
    
    // Regular expression to find URLs in CSS
    $cssUrlPattern = '/url\(\s*[\'"]?([^\'"\)]+)[\'"]?\s*\)/i';
    
    return preg_replace_callback($cssUrlPattern, function($matches) use ($baseDomain, $basePath, $scheme) {
        $url = trim($matches[1]);
        
        // Skip data URIs
        if (strpos($url, 'data:') === 0) {
            return $matches[0];
        }
        
        // Process the URL to make it absolute if it's relative
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            // Handle different kinds of relative URLs
            if (strpos($url, '//') === 0) {
                // Protocol-relative URL
                $url = $scheme . ':' . $url;
            } elseif (strpos($url, '/') === 0) {
                // Absolute path relative to domain root
                $url = $baseDomain . $url;
            } else {
                // Relative path
                $url = $baseDomain . $basePath . $url;
            }
        }
        
        // Rewrite URL to go through the proxy
        return 'url("proxy.php?url=' . urlencode($url) . '")';
    }, $css);
}

/**
 * Process JavaScript content to rewrite URLs
 * 
 * @param string $js JavaScript content to process
 * @param string $baseUrl Base URL of the original content
 * @return string Processed JavaScript
 */
function process_javascript($js, $baseUrl) {
    // Disable JavaScript processing to prevent regex errors
    // Simply return the original JavaScript as processing it safely is complex
    return $js;
    
    // The JavaScript processing below is commented out due to regex issues
    /*
    // Parse the base URL
    $parsedUrl = parse_url($baseUrl);
    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $basePath = isset($parsedUrl['path']) ? dirname($parsedUrl['path']) : '/';
    if ($basePath !== '/') {
        $basePath .= '/';
    }
    
    // The base domain with scheme
    $baseDomain = $scheme . '://' . $host;
    
    // Pattern for URL strings in JavaScript
    $patterns = [
        // Match URLs in single quotes
        '/([\'"])(https?:\/\/[^\'"]+)([\'"])/i',
        // Match URLs in double quotes
        '/(["|\'])(\/\/[^"\']+)(["|\'])/i',
        // Match absolute paths
        '/(["|\'])(\/[^"\']*)(["|\'])/i',
        // Match URLs in fetch, XMLHttpRequest, and other common patterns
        '/(fetch|src|href|url|ajax|load)\s*\(\s*([\'"])(https?:\/\/[^\'"]+|\/[^\'"]+)([\'"])/i'
    ];
    
    $processedJs = $js;
    
    // Process each pattern
    foreach ($patterns as $pattern) {
        $processedJs = preg_replace_callback($pattern, function($matches) use ($baseDomain, $basePath, $scheme) {
            // Full match is in $matches[0]
            
            // Handle different patterns differently
            if (count($matches) >= 4) {
                $quote = $matches[1]; // The quote character used
                $url = $matches[2]; // The URL part
                $endQuote = $matches[3]; // The ending quote
                
                // Skip if this isn't a URL we should process
                if (strpos($url, 'javascript:') === 0 || 
                    strpos($url, '#') === 0 ||
                    strpos($url, 'data:') === 0 ||
                    $url === '') {
                    return $matches[0];
                }
                
                // If it's a protocol-relative URL (starts with //)
                if (strpos($url, '//') === 0) {
                    $url = $scheme . ':' . $url;
                }
                // If it's an absolute path
                elseif (strpos($url, '/') === 0) {
                    $url = $baseDomain . $url;
                }
                // For full URLs, leave them as is
                elseif (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
                    // Relative path
                    $url = $baseDomain . $basePath . $url;
                }
                
                // For fetch/ajax/src patterns
                if (count($matches) >= 5) {
                    $func = $matches[1]; // The function/attribute name
                    $quote = $matches[2]; // The quote character
                    $url = $matches[3]; // The URL
                    $endQuote = $matches[4]; // End quote
                    
                    // Rewrite it
                    return $func . '(' . $quote . 'proxy.php?url=' . urlencode($url) . $endQuote;
                }
                
                // Rewrite the URL to go through the proxy
                return $quote . 'proxy.php?url=' . urlencode($url) . $endQuote;
            }
            
            // If the pattern doesn't match our expected format, return original
            return $matches[0];
        }, $processedJs);
    }
    
    return $processedJs;
    */
}
