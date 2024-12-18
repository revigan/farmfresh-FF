<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'consumer' ORDER BY id DESC");
$users = $stmt->fetchAll();
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class='bx bx-user-circle' style='color: #3498db;'></i> Kelola Pengguna
                            </h3>
                            <p class="text-muted mb-0">Kelola semua akun pengguna terdaftar</p>
                        </div>
                        <div class="search-box mt-3 mt-md-0">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class='bx bx-search'></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           class="form-control border-0 bg-light" 
                                           placeholder="Cari pengguna..." 
                                           value="<?= htmlspecialchars($search ?? ''); ?>">
                                    <?php if(isset($search) && $search != ''): ?>
                                        <a href="users.php" class="input-group-text bg-light border-0">
                                            <i class='bx bx-x'></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                            <th class="text-center">Total Pesanan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <img src="../assets/images/no-data.svg" alt="No Users" 
                                         class="mb-3" style="width: 200px; opacity: 0.6">
                                    <h5 class="text-muted mb-0">Belum ada pengguna</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary">
                                        #<?= $user['id']; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if(!empty($user['profile_image'])): ?>
                                                <img src="../assets/uploads/profiles/<?= $user['profile_image'] ?>" 
                                                     alt="<?= $user['name'] ?>"
                                                     class="rounded-circle me-3"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="user-avatar me-3">
                                                    <?= strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?= $user['name']; ?></div>
                                                <small class="text-muted">
                                                    Member sejak <?= date('d M Y', strtotime($user['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <i class='bx bx-envelope me-1'></i>
                                            <?= $user['email']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class='bx bx-calendar me-1'></i>
                                        <?= date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            <i class='bx bx-shopping-bag me-1'></i>
                                            <?php 
                                                $order_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = " . $user['id'])->fetchColumn();
                                                echo $order_count;
                                            ?> Pesanan
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success">
                                            <i class='bx bx-check-circle me-1'></i>Aktif
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="view_user.php?id=<?= $user['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class='bx bx-show me-1'></i> Detail
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-danger"
                                                    onclick="deleteUser(<?= $user['id']; ?>)">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.search-box .input-group {
    width: 300px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: #3498db;
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.2rem;
}

.table thead th {
    font-weight: 600;
    color: #344767;
    background-color: #f8f9fa;
}

.badge {
    padding: 0.6em 1em;
    font-weight: 500;
}

.btn-group .btn {
    padding: 0.5rem;
    margin: 0 2px;
}

.bg-success-subtle {
    background-color: rgba(46, 204, 113, 0.1);
}

.bg-danger-subtle {
    background-color: rgba(231, 76, 60, 0.1);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

.input-group-text {
    color: #3498db;
}

/* Hide horizontal scrollbar */
.table-responsive {
    overflow-x: auto; /* Enable horizontal scrolling */
    -ms-overflow-style: none;  /* Hide scrollbar for Internet Explorer and Edge */
    scrollbar-width: none;  /* Hide scrollbar for Firefox */
}

.table-responsive::-webkit-scrollbar {
    display: none; /* Hide scrollbar for Chrome, Safari, and Opera */
}

/* Remove hover effects */
.btn-group .btn:hover,
.table tbody tr:hover {
    background-color: inherit !important; /* Keep background color the same */
    color: inherit !important; /* Keep text color the same */
    transform: none !important; /* Prevent floating effect */
}

/* Mobile Responsive Adjustments */
@media (max-width: 576px) {
    .search-box .input-group {
        width: 100%; /* Make the search box full width on small screens */
    }

    .table thead th, .table tbody td {
        font-size: 0.9rem; /* Adjust font size for better readability */
    }

    .btn-group .btn {
        padding: 0.3rem; /* Adjust button padding for smaller screens */
    }

    .user-avatar {
        width: 30px; /* Smaller avatar size on mobile */
        height: 30px;
    }
}
</style>

<script>
function deleteUser(userId) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Pengguna yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'delete_user.php?id=' + userId;
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
