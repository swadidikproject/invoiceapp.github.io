<?php
require_once 'config_database.php';
require_once 'include_functions.php';

$id = $_GET['id'] ?? 0;

// Get customer data
$query = "SELECT * FROM customers WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: customers.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            $id
        ]);
        
        header("Location: customers.php?msg=updated");
        exit();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - Edit Pelanggan <?= htmlspecialchars($customer['name']) ?></title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #2c3e50;
            --light-bg: #f8fafc;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
        }
        
        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #1e2b3a 100%);
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            margin: 0.2rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, #4361ee, #3a56d4);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            font-size: 1.1rem;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h5 {
            color: white;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
        }
        
        .sidebar-brand i {
            font-size: 2rem;
            color: #4361ee;
            margin-bottom: 0.5rem;
        }
        
        /* Main Content */
        main {
            padding: 2rem;
            background-color: var(--light-bg);
        }
        
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background: transparent;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        /* Buttons */
        .btn-custom {
            padding: 0.5rem 1.2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary-custom:hover {
            color: white;
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }
        
        .btn-secondary-custom {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .btn-secondary-custom:hover {
            background: #dee2e6;
            color: #495057;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        /* Form Card */
        .form-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        
        .form-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .form-card .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .form-card .card-header h6 i {
            color: var(--primary-color);
            margin-right: 8px;
        }
        
        .form-card .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.3rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
        }
        
        .form-control:readonly {
            background-color: #f8fafc;
        }
        
        .required-star {
            color: var(--danger-color);
            margin-left: 2px;
        }
        
        /* Alert */
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        /* Info Section */
        .info-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
            margin-bottom: 2rem;
        }
        
        .info-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .info-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            flex: 1;
            min-width: 150px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            main {
                padding: 1rem;
            }
            
            .form-card .card-body {
                padding: 1.5rem;
            }
            
            .info-stats {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar p-0">
                <div class="position-sticky">
                    <div class="sidebar-brand text-center">
                        <i class="bi bi-receipt"></i>
                        <h5>Invoice App</h5>
                    </div>
                    <ul class="nav flex-column mt-3">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_invoice.php">
                                <i class="bi bi-plus-circle"></i> Buat Invoice
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="customers.php">
                                <i class="bi bi-people"></i> Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="invoices.php">
                                <i class="bi bi-file-text"></i> Daftar Invoice
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-1">Edit Pelanggan</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="customers.php">Pelanggan</a></li>
                                <li class="breadcrumb-item"><a href="view_customer.php?id=<?= $customer['id'] ?>"><?= htmlspecialchars($customer['name']) ?></a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="view_customer.php?id=<?= $customer['id'] ?>" class="btn-custom btn-primary-custom">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        <a href="customers.php" class="btn-custom btn-secondary-custom">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Customer Info Summary -->
                <div class="info-section">
                    <div class="info-title">
                        <i class="bi bi-person-circle"></i> Informasi Pelanggan
                    </div>
                    <div class="info-stats">
                        <div class="stat-item">
                            <div class="stat-label">ID Pelanggan</div>
                            <div class="stat-value">#<?= str_pad($customer['id'], 4, '0', STR_PAD_LEFT) ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Nama</div>
                            <div class="stat-value"><?= htmlspecialchars($customer['name']) ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Kontak</div>
                            <div class="stat-value"><?= htmlspecialchars($customer['phone'] ?: '-') ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Email</div>
                            <div class="stat-value"><?= htmlspecialchars($customer['email'] ?: '-') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="form-card">
                    <div class="card-header">
                        <h6><i class="bi bi-pencil-square"></i> Formulir Edit Pelanggan</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" onsubmit="return validateForm()">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Nama Pelanggan <span class="required-star">*</span>
                                        </label>
                                        <input type="text" name="name" class="form-control" required 
                                               value="<?= htmlspecialchars($customer['name']) ?>"
                                               placeholder="Masukkan nama lengkap / perusahaan">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Nama lengkap atau nama perusahaan
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?= htmlspecialchars($customer['email']) ?>"
                                               placeholder="email@example.com">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Contoh: customer@email.com
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">No. Telepon</label>
                                        <input type="text" name="phone" class="form-control" 
                                               value="<?= htmlspecialchars($customer['phone']) ?>"
                                               placeholder="08123456789">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Nomor yang bisa dihubungi
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea name="address" class="form-control" rows="3" 
                                                  placeholder="Jl. Contoh No. 123, Jakarta"><?= htmlspecialchars($customer['address']) ?></textarea>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Alamat lengkap untuk pengiriman
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="delete_customer.php?id=<?= $customer['id'] ?>" 
                                       class="btn-custom btn-danger-custom"
                                       onclick="return confirm('Yakin ingin menghapus pelanggan ini? Semua data yang terkait akan ikut terhapus.')">
                                        <i class="bi bi-trash"></i> Hapus Pelanggan
                                    </a>
                                </div>
                                <div>
                                    <a href="customers.php" class="btn-custom btn-secondary-custom me-2">
                                        <i class="bi bi-x-lg"></i> Batal
                                    </a>
                                    <button type="submit" class="btn-custom btn-primary-custom">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Related Invoices Preview -->
                <?php
                // Get related invoices count
                $stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                                        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                                        SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count
                                        FROM invoices WHERE customer_id = ?");
                $stmt->execute([$id]);
                $invoiceStats = $stmt->fetch();
                
                if ($invoiceStats['total'] > 0):
                ?>
                <div class="form-card mt-4">
                    <div class="card-header">
                        <h6><i class="bi bi-file-text"></i> Invoice Terkait</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h3 mb-0"><?= $invoiceStats['total'] ?></div>
                                    <small class="text-muted">Total Invoice</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h3 mb-0 text-success"><?= $invoiceStats['paid_count'] ?></div>
                                    <small class="text-muted">Lunas</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h3 mb-0 text-warning"><?= $invoiceStats['unpaid_count'] ?></div>
                                    <small class="text-muted">Belum Dibayar</small>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="invoices.php?customer_id=<?= $id ?>" class="btn-custom btn-secondary-custom">
                                <i class="bi bi-eye"></i> Lihat Semua Invoice
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Format phone number input
        document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Form validation
        function validateForm() {
            const name = document.querySelector('input[name="name"]').value.trim();
            
            if (name === '') {
                alert('Nama pelanggan harus diisi');
                return false;
            }
            
            return true;
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>