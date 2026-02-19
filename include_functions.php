<?php
function generateInvoiceNumber($pdo) {
    $year = date('Y');
    $month = date('m');
    
    $query = "SELECT COUNT(*) as total FROM invoices WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$year, $month]);
    $result = $stmt->fetch();
    
    $number = $result['total'] + 1;
    $invoiceNumber = 'INV/PGM/' . $year . $month . str_pad($number, 4, '0', STR_PAD_LEFT);
    
    return $invoiceNumber;
}

function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function getStatusBadge($status) {
    switch($status) {
        case 'paid':
            return '<span class="badge bg-success">Lunas</span>';
        case 'unpaid':
            return '<span class="badge bg-warning">Belum Dibayar</span>';
        default:
            return '<span class="badge bg-secondary">' . $status . '</span>';
    }
}

function getCustomers($pdo) {
    $stmt = $pdo->query("SELECT * FROM customers ORDER BY name");
    return $stmt->fetchAll();
}



function getCustomerStats($pdo, $customer_id) {
    $query = "SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = 'paid' THEN total ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = 'unpaid' THEN total ELSE 0 END) as total_unpaid
              FROM invoices 
              WHERE customer_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$customer_id]);
    return $stmt->fetch();
}

function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

function getInvoiceCountByStatus($pdo, $status = null) {
    $query = "SELECT COUNT(*) FROM invoices";
    if ($status) {
        $query .= " WHERE status = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query($query);
    }
    return $stmt->fetchColumn();
}

function getRecentInvoices($pdo, $limit = 5) {
    $query = "SELECT i.*, c.name as customer_name 
              FROM invoices i 
              LEFT JOIN customers c ON i.customer_id = c.id 
              ORDER BY i.created_at DESC 
              LIMIT ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function generateCustomerCode() {
    $prefix = 'CUST';
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    $random = rand(100, 999);
    return $prefix . $year . $month . $day . $random;
}
?>