let history = [];
let currentIndex = -1;

document.getElementById('loadBtn').addEventListener('click', function() {
    const urlInput = document.getElementById('url').value;
    const url = formatUrl(urlInput);
    loadPage(url);
});

document.getElementById('backBtn').addEventListener('click', function() {
    if (currentIndex > 0) {
        currentIndex--;
        loadPage(history[currentIndex]);
    }
});

document.getElementById('forwardBtn').addEventListener('click', function() {
    if (currentIndex < history.length - 1) {
        currentIndex++;
        loadPage(history[currentIndex]);
    }
});

function loadPage(url) {
    const iframe = document.getElementById('webFrame');
    iframe.src = `proxy.php?url=${encodeURIComponent(url)}`;

    // Update history
    if (currentIndex === -1 || history[currentIndex] !== url) {
        history.push(url);
        currentIndex++;
    }
}

function formatUrl(url) {
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'http://' + url;
    }
    return url;
}
