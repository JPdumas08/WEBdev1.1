<?php
require_once __DIR__ . '/init_session.php';
require_once 'db.php'; 
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

$first = trim($_POST['first_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
if ($first === '') $errors[] = 'First name is required.';
if ($last === '') $errors[] = 'Last name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if ($username === '') $errors[] = 'Username is required.';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

if (empty($errors)) {
  try {
    // Detect which column names the `users` table actually uses.
    $cols = [];
    $stmt = $pdo->query('SHOW COLUMNS FROM users');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
      $cols[] = $r['Field'];
    }

    $find = function(array $candidates) use ($cols) {
      foreach ($candidates as $cand) {
        foreach ($cols as $col) {
          if (strtolower($col) === strtolower($cand)) return $col;
        }
      }
      return null;
    };

    $emailCol = $find(['email', 'email_address']);
    $usernameCol = $find(['username', 'user_name', 'user']);
    $passwordCol = $find(['password', 'pass', 'passwd']);
    $firstCol = $find(['first_name', 'firstname', 'first']);
    $lastCol = $find(['last_name', 'lastname', 'last']);

    $missing = [];
    if (!$emailCol) $missing[] = 'email (or email_address)';
    if (!$usernameCol) $missing[] = 'username (or user_name)';
    if (!$passwordCol) $missing[] = 'password (or pass/passwd)';
    if (!$firstCol) $missing[] = 'first_name';
    if (!$lastCol) $missing[] = 'last_name';

    if (!empty($missing)) {
      $errors[] = 'Users table is missing required column(s): ' . implode(', ', $missing) . 
        '. Existing columns: ' . implode(', ', $cols);
    } else {
      // Use detected column names (safe because they come from SHOW COLUMNS)
      $emailColQ = "`$emailCol`";
      $usernameColQ = "`$usernameCol`";
      $firstColQ = "`$firstCol`";
      $lastColQ = "`$lastCol`";
      $passwordColQ = "`$passwordCol`";

      $checkSql = "SELECT COUNT(*) FROM users WHERE {$emailColQ} = :email OR {$usernameColQ} = :username";
      $check = $pdo->prepare($checkSql);
      $check->execute([':email' => $email, ':username' => $username]);

      if ($check->fetchColumn() > 0) {
        $errors[] = 'An account with that email or username already exists.';
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $colsList = [$firstColQ, $lastColQ, $emailColQ, $usernameColQ, $passwordColQ];
        $placeholders = [':first', ':last', ':email', ':username', ':password'];
        $insSql = 'INSERT INTO users (' . implode(', ', $colsList) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $ins = $pdo->prepare($insSql);
        $ok = $ins->execute([
          ':first' => $first,
          ':last' => $last,
          ':email' => $email,
          ':username' => $username,
          ':password' => $hash
        ]);
        if ($ok) {
          header('Location: login.php?registered=1');
          exit;
        }
        $errors[] = 'Failed to create account.';
      }
    }
  } catch (Exception $e) {
    $errors[] = 'Database error: ' . $e->getMessage();
  }
}

?>
<?php
$pageTitle = 'Registration Result';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body text-center">
          <?php if (empty($errors)): ?>
            <h4 class="text-success">Account created</h4>
            <p class="mb-3">Your account has been created. Please sign in.</p>
            <a href="login.php" class="btn btn-primary">Sign in</a>
          <?php else: ?>
            <h4 class="text-danger">Registration failed</h4>
            <div class="text-start small text-muted mb-3">
              <strong>Errors:</strong>
              <ul>
                <?php foreach ($errors as $err): ?>
                  <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <a href="home.php" class="btn btn-secondary">Back</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$extra_scripts = '';
include __DIR__ . '/includes/footer.php';
?>
