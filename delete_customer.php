<?php
require_once 'config_database.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        // Set customer_id to NULL in invoices
        $stmt = $pdo->prepare("UPDATE invoices SET customer_id = NULL WHERE customer_id = ?");
        $stmt->execute([$id]);
        
        // Delete customer
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: customers.php?msg=deleted");
        exit();
    } catch (Exception $e) {
        header("Location: customers.php?error=delete_failed");
        exit();
    }
} else {
    header("Location: customers.php");
    exit();
}
?>