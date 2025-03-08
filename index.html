<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JavaScript Web Proxy</title>
</head>
<body>
    <h1>JavaScript Proxy</h1>
    <form id="proxyForm">
        <label for="url">Enter URL to Proxy:</label>
        <input type="text" id="url" name="url" placeholder="https://example.com" required>
        <button type="submit">Proxy</button>
    </form>

    <div id="result"></div>

    <script>
        document.getElementById('proxyForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const url = document.getElementById('url').value;
            const resultDiv = document.getElementById('result');

            // Show loading message
            resultDiv.innerHTML = "Loading...";

            try {
                // Fetch the proxied content from the Node.js server
                const response = await fetch(`http://localhost:3000/proxy?url=${encodeURIComponent(url)}`);

                if (response.ok) {
                    // Display the fetched content
                    const content = await response.text();
                    resultDiv.innerHTML = `<pre>${content}</pre>`;
                } else {
                    resultDiv.innerHTML = "Error: Could not fetch the URL.";
                }
            } catch (error) {
                resultDiv.innerHTML = "Error: Failed to fetch the URL.";
            }
        });
    </script>
</body>
</html>
