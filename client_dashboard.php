<?php
require_once 'config/db.php';
require_once 'auth.php';

// Check if user is a client
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit;
}

$db = getDB();
$conn = $db->getConnection();

$client_id = $_SESSION['user_id'];
$client_name = $_SESSION['username'];

// Get client's purchase history
$sales = $conn->query("
    SELECT s.*, p.type as product_type, m.name as member_name
    FROM sales s
    LEFT JOIN products p ON s.product_id = p.id
    LEFT JOIN members m ON p.member_id = m.id
    WHERE s.client_id = $client_id
    ORDER BY s.sale_date DESC
");

// Get client info
$client_info = $conn->query("SELECT * FROM clients WHERE id = $client_id")->fetch_assoc();

// Calculate totals
$total_purchases = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM sales WHERE client_id = $client_id")->fetch_assoc()['total'];
$total_quantity = $conn->query("SELECT COALESCE(SUM(quantity), 0) as total FROM sales WHERE client_id = $client_id")->fetch_assoc()['total'];
$purchase_count = $conn->query("SELECT COUNT(*) as count FROM sales WHERE client_id = $client_id")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - UMUHUZA Cooperative</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
        }

        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .header .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-banner {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .welcome-banner h2 {
            color: #27ae60;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            color: #666;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #27ae60;
        }

        .content-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .content-card h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #27ae60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .contact-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .contact-item label {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .contact-item span {
            color: #333;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UMUHUZA Cooperative - Client Portal</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($client_name); ?>!</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-banner">
            <h2>Welcome to Your Dashboard</h2>
            <p>Track your purchases and view your transaction history with UMUHUZA Cooperative.</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Purchases</h3>
                <div class="value"><?php echo number_format($total_purchases, 2); ?> RWF</div>
            </div>
            <div class="stat-card">
                <h3>Total Quantity (kg)</h3>
                <div class="value"><?php echo number_format($total_quantity, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <div class="value"><?php echo $purchase_count; ?></div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="content-card">
            <h3>Your Information</h3>
            <div class="contact-info">
                <div class="contact-item">
                    <label>Company/Client Name</label>
                    <span><?php echo htmlspecialchars($client_info['name']); ?></span>
                </div>
                <div class="contact-item">
                    <label>Phone</label>
                    <span><?php echo htmlspecialchars($client_info['phone']); ?></span>
                </div>
                <div class="contact-item">
                    <label>Location</label>
                    <span><?php echo htmlspecialchars($client_info['location']); ?></span>
                </div>
            </div>
        </div>

        <!-- Purchase History -->
        <div class="content-card">
            <h3>Your Purchase History</h3>
            <?php if ($sales && $sales->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product Type</th>
                            <th>From Member</th>
                            <th>Quantity (kg)</th>
                            <th>Total (RWF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $sales->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['sale_date']; ?></td>
                            <td><?php echo htmlspecialchars($row['product_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                            <td><?php echo number_format($row['quantity'], 2); ?></td>
                            <td><?php echo number_format($row['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666; text-align: center; padding: 30px;">No purchase history yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
