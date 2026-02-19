<?php
require_once 'config_database.php';

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

if ($id && $status) {
    try {
        $query = "UPDATE invoices SET status = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$status, $id]);
        
        header("Location: invoices.php?msg=status_updated");
        exit();
    } catch (Exception $e) {
        header("Location: invoices.php?error=status_failed");
        exit();
    }
} else {
    header("Location: invoices.php");
    exit();
}
?>