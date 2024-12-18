<?php
session_start();
require_once 'config/database.php';

// Cek apakah ada data checkout
if (!isset($_SESSION['checkout_data']) || !isset($_SESSION['cart_data'])) {
    header('Location: checkout.php');
    exit();
}

// Ambil detail metode pembayaran
$stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = ?");
$stmt->execute([$_SESSION['checkout_data']['payment_method_id']]);
$payment_method = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Hero Section -->
    <div class="hero-section mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold text-white mb-2">Konfirmasi Pembayaran</h2>
                <p class="text-white-50 mb-0">Selesaikan pembayaran untuk menyelesaikan pesanan Anda</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="hero-icon">
                    <i class='bx bx-wallet'></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="payment-card">
                <!-- Detail Pengiriman -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class='bx bx-map-pin'></i>
                        <span>Detail Pengiriman</span>
                    </div>
                    <div class="info-container">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Penerima</label>
                                    <p><?= htmlspecialchars($_SESSION['checkout_data']['recipient_name']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Telepon</label>
                                    <p><?= htmlspecialchars($_SESSION['checkout_data']['phone']) ?></p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <label>Alamat</label>
                                    <p><?= htmlspecialchars($_SESSION['checkout_data']['address']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Pembayaran -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class='bx bx-credit-card'></i>
                        <span>Informasi Pembayaran</span>
                    </div>
                    <div class="info-container payment-details">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Bank</label>
                                    <p><?= htmlspecialchars($payment_method['name']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Atas Nama</label>
                                    <p><?= htmlspecialchars($payment_method['account_name']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Nomor Rekening</label>
                                    <p class="account-number"><?= htmlspecialchars($payment_method['account_number']) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label>Total Pembayaran</label>
                                    <p class="total-amount">Rp <?= number_format($_SESSION['total_amount'], 0, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instruksi -->
                <div class="payment-section">
                    <div class="section-title">
                        <i class='bx bx-info-circle'></i>
                        <span>Petunjuk Pembayaran</span>
                    </div>
                    <div class="instruction-container">
                        <ol class="instruction-list">
                            <li>Transfer sesuai nominal yang tertera</li>
                            <li>Simpan bukti pembayaran Anda</li>
                            <li>Pembayaran akan diverifikasi secara otomatis</li>
                            <li>Pesanan akan diproses setelah pembayaran terverifikasi</li>
                        </ol>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <form action="actions/simulate_payment.php" method="POST" class="payment-actions">
                    <input type="hidden" name="payment_method_id" value="<?= $_SESSION['checkout_data']['payment_method_id'] ?>">
                    <input type="hidden" name="total_amount" value="<?= $_SESSION['total_amount'] ?>">
                    <button type="submit" name="confirm_payment" class="btn-confirm">
                        <i class='bx bx-check-circle'></i>
                        <span>Konfirmasi Pembayaran</span>
                    </button>
                    <a href="checkout.php" class="btn-back">
                        <i class='bx bx-arrow-back'></i>
                        <span>Kembali ke Checkout</span>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/payment.css">

<?php include 'includes/footer.php'; ?>
