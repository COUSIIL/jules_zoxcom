<?php
if (isset($_GET['url'])) {
    // 🔥 Correction : Encoder correctement l'URL
    $url = rawurldecode($_GET['url']);
    $url = str_replace(' ', '%20', $url);


    // ✅ Vérifie si l'URL est valide
    $headers = @get_headers($url, 1);

    if ($headers && strpos($headers[0], '200')) {
        $content = file_get_contents($url);
        if ($content !== false) {
            // ✅ Forcer le type MIME et désactiver le cache
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . strlen($content));
            header('Content-Disposition: inline; filename="' . basename($url) . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // ✅ Envoyer le fichier binaire brut
            echo $content;
            exit();
        } else {
            http_response_code(500);
            echo 'Failed to read file';
            exit();
        }
    } else {
        http_response_code(404);
        echo 'File not found';
        exit();
    }
} else {
    http_response_code(400);
    echo 'Missing URL';
    exit();
}
