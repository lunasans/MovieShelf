<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Debug Mode</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Dir: " . __DIR__ . "<br><br>";

// Teste bootstrap.php
echo "Loading bootstrap.php...<br>";
try {
    require_once __DIR__ . '/includes/bootstrap.php';
    echo "✅ bootstrap.php loaded<br>";
} catch (Throwable $e) {
    echo "❌ ERROR in bootstrap.php:<br>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Teste functions.php
echo "Checking functions...<br>";
if (function_exists('findCoverImage')) {
    echo "✅ findCoverImage exists<br>";
} else {
    echo "❌ findCoverImage NOT found<br>";
}

if (function_exists('getActorsByDvdId')) {
    echo "✅ getActorsByDvdId exists<br>";
} else {
    echo "❌ getActorsByDvdId NOT found<br>";
}

echo "<br>If you see this, bootstrap works!<br>";
echo "<a href='index.php'>Try index.php now</a>";
?>