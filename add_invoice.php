<?php
require_once 'config_database.php';
require_once 'include_functions.php';

$customers = getCustomers($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Generate invoice number
        $invoiceNumber = generateInvoiceNumber($pdo);
        
        // Insert invoice
        $query = "INSERT INTO invoices (invoice_number, customer_id, invoice_date, subtotal, tax, total, status, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        
        $subtotal = (float)$_POST['subtotal'];
        $tax = (float)($_POST['tax'] ?? 0);
        $total = $subtotal + $tax;
        
        $stmt->execute([
            $invoiceNumber,
            $_POST['customer_id'] ?: null,
            $_POST['invoice_date'],
            $subtotal,
            $tax,
            $total,
            $_POST['status'],
            $_POST['notes']
        ]);
        
        $invoiceId = $pdo->lastInsertId();
        
        // Insert invoice items
        $descriptions = $_POST['description'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['price'];
        $units = $_POST['unit'] ?? [];
        
        $itemQuery = "INSERT INTO invoice_items (invoice_id, description, quantity, unit, price, subtotal) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $itemStmt = $pdo->prepare($itemQuery);
        
        for ($i = 0; $i < count($descriptions); $i++) {
            if (!empty($descriptions[$i])) {
                $itemSubtotal = (float)$quantities[$i] * (float)$prices[$i];
                $itemStmt->execute([
                    $invoiceId,
                    $descriptions[$i],
                    (int)$quantities[$i],
                    $units[$i] ?? 'Unit',
                    (float)$prices[$i],
                    $itemSubtotal
                ]);
            }
        }
        
        $pdo->commit();
        header("Location: view_invoice.php?id=$invoiceId");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice App - Buat Invoice Baru</title>
    
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
        
        .breadcrumb-item.active {
            color: #6c757d;
        }
        
        /* Form Card */
        .form-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        
        .form-card .card-body {
            padding: 2rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e9ecef;
        }
        
        .section-title i {
            color: var(--primary-color);
            margin-right: 8px;
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
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.1);
        }
        
        .input-group-text {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: #f8fafc;
            font-weight: 600;
            color: #6c757d;
        }
        
        /* Items Table */
        .items-table {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .items-table table {
            margin-bottom: 0;
        }
        
        .items-table thead th {
            background: #f8fafc;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
        }
        
        .items-table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .items-table tfoot td {
            background: #f8fafc;
            padding: 0.75rem;
        }
        
        .btn-add-item {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .btn-add-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .btn-remove-item {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: none;
        }
        
        .btn-remove-item:hover {
            background: var(--danger-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        /* Summary Section */
        .summary-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #e9ecef;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .summary-value {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 1.1rem;
        }
        
        .summary-value.total {
            font-size: 1.3rem;
            color: var(--primary-color);
        }
        
        /* Action Buttons */
        .btn-action {
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
            color: white;
        }
        
        .btn-cancel {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .btn-cancel:hover {
            background: #dee2e6;
            color: #495057;
            transform: translateY(-2px);
        }
        
        /* Alert */
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.1);
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
            
            .items-table {
                overflow-x: auto;
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
                            <a class="nav-link active" href="add_invoice.php">
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
                        <h1 class="h2 mb-1">Buat Invoice Baru</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="invoices.php">Invoice</a></li>
                                <li class="breadcrumb-item active">Buat Baru</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <div class="card-body">
                        <form method="POST" id="invoiceForm">
                            <!-- Customer Information -->
                            <div class="section-title">
                                <i class="bi bi-person"></i> Informasi Pelanggan
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Pelanggan</label>
                                        <select name="customer_id" class="form-select">
                                            <option value="">-- Pilih Pelanggan --</option>
                                            <?php foreach($customers as $customer): ?>
                                                <option value="<?= $customer['id'] ?>">
                                                    <?= htmlspecialchars($customer['name']) ?> - <?= htmlspecialchars($customer['phone']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> 
                                            Kosongkan untuk pelanggan umum
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Information -->
                            <div class="section-title">
                                <i class="bi bi-file-text"></i> Informasi Invoice
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal Invoice</label>
                                        <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="unpaid">Belum Dibayar</option>
                                            <option value="paid">Lunas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="section-title">
                                <i class="bi bi-cart"></i> Item Invoice
                            </div>
                            
                            <div class="items-table">
                                <table class="table" id="invoiceItems">
                                    <thead>
                                        <tr>
                                            <th width="35%">Deskripsi</th>
                                            <th width="10%">Qty</th>
                                            <th width="12%">Satuan</th>
                                            <th width="18%">Harga</th>
                                            <th width="18%">Subtotal</th>
                                            <th width="7%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" name="description[]" class="form-control" 
                                                       placeholder="Nama item / deskripsi" required>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control quantity" 
                                                       value="1" min="1" step="1" required>
                                            </td>
                                            <td>
                                                <input type="text" name="unit[]" class="form-control unit" 
                                                       placeholder="Unit" value="Unit">
                                            </td>
                                            <td>
                                                <input type="number" name="price[]" class="form-control price" 
                                                       value="0" min="0" step="1000" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control subtotal" readonly 
                                                       placeholder="Subtotal">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn-remove-item" onclick="removeItem(this)" title="Hapus item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <button type="button" class="btn-add-item" onclick="addItem()" title="Tambah item">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Summary -->
                            <div class="summary-section">
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <div class="summary-row">
                                            <span class="summary-label">Subtotal</span>
                                            <span class="summary-value" id="subtotalDisplay">Rp 0</span>
                                            <input type="hidden" name="subtotal" id="subtotal">
                                        </div>
                                        <div class="summary-row">
                                            <span class="summary-label">Pajak</span>
                                            <span class="summary-value">
                                                <div class="input-group" style="width: 150px;">
                                                    <input type="number" name="tax" id="tax" class="form-control" value="0" min="0" step="0.1" style="text-align: right;">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </span>
                                        </div>
                                        <div class="summary-row">
                                            <span class="summary-label">Total</span>
                                            <span class="summary-value total" id="totalDisplay">Rp 0</span>
                                            <input type="hidden" name="total" id="total">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-pencil"></i> Catatan
                                </label>
                                <textarea name="notes" class="form-control" rows="3" 
                                          placeholder="Catatan tambahan untuk invoice..."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-end">
                                <a href="index.php" class="btn-action btn-cancel me-2">
                                    <i class="bi bi-x-lg"></i> Batal
                                </a>
                                <button type="submit" class="btn-action btn-save">
                                    <i class="bi bi-check-lg"></i> Simpan Invoice
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function addItem() {
        const tbody = document.querySelector('#invoiceItems tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" name="description[]" class="form-control" 
                       placeholder="Nama item / deskripsi" required>
            </td>
            <td>
                <input type="number" name="quantity[]" class="form-control quantity" 
                       value="1" min="1" step="1" required>
            </td>
            <td>
                <input type="text" name="unit[]" class="form-control unit" 
                       placeholder="Unit" value="Unit">
            </td>
            <td>
                <input type="number" name="price[]" class="form-control price" 
                       value="0" min="0" step="1000" required>
            </td>
            <td>
                <input type="text" class="form-control subtotal" readonly 
                       placeholder="Subtotal">
            </td>
            <td class="text-center">
                <button type="button" class="btn-remove-item" onclick="removeItem(this)" title="Hapus item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        
        // Add event listeners to new row
        row.querySelector('.quantity').addEventListener('input', calculateRow);
        row.querySelector('.price').addEventListener('input', calculateRow);
    }

    function removeItem(button) {
        const tbody = document.querySelector('#invoiceItems tbody');
        if (tbody.children.length > 1) {
            button.closest('tr').remove();
            calculateTotal();
        } else {
            alert('Minimal harus ada satu item');
        }
    }

    function calculateRow(event) {
        const row = event.target.closest('tr');
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const subtotal = quantity * price;
        row.querySelector('.subtotal').value = formatNumber(subtotal);
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.subtotal').forEach(input => {
            const value = parseFloat(input.value.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
            subtotal += value;
        });
        
        document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
        document.getElementById('subtotal').value = subtotal;
        
        const taxRate = parseFloat(document.getElementById('tax').value) || 0;
        const tax = subtotal * (taxRate / 100);
        const total = subtotal + tax;
        
        document.getElementById('totalDisplay').textContent = formatRupiah(total);
        document.getElementById('total').value = total;
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    }

    // Add event listeners to existing rows
    document.querySelectorAll('.quantity, .price').forEach(input => {
        input.addEventListener('input', calculateRow);
    });
    
    document.getElementById('tax').addEventListener('input', calculateTotal);
    
    // Initial calculation
    calculateTotal();
    </script>
</body>
</html>