<?php
// معلومات الاتصال بقاعدة البيانات
$host = 'localhost';      // عنوان السيرفر
$dbname = 'registration'; // اسم قاعدة البيانات
$username = 'root';       // اسم المستخدم
$password = '';           // كلمة المرور

try {
    // إنشاء اتصال PDO
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // تعيين وضع الخطأ إلى استثناءات
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // في حالة وجود خطأ
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>