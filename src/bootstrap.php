<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

set_exception_handler( function (\Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'succes' => false,
        'message' => "ERREUR INTERNE SUR LE SERVEUR"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
});
?>