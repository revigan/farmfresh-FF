<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get order statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_amount) as total_spent
    FROM orders 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

include 'includes/header.php';
?>

<!-- Alert Messages -->
<?php if(isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Profile Section -->
<div class="container py-4">
    <!-- Hero Section -->
    <div class="hero-section mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold text-white mb-2">Profil Saya</h2>
                <p class="text-white-50 mb-0">Kelola informasi profil dan pengaturan akun Anda</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="hero-icon">
                    <i class='bx bx-user'></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-content">
                    <div class="profile-image-container">
                        <?php if(isset($user['profile_image']) && !empty($user['profile_image'])): ?>
                            <img src="assets/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                 alt="Profile Image" class="profile-img">
                        <?php else: ?>
                            <div class="profile-initial">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h4 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="profile-date">
                        <i class='bx bx-calendar-alt'></i>
                        <span>Member sejak <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </p>
                    <button class="btn-custom btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class='bx bx-edit'></i>
                        <span>Edit Profil</span>
                    </button>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="stats-card">
                <div class="stats-header">
                    <h5>Statistik Belanja</h5>
                </div>
                <div class="stats-body">
                    <div class="stat-item">
                        <div class="stat-icon orders">
                            <i class='bx bx-shopping-bag'></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Pesanan</span>
                            <span class="stat-value"><?php echo $stats['total_orders']; ?></span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon completed">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Pesanan Selesai</span>
                            <span class="stat-value"><?php echo $stats['completed_orders']; ?></span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon spent">
                            <i class='bx bx-wallet'></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Belanja</span>
                            <span class="stat-value">Rp <?php echo number_format($stats['total_spent'] ?? 0, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <!-- Info Card -->
            <div class="info-card">
                <div class="info-header">
                    <h5>Informasi Pribadi</h5>
                    <span class="info-badge">
                        <i class='bx bx-user'></i>
                        Personal
                    </span>
                </div>
                <div class="info-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Nama Lengkap</label>
                                <p><?php echo htmlspecialchars($user['name']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Email</label>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label>Telepon</label>
                                <p><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <label>Alamat</label>
                                <p><?php echo htmlspecialchars($user['address'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="security-card">
                <div class="security-header">
                    <h5>Keamanan</h5>
                    <span class="security-badge">
                        <i class='bx bx-lock-alt'></i>
                        Privasi
                    </span>
                </div>
                <div class="security-body">
                    <button class="btn-custom btn-password" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class='bx bx-lock-alt'></i>
                        <span>Ubah Password</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="actions/update_profile.php" method="POST" enctype="multipart/form-data" id="editProfileForm">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class='bx bx-edit text-primary'></i>
                        <span>Edit Profil</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Profile Image Upload -->
                    <div class="upload-section">
                        <div class="upload-preview">
                            <?php if(isset($user['profile_image']) && !empty($user['profile_image'])): ?>
                                <img src="assets/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                     id="profileImagePreview" alt="Profile">
                            <?php else: ?>
                                <div class="upload-initial" id="profileImagePreview">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <label for="profile_image" class="upload-trigger">
                                <i class='bx bx-camera'></i>
                            </label>
                        </div>
                        <input type="file" id="profile_image" name="profile_image" class="d-none" 
                               accept="image/jpeg,image/png,image/jpg" onchange="previewImage(this)">
                        <div class="upload-info">
                            <span>Upload Foto Profil</span>
                            <small>Format: JPG, JPEG, PNG (Maks. 2MB)</small>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-phone'></i></span>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   pattern="[0-9]{10,13}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-map'></i></span>
                            <textarea name="address" class="form-control" rows="3"
                                    ><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-cancel" data-bs-dismiss="modal">
                        <i class='bx bx-x'></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="btn-modal btn-save">
                        <i class='bx bx-check'></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="actions/update_password.php" method="POST" id="changePasswordForm">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        <i class='bx bx-lock-alt me-2 text-danger'></i>Ubah Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-medium">Password Lama</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" 
                               minlength="6" required id="newPassword">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_new_password" class="form-control" 
                               required id="confirmPassword">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class='bx bx-x me-2'></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class='bx bx-check me-2'></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<link rel="stylesheet" href="assets/css/profile.css">
<script src="assets/js/profile.js"></script>

<?php include 'includes/footer.php'; ?>
