<?php
require_once __DIR__ . '/includes/bootstrap.php';
echo "Environment: " . getSetting('environment', 'production') . "\n";
echo "IS_DEV logic: " . (getSetting('environment', 'production') === 'development' ? 'true' : 'false') . "\n";
?>
