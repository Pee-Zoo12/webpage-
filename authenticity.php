<?php
require_once 'config.php';
session_start();

class Auth {
    private $usersFile;
    
    public function __construct($userDbPath) {
        $this->usersFile = $userDbPath;
    }
    
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ["success" => false, "message" => "Please enter both email and password"];
        }
        
        $users = $this->loadUsers();
        
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_logged_in'] = true;
                
                // Set user type
                if (isset($user['customerID'])) {
                    $_SESSION['user_type'] = 'customer';
                } elseif (isset($user['employeeID'])) {
                    $_SESSION['user_type'] = 'employee';
                } elseif (isset($user['resellerID'])) {
                    $_SESSION['user_type'] = 'reseller';
                }
                
                return ["success" => true, "message" => "Login successful"];
            }
        }
        
        return ["success" => false, "message" => "Invalid email or password"];
    }
    
    public function register($userData) {
        // Required fields validation
        $requiredFields = ['username', 'email', 'password', 'confirm_password', 'age', 'gender', 'address'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return ["success" => false, "message" => "All fields are required"];
            }
        }
        
        // Password match validation
        if ($userData['password'] !== $userData['confirm_password']) {
            return ["success" => false, "message" => "Passwords do not match"];
        }
        
        // Email validation
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Invalid email format"];
        }
        
        $users = $this->loadUsers();
        
        // Check if email already exists
        foreach ($users as $user) {
            if ($user['email'] === $userData['email']) {
                return ["success" => false, "message" => "Email already registered"];
            }
        }
        
        // Create new user
        $newUser = [
            'userID' => uniqid('user_'),
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'age' => $userData['age'],
            'gender' => $userData['gender'],
            'address' => $userData['address'],
            'customerID' => uniqid('cust_'), // By default, register as customer
            'loyaltyStatus' => 'Bronze', // Default loyalty status
            'purchaseHistory' => [],
            'registrationDate' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $newUser;
        
        if ($this->saveUsers($users)) {
            // Auto-login after registration
            $_SESSION['user_id'] = $newUser['userID'];
            $_SESSION['username'] = $newUser['username'];
            $_SESSION['email'] = $newUser['email'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_type'] = 'customer';
            
            return ["success" => true, "message" => "Registration successful"];
        } else {
            return ["success" => false, "message" => "Failed to register. Please try again."];
        }
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        return ["success" => true, "message" => "Logout successful"];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $users = $this->loadUsers();
        foreach ($users as $user) {
            if ($user['userID'] === $_SESSION['user_id']) {
                return $user;
            }
        }
        
        return null;
    }
    
    public function updateProfile($userData) {
        if (!$this->isLoggedIn()) {
            return ["success" => false, "message" => "You are not logged in"];
        }
        
        $users = $this->loadUsers();
        $updated = false;
        
        foreach ($users as &$user) {
            if ($user['userID'] === $_SESSION['user_id']) {
                // Update basic profile data
                if (isset($userData['username'])) $user['username'] = $userData['username'];
                if (isset($userData['age'])) $user['age'] = $userData['age'];
                if (isset($userData['gender'])) $user['gender'] = $userData['gender'];
                if (isset($userData['address'])) $user['address'] = $userData['address'];
                
                // Password change if provided
                if (!empty($userData['new_password']) && !empty($userData['current_password'])) {
                    // Verify current password
                    if (password_verify($userData['current_password'], $user['password'])) {
                        $user['password'] = password_hash($userData['new_password'], PASSWORD_DEFAULT);
                    } else {
                        return ["success" => false, "message" => "Current password is incorrect"];
                    }
                }
                
                $updated = true;
                break;
            }
        }
        
        if ($updated && $this->saveUsers($users)) {
            // Update session if username changed
            if (isset($userData['username'])) {
                $_SESSION['username'] = $userData['username'];
            }
            
            return ["success" => true, "message" => "Profile updated successfully"];
        } else {
            return ["success" => false, "message" => "Failed to update profile"];
        }
    }
    
    private function loadUsers() {
        if (file_exists($this->usersFile)) {
            $data = file_get_contents($this->usersFile);
            return json_decode($data, true) ?: [];
        }
        return [];
    }
    
    private function saveUsers($users) {
        return file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }
}

// Create Auth instance
$auth = new Auth(USER_DB_PATH);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $result = $auth->login($_POST['email'], $_POST['password']);
            if ($result['success']) {
                header('Location: index.php');
                exit;
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'register':
            $result = $auth->register($_POST);
            if ($result['success']) {
                header('Location: index.php');
                exit;
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'logout':
            $auth->logout();
            header('Location: index.php');
            exit;
            break;
            
        case 'update_profile':
            $result = $auth->updateProfile($_POST);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
            break;
    }
}
?>