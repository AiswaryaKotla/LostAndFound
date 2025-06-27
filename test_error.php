<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Hello! If you see this, PHP is working and errors can show.";

// Now force an error to test:
echo $undefined_variable;
