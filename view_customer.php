<?php
require_once 'config_database.php';
require_once 'include_functions.php';

$id = $_GET['id'] ?? 0;

// Get customer details
$query = "SELECT * FROM customers WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: customers.php");
    exit();
}

// Get customer statistics - PERBAIKAN: Hapus koma berlebih di akhir SELECT
$query = "SELECT 
            COUNT(*) as total_invoices,
            SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as total_paid,
            SUM(CASE WHEN status = 'unpaid' THEN total ELSE 0 END) as total_unpaid,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_invoices,
            COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_invoices
          FROM invoices 
          WHERE customer_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$stats = $stmt->fetch();

// Get customer invoices
$query = "SELECT * FROM invoices WHERE customer_id = ? ORDER BY invoice_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$invoices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - Detail Pelanggan <?= htmlspecialchars($customer['name']) ?></title>
    
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
            --info-color: #3498db;
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
        
        .btn-success-custom {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .btn-success-custom:hover {
            color: white;
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        }
        
        .btn-warning-custom {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
        }
        
        .btn-warning-custom:hover {
            color: white;
            box-shadow: 0 8px 20px rgba(243, 156, 18, 0.4);
        }
        
        .btn-secondary-custom {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .btn-secondary-custom:hover {
            background: #dee2e6;
            color: #495057;
        }
        
        .btn-info-custom {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
            border: none;
        }
        
        .btn-info-custom:hover {
            background: #4361ee;
            color: white;
        }
        
        /* Customer Header Card */
        .customer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .customer-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .customer-id {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .customer-name {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .customer-contact {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            position: relative;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        
        .contact-item i {
            font-size: 1.2rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-icon.total { background: rgba(67, 97, 238, 0.1); color: #4361ee; }
        .stat-icon.paid { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .stat-icon.unpaid { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        .stat-icon.revenue { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        
        .stat-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            line-height: 1.2;
        }
        
        .stat-sub {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        /* Info Card */
        .info-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
        }
        
        .info-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .info-card .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .info-card .card-header h6 i {
            color: var(--primary-color);
            margin-right: 8px;
        }
        
        .info-card .card-body {
            padding: 1.5rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            gap: 1rem;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .info-content {
            flex: 1;
        }
        
        .info-content .label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .info-content .value {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        /* Table Card */
        .table-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        
        .table-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .table-card .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .table-card .card-body {
            padding: 1.5rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: #f8fafc;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .invoice-number {
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .invoice-number:hover {
            text-decoration: underline;
        }
        
        /* Status Badges */
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-paid {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            box-shadow: 0 3px 10px rgba(46, 204, 113, 0.3);
        }
        
        .badge-unpaid {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            box-shadow: 0 3px 10px rgba(243, 156, 18, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h5 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #adb5bd;
            margin-bottom: 1.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            main {
                padding: 1rem;
            }
            
            .customer-name {
                font-size: 2rem;
            }
            
            .customer-contact {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                        <h1 class="h2 mb-1">Detail Pelanggan</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="customers.php">Pelanggan</a></li>
                                <li class="breadcrumb-item active"><?= htmlspecialchars($customer['name']) ?></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="add_invoice.php?customer_id=<?= $customer['id'] ?>" class="btn-custom btn-success-custom">
                            <i class="bi bi-plus-circle"></i> Buat Invoice
                        </a>
                        <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="btn-custom btn-warning-custom">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="customers.php" class="btn-custom btn-secondary-custom">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Customer Header -->
                <div class="customer-header">
                    <div class="customer-id">
                        <i class="bi bi-hash"></i> ID Pelanggan: #<?= str_pad($customer['id'], 4, '0', STR_PAD_LEFT) ?>
                    </div>
                    <div class="customer-name">
                        <?= htmlspecialchars($customer['name']) ?>
                    </div>
                    <div class="customer-contact">
                        <?php if ($customer['email']): ?>
                        <div class="contact-item">
                            <i class="bi bi-envelope"></i>
                            <?= htmlspecialchars($customer['email']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($customer['phone']): ?>
                        <div class="contact-item">
                            <i class="bi bi-telephone"></i>
                            <?= htmlspecialchars($customer['phone']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="contact-item">
                            <i class="bi bi-calendar"></i>
                            Terdaftar: <?= date('d F Y', strtotime($customer['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="stat-label">Total Invoice</div>
                        <div class="stat-value"><?= $stats['total_invoices'] ?? 0 ?></div>
                        <div class="stat-sub">Semua transaksi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon paid">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-label">Invoice Lunas</div>
                        <div class="stat-value"><?= $stats['paid_invoices'] ?? 0 ?></div>
                        <div class="stat-sub"><?= $stats['paid_invoices'] ?? 0 ?> transaksi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon unpaid">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="stat-label">Belum Dibayar</div>
                        <div class="stat-value"><?= $stats['unpaid_invoices'] ?? 0 ?></div>
                        <div class="stat-sub"><?= $stats['unpaid_invoices'] ?? 0 ?> transaksi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-label">Total Pembayaran</div>
                        <div class="stat-value"><?= formatRupiah($stats['total_paid'] ?? 0) ?></div>
                        <div class="stat-sub">Pendapatan diterima</div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="info-card">
                    <div class="card-header">
                        <h6><i class="bi bi-info-circle"></i> Informasi Lengkap</h6>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="info-content">
                                    <div class="label">Nama Perusahaan / Pribadi</div>
                                    <div class="value"><?= htmlspecialchars($customer['name']) ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <div class="label">Email</div>
                                    <div class="value"><?= htmlspecialchars($customer['email'] ?: '-') ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div class="info-content">
                                    <div class="label">Nomor Telepon</div>
                                    <div class="value"><?= htmlspecialchars($customer['phone'] ?: '-') ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="info-content">
                                    <div class="label">Alamat</div>
                                    <div class="value"><?= nl2br(htmlspecialchars($customer['address'] ?: '-')) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices List -->
                <div class="table-card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6><i class="bi bi-file-text"></i> Daftar Invoice</h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-receipt"></i> <?= count($invoices) ?> invoice
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($invoices) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No. Invoice</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="invoice-number">
                                                    <?= $invoice['invoice_number'] ?>
                                                </a>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                            <td><strong><?= formatRupiah($invoice['total']) ?></strong></td>
                                            <td>
                                                <?php if ($invoice['status'] == 'paid'): ?>
                                                    <span class="badge-status badge-paid">
                                                        <i class="bi bi-check-circle"></i> Lunas
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge-status badge-unpaid">
                                                        <i class="bi bi-clock"></i> Belum Dibayar
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn-custom btn-info-custom" style="padding: 0.3rem 1rem;" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-file-text"></i>
                                <h5>Belum Ada Invoice</h5>
                                <p>Pelanggan ini belum memiliki invoice. Buat invoice sekarang untuk memulai transaksi.</p>
                                <a href="add_invoice.php?customer_id=<?= $customer['id'] ?>" class="btn-custom btn-primary-custom">
                                    <i class="bi bi-plus-circle"></i> Buat Invoice Sekarang
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Financial Summary -->
                <?php if (($stats['total_unpaid'] ?? 0) > 0): ?>
                <div class="info-card mt-4">
                    <div class="card-header">
                        <h6><i class="bi bi-exclamation-triangle text-warning"></i> Ringkasan Keuangan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-exclamation-circle fs-3 me-3"></i>
                                        <div>
                                            <strong>Total Piutang</strong><br>
                                            <span class="h5"><?= formatRupiah($stats['total_unpaid'] ?? 0) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle fs-3 me-3"></i>
                                        <div>
                                            <strong>Total Pembayaran</strong><br>
                                            <span class="h5"><?= formatRupiah($stats['total_paid'] ?? 0) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>