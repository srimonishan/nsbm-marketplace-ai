<?php
/**
 * GreenLink Market - Installation Script
 * 
 * Run this script once to set up the database.
 * DELETE THIS FILE AFTER INSTALLATION!
 */

// Prevent access if already installed
$lockFile = __DIR__ . '/.installed';

echo "<!DOCTYPE html><html><head><title>GreenLink Market - Installation</title>";
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<style>body{background:#0a0a1a;color:#e0e0e0;font-family:Inter,sans-serif;padding:2rem;} .card{background:rgba(16, 37, 27,0.9);border:1px solid rgba(255,255,255,0.1);border-radius:12px;} .text-success{color:#b6e34a!important;} .text-danger{color:#f2b84b!important;} .text-warning{color:#ffc107!important;}</style>';
echo "</head><body><div class='container' style='max-width:700px;'>";
echo "<div class='card p-4'>";
echo "<h2 class='text-center mb-4' style='background:linear-gradient(135deg,#087a4b,#b6e34a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;'>GreenLink Market - Installer</h2>";

if (file_exists($lockFile)) {
    echo "<div class='alert alert-warning'><strong>Already Installed!</strong> Delete this file (install.php) for security.</div>";
    echo "</div></div></body></html>";
    exit;
}

$step = $_GET['step'] ?? 'check';

if ($step === 'check') {
    echo "<h5>System Requirements Check</h5><hr>";
    
    $checks = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'cURL Extension' => extension_loaded('curl'),
        'JSON Extension' => extension_loaded('json'),
        'Session Support' => extension_loaded('session'),
        'Config Writable' => is_writable(__DIR__ . '/config'),
    ];
    
    $allPassed = true;
    foreach ($checks as $name => $passed) {
        $icon = $passed ? '✅' : '❌';
        $class = $passed ? 'text-success' : 'text-danger';
        echo "<p class='{$class}'>{$icon} {$name}</p>";
        if (!$passed) $allPassed = false;
    }
    
    if ($allPassed) {
        echo "<hr><form method='POST' action='install.php?step=install'>";
        echo "<h5>Database Configuration</h5>";
        echo "<div class='mb-3'><label class='form-label'>MySQL Host</label><input type='text' name='db_host' class='form-control bg-dark text-light border-secondary' value='localhost' required></div>";
        echo "<div class='mb-3'><label class='form-label'>Database Name</label><input type='text' name='db_name' class='form-control bg-dark text-light border-secondary' value='nsbm_marketplace' required></div>";
        echo "<div class='mb-3'><label class='form-label'>MySQL Username</label><input type='text' name='db_user' class='form-control bg-dark text-light border-secondary' value='root' required></div>";
        echo "<div class='mb-3'><label class='form-label'>MySQL Password</label><input type='password' name='db_pass' class='form-control bg-dark text-light border-secondary'></div>";
        echo "<div class='mb-3'><label class='form-label'>Gemini API Key (optional)</label><input type='text' name='gemini_key' class='form-control bg-dark text-light border-secondary' placeholder='Get from Google AI Studio'></div>";
        echo "<button type='submit' class='btn btn-primary w-100'>Install Database</button>";
        echo "</form>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Please fix the requirements above before installing.</div>";
    }
}

if ($step === 'install' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? 'nsbm_marketplace';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    $geminiKey = $_POST['gemini_key'] ?? '';
    
    echo "<h5>Installation Progress</h5><hr>";
    
    try {
        // Connect to MySQL (without database first)
        $pdo = new PDO("mysql:host={$dbHost}", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "<p class='text-success'>✅ Connected to MySQL server</p>";
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p class='text-success'>✅ Database '{$dbName}' created</p>";
        
        // Use database
        $pdo->exec("USE `{$dbName}`");
        
        // Run SQL schema
        $sqlFile = __DIR__ . '/sql/database.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
            echo "<p class='text-success'>✅ Database tables created</p>";
            echo "<p class='text-success'>✅ Sample data inserted</p>";
        } else {
            echo "<p class='text-danger'>❌ SQL file not found</p>";
        }
        
        // Update config file
        $configContent = "<?php\n/**\n * Database Configuration - GreenLink Market\n * Auto-generated by installer\n */\n\n";
        $configContent .= "define('DB_HOST', '{$dbHost}');\n";
        $configContent .= "define('DB_NAME', '{$dbName}');\n";
        $configContent .= "define('DB_USER', '{$dbUser}');\n";
        $configContent .= "define('DB_PASS', '{$dbPass}');\n";
        $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
        $configContent .= "class Database {\n";
        $configContent .= "    private static \$instance = null;\n";
        $configContent .= "    private \$connection;\n\n";
        $configContent .= "    private function __construct() {\n";
        $configContent .= "        try {\n";
        $configContent .= "            \$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;\n";
        $configContent .= "            \$this->connection = new PDO(\$dsn, DB_USER, DB_PASS, [\n";
        $configContent .= "                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n";
        $configContent .= "                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n";
        $configContent .= "                PDO::ATTR_EMULATE_PREPARES => false,\n";
        $configContent .= "            ]);\n";
        $configContent .= "        } catch (PDOException \$e) {\n";
        $configContent .= "            error_log('Database connection failed: ' . \$e->getMessage());\n";
        $configContent .= "            throw new Exception('Database connection failed');\n";
        $configContent .= "        }\n";
        $configContent .= "    }\n\n";
        $configContent .= "    public static function getInstance() {\n";
        $configContent .= "        if (self::\$instance === null) {\n";
        $configContent .= "            self::\$instance = new self();\n";
        $configContent .= "        }\n";
        $configContent .= "        return self::\$instance;\n";
        $configContent .= "    }\n\n";
        $configContent .= "    public function getConnection() {\n";
        $configContent .= "        return \$this->connection;\n";
        $configContent .= "    }\n";
        $configContent .= "}\n";
        
        file_put_contents(__DIR__ . '/config/database.php', $configContent);
        echo "<p class='text-success'>✅ Database config updated</p>";
        
        // Update Gemini key in app.php if provided
        if (!empty($geminiKey)) {
            $appConfig = file_get_contents(__DIR__ . '/config/app.php');
            $appConfig = str_replace("'your-gemini-api-key-here'", "'{$geminiKey}'", $appConfig);
            file_put_contents(__DIR__ . '/config/app.php', $appConfig);
            echo "<p class='text-success'>✅ Gemini API key configured</p>";
        } else {
            echo "<p class='text-warning'>⚠️ Gemini API key not set (AI features will use fallback mode)</p>";
        }
        
        // Create lock file
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        echo "<hr>";
        echo "<div class='alert alert-success'>";
        echo "<h5>🎉 Installation Complete!</h5>";
        echo "<p>GreenLink Market has been installed successfully.</p>";
        echo "<p><strong>Admin Login:</strong><br>Email: admin@nsbm.ac.lk<br>Password: admin123</p>";
        echo "<p><strong>Customer Login:</strong><br>Email: student1@students.nsbm.ac.lk<br>Password: student123</p>";
        echo "</div>";
        echo "<div class='alert alert-danger'>⚠️ <strong>IMPORTANT:</strong> Delete install.php after installation!</div>";
        echo "<a href='index.php' class='btn btn-primary w-100 mb-2'>Go to Store</a>";
        echo "<a href='admin/index.php' class='btn btn-outline-light w-100'>Go to Admin Panel</a>";
        
    } catch (Exception $e) {
        echo "<p class='text-danger'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<a href='install.php' class='btn btn-warning mt-3'>Try Again</a>";
    }
}

echo "</div></div></body></html>";
