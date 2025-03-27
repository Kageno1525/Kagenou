<?php
// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استيراد ملف المستخدمين
require_once 'users.php';

// التأكد من أن المستخدم الحالي هو مدير
if (!isAdmin()) {
    // إعادة توجيه المستخدم إلى صفحة تسجيل الدخول إذا لم يكن مديراً
    header('Location: login.php');
    exit;
}

// تهيئة المتغيرات
$message = '';
$messageType = '';
$showUserForm = false;
$editUser = null;
$userToDelete = null;
$banUser = null;
$unbanUser = null;
$changeMembership = null;

// معالجة النموذج لتحديث بيانات المستخدم
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تحديث بيانات المستخدم
    if (isset($_POST['action']) && $_POST['action'] === 'update_user') {
        $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        $userName = isset($_POST['name']) ? $_POST['name'] : '';
        $userUsername = isset($_POST['username']) ? $_POST['username'] : '';
        $userPassword = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (!empty($userEmail)) {
            $userData = [
                'name' => $userName,
                'username' => $userUsername
            ];
            
            if (!empty($userPassword)) {
                $userData['password'] = $userPassword;
            }
            
            if (updateUser($userEmail, $userData)) {
                $message = "تم تحديث بيانات المستخدم بنجاح";
                $messageType = "success";
                $showUserForm = false;
            } else {
                $message = "فشل في تحديث بيانات المستخدم";
                $messageType = "error";
            }
        }
    }
    
    // حذف المستخدم
    elseif (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        
        if (!empty($userEmail)) {
            if (deleteUser($userEmail)) {
                $message = "تم حذف المستخدم بنجاح";
                $messageType = "success";
            } else {
                $message = "فشل في حذف المستخدم";
                $messageType = "error";
            }
        }
    }
    
    // حظر المستخدم
    elseif (isset($_POST['action']) && $_POST['action'] === 'ban_user') {
        $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        $banType = isset($_POST['ban_type']) ? $_POST['ban_type'] : '';
        $banReason = isset($_POST['ban_reason']) ? $_POST['ban_reason'] : '';
        $banUntil = null;
        
        if ($banType === BAN_STATUS_TEMPORARY) {
            $banDays = isset($_POST['ban_days']) ? (int)$_POST['ban_days'] : 1;
            // حساب تاريخ انتهاء الحظر
            $banUntil = date('Y-m-d H:i:s', strtotime("+{$banDays} days"));
        }
        
        if (!empty($userEmail) && !empty($banType) && !empty($banReason)) {
            if (banUser($userEmail, $banType, $banReason, $banUntil)) {
                $message = "تم حظر المستخدم بنجاح";
                $messageType = "success";
                $banUser = null;
            } else {
                $message = "فشل في حظر المستخدم";
                $messageType = "error";
            }
        }
    }
    
    // إلغاء حظر المستخدم
    elseif (isset($_POST['action']) && $_POST['action'] === 'unban_user') {
        $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        
        if (!empty($userEmail)) {
            if (unbanUser($userEmail)) {
                $message = "تم إلغاء حظر المستخدم بنجاح";
                $messageType = "success";
            } else {
                $message = "فشل في إلغاء حظر المستخدم";
                $messageType = "error";
            }
        }
    }
    
    // تغيير نوع العضوية
    elseif (isset($_POST['action']) && $_POST['action'] === 'change_membership') {
        $userEmail = isset($_POST['email']) ? $_POST['email'] : '';
        $membership = isset($_POST['membership']) ? $_POST['membership'] : '';
        
        if (!empty($userEmail) && !empty($membership)) {
            if (updateMembership($userEmail, $membership)) {
                $message = "تم تغيير نوع العضوية بنجاح";
                $messageType = "success";
                $changeMembership = null;
            } else {
                $message = "فشل في تغيير نوع العضوية";
                $messageType = "error";
            }
        }
    }
}

// عرض نموذج تحرير المستخدم
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $userEmail = $_GET['edit'];
    $editUser = findUserByEmail($userEmail);
    $showUserForm = true;
}

// عرض نموذج حذف المستخدم
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $userEmail = $_GET['delete'];
    $userToDelete = findUserByEmail($userEmail);
}

// عرض نموذج حظر المستخدم
if (isset($_GET['ban']) && !empty($_GET['ban'])) {
    $userEmail = $_GET['ban'];
    $banUser = findUserByEmail($userEmail);
}

// عرض نموذج إلغاء حظر المستخدم
if (isset($_GET['unban']) && !empty($_GET['unban'])) {
    $userEmail = $_GET['unban'];
    $unbanUser = findUserByEmail($userEmail);
}

// عرض نموذج تغيير نوع العضوية
if (isset($_GET['membership']) && !empty($_GET['membership'])) {
    $userEmail = $_GET['membership'];
    $changeMembership = findUserByEmail($userEmail);
}

// الحصول على جميع المستخدمين
$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* أنماط خاصة بصفحة إدارة المستخدمين */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: rgba(18, 18, 18, 0.9);
            border-radius: 10px;
            color: #e0e0e0;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .admin-nav a {
            color: #bb86fc;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: rgba(187, 134, 252, 0.1);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .admin-table th, .admin-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #333;
        }
        
        .admin-table th {
            background-color: #1f1f1f;
            font-weight: bold;
            color: #bb86fc;
        }
        
        .admin-table tr:nth-child(even) {
            background-color: rgba(30, 30, 30, 0.5);
        }
        
        .admin-table tr:hover {
            background-color: rgba(187, 134, 252, 0.1);
        }
        
        .user-actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .edit-btn {
            background-color: #2196F3;
            color: white;
        }
        
        .delete-btn {
            background-color: #F44336;
            color: white;
        }
        
        .ban-btn {
            background-color: #FF9800;
            color: white;
        }
        
        .unban-btn {
            background-color: #4CAF50;
            color: white;
        }
        
        .membership-btn {
            background-color: #9C27B0;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        .user-form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background-color: #1e1e1e;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        
        .form-title {
            color: #bb86fc;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #e0e0e0;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #2a2a2a;
            color: #e0e0e0;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .submit-btn {
            background-color: #bb86fc;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .cancel-btn {
            background-color: #333;
            color: #e0e0e0;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .submit-btn:hover {
            background-color: #a46ef4;
        }
        
        .cancel-btn:hover {
            background-color: #444;
        }
        
        .user-status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-regular {
            background-color: #2196F3;
            color: white;
        }
        
        .status-bronze {
            background-color: #cd7f32;
            color: white;
        }
        
        .status-silver {
            background-color: #c0c0c0;
            color: black;
        }
        
        .status-vip {
            background-color: #ffd700;
            color: black;
        }
        
        .status-banned {
            background-color: #F44336;
            color: white;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
        }
        
        .modal-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .membership-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .dashboard-link {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
        
        .dashboard-link a {
            color: #bb86fc;
            text-decoration: none;
        }
        
        .dashboard-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="particles-js" class="background-particles"></div>
    
    <div class="admin-container">
        <div class="admin-header">
            <h2>إدارة المستخدمين</h2>
            <div class="admin-nav">
                <a href="dashboard.php">لوحة التحكم</a>
                <a href="login.php?logout=1">تسجيل الخروج</a>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($showUserForm && $editUser): ?>
            <!-- نموذج تحرير المستخدم -->
            <div class="user-form">
                <h3 class="form-title">تعديل بيانات المستخدم</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>">
                    
                    <div class="form-group">
                        <label for="name">الاسم:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">اسم المستخدم:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($editUser['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">كلمة المرور الجديدة (اتركها فارغة للاحتفاظ بالحالية):</label>
                        <input type="password" id="password" name="password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">حفظ التغييرات</button>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="cancel-btn">إلغاء</a>
                    </div>
                </form>
            </div>
        <?php elseif ($userToDelete): ?>
            <!-- تأكيد حذف المستخدم -->
            <div class="modal-overlay">
                <div class="modal-content">
                    <h3 class="form-title">تأكيد حذف المستخدم</h3>
                    <p>هل أنت متأكد من حذف حساب المستخدم "<?php echo htmlspecialchars($userToDelete['name']); ?>"؟</p>
                    <p>لا يمكن التراجع عن هذا الإجراء.</p>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($userToDelete['email']); ?>">
                        
                        <div class="modal-actions">
                            <button type="submit" class="submit-btn delete-btn">نعم، حذف المستخدم</button>
                            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="cancel-btn">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($banUser): ?>
            <!-- نموذج حظر المستخدم -->
            <div class="user-form">
                <h3 class="form-title">حظر المستخدم</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="ban_user">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($banUser['email']); ?>">
                    
                    <div class="form-group">
                        <label>المستخدم: <?php echo htmlspecialchars($banUser['name']); ?> (<?php echo htmlspecialchars($banUser['email']); ?>)</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="ban_type">نوع الحظر:</label>
                        <select id="ban_type" name="ban_type" required>
                            <option value="<?php echo BAN_STATUS_TEMPORARY; ?>">مؤقت</option>
                            <option value="<?php echo BAN_STATUS_PERMANENT; ?>">دائم</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="ban_days_group">
                        <label for="ban_days">مدة الحظر (بالأيام):</label>
                        <input type="number" id="ban_days" name="ban_days" min="1" value="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="ban_reason">سبب الحظر:</label>
                        <textarea id="ban_reason" name="ban_reason" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn ban-btn">حظر المستخدم</button>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="cancel-btn">إلغاء</a>
                    </div>
                </form>
            </div>
            
            <script>
                // إخفاء/إظهار حقل المدة بناءً على نوع الحظر
                document.getElementById('ban_type').addEventListener('change', function() {
                    var banDaysGroup = document.getElementById('ban_days_group');
                    if (this.value === '<?php echo BAN_STATUS_PERMANENT; ?>') {
                        banDaysGroup.style.display = 'none';
                    } else {
                        banDaysGroup.style.display = 'block';
                    }
                });
            </script>
        <?php elseif ($unbanUser): ?>
            <!-- تأكيد إلغاء حظر المستخدم -->
            <div class="modal-overlay">
                <div class="modal-content">
                    <h3 class="form-title">تأكيد إلغاء الحظر</h3>
                    <p>هل أنت متأكد من إلغاء حظر المستخدم "<?php echo htmlspecialchars($unbanUser['name']); ?>"؟</p>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="action" value="unban_user">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($unbanUser['email']); ?>">
                        
                        <div class="modal-actions">
                            <button type="submit" class="submit-btn unban-btn">نعم، إلغاء الحظر</button>
                            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="cancel-btn">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($changeMembership): ?>
            <!-- نموذج تغيير نوع العضوية -->
            <div class="user-form">
                <h3 class="form-title">تغيير نوع العضوية</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="change_membership">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($changeMembership['email']); ?>">
                    
                    <div class="form-group">
                        <label>المستخدم: <?php echo htmlspecialchars($changeMembership['name']); ?> (<?php echo htmlspecialchars($changeMembership['email']); ?>)</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="membership">نوع العضوية:</label>
                        <select id="membership" name="membership" required>
                            <option value="<?php echo MEMBERSHIP_REGULAR; ?>" <?php echo ($changeMembership['membership'] === MEMBERSHIP_REGULAR) ? 'selected' : ''; ?>>عادي</option>
                            <option value="<?php echo MEMBERSHIP_BRONZE; ?>" <?php echo ($changeMembership['membership'] === MEMBERSHIP_BRONZE) ? 'selected' : ''; ?>>برونزي</option>
                            <option value="<?php echo MEMBERSHIP_SILVER; ?>" <?php echo ($changeMembership['membership'] === MEMBERSHIP_SILVER) ? 'selected' : ''; ?>>فضي</option>
                            <option value="<?php echo MEMBERSHIP_VIP; ?>" <?php echo ($changeMembership['membership'] === MEMBERSHIP_VIP) ? 'selected' : ''; ?>>VIP</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn membership-btn">تغيير العضوية</button>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="cancel-btn">إلغاء</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- جدول المستخدمين -->
            <?php if (empty($users)): ?>
                <p>لا يوجد مستخدمين مسجلين حتى الآن.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>اسم المستخدم</th>
                            <th>العضوية</th>
                            <th>تاريخ التسجيل</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <span class="membership-badge status-<?php echo htmlspecialchars($user['membership']); ?>">
                                        <?php
                                            switch ($user['membership']) {
                                                case MEMBERSHIP_BRONZE:
                                                    echo "برونزي";
                                                    break;
                                                case MEMBERSHIP_SILVER:
                                                    echo "فضي";
                                                    break;
                                                case MEMBERSHIP_VIP:
                                                    echo "VIP";
                                                    break;
                                                default:
                                                    echo "عادي";
                                            }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td>
                                    <?php if ($user['ban_status'] !== BAN_STATUS_NONE): ?>
                                        <span class="user-status status-banned">
                                            <?php echo ($user['ban_status'] === BAN_STATUS_PERMANENT) ? 'محظور دائماً' : 'محظور مؤقتاً'; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="user-status status-regular">نشط</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <a href="?edit=<?php echo urlencode($user['email']); ?>" class="action-btn edit-btn">تعديل</a>
                                        <a href="?delete=<?php echo urlencode($user['email']); ?>" class="action-btn delete-btn">حذف</a>
                                        
                                        <?php if ($user['ban_status'] === BAN_STATUS_NONE): ?>
                                            <a href="?ban=<?php echo urlencode($user['email']); ?>" class="action-btn ban-btn">حظر</a>
                                        <?php else: ?>
                                            <a href="?unban=<?php echo urlencode($user['email']); ?>" class="action-btn unban-btn">إلغاء الحظر</a>
                                        <?php endif; ?>
                                        
                                        <a href="?membership=<?php echo urlencode($user['email']); ?>" class="action-btn membership-btn">العضوية</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="dashboard-link">
            <a href="dashboard.php">العودة إلى لوحة التحكم</a>
        </div>
    </div>
    
    <!-- إضافة سكربت الجزيئات المتقدم للخلفية -->
    <script src="js/particles.js"></script>
    <script src="js/advanced_particles.js"></script>
</body>
</html>