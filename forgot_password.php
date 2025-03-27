<?php
// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استيراد ملف المستخدمين
require_once 'users.php';

// تهيئة المتغيرات
$email = '';
$message = '';
$messageType = '';

// معالجة النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    
    if (empty($email)) {
        $message = "يرجى إدخال البريد الإلكتروني";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "صيغة البريد الإلكتروني غير صحيحة";
        $messageType = "error";
    } else {
        // التحقق من وجود البريد الإلكتروني
        if (emailExists($email)) {
            // في نظام حقيقي، سيتم هنا إرسال رابط إعادة تعيين كلمة المرور إلى البريد الإلكتروني
            // لكن هنا سنكتفي برسالة تأكيد
            $message = "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني";
            $messageType = "success";
            $email = ''; // تفريغ الحقل بعد النجاح
        } else {
            $message = "هذا البريد الإلكتروني غير مسجل في نظامنا";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="particles-js" class="background-particles"></div>
    <div class="container">
        <h2>استعادة كلمة المرور</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <p class="form-description">
            أدخل بريدك الإلكتروني المسجل لدينا، وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
        </p>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            
            <input type="submit" value="إرسال رابط الاستعادة">
        </form>
        
        <div class="login-options">
            <div class="back-to-login">
                <a href="login.php">العودة إلى تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <!-- إضافة سكربت الجزيئات المتقدم للخلفية -->
    <script src="js/particles.js"></script>
    <script src="js/advanced_particles.js"></script>
</body>
</html>