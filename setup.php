<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - WMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2563eb; }
        .success { 
            background: #d1fae5; 
            color: #065f46; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 20px 0;
        }
        .error { 
            background: #fee2e2; 
            color: #991b1b; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 20px 0;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #1e40af;
        }
        ul { line-height: 2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏭 WMS Database Setup</h1>
        
        <?php
        // Include database configuration
        require_once 'config/database.php';
        
        $messages = [];
        $setupComplete = false;
        
        if (isset($_POST['setup'])) {
            try {
                // Create database
                $conn = initializeDatabase();
                
                // Read and execute schema
                $schema = file_get_contents('database/schema.sql');
                
                // Split into individual queries
                $queries = array_filter(
                    array_map('trim', explode(';', $schema)),
                    function($q) { return !empty($q); }
                );
                
                $successCount = 0;
                foreach ($queries as $query) {
                    if (!empty(trim($query))) {
                        if ($conn->query($query)) {
                            $successCount++;
                        } else {
                            $messages[] = ['type' => 'error', 'text' => 'Query failed: ' . $conn->error];
                        }
                    }
                }
                
                $messages[] = ['type' => 'success', 'text' => "Database setup completed! Executed $successCount queries successfully."];
                $setupComplete = true;
                
                $conn->close();
            } catch (Exception $e) {
                $messages[] = ['type' => 'error', 'text' => 'Setup failed: ' . $e->getMessage()];
            }
        }
        
        // Display messages
        foreach ($messages as $msg) {
            echo "<div class='{$msg['type']}'>{$msg['text']}</div>";
        }
        ?>
        
        <?php if (!$setupComplete): ?>
            <div class="info">
                <strong>ℹ️ Before you begin:</strong>
                <ul>
                    <li>Ensure XAMPP Apache and MySQL services are running</li>
                    <li>This will create the <code>wms_db</code> database</li>
                    <li>All necessary tables will be created automatically</li>
                    <li>Sample data will be inserted (admin user, bins, products)</li>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" name="setup" class="btn">
                    🚀 Setup Database Now
                </button>
            </form>
        <?php else: ?>
            <div class="success">
                <h3>✅ Setup Complete!</h3>
                <p>Your database is ready. You can now use the system.</p>
                <br>
                <strong>Default Login Credentials:</strong>
                <pre>Username: admin
Password: admin123</pre>
                <br>
                <a href="login.php" class="btn">Go to Login Page</a>
            </div>
            
            <div class="info">
                <h3>📊 What was created:</h3>
                <ul>
                    <li>✓ Database: wms_db</li>
                    <li>✓ Users table with admin account</li>
                    <li>✓ Products table with 3 sample products</li>
                    <li>✓ Bins table with 6 sample locations</li>
                    <li>✓ Invoices, Shipments, Inventory tables</li>
                    <li>✓ Activity logging system</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
            <strong>Need help?</strong> Check <a href="INSTALL.md">INSTALL.md</a> or <a href="README.md">README.md</a> for detailed instructions.
        </div>
    </div>
</body>
</html>
