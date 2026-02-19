<?php
require_once 'config_database.php';
require_once 'include_functions.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "i.status = ?";
    $params[] = $status_filter;
}

if ($date_from) {
    $where_conditions[] = "i.invoice_date >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "i.invoice_date <= ?";
    $params[] = $date_to;
}

if ($search) {
    $where_conditions[] = "(i.invoice_number LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count
$count_query = "SELECT COUNT(*) FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id 
                $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_invoices = $stmt->fetchColumn();
$total_pages = ceil($total_invoices / $limit);

// Get invoices for current page
$query = "SELECT i.*, c.name as customer_name 
          FROM invoices i 
          LEFT JOIN customers c ON i.customer_id = c.id 
          $where_clause 
          ORDER BY i.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$invoices = $stmt->fetchAll();

// Get summary statistics
$summary_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as total_paid,
                    SUM(CASE WHEN status = 'unpaid' THEN total ELSE 0 END) as total_unpaid
                  FROM invoices";
$stmt_summary = $pdo->query($summary_query);
$summary = $stmt_summary->fetch();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - Daftar Invoice</title>
    
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
        
        .btn-create {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
            color: white;
        }
        
        .btn-create i {
            margin-right: 8px;
        }
        
        /* Summary Cards */
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .summary-icon.total { background: rgba(67, 97, 238, 0.1); color: #4361ee; }
        .summary-icon.paid { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .summary-icon.unpaid { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        
        .summary-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            line-height: 1.2;
        }
        
        .summary-sub {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
        }
        
        .filter-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .filter-card .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .filter-card .card-body {
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.3rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
            color: white;
        }
        
        .btn-filter i {
            margin-right: 8px;
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
            color: #495057;
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
            color: #3a56d4;
            text-decoration: underline;
        }
        
        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .item-preview {
            font-size: 0.85rem;
            color: #6c757d;
            background: #f8fafc;
            padding: 0.2rem 0.5rem;
            border-radius: 5px;
            display: inline-block;
            margin-top: 0.3rem;
        }
        
        /* Status Badges */
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        .btn-paid {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .btn-paid:hover {
            background: #2ecc71;
            color: white;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
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
        
        /* Pagination */
        .pagination {
            gap: 5px;
        }
        
        .page-link {
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            color: #6c757d;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .page-link:hover {
            background: #e9ecef;
            color: #4361ee;
            transform: translateY(-2px);
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
            
            .summary-card {
                margin-bottom: 1rem;
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
                            <a class="nav-link active" href="invoices.php">
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
                        <h1 class="h2 mb-1">Daftar Invoice</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Daftar Invoice</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="add_invoice.php" class="btn btn-create">
                        <i class="bi bi-plus-circle"></i> Invoice Baru
                    </a>
                </div>

                <!-- Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-4 col-md-4">
                        <div class="summary-card">
                            <div class="summary-icon total">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="summary-label">Total Invoice</div>
                            <div class="summary-value"><?= number_format($summary['total'] ?? 0, 0, ',', '.') ?></div>
                            <div class="summary-sub">Semua invoice</div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4">
                        <div class="summary-card">
                            <div class="summary-icon paid">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="summary-label">Total Lunas</div>
                            <div class="summary-value"><?= formatRupiah($summary['total_paid'] ?? 0) ?></div>
                            <div class="summary-sub">Invoice lunas</div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4">
                        <div class="summary-card">
                            <div class="summary-icon unpaid">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="summary-label">Belum Dibayar</div>
                            <div class="summary-value"><?= formatRupiah($summary['total_unpaid'] ?? 0) ?></div>
                            <div class="summary-sub">Menunggu pembayaran</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="filter-card">
                    <div class="card-header">
                        <h6><i class="bi bi-funnel me-2"></i>Filter Invoice</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="invoices.php" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="paid" <?= $status_filter == 'paid' ? 'selected' : '' ?>>Lunas</option>
                                    <option value="unpaid" <?= $status_filter == 'unpaid' ? 'selected' : '' ?>>Belum Dibayar</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cari</label>
                                <input type="text" name="search" class="form-control" placeholder="No. Invoice / Pelanggan" value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn-filter">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="table-card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6><i class="bi bi-table me-2"></i>Semua Invoice</h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-file-text me-1"></i>Total: <?= $total_invoices ?> invoice
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No. Invoice</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($invoices) > 0): ?>
                                        <?php foreach($invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="invoice-number">
                                                    <?= $invoice['invoice_number'] ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="customer-name">
                                                    <?= $invoice['customer_name'] ?? '<em class="text-muted">- Pelanggan Umum -</em>' ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                            <td>
                                                <strong><?= formatRupiah($invoice['total']) ?></strong>
                                                <?php
                                                // Get first item for preview
                                                $stmt_item = $pdo->prepare("SELECT description, quantity, unit FROM invoice_items WHERE invoice_id = ? LIMIT 1");
                                                $stmt_item->execute([$invoice['id']]);
                                                $first_item = $stmt_item->fetch();
                                                if ($first_item): ?>
                                                    <div class="item-preview">
                                                        <i class="bi bi-box"></i>
                                                        <?= htmlspecialchars(substr($first_item['description'], 0, 25)) ?>
                                                        <?php if ($first_item['quantity'] > 1): ?>
                                                            (<?= $first_item['quantity'] ?> <?= htmlspecialchars($first_item['unit'] ?? 'item') ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
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
                                            <td>
                                                <div class="action-group">
                                                    <a href="view_invoice.php?id=<?= $invoice['id'] ?>" class="btn-action btn-view" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit_invoice.php?id=<?= $invoice['id'] ?>" class="btn-action btn-edit" title="Edit Invoice">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if($invoice['status'] == 'unpaid'): ?>
                                                    <a href="update_status.php?id=<?= $invoice['id'] ?>&status=paid" 
                                                       class="btn-action btn-paid" 
                                                       onclick="return confirm('Tandai invoice ini sebagai LUNAS?')"
                                                       title="Tandai Lunas">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <a href="delete_invoice.php?id=<?= $invoice['id'] ?>" 
                                                       class="btn-action btn-delete" 
                                                       onclick="return confirm('Yakin ingin menghapus invoice ini? Data yang dihapus tidak dapat dikembalikan.')"
                                                       title="Hapus Invoice">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">
                                                <i class="bi bi-file-text"></i>
                                                <h5>Tidak Ada Invoice</h5>
                                                <p>Belum ada data invoice yang tersedia. Buat invoice baru untuk memulai.</p>
                                                <a href="add_invoice.php" class="btn btn-create">
                                                    <i class="bi bi-plus-circle"></i> Buat Invoice Baru
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page-1 ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&search=<?= urlencode($search) ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i >= $page - 2 && $i <= $page + 2): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&search=<?= urlencode($search) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page+1 ?>&status=<?= $status_filter ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>&search=<?= urlencode($search) ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Optional: Add tooltip initialization -->
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>