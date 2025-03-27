<?php
/**
 * User Management Functions
 * 
 * This file handles user registration and validation using a database
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// استيراد ملف الاتصال بقاعدة البيانات
require_once 'db_config.php';

// Define membership levels
define('MEMBERSHIP_BRONZE', 'bronze');
define('MEMBERSHIP_SILVER', 'silver');
define('MEMBERSHIP_VIP', 'vip');
define('MEMBERSHIP_REGULAR', 'regular');

// Define ban status constants
define('BAN_STATUS_NONE', 'none');
define('BAN_STATUS_TEMPORARY', 'temporary');
define('BAN_STATUS_PERMANENT', 'permanent');

// Define admin credentials
define('ADMIN_USERNAME', 'kageno');
define('ADMIN_PASSWORD', '$2y$10$pCISISkcfC4WW.MMF98wL.WtlU2LWpaYPvJE0c0Bg/0L9O13/wttm'); // للتوافق مع الكود القديم

/**
 * Register a new user
 * 
 * @param string $name User's name
 * @param string $email User's email
 * @param string $hashedPassword Hashed password
 * @return bool Success status
 */
function registerUser($name, $email, $hashedPassword) {
    global $db;
    
    try {
        $stmt = $db->prepare("INSERT INTO users (id, name, email, username, password, membership, ban_status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $userId = uniqid();
        $stmt->execute([
            $userId,
            $name,
            $email,
            $email, // Default username is email
            $hashedPassword,
            MEMBERSHIP_REGULAR,
            BAN_STATUS_NONE,
            date('Y-m-d H:i:s')
        ]);
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Check if email already exists
 * 
 * @param string $email Email to check
 * @return bool True if email exists, false otherwise
 */
function emailExists($email) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Get all registered users
 * 
 * @return array Array of all users
 */
function getAllUsers() {
    global $db;
    
    try {
        $stmt = $db->query("SELECT * FROM users WHERE email != 'admin@example.com'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

/**
 * Find user by email
 * 
 * @param string $email User's email
 * @return array|null User data or null if not found
 */
function findUserByEmail($email) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }
        
        return null;
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Find user by ID
 * 
 * @param string $id User ID
 * @return array|null User data or null if not found
 */
function findUserById($id) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        }
        
        return null;
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Authenticate user
 * 
 * @param string $usernameOrEmail User's email or username
 * @param string $password Plain password to verify
 * @return array|bool User data if authenticated, false otherwise
 */
function authenticateUser($usernameOrEmail, $password) {
    global $db;
    
    // Check for admin login
    if ($usernameOrEmail === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
        $_SESSION['user'] = [
            'id' => 'admin',
            'name' => 'مدير النظام',
            'email' => 'admin@example.com',
            'isAdmin' => true
        ];
        return true;
    }
    
    try {
        // Try to find by email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Check if user is banned
                if ($user['ban_status'] !== BAN_STATUS_NONE) {
                    // If temporary ban and ban period is over
                    if ($user['ban_status'] === BAN_STATUS_TEMPORARY && 
                        !empty($user['ban_until']) && 
                        strtotime($user['ban_until']) < time()) {
                        // Remove ban
                        $updateStmt = $db->prepare("UPDATE users SET ban_status = ?, ban_reason = ?, ban_until = ? WHERE id = ?");
                        $updateStmt->execute([BAN_STATUS_NONE, '', null, $user['id']]);
                    } else {
                        // User is still banned
                        $_SESSION['ban_message'] = [
                            'status' => $user['ban_status'],
                            'reason' => $user['ban_reason'],
                            'until' => $user['ban_until']
                        ];
                        return false;
                    }
                }
                
                // Update last login time
                $updateStmt = $db->prepare("UPDATE users SET last_login = ? WHERE id = ?");
                $updateStmt->execute([date('Y-m-d H:i:s'), $user['id']]);
                
                // Set session user data
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'isAdmin' => false,
                    'membership' => $user['membership']
                ];
                
                return true;
            }
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Check if a user is logged in
 *
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * Check if current user is admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['isAdmin']) && $_SESSION['user']['isAdmin'];
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Logout the current user
 */
function logoutUser() {
    if (isset($_SESSION['user'])) {
        unset($_SESSION['user']);
    }
}

// Admin Functions

/**
 * Update user data by email
 * 
 * @param string $email User's email
 * @param array $data Data to update
 * @return bool Success status
 */
function updateUser($email, $data) {
    global $db;
    
    try {
        $user = findUserByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
        }
        
        if (isset($data['username'])) {
            $updates[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['password'])) {
            // Hash password if it's not already hashed
            if (substr($data['password'], 0, 4) !== '$2y$') {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $updates[] = "password = ?";
            $params[] = $data['password'];
        }
        
        if (isset($data['membership'])) {
            $updates[] = "membership = ?";
            $params[] = $data['membership'];
        }
        
        if (!empty($updates)) {
            $updateStr = implode(", ", $updates);
            $params[] = $email; // For the WHERE clause
            
            $stmt = $db->prepare("UPDATE users SET $updateStr WHERE email = ?");
            $stmt->execute($params);
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Delete user by email
 * 
 * @param string $email User's email
 * @return bool Success status
 */
function deleteUser($email) {
    global $db;
    
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Ban a user
 * 
 * @param string $email User's email
 * @param string $status Ban status (BAN_STATUS_TEMPORARY or BAN_STATUS_PERMANENT)
 * @param string $reason Reason for the ban
 * @param string|null $until Date until ban (format: Y-m-d H:i:s)
 * @return bool Success status
 */
function banUser($email, $status, $reason, $until = null) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE users SET ban_status = ?, ban_reason = ?, ban_until = ? WHERE email = ?");
        $stmt->execute([$status, $reason, $until, $email]);
        
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Remove ban from a user
 * 
 * @param string $email User's email
 * @return bool Success status
 */
function unbanUser($email) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE users SET ban_status = ?, ban_reason = ?, ban_until = ? WHERE email = ?");
        $stmt->execute([BAN_STATUS_NONE, '', null, $email]);
        
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        return false;
    }
}

/**
 * Update user membership
 * 
 * @param string $email User's email
 * @param string $membership New membership level
 * @return bool Success status
 */
function updateMembership($email, $membership) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE users SET membership = ? WHERE email = ?");
        $stmt->execute([$membership, $email]);
        
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        return false;
    }
}
?>