<?php
// الجزء الخاص بـ PHP لمعالجة البيانات
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // هنا هنفترض إنك عندك قاعدة بيانات MySQL
    // هنعمل كونكشن لقاعدة البيانات (غير ال host, user, password حسب إعداداتك)
    $conn = new mysqli("localhost", "root", "", "mywebsite");

    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    // تشفير كلمة المرور للأمان
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // إضافة البيانات لجدول users
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('تم التسجيل بنجاح!');</script>";
    } else {
        echo "<script>alert('حصل خطأ: " . $conn->error . "');</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب جديد</title>
    <style>
        /* التصميم العام */
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* خلفية متحركة (جزيئات صغيرة تتلألأ) */
        .background-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #121212, #1e1e1e);
            z-index: -1;
        }

        .container {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1s ease-in-out;
            position: relative;
            z-index: 1;
        }

        /* أنيميشن النموذج */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        h2 {
            text-align: center;
            color: #bb86fc;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            color: #e0e0e0;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #6200ea;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s, background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #3700b3;
            transform: scale(1.05);
        }

        input[type="submit"]:active {
            transform: scale(0.95);
        }

        /* تصميم متجاوب */
        @media (max-width: 480px) {
            .container {
                padding: 20px;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="background-particles"></div>
    <div class="container">
        <h2>تسجيل حساب جديد</h2>
        <form action="register.php" method="post">
            <label for="name">الاسم:</label>
            <input type="text" id="name" name="name" required>
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">كلمة المرور:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="تسجيل">
        </form>
    </div>
</body>
</html>