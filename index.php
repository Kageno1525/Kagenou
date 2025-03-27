<?php
// Include the users file for handling user data
require_once 'users.php';

// Initialize variables for form values and messages
$name = '';
$email = '';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        $message = "جميع الحقول مطلوبة";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "صيغة البريد الإلكتروني غير صحيحة";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "كلمة المرور يجب أن تكون على الأقل 6 أحرف";
        $messageType = "error";
    } elseif (emailExists($email)) {
        $message = "البريد الإلكتروني مسجل بالفعل";
        $messageType = "error";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Register the user
        registerUser($name, $email, $hashed_password);
        
        $message = "تم التسجيل بنجاح! سيتم تحويلك إلى صفحة تسجيل الدخول...";
        $messageType = "success";
        
        // Reset form data after successful registration
        $name = '';
        $email = '';
        
        // Redirect to login page after 2 seconds
        echo "<script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 2000);
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب جديد</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="particles-js" class="background-particles"></div>
    <div class="container">
        <h2>تسجيل حساب جديد</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="name">الاسم:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            
            <label for="password">كلمة المرور:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="تسجيل">
        </form>
        
        <div class="login-options">
            <div class="login-link">لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></div>
        </div>
    </div>

    <!-- إضافة سكربت الجزيئات المتقدم للخلفية -->
    <script src="js/particles.js"></script>
    <script src="js/advanced_particles.js"></script>
</body>
</html>
