<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('account.php');
}

$pageTitle = 'Login - ' . SITE_NAME;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Initialize database
    $db = new JsonDatabase();
    
    // Get user
    $user = $db->getUserByEmail($email);
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['userID'];
        $_SESSION['user_email'] = $user['email'];
        
        // Set success message
        setFlashMessage('success', 'Welcome back, ' . $user['firstName'] . '!');
        
        // Redirect to account page
        redirect('account.php');
    } else {
        setFlashMessage('error', 'Invalid email or password.');
    }
}

include 'includes/header.php';
?>

<div class="auth-header">
    <div class="container">
        <h1>Login</h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Login' => 'login.php']); ?>
    </div>
</div>

<div class="auth-content">
    <div class="container">
        <div class="auth-grid">
            <!-- Login Form -->
            <div class="auth-form">
                <form method="post" action="login.php">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Remember me</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <div class="auth-links">
                    <a href="forgot-password.php">Forgot Password?</a>
                    <span class="divider">|</span>
                    <a href="register.php">Create Account</a>
                </div>
            </div>
            
            <!-- Auth Info -->
            <div class="auth-info">
                <h2>Welcome Back!</h2>
                <p>Login to your account to:</p>
                <ul>
                    <li><i class="fas fa-check"></i> View your order history</li>
                    <li><i class="fas fa-check"></i> Track your orders</li>
                    <li><i class="fas fa-check"></i> Save your shipping details</li>
                    <li><i class="fas fa-check"></i> Access exclusive offers</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 