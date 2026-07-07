<?php
// Simple test download script
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="test_download.html"');

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Download</title></head>";
echo "<body><h1>Test Download Working!</h1>";
echo "<p>Generated at: " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
exit();
?>
