<?php
echo json_encode([
    'status' => 'success',
    'message' => 'PHP is working correctly!',
    'php_version' => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>