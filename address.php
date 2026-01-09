<?php
require_once __DIR__ . '/init_session.php';
require_once __DIR__ . '/db.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?redirect=address');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Check for redirect message from checkout
if (isset($_SESSION['checkout_redirect_message'])) {
    $error_message = $_SESSION['checkout_redirect_message'];
    unset($_SESSION['checkout_redirect_message']);
}

// Handle add address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address_line1 = trim($_POST['address_line1'] ?? '');
    $address_line2 = trim($_POST['address_line2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    if (empty($full_name) || empty($phone) || empty($address_line1) || empty($city) || empty($postal_code)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // If this is set as default, unset other defaults
        if ($is_default) {
            $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid")->execute([':uid' => $user_id]);
        }
        
        $insert_sql = "INSERT INTO addresses (user_id, full_name, phone, address_line1, address_line2, city, state, postal_code, is_default) 
                       VALUES (:uid, :fname, :phone, :addr1, :addr2, :city, :state, :postal, :def)";
        $stmt = $pdo->prepare($insert_sql);
        
        if ($stmt->execute([
            ':uid' => $user_id,
            ':fname' => $full_name,
            ':phone' => $phone,
            ':addr1' => $address_line1,
            ':addr2' => $address_line2,
            ':city' => $city,
            ':state' => $state,
            ':postal' => $postal_code,
            ':def' => $is_default
        ])) {
            $success_message = 'Address added successfully.';
        } else {
            $error_message = 'Failed to add address.';
        }
    }
}

// Handle set default
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default'])) {
    $address_id = (int) $_POST['address_id'];
    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid")->execute([':uid' => $user_id]);
    $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE address_id = :aid AND user_id = :uid")
        ->execute([':aid' => $address_id, ':uid' => $user_id]);
    $success_message = 'Default address updated.';
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_address'])) {
    $address_id = (int) $_POST['address_id'];
    $pdo->prepare("DELETE FROM addresses WHERE address_id = :aid AND user_id = :uid")
        ->execute([':aid' => $address_id, ':uid' => $user_id]);
    $success_message = 'Address deleted successfully.';
}

// Fetch addresses
$addresses_sql = "SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, created_at DESC";
$stmt = $pdo->prepare($addresses_sql);
$stmt->execute([':uid' => $user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
$pageTitle = 'My Addresses - Jeweluxe';
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
                <h1 class="mb-0 text-white">My Addresses</h1>
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
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Saved Addresses</h5>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            + Add New Address
                        </button>
                    </div>

                    <?php if (empty($addresses)): ?>
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-body text-center py-5">
                                <div class="mb-3" style="font-size: 3rem;">📍</div>
                                <h5 class="mb-2">No addresses saved</h5>
                                <p class="text-muted mb-3">Add your shipping address to make checkout faster.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    Add Address
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($addresses as $address): ?>
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 rounded-4 h-100 <?php echo $address['is_default'] ? 'border-primary' : ''; ?>" style="<?php echo $address['is_default'] ? 'border: 2px solid #0d6efd !important;' : ''; ?>">
                                        <div class="card-body">
                                            <?php if ($address['is_default']): ?>
                                                <span class="badge bg-primary mb-2">Default</span>
                                            <?php endif; ?>
                                            <h6 class="mb-2"><?php echo htmlspecialchars($address['full_name']); ?></h6>
                                            <p class="mb-1 small"><?php echo htmlspecialchars($address['phone']); ?></p>
                                            <p class="mb-2 small text-muted">
                                                <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                                <?php if ($address['address_line2']): ?>
                                                    <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br>
                                                <?php echo htmlspecialchars($address['country']); ?>
                                            </p>
                                            <div class="d-flex gap-2 mt-3">
                                                <?php if (!$address['is_default']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                                        <button type="submit" name="set_default" class="btn btn-sm btn-outline-primary">Set as Default</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline delete-address-form">
                                                    <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-address-btn">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h6 class="mb-3">💡 Address Tips</h6>
                            <ul class="small text-muted">
                                <li>Mark your most-used address as default</li>
                                <li>Include apartment/unit numbers</li>
                                <li>Provide accurate phone number</li>
                                <li>Double-check postal codes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="address_line1" class="form-label">Address Line 1 *</label>
                            <input type="text" class="form-control" id="address_line1" name="address_line1" placeholder="Street address" required>
                        </div>
                        <div class="mb-3">
                            <label for="address_line2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="address_line2" name="address_line2" placeholder="Apt, suite, unit, etc.">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Postal Code *</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                            <label class="form-check-label" for="is_default">
                                Set as default address
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_address" class="btn btn-primary">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script>
        // Handle address delete with custom confirmation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-address-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = this.closest('.delete-address-form');
                    const addressId = form.querySelector('input[name="address_id"]').value;
                    
                    ConfirmModal.show(
                        '⚠️ Delete Address',
                        'Are you sure you want to delete this address?',
                        function() {
                            const formData = new FormData();
                            formData.append('address_id', addressId);
                            formData.append('delete_address', '1');
                            
                            fetch('', {
                                method: 'POST',
                                body: formData
                            }).then(() => {
                                ToastNotification.success('Address deleted successfully.');
                                setTimeout(() => location.reload(), 1500);
                            }).catch(error => {
                                ToastNotification.error('Error deleting address.');
                                console.error('Error:', error);
                            });
                        }
                    );
                });
            });
        });
    </script>