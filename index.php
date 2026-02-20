<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - Dashboard</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
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
        
        .breadcrumb-item.active {
            color: #6c757d;
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
            font-size: 1.1rem;
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
        .summary-icon.revenue { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        
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
        
        /* Chart Card */
        .chart-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
        }
        
        .chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .chart-card .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }
        
        .chart-card .card-body {
            padding: 1.5rem;
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
        
        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
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
        
        .welcome-section h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .welcome-section p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
        }
        
        .welcome-section i {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            font-size: 5rem;
            opacity: 0.1;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .quick-action-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }
        
        .quick-action-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-2px);
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
            
            .welcome-section h2 {
                font-size: 1.5rem;
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
                            <a class="nav-link active" href="#">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-plus-circle"></i> Buat Invoice
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-people"></i> Pelanggan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-file-text"></i> Daftar Invoice
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h2>Selamat Datang di Invoice App</h2>
                    <p>Kelola invoice dan pelanggan Anda dengan mudah dan efisien</p>
                    <i class="bi bi-receipt"></i>
                    <div class="quick-actions">
                        <a href="#" class="quick-action-btn">
                            <i class="bi bi-plus-circle"></i> Buat Invoice Baru
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="bi bi-people"></i> Kelola Pelanggan
                        </a>
                    </div>
                </div>

                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-1">Dashboard</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                <li class="breadcrumb-item active">Overview</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="#" class="btn-create">
                        <i class="bi bi-plus-circle"></i> Invoice Baru
                    </a>
                </div>

                <!-- Statistics Cards (Contoh Data Statis) -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="summary-card">
                            <div class="summary-icon total">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="summary-label">Total Invoice</div>
                            <div class="summary-value">1.250</div>
                            <div class="summary-sub">Semua invoice</div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="summary-card">
                            <div class="summary-icon paid">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="summary-label">Lunas</div>
                            <div class="summary-value">980</div>
                            <div class="summary-sub">Invoice terbayar</div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="summary-card">
                            <div class="summary-icon unpaid">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="summary-label">Belum Dibayar</div>
                            <div class="summary-value">270</div>
                            <div class="summary-sub">Menunggu pembayaran</div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="summary-card">
                            <div class="summary-icon revenue">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="summary-label">Total Pendapatan</div>
                            <div class="summary-value">Rp 450.500.000</div>
                            <div class="summary-sub">Dari invoice lunas</div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="chart-card">
                    <div class="card-header">
                        <h6><i class="bi bi-graph-up"></i> Statistik 6 Bulan Terakhir</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" style="height: 300px;"></canvas>
                    </div>
                </div>

                <!-- Recent Invoices Table -->
                <div class="table-card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6><i class="bi bi-clock-history"></i> Invoice Terbaru</h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i> Lihat Semua
                                </a>
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
                                    <!-- Data Invoice Contoh -->
                                    <tr>
                                        <td>
                                            <a href="#" class="invoice-number">INV-2024-001</a>
                                        </td>
                                        <td>
                                            <span class="customer-name">PT Maju Jaya</span>
                                        </td>
                                        <td>15/01/2024</td>
                                        <td>
                                            <strong>Rp 15.750.000</strong>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-paid">
                                                <i class="bi bi-check-circle"></i> Lunas
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="#" class="btn-action btn-view" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-edit" title="Edit Invoice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-delete" 
                                                   onclick="return confirm('Yakin ingin menghapus invoice ini? Data yang dihapus tidak dapat dikembalikan.')"
                                                   title="Hapus Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="#" class="invoice-number">INV-2024-002</a>
                                        </td>
                                        <td>
                                            <span class="customer-name">CV Sukses Abadi</span>
                                        </td>
                                        <td>14/01/2024</td>
                                        <td>
                                            <strong>Rp 8.450.000</strong>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-unpaid">
                                                <i class="bi bi-clock"></i> Belum Dibayar
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="#" class="btn-action btn-view" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-edit" title="Edit Invoice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-delete" 
                                                   onclick="return confirm('Yakin ingin menghapus invoice ini? Data yang dihapus tidak dapat dikembalikan.')"
                                                   title="Hapus Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="#" class="invoice-number">INV-2024-003</a>
                                        </td>
                                        <td>
                                            <span class="customer-name">Toko Berkah</span>
                                        </td>
                                        <td>13/01/2024</td>
                                        <td>
                                            <strong>Rp 22.300.000</strong>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-paid">
                                                <i class="bi bi-check-circle"></i> Lunas
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="#" class="btn-action btn-view" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-edit" title="Edit Invoice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-delete" 
                                                   onclick="return confirm('Yakin ingin menghapus invoice ini? Data yang dihapus tidak dapat dikembalikan.')"
                                                   title="Hapus Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="#" class="invoice-number">INV-2024-004</a>
                                        </td>
                                        <td>
                                            <span class="customer-name">UD Makmur</span>
                                        </td>
                                        <td>12/01/2024</td>
                                        <td>
                                            <strong>Rp 5.950.000</strong>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-paid">
                                                <i class="bi bi-check-circle"></i> Lunas
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="#" class="btn-action btn-view" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-edit" title="Edit Invoice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-delete" 
                                                   onclick="return confirm('Yakin ingin menghapus invoice ini? Data yang dihapus tidak dapat dikembalikan.')"
                                                   title="Hapus Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a href="#" class="invoice-number">INV-2024-005</a>
                                        </td>
                                        <td>
                                            <span class="customer-name">PT Sentosa</span>
                                        </td>
                                        <td>11/01/2024</td>
                                        <td>
                                            <strong>Rp 18.250.000</strong>
                                        </td>
                                        <td>
                                            <span class="badge-status badge-unpaid">
                                                <i class="bi bi-clock"></i> Belum Dibayar
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="#" class="btn-action btn-view" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-edit" title="Edit Invoice">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" class="btn-action btn-delete" 
                                                   onclick="return confirm('Yakin ingin menghapus invoice ini? Data yang dihapus tidak dapat dikembalikan.')"
                                                   title="Hapus Invoice">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Data contoh untuk chart
            const months = ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06'];
            const revenue = [85000000, 92000000, 78000000, 110000000, 95000000, 125000000];
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months.map(month => {
                        const [year, monthNum] = month.split('-');
                        return `Bulan ${monthNum}/${year}`;
                    }),
                    datasets: [{
                        label: 'Pendapatan',
                        data: revenue,
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    
    <!-- Tooltip Initialization -->
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>
