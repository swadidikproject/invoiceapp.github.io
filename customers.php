<?php
require_once 'config_database.php';
require_once 'include_functions.php';

// Get all customers
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM invoices WHERE customer_id = c.id) as total_invoices,
          (SELECT SUM(total) FROM invoices WHERE customer_id = c.id AND status = 'paid') as total_paid,
          (SELECT SUM(total) FROM invoices WHERE customer_id = c.id AND status = 'unpaid') as total_unpaid
          FROM customers c 
          ORDER BY c.created_at DESC";
$stmt = $pdo->query($query);
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - Daftar Pelanggan</title>
    
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
        
        .btn-create {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
            color: white;
        }
        
        .btn-create i {
            margin-right: 8px;
        }
        
        /* Alert */
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.1);
        }
        
        /* Search Box */
        .search-box {
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .search-box input {
            padding-left: 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            height: 45px;
        }
        
        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
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
        
        .table-card .card-header h6 i {
            color: var(--primary-color);
            margin-right: 8px;
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
            color: #495057;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .customer-name {
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .customer-name:hover {
            color: #3a56d4;
            text-decoration: underline;
        }
        
        .contact-info {
            font-size: 0.9rem;
        }
        
        .contact-info i {
            width: 20px;
            color: #6c757d;
        }
        
        /* Badges */
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-invoice {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }
        
        /* Action Buttons */
        .action-group {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .btn-view {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
        }
        
        .btn-view:hover {
            background: #4361ee;
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-edit {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }
        
        .btn-edit:hover {
            background: #f39c12;
            color: white;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
        }
        
        .btn-delete {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .btn-delete:hover {
            background: #e74c3c;
            color: white;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        /* Amount Styles */
        .amount-positive {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .amount-negative {
            color: var(--danger-color);
            font-weight: 600;
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
            
            .table-card .card-header .row {
                gap: 1rem;
            }
            
            .action-group {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
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
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
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
                        <h1 class="h2 mb-1">Daftar Pelanggan</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Pelanggan</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="add_customer.php" class="btn-create">
                        <i class="bi bi-plus-circle"></i> Tambah Pelanggan
                    </a>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php 
                            if($_GET['msg'] == 'added') echo 'Pelanggan berhasil ditambahkan!';
                            if($_GET['msg'] == 'updated') echo 'Pelanggan berhasil diperbarui!';
                            if($_GET['msg'] == 'deleted') echo 'Pelanggan berhasil dihapus!';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="table-card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6><i class="bi bi-people"></i> Semua Pelanggan</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="searchCustomer" class="form-control" placeholder="Cari pelanggan...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="customerTable">
                                <thead>
                                    <tr>
                                        <th>Nama & Kontak</th>
                                        <th class="text-center">Total Invoice</th>
                                        <th class="text-end">Total Pembayaran</th>
                                        <th class="text-end">Piutang</th>
                                        <th class="text-center">Terdaftar</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($customers) > 0): ?>
                                        <?php foreach($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <a href="view_customer.php?id=<?= $customer['id'] ?>" class="customer-name">
                                                    <?= htmlspecialchars($customer['name']) ?>
                                                </a>
                                                <div class="contact-info">
                                                    <small>
                                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($customer['email'] ?: '-') ?><br>
                                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($customer['phone'] ?: '-') ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-custom badge-invoice">
                                                    <i class="bi bi-file-text"></i>
                                                    <?= $customer['total_invoices'] ?> Invoice
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="amount-positive">
                                                    <?= formatRupiah($customer['total_paid'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <?php if (($customer['total_unpaid'] ?? 0) > 0): ?>
                                                    <span class="amount-negative">
                                                        <?= formatRupiah($customer['total_unpaid'] ?? 0) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i>
                                                    <?= date('d/m/Y', strtotime($customer['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-group">
                                                    <a href="view_customer.php?id=<?= $customer['id'] ?>" class="btn-action btn-view" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="btn-action btn-edit" title="Edit Pelanggan">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="delete_customer.php?id=<?= $customer['id'] ?>" 
                                                       class="btn-action btn-delete" 
                                                       onclick="return confirm('Yakin ingin menghapus pelanggan ini? Semua invoice terkait akan kehilangan data pelanggan.')"
                                                       title="Hapus Pelanggan">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">
                                                <i class="bi bi-people"></i>
                                                <h5>Belum Ada Pelanggan</h5>
                                                <p>Tambahkan pelanggan pertama Anda untuk memulai.</p>
                                                <a href="add_customer.php" class="btn-create">
                                                    <i class="bi bi-plus-circle"></i> Tambah Pelanggan
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Summary Footer -->
                        <?php if (count($customers) > 0): ?>
                        <div class="mt-3 text-muted small">
                            <i class="bi bi-info-circle"></i>
                            Total: <?= count($customers) ?> pelanggan terdaftar
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Search Functionality -->
    <script>
    document.getElementById('searchCustomer').addEventListener('keyup', function() {
        let input = this.value.toLowerCase();
        let rows = document.querySelectorAll('#customerTable tbody tr');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
        
        // Show/hide empty message
        let visibleRows = document.querySelectorAll('#customerTable tbody tr:not([style*="display: none"])').length;
        let emptyMessage = document.querySelector('#customerTable tbody tr.empty-search');
        
        if (visibleRows === 0) {
            if (!emptyMessage) {
                let tbody = document.querySelector('#customerTable tbody');
                let messageRow = document.createElement('tr');
                messageRow.className = 'empty-search';
                messageRow.innerHTML = `
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-search" style="font-size: 2rem; color: #dee2e6;"></i>
                        <p class="mt-2 text-muted">Tidak ada pelanggan yang cocok dengan pencarian</p>
                    </td>
                `;
                tbody.appendChild(messageRow);
            }
        } else if (emptyMessage) {
            emptyMessage.remove();
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
    </script>
</body>
</html>