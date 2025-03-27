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
$banInfo = null;

// معالجة تسجيل الخروج
if (isset($_GET['logout'])) {
    logoutUser();
}

// التحقق إذا كان هناك رسالة حظر
if (isset($_SESSION['ban_message'])) {
    $banInfo = $_SESSION['ban_message'];
    
    // حذف رسالة الحظر من الجلسة بعد عرضها
    unset($_SESSION['ban_message']);
}

// معالجة تسجيل الدخول
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // الحصول على البيانات
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // التحقق إذا كان مستخدم
    if (!empty($email) && !empty($password)) {
        // محاولة تسجيل الدخول (تم تحديث الدالة لتتعامل مع المستخدم العادي والمدير)
        if (authenticateUser($email, $password)) {
            // تم تسجيل الدخول بنجاح، الانتقال إلى لوحة التحكم
            header("Location: dashboard.php");
            exit;
        } else {
            // التحقق مما إذا كان المستخدم محظوراً
            if (isset($_SESSION['ban_message'])) {
                $banInfo = $_SESSION['ban_message'];
                unset($_SESSION['ban_message']);
            } else {
                $message = "اسم المستخدم أو كلمة المرور غير صحيحة";
                $messageType = "error";
            }
        }
    } else {
        $message = "يرجى إدخال اسم المستخدم وكلمة المرور";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="particles-js" class="background-particles"></div>
    <div class="container">
        <h2>تسجيل الدخول</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($banInfo): ?>
            <div class="message error ban-message">
                <h3>حسابك محظور</h3>
                <p><strong>سبب الحظر:</strong> <?php echo htmlspecialchars($banInfo['reason']); ?></p>
                
                <?php if ($banInfo['status'] === BAN_STATUS_TEMPORARY && !empty($banInfo['until'])): ?>
                    <p><strong>مدة الحظر:</strong> حتى <?php echo htmlspecialchars($banInfo['until']); ?></p>
                <?php elseif ($banInfo['status'] === BAN_STATUS_PERMANENT): ?>
                    <p><strong>نوع الحظر:</strong> حظر دائم</p>
                <?php endif; ?>
                
                <p>إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع إدارة الموقع.</p>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="email">البريد الإلكتروني أو اسم المستخدم:</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            
            <label for="password">كلمة المرور:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="دخول">
        </form>
        
        <div class="login-options">
            <a href="forgot_password.php" class="forgot-password">هل نسيت كلمة السر؟</a>
            <div class="register-link">ليس لديك حساب؟ <a href="index.php">سجل الآن</a></div>
        </div>
    </div>

    <!-- إضافة سكربت الجزيئات المتقدم للخلفية -->
    <script src="js/particles.js"></script>
    <script src="js/advanced_particles.js"></script>
</body>
</html>