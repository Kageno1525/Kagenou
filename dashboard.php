<?php
// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استيراد ملف المستخدمين
require_once 'users.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    // إعادة التوجيه إلى صفحة تسجيل الدخول
    header("Location: login.php");
    exit;
}

// الحصول على بيانات المستخدم الحالي
$currentUser = getCurrentUser();
$user_name = $currentUser['name'];
$is_admin = isAdmin();
$membership = isset($currentUser['membership']) ? $currentUser['membership'] : MEMBERSHIP_REGULAR;

// الحصول على قائمة المستخدمين إذا كان الشخص مدير
$users = $is_admin ? getAllUsers() : [];

// معالجة تسجيل الخروج
if (isset($_POST['logout'])) {
    // تسجيل الخروج
    logoutUser();
    
    // إعادة التوجيه إلى صفحة تسجيل الدخول
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* أنماط إضافية للوحة التحكم */
        .dashboard-container {
            padding: 20px;
            color: #e0e0e0;
        }
        
        .welcome-message {
            font-size: 18px;
            margin-bottom: 20px;
            border-bottom: 1px solid #3d3d3d;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .admin-label {
            background-color: #6200ea;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-right: 10px;
        }
        
        .membership-badge {
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .membership-bronze {
            background-color: #cd7f32;
            color: white;
        }
        
        .membership-silver {
            background-color: #c0c0c0;
            color: black;
        }
        
        .membership-vip {
            background-color: #ffd700;
            color: black;
        }
        
        .membership-regular {
            background-color: #2196F3;
            color: white;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .user-table th, .user-table td {
            border: 1px solid #3d3d3d;
            padding: 8px 12px;
            text-align: right;
        }
        
        .user-table th {
            background-color: #2d2d2d;
        }
        
        .logout-form {
            margin-top: 20px;
        }
        
        .logout-button {
            background-color: #cf6679;
            color: #121212;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .logout-button:hover {
            background-color: #ba4d5f;
        }
        
        .container {
            max-width: 900px;
        }
        
        .admin-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .admin-btn {
            background-color: #6200ea;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .admin-btn:hover {
            background-color: #7c4dff;
        }
        
        .user-card {
            background-color: rgba(30, 30, 30, 0.7);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .membership-features {
            margin-top: 20px;
        }
        
        .feature-list {
            list-style-type: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #3d3d3d;
            display: flex;
            align-items: center;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            margin-left: 10px;
            color: #bb86fc;
        }
        
        .membership-title {
            color: #bb86fc;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background-color: rgba(30, 30, 30, 0.7);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #bb86fc;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #e0e0e0;
            font-size: 16px;
        }
        
        .icon-container {
            background-color: rgba(187, 134, 252, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        
        .material-icon {
            font-size: 30px;
            color: #bb86fc;
        }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div id="particles-js" class="background-particles"></div>
    <div class="container">
        <h2>لوحة التحكم</h2>
        
        <div class="dashboard-container">
            <div class="welcome-message">
                <div class="user-info">
                    أهلاً بك، <?php echo htmlspecialchars($user_name); ?>
                    
                    <?php if ($is_admin): ?>
                        <span class="admin-label">مدير</span>
                    <?php else: ?>
                        <span class="membership-badge membership-<?php echo htmlspecialchars($membership); ?>">
                            <?php
                                switch ($membership) {
                                    case MEMBERSHIP_BRONZE:
                                        echo "عضوية برونزية";
                                        break;
                                    case MEMBERSHIP_SILVER:
                                        echo "عضوية فضية";
                                        break;
                                    case MEMBERSHIP_VIP:
                                        echo "عضوية VIP";
                                        break;
                                    default:
                                        echo "عضوية عادية";
                                }
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <form method="post" class="logout-form" style="margin-top: 0;">
                    <button type="submit" name="logout" class="logout-button">تسجيل الخروج</button>
                </form>
            </div>
            
            <?php if ($is_admin): ?>
                <!-- قسم لوحة المدير -->
                <div class="admin-actions">
                    <a href="admin_users.php" class="admin-btn">إدارة المستخدمين</a>
                </div>
                
                <div class="dashboard-cards">
                    <div class="stat-card">
                        <div class="icon-container">
                            <span class="material-icons">people</span>
                        </div>
                        <div class="stat-value"><?php echo count($users); ?></div>
                        <div class="stat-label">المستخدمين المسجلين</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon-container">
                            <span class="material-icons">verified_user</span>
                        </div>
                        <div class="stat-value">
                            <?php
                                $vipCount = 0;
                                foreach ($users as $user) {
                                    if (isset($user['membership']) && $user['membership'] === MEMBERSHIP_VIP) {
                                        $vipCount++;
                                    }
                                }
                                echo $vipCount;
                            ?>
                        </div>
                        <div class="stat-label">أعضاء VIP</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon-container">
                            <span class="material-icons">block</span>
                        </div>
                        <div class="stat-value">
                            <?php
                                $bannedCount = 0;
                                foreach ($users as $user) {
                                    if (isset($user['ban_status']) && $user['ban_status'] !== BAN_STATUS_NONE) {
                                        $bannedCount++;
                                    }
                                }
                                echo $bannedCount;
                            ?>
                        </div>
                        <div class="stat-label">المستخدمين المحظورين</div>
                    </div>
                </div>
                
                <h3>آخر المستخدمين المسجلين</h3>
                <?php if (empty($users)): ?>
                    <p>لا يوجد مستخدمين مسجلين بعد.</p>
                <?php else: ?>
                    <?php 
                        // ترتيب المستخدمين بناءً على تاريخ التسجيل (الأحدث أولاً)
                        usort($users, function($a, $b) {
                            return strtotime($b['created_at']) - strtotime($a['created_at']);
                        });
                        
                        // أخذ آخر 5 مستخدمين فقط
                        $latestUsers = array_slice($users, 0, 5);
                    ?>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>اسم المستخدم</th>
                                <th>العضوية</th>
                                <th>تاريخ التسجيل</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td>
                                        <span class="membership-badge membership-<?php echo htmlspecialchars($user['membership']); ?>">
                                            <?php
                                                switch ($user['membership']) {
                                                    case MEMBERSHIP_BRONZE:
                                                        echo "برونزية";
                                                        break;
                                                    case MEMBERSHIP_SILVER:
                                                        echo "فضية";
                                                        break;
                                                    case MEMBERSHIP_VIP:
                                                        echo "VIP";
                                                        break;
                                                    default:
                                                        echo "عادية";
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                    <td>
                                        <a href="admin_users.php?edit=<?php echo urlencode($user['email']); ?>" class="admin-btn" style="padding: 3px 8px; font-size: 12px;">تعديل</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="text-align: center; margin-top: 10px;">
                        <a href="admin_users.php" class="admin-btn">عرض كل المستخدمين</a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- قسم لوحة المستخدم العادي -->
                <div class="user-card">
                    <div class="membership-title">
                        معلومات العضوية
                    </div>
                    
                    <div class="membership-features">
                        <ul class="feature-list">
                            <?php if ($membership === MEMBERSHIP_VIP): ?>
                                <li><span class="feature-icon">✓</span> وصول كامل لجميع محتويات الموقع</li>
                                <li><span class="feature-icon">✓</span> أولوية الدعم الفني خلال 24 ساعة</li>
                                <li><span class="feature-icon">✓</span> محتوى حصري للأعضاء المميزين</li>
                                <li><span class="feature-icon">✓</span> تنزيلات غير محدودة</li>
                                <li><span class="feature-icon">✓</span> تخصيص كامل للملف الشخصي</li>
                            <?php elseif ($membership === MEMBERSHIP_SILVER): ?>
                                <li><span class="feature-icon">✓</span> وصول متميز للمحتوى</li>
                                <li><span class="feature-icon">✓</span> أولوية الدعم الفني خلال 48 ساعة</li>
                                <li><span class="feature-icon">✓</span> تنزيلات شهرية محدودة</li>
                                <li><span class="feature-icon">✓</span> خيارات تخصيص للملف الشخصي</li>
                                <li><span class="feature-icon">✗</span> محتوى حصري للأعضاء المميزين</li>
                            <?php elseif ($membership === MEMBERSHIP_BRONZE): ?>
                                <li><span class="feature-icon">✓</span> وصول محسن للمحتوى</li>
                                <li><span class="feature-icon">✓</span> دعم فني في غضون 72 ساعة</li>
                                <li><span class="feature-icon">✓</span> تنزيلات محدودة</li>
                                <li><span class="feature-icon">✗</span> خيارات تخصيص للملف الشخصي</li>
                                <li><span class="feature-icon">✗</span> محتوى حصري للأعضاء المميزين</li>
                            <?php else: ?>
                                <li><span class="feature-icon">✓</span> وصول أساسي للمحتوى</li>
                                <li><span class="feature-icon">✓</span> دعم فني أساسي</li>
                                <li><span class="feature-icon">✗</span> تنزيلات محدودة</li>
                                <li><span class="feature-icon">✗</span> خيارات تخصيص للملف الشخصي</li>
                                <li><span class="feature-icon">✗</span> محتوى حصري للأعضاء المميزين</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <p style="margin-top: 20px;">يمكنك ترقية عضويتك للحصول على مزيد من المميزات! تواصل مع مدير الموقع.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- إضافة سكربت الجزيئات المتقدم للخلفية -->
    <script src="js/particles.js"></script>
    <script src="js/advanced_particles.js"></script>
</body>
</html>