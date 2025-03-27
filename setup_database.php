<?php
// استيراد ملف الاتصال
require_once 'db_config.php';

try {
    // إنشاء جدول المستخدمين
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        username VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        membership VARCHAR(20) DEFAULT 'regular',
        ban_status VARCHAR(20) DEFAULT 'none',
        ban_reason TEXT,
        ban_until DATETIME NULL,
        created_at DATETIME NOT NULL,
        last_login DATETIME NULL
    )");
    
    // التحقق مما إذا كان المدير موجود بالفعل
    $stmt = $db->prepare("SELECT * FROM users WHERE email = 'admin@example.com' OR username = 'kageno'");
    $stmt->execute();
    
    // إذا لم يكن المدير موجودًا، قم بإنشائه
    if ($stmt->rowCount() == 0) {
        $admin_password = password_hash('1525', PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (id, name, email, username, password, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute(['admin', 'مدير النظام', 'admin@example.com', 'kageno', $admin_password, date('Y-m-d H:i:s')]);
        
        echo "تم إنشاء حساب المدير بنجاح!";
    } else {
        echo "حساب المدير موجود بالفعل.";
    }
    
    echo "<br>تم إعداد قاعدة البيانات بنجاح!";
    
} catch(PDOException $e) {
    die("خطأ في إعداد قاعدة البيانات: " . $e->getMessage());
}
?>