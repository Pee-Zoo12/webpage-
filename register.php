<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('account.php');
}

$pageTitle = 'Register - ' . SITE_NAME;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['firstName']);
    $lastName = sanitize($_POST['lastName']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Initialize database
    $db = new JsonDatabase();
    
    // Validate form data
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($db->getUserByEmail($email)) {
        $errors[] = 'Email address is already registered.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($errors)) {
        // Create user
        $user = [
            'userID' => generateRandomString(),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'customer',
            'createdAt' => date('Y-m-d H:i:s')
        ];
        
        if ($db->addUser($user)) {
            // Set session
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['user_email'] = $user['email'];
            
            // Set success message
            setFlashMessage('success', 'Welcome to ' . SITE_NAME . ', ' . $firstName . '!');
            
            // Redirect to account page
            redirect('account.php');
        } else {
            setFlashMessage('error', 'There was an error creating your account. Please try again.');
        }
    } else {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

include 'includes/header.php';
?>

<div class="auth-header">
    <div class="container">
        <h1>Create Account</h1>
        <?php echo generateBreadcrumbs(['Home' => 'index.php', 'Register' => 'register.php']); ?>
    </div>
</div>

<div class="auth-content">
    <div class="container">
        <div class="auth-grid">
            <!-- Registration Form -->
            <div class="auth-form">
                <form method="post" action="register.php">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <small>Must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span>I agree to the <a href="terms.php">Terms & Conditions</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>
                
                <div class="auth-links">
                    <span>Already have an account?</span>
                    <a href="login.php">Login</a>
                </div>
            </div>
            
            <!-- Auth Info -->
            <div class="auth-info">
                <h2>Join Our Community!</h2>
                <p>Create an account to:</p>
                <ul>
                    <li><i class="fas fa-check"></i> Get exclusive access to new releases</li>
                    <li><i class="fas fa-check"></i> Earn rewards points on purchases</li>
                    <li><i class="fas fa-check"></i> Save your shipping details</li>
                    <li><i class="fas fa-check"></i> Track your orders</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 