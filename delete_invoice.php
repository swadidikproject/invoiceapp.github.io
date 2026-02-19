<?php
require_once 'config_database.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $pdo->beginTransaction();
        
        // Delete invoice items (automatically deleted with ON DELETE CASCADE)
        // Just delete the invoice
        $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        header("Location: index.php?msg=deleted");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: index.php?error=delete_failed");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>