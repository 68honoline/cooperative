<?php
require_once 'config/db.php';
require_once 'auth.php';

$db = getDB();
$conn = $db->getConnection();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Members CRUD
    if ($action === 'add_member') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $village = trim($_POST['village'] ?? '');
        $join_date = $_POST['join_date'] ?? date('Y-m-d');
        
        if (!empty($name) && !empty($phone) && !empty($village)) {
            $stmt = $conn->prepare("INSERT INTO members (name, phone, village, join_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $phone, $village, $join_date);
            if ($stmt->execute()) {
                $message = 'Member added successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'edit_member') {
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $village = trim($_POST['village'] ?? '');
        $join_date = $_POST['join_date'] ?? date('Y-m-d');
        
        if (!empty($name) && !empty($phone) && !empty($village) && $id > 0) {
            $stmt = $conn->prepare("UPDATE members SET name = ?, phone = ?, village = ?, join_date = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $phone, $village, $join_date, $id);
            if ($stmt->execute()) {
                $message = 'Member updated successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'delete_member') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Member deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    // Products CRUD
    if ($action === 'add_product') {
        $member_id = $_POST['member_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;
        $price = $_POST['price'] ?? 0;
        $type = trim($_POST['type'] ?? 'Maize');
        
        if ($member_id > 0 && $quantity > 0 && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO products (member_id, quantity, price, type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idds", $member_id, $quantity, $price, $type);
            if ($stmt->execute()) {
                $message = 'Product added successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'edit_product') {
        $id = $_POST['id'] ?? 0;
        $member_id = $_POST['member_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;
        $price = $_POST['price'] ?? 0;
        $type = trim($_POST['type'] ?? 'Maize');
        
        if ($member_id > 0 && $quantity > 0 && $price > 0 && $id > 0) {
            $stmt = $conn->prepare("UPDATE products SET member_id = ?, quantity = ?, price = ?, type = ? WHERE id = ?");
            $stmt->bind_param("iddi", $member_id, $quantity, $price, $type, $id);
            if ($stmt->execute()) {
                $message = 'Product updated successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'delete_product') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Product deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    // Clients CRUD
    if ($action === 'add_client') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (!empty($name) && !empty($phone) && !empty($location)) {
            $stmt = $conn->prepare("INSERT INTO clients (name, phone, location) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $phone, $location);
            if ($stmt->execute()) {
                $message = 'Client added successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'edit_client') {
        $id = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (!empty($name) && !empty($phone) && !empty($location) && $id > 0) {
            $stmt = $conn->prepare("UPDATE clients SET name = ?, phone = ?, location = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $phone, $location, $id);
            if ($stmt->execute()) {
                $message = 'Client updated successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'delete_client') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Client deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    // Set client password
    if ($action === 'set_client_password') {
        $id = $_POST['id'] ?? 0;
        $password = $_POST['password'] ?? '';
        
        if ($id > 0 && !empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE clients SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $id);
            if ($stmt->execute()) {
                $message = 'Password set successfully! Client can now login.';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    // Sales CRUD
    if ($action === 'add_sale') {
        $client_id = $_POST['client_id'] ?? 0;
        $product_id = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;
        $sale_date = $_POST['sale_date'] ?? date('Y-m-d');
        
        // Get product price
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product && $client_id > 0 && $quantity > 0) {
            $total = $quantity * $product['price'];
            
            $stmt = $conn->prepare("INSERT INTO sales (client_id, product_id, quantity, total, sale_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iidds", $client_id, $product_id, $quantity, $total, $sale_date);
            if ($stmt->execute()) {
                // Update product quantity
                $stmt2 = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $stmt2->bind_param("di", $quantity, $product_id);
                $stmt2->execute();
                $stmt2->close();
                
                $message = 'Sale recorded successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
    
    if ($action === 'delete_sale') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Sale deleted successfully!';
                $message_type = 'success';
            }
            $stmt->close();
        }
    }
}

// Get data for display
$members = $conn->query("SELECT * FROM members ORDER BY name");
$products = $conn->query("SELECT p.*, m.name as member_name FROM products p LEFT JOIN members m ON p.member_id = m.id ORDER BY p.created_at DESC");
$clients = $conn->query("SELECT * FROM clients ORDER BY name");
$sales = $conn->query("SELECT s.*, c.name as client_name, p.type as product_type, m.name as member_name 
    FROM sales s 
    LEFT JOIN clients c ON s.client_id = c.id 
    LEFT JOIN products p ON s.product_id = p.id
    LEFT JOIN members m ON p.member_id = m.id
    ORDER BY s.sale_date DESC");

// Get statistics
$total_members = $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_clients = $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT COALESCE(SUM(total), 0) as total FROM sales")->fetch_assoc()['total'];
$total_quantity = $conn->query("SELECT COALESCE(SUM(quantity), 0) as total FROM products")->fetch_assoc()['total'];

// Search functionality
$search = $_GET['search'] ?? '';
$search_results = null;
if (!empty($search)) {
    $search_term = "%$search%";
    $search_results = $conn->query("
        SELECT 'member' as type, id, name, phone, village as location FROM members WHERE name LIKE '$search_term' OR phone LIKE '$search_term'
        UNION ALL
        SELECT 'client' as type, id, name, phone, location FROM clients WHERE name LIKE '$search_term' OR phone LIKE '$search_term'
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UMUHUZA Cooperative</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #333;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 12px 24px;
            background: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .tab-btn:hover, .tab-btn.active {
            background: #667eea;
            color: white;
        }

        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-content.active {
            display: block;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cec;
        }

        .message.error {
            background: #fee;
            color: #c33;
            border: 1px solid #ecc;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

        .actions {
            display: flex;
            gap: 5px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .search-results {
            margin-top: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #333;
        }

        .close {
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .report-section {
            margin-top: 30px;
        }

        .report-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .report-card h3 {
            color: #333;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UMUHUZA Cooperative Management System</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Members</h3>
                <div class="value"><?php echo $total_members; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Products (kg)</h3>
                <div class="value"><?php echo number_format($total_quantity, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Clients</h3>
                <div class="value"><?php echo $total_clients; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Sales (RWF)</h3>
                <div class="value"><?php echo number_format($total_sales, 2); ?></div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search members or clients...">
            <button class="btn" onclick="performSearch()">Search</button>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="search-results" style="display: none;">
            <div class="report-card">
                <h3>Search Results</h3>
                <div id="searchContent"></div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('members')">Members</button>
            <button class="tab-btn" onclick="showTab('products')">Products</button>
            <button class="tab-btn" onclick="showTab('clients')">Clients</button>
            <button class="tab-btn" onclick="showTab('sales')">Sales</button>
            <button class="tab-btn" onclick="showTab('reports')">Reports</button>
        </div>

        <!-- Members Tab -->
        <div id="members" class="tab-content active">
            <h2>Members Management</h2>
            <button class="btn" onclick="showMemberModal()">Add New Member</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Village</th>
                        <th>Join Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $members->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['village']); ?></td>
                        <td><?php echo $row['join_date']; ?></td>
                        <td class="actions">
                            <button class="btn btn-sm" onclick="editMember(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['phone']); ?>', '<?php echo htmlspecialchars($row['village']); ?>', '<?php echo $row['join_date']; ?>')">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                <input type="hidden" name="action" value="delete_member">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content">
            <h2>Products Management</h2>
            <button class="btn" onclick="showProductModal()">Add New Product</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Quantity (kg)</th>
                        <th>Price (RWF)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo number_format($row['quantity'], 2); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td class="actions">
                            <button class="btn btn-sm" onclick="editProduct(<?php echo $row['id']; ?>, <?php echo $row['member_id']; ?>, '<?php echo htmlspecialchars($row['type']); ?>', <?php echo $row['quantity']; ?>, <?php echo $row['price']; ?>)">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Clients Tab -->
        <div id="clients" class="tab-content">
            <h2>Clients Management</h2>
            <button class="btn" onclick="showClientModal()">Add New Client</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Login Access</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $clients->data_seek(0);
                    while ($row = $clients->fetch_assoc()): 
                        $has_login = !empty($row['password']);
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td>
                            <?php if ($has_login): ?>
                                <span style="color: green; font-weight: bold;">✓ Active</span>
                            <?php else: ?>
                                <span style="color: #999;">Not set</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <button class="btn btn-sm" onclick="showPasswordModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                                <?php echo $has_login ? 'Change Password' : 'Set Password'; ?>
                            </button>
                            <button class="btn btn-sm" onclick="editClient(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['phone']); ?>', '<?php echo htmlspecialchars($row['location']); ?>')">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                <input type="hidden" name="action" value="delete_client">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Sales Tab -->
        <div id="sales" class="tab-content">
            <h2>Sales Management</h2>
            <button class="btn" onclick="showSaleModal()">Record New Sale</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Product Type</th>
                        <th>Member</th>
                        <th>Quantity (kg)</th>
                        <th>Total (RWF)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sales->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['sale_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                        <td><?php echo number_format($row['quantity'], 2); ?></td>
                        <td><?php echo number_format($row['total'], 2); ?></td>
                        <td class="actions">
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this sale?');">
                                <input type="hidden" name="action" value="delete_sale">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <h2>Reports</h2>
            
            <div class="report-section">
                <div class="report-card">
                    <h3>Sales Report Summary</h3>
                    <table>
                        <tr>
                            <td><strong>Total Sales Amount:</strong></td>
                            <td><?php echo number_format($total_sales, 2); ?> RWF</td>
                        </tr>
                        <tr>
                            <td><strong>Total Transactions:</strong></td>
                            <td><?php echo $conn->query("SELECT COUNT(*) as count FROM sales")->fetch_assoc()['count']; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="report-card">
                    <h3>Product Summary by Member</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Total Quantity (kg)</th>
                                <th>Total Value (RWF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $product_summary = $conn->query("
                                SELECT m.name, 
                                       SUM(p.quantity) as total_qty, 
                                       SUM(p.quantity * p.price) as total_value
                                FROM products p
                                LEFT JOIN members m ON p.member_id = m.id
                                GROUP BY m.id, m.name
                            ");
                            while ($row = $product_summary->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($row['total_qty'] ?? 0, 2); ?></td>
                                <td><?php echo number_format($row['total_value'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="report-card">
                    <h3>Sales by Client</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Total Purchases (RWF)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $client_sales = $conn->query("
                                SELECT c.name, SUM(s.total) as total_purchases
                                FROM sales s
                                LEFT JOIN clients c ON s.client_id = c.id
                                GROUP BY c.id, c.name
                            ");
                            while ($row = $client_sales->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($row['total_purchases'] ?? 0, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Modal -->
    <div id="memberModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="memberModalTitle">Add Member</h2>
                <span class="close" onclick="closeModal('memberModal')">&times;</span>
            </div>
            <form method="POST" id="memberForm">
                <input type="hidden" name="action" id="memberAction" value="add_member">
                <input type="hidden" name="id" id="memberId" value="">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="memberName" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="memberPhone" required>
                </div>
                <div class="form-group">
                    <label>Village</label>
                    <input type="text" name="village" id="memberVillage" required>
                </div>
                <div class="form-group">
                    <label>Join Date</label>
                    <input type="date" name="join_date" id="memberJoinDate" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">Add Product</h2>
                <span class="close" onclick="closeModal('productModal')">&times;</span>
            </div>
            <form method="POST" id="productForm">
                <input type="hidden" name="action" id="productAction" value="add_product">
                <input type="hidden" name="id" id="productId" value="">
                <div class="form-group">
                    <label>Member</label>
                    <select name="member_id" id="productMemberId" required>
                        <option value="">Select Member</option>
                        <?php 
                        $members->data_seek(0);
                        while ($row = $members->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Type</label>
                    <input type="text" name="type" id="productType" value="Maize" required>
                </div>
                <div class="form-group">
                    <label>Quantity (kg)</label>
                    <input type="number" step="0.01" name="quantity" id="productQuantity" required>
                </div>
                <div class="form-group">
                    <label>Price per kg (RWF)</label>
                    <input type="number" step="0.01" name="price" id="productPrice" required>
                </div>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <!-- Client Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="clientModalTitle">Add Client</h2>
                <span class="close" onclick="closeModal('clientModal')">&times;</span>
            </div>
            <form method="POST" id="clientForm">
                <input type="hidden" name="action" id="clientAction" value="add_client">
                <input type="hidden" name="id" id="clientId" value="">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="clientName" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="clientPhone" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" id="clientLocation" required>
                </div>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <!-- Client Password Modal -->
    <div id="clientPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Set Client Password</h2>
                <span class="close" onclick="closeModal('clientPasswordModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="set_client_password">
                <input type="hidden" name="id" id="passwordClientId" value="">
                <p style="margin-bottom: 15px; color: #666;">
                    Set password for client: <strong id="passwordClientName"></strong>
                </p>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters">
                </div>
                <button type="submit" class="btn">Set Password</button>
            </form>
        </div>
    </div>

    <!-- Sale Modal -->
    <div id="saleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Record Sale</h2>
                <span class="close" onclick="closeModal('saleModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_sale">
                <div class="form-group">
                    <label>Client</label>
                    <select name="client_id" required>
                        <option value="">Select Client</option>
                        <?php 
                        $clients->data_seek(0);
                        while ($row = $clients->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product</label>
                    <select name="product_id" required>
                        <option value="">Select Product</option>
                        <?php 
                        $products->data_seek(0);
                        while ($row = $products->fetch_assoc()): 
                            if ($row['quantity'] > 0): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['member_name'] . ' - ' . $row['type'] . ' (' . $row['quantity'] . ' kg)'); ?></option>
                        <?php 
                            endif;
                        endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity (kg)</label>
                    <input type="number" step="0.01" name="quantity" required>
                </div>
                <div class="form-group">
                    <label>Sale Date</label>
                    <input type="date" name="sale_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" class="btn">Record Sale</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function showMemberModal() {
            document.getElementById('memberModalTitle').textContent = 'Add Member';
            document.getElementById('memberAction').value = 'add_member';
            document.getElementById('memberId').value = '';
            document.getElementById('memberForm').reset();
            document.getElementById('memberModal').classList.add('active');
        }

        function editMember(id, name, phone, village, joinDate) {
            document.getElementById('memberModalTitle').textContent = 'Edit Member';
            document.getElementById('memberAction').value = 'edit_member';
            document.getElementById('memberId').value = id;
            document.getElementById('memberName').value = name;
            document.getElementById('memberPhone').value = phone;
            document.getElementById('memberVillage').value = village;
            document.getElementById('memberJoinDate').value = joinDate;
            document.getElementById('memberModal').classList.add('active');
        }

        function showProductModal() {
            document.getElementById('productModalTitle').textContent = 'Add Product';
            document.getElementById('productAction').value = 'add_product';
            document.getElementById('productId').value = '';
            document.getElementById('productForm').reset();
            document.getElementById('productModal').classList.add('active');
        }

        function editProduct(id, memberId, type, quantity, price) {
            document.getElementById('productModalTitle').textContent = 'Edit Product';
            document.getElementById('productAction').value = 'edit_product';
            document.getElementById('productId').value = id;
            document.getElementById('productMemberId').value = memberId;
            document.getElementById('productType').value = type;
            document.getElementById('productQuantity').value = quantity;
            document.getElementById('productPrice').value = price;
            document.getElementById('productModal').classList.add('active');
        }

        function showClientModal() {
            document.getElementById('clientModalTitle').textContent = 'Add Client';
            document.getElementById('clientAction').value = 'add_client';
            document.getElementById('clientId').value = '';
            document.getElementById('clientForm').reset();
            document.getElementById('clientModal').classList.add('active');
        }

        function editClient(id, name, phone, location) {
            document.getElementById('clientModalTitle').textContent = 'Edit Client';
            document.getElementById('clientAction').value = 'edit_client';
            document.getElementById('clientId').value = id;
            document.getElementById('clientName').value = name;
            document.getElementById('clientPhone').value = phone;
            document.getElementById('clientLocation').value = location;
            document.getElementById('clientModal').classList.add('active');
        }

        function showPasswordModal(id, name) {
            document.getElementById('passwordClientId').value = id;
            document.getElementById('passwordClientName').textContent = name;
            document.getElementById('clientPasswordModal').classList.add('active');
        }

        function showSaleModal() {
            document.getElementById('saleModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function performSearch() {
            const search = document.getElementById('searchInput').value;
            if (search.length > 0) {
                window.location.href = 'dashboard.php?search=' + encodeURIComponent(search);
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
