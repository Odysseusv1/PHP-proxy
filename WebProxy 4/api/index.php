<?php
// Include functions file
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebVoyager Proxy</title>
    <link rel="stylesheet" href="/styles.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌐</text></svg>">
</head>
<body>
    <div class="hero">
        <div class="hero-content">
            <h1>WebVoyager Proxy</h1>
            <p class="tagline">Access any website anonymously through our secure proxy</p>
            
            <form action="/proxy" method="GET" id="proxy-form" class="search-box">
                <input type="url" name="url" id="url" placeholder="Enter any website URL (e.g., https://example.com)" 
                       required class="search-input" 
                       value="<?php echo isset($_GET['url']) ? htmlspecialchars($_GET['url']) : ''; ?>">
                <button type="submit" class="search-button">
                    <span class="button-text">Browse</span>
                    <span class="button-icon">→</span>
                </button>
            </form>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="recently-visited">
                <h3>Quick Access</h3>
                <div class="site-buttons">
                    <a href="/proxy?url=https://www.google.com" class="site-button">Google</a>
                    <a href="/proxy?url=https://www.wikipedia.org" class="site-button">Wikipedia</a>
                    <a href="/proxy?url=https://www.reddit.com" class="site-button">Reddit</a>
                    <a href="/proxy?url=https://news.ycombinator.com" class="site-button">Hacker News</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="features">
        <div class="container">
            <h2>Why Use WebVoyager?</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Anonymous Browsing</h3>
                    <p>Browse websites without revealing your IP address or location information.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🌍</div>
                    <h3>Access Anywhere</h3>
                    <p>Bypass geographical restrictions and access content from anywhere in the world.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Fast Performance</h3>
                    <p>Our optimized proxy ensures quick loading times for all your browsing needs.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Enhanced Security</h3>
                    <p>Additional layer of protection between you and the websites you visit.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Enter a URL</h3>
                    <p>Type or paste any website address into the search box above.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>We Fetch the Content</h3>
                    <p>Our server retrieves the website content securely on your behalf.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Browse Anonymously</h3>
                    <p>View and interact with the website through our proxy connection.</p>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p class="warning">Note: This proxy is for educational purposes. Be mindful of the websites you access through it.</p>
            <p class="copyright">WebVoyager Proxy &copy; 2025</p>
        </div>
    </footer>
    
    <script>
        // URL validation and formatting
        document.getElementById('proxy-form').addEventListener('submit', function(e) {
            const urlInput = document.getElementById('url');
            let url = urlInput.value.trim();
            
            // If no protocol specified, add https://
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                e.preventDefault();
                urlInput.value = 'https://' + url;
                this.submit();
            }
        });
    </script>
</body>
</html>