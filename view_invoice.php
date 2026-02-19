<?php
require_once 'config_database.php';
require_once 'include_functions.php';

$id = $_GET['id'] ?? 0;

// Get invoice details
$query = "SELECT i.*, c.name as customer_name, c.email, c.phone, c.address 
          FROM invoices i 
          LEFT JOIN customers c ON i.customer_id = c.id 
          WHERE i.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    header("Location: index.php");
    exit();
}

// Get invoice items
$query = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - <?= $invoice['invoice_number'] ?></title>
    
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
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-success-custom {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .btn-warning-custom {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
        }
        
        .btn-info-custom {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary-custom {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .btn-secondary-custom:hover {
            background: #dee2e6;
            color: #495057;
        }
        
        /* Invoice Card */
        .invoice-card {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            color: white;
        }
        
        .invoice-title {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
        }
        
        .invoice-number {
            font-size: 1.5rem;
            font-weight: 600;
            opacity: 0.9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-paid {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        
        .status-unpaid {
            background: rgba(243, 156, 18, 0.2);
            color: #f39c12;
            border: 1px solid #f39c12;
        }
        
        /* Customer Info */
        .customer-info {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .customer-info h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .customer-detail {
            margin-bottom: 0.5rem;
            color: #6c757d;
        }
        
        .customer-detail i {
            width: 25px;
            color: var(--primary-color);
        }
        
        /* Items Table */
        .invoice-items {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .invoice-items table {
            margin-bottom: 0;
        }
        
        .invoice-items thead th {
            background: #f8fafc;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
        }
        
        .invoice-items tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .invoice-items tfoot {
            background: #f8fafc;
            font-weight: 600;
        }
        
        .invoice-items tfoot td {
            padding: 1rem;
        }
        
        .unit-badge {
            background: #e9ecef;
            color: #6c757d;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Totals */
        .totals-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .total-row:last-child {
            border-bottom: none;
        }
        
        .total-label {
            font-weight: 500;
            opacity: 0.9;
        }
        
        .total-value {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .grand-total {
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        /* Notes */
        .notes-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .notes-section h6 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .page-header .btn-custom, .btn-custom, .breadcrumb {
                display: none !important;
            }
            
            .col-md-10 {
                width: 100% !important;
                margin-left: 0 !important;
            }
            
            .invoice-card {
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
            
            .invoice-header {
                background: #f8fafc !important;
                color: black;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .totals-section {
                background: #f8fafc !important;
                color: black;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            main {
                padding: 1rem;
            }
            
            .invoice-title {
                font-size: 2rem;
            }
            
            .invoice-number {
                font-size: 1.2rem;
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
                            <a class="nav-link" href="customers.php">
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
                        
                        
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="invoices.php">Invoice</a></li>
                                <li class="breadcrumb-item active"><?= $invoice['invoice_number'] ?></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn-custom btn-success-custom">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                        <a href="edit_invoice.php?id=<?= $invoice['id'] ?>" class="btn-custom btn-warning-custom">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="invoices.php" class="btn-custom btn-secondary-custom">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Invoice berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Invoice Card -->
                <div class="invoice-card">
                    <!-- Header -->
                    <div class="invoice-header">
                        <div class="row">
                            <div class="col-8">
                                <div class="invoice-title">INVOICE</div>
                                <div class="invoice-number">#<?= $invoice['invoice_number'] ?></div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="status-badge <?= $invoice['status'] == 'paid' ? 'status-paid' : 'status-unpaid' ?>">
                                    <i class="bi <?= $invoice['status'] == 'paid' ? 'bi-check-circle' : 'bi-clock' ?>"></i>
                                    <?= $invoice['status'] == 'paid' ? 'LUNAS' : 'BELUM DIBAYAR' ?>
                                </div>
                                <div class="mt-3">
                                    <small>Tanggal Invoice</small><br>
                                    <strong><?= date('d F Y', strtotime($invoice['invoice_date'])) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4">
                        <!-- Customer Info -->
                        <div class="customer-info">
                            <h5><i class="bi bi-person"></i> Tagihan Kepada:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="customer-detail">
                                        <i class="bi bi-building"></i> 
                                        <strong><?= $invoice['customer_name'] ?? 'Pelanggan Umum' ?></strong>
                                    </div>
                                    <?php if ($invoice['email']): ?>
                                    <div class="customer-detail">
                                        <i class="bi bi-envelope"></i> <?= $invoice['email'] ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($invoice['phone']): ?>
                                    <div class="customer-detail">
                                        <i class="bi bi-telephone"></i> <?= $invoice['phone'] ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($invoice['address']): ?>
                                    <div class="customer-detail">
                                        <i class="bi bi-geo-alt"></i> <?= nl2br($invoice['address']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="invoice-items">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['description']) ?></td>
                                        <td class="text-center"><?= (int)$item['quantity'] ?></td>
                                        <td class="text-center">
                                            <span class="unit-badge">
                                                <?= htmlspecialchars($item['unit'] ?? 'Unit') ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?= formatRupiah($item['price']) ?></td>
                                        <td class="text-end"><?= formatRupiah($item['subtotal']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="totals-section">
                                    <div class="total-row">
                                        <span class="total-label">Subtotal</span>
                                        <span class="total-value"><?= formatRupiah($invoice['subtotal']) ?></span>
                                    </div>
                                    <div class="total-row">
                                        <span class="total-label">Pajak</span>
                                        <span class="total-value"><?= formatRupiah($invoice['tax']) ?></span>
                                    </div>
                                    <div class="total-row">
                                        <span class="total-label">Total</span>
                                        <span class="total-value grand-total"><?= formatRupiah($invoice['total']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <?php if($invoice['notes']): ?>
                        <div class="notes-section">
                            <h6><i class="bi bi-pencil"></i> Catatan:</h6>
                            <p class="mb-0"><?= nl2br($invoice['notes']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto hide alert after 3 seconds
        setTimeout(function() {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
</body>
</html>