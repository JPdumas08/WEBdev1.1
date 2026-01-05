<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=account_settings');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current user data
$user_sql = "SELECT * FROM users WHERE user_id = :uid";
$stmt = $pdo->prepare($user_sql);
$stmt->execute([':uid' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = 'First name, last name, and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if email is already taken by another user
        $check_email_sql = "SELECT user_id FROM users WHERE email = :email AND user_id != :uid";
        $stmt_check = $pdo->prepare($check_email_sql);
        $stmt_check->execute([':email' => $email, ':uid' => $user_id]);
        
        if ($stmt_check->fetch()) {
            $error_message = 'This email is already registered to another account.';
        } else {
            // Update user information
            $update_sql = "UPDATE users SET first_name = :fname, last_name = :lname, email = :email, phone = :phone WHERE user_id = :uid";
            $stmt_update = $pdo->prepare($update_sql);
            
            if ($stmt_update->execute([
                ':fname' => $first_name,
                ':lname' => $last_name,
                ':email' => $email,
                ':phone' => $phone,
                ':uid' => $user_id
            ])) {
                $_SESSION['user_name'] = $first_name;
                $_SESSION['user_email'] = $email;
                $success_message = 'Your profile has been updated successfully.';
                
                // Refresh user data
                $stmt->execute([':uid' => $user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error_message = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'New password must be at least 6 characters long.';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pwd_sql = "UPDATE users SET password = :pwd WHERE user_id = :uid";
            $stmt_pwd = $pdo->prepare($update_pwd_sql);
            
            if ($stmt_pwd->execute([':pwd' => $new_password_hash, ':uid' => $user_id])) {
                $success_message = 'Your password has been changed successfully.';
            } else {
                $error_message = 'Failed to change password. Please try again.';
            }
        } else {
            $error_message = 'Current password is incorrect.';
        }
    }
}
?>

<?php
$pageTitle = 'Account Settings - Jeweluxe';
include 'includes/header.php';
?>
<link rel="stylesheet" href="styles.css">
<body class="order-history-page">

    <section class="orders-hero">
        <div class="container-xl">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light btn-sm" onclick="window.history.back();" type="button" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h1 class="mb-0 text-white">Account Settings</h1>
            </div>
        </div>
    </section>

    <div class="orders-wrapper py-5">
        <div class="container-xl">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Personal Information</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
                                    <small class="text-muted">Username cannot be changed</small>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Change Password</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" 
                                           name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" required>
                                    <small class="text-muted">Must be at least 6 characters</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
