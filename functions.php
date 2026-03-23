<?php
// profile
function get_user_orders($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get product by ID
function get_product_by_id($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ? AND p.deleted = 0
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_user_by_id($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_order_by_id($conn, $order_id) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_order_items($conn, $order_id) {
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function complete_order($conn, $order_id) {
    $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    return $stmt->execute([$order_id]);
}

function get_all_products($conn) {
    $stmt = $conn->query("SELECT * FROM products WHERE deleted = 0");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function update_product($conn, $product_id, $name, $description, $price, $stock, $category_id) {
    $stmt = $conn->prepare("
        UPDATE products 
        SET name = ?, description = ?, price = ?, stock = ?, category_id = ? 
        WHERE id = ? AND deleted = 0
    ");
    return $stmt->execute([$name, $description, $price, $stock, $category_id, $product_id]);
}

function delete_product($conn, $product_id) {
    // Implement soft delete by setting the deleted flag to 1
    $stmt = $conn->prepare("UPDATE products SET deleted = 1 WHERE id = ?");
    return $stmt->execute([$product_id]);
}

function get_products($conn, $category = null, $search = null) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'approved' AND p.deleted = 0";
    $params = [];

    if ($category) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category;
    }

    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_categories($conn) {
    $stmt = $conn->query("SELECT * FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_to_cart($conn, $user_id, $product_id, $quantity) {
    // Check if product is not deleted before adding to cart
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ? AND deleted = 0");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $product_id, $quantity]);
}

function get_cart_items($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.image, p.id as product_id 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? AND p.deleted = 0
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function create_order($conn, $user_id, $total_amount) {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$user_id, $total_amount]);
    return $conn->lastInsertId();
}

function add_order_item($conn, $order_id, $product_id, $quantity, $price) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$order_id, $product_id, $quantity, $price]);
}   

function clear_cart($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
}

function authenticate_user($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return false;
}

function register_user($conn, $name, $email, $password, $user_type) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$name, $email, $hashed_password, $user_type]);

    if ($result) {
        return $conn->lastInsertId();
    }

    return false;
}

function get_pending_products($conn) {
    $stmt = $conn->query("SELECT * FROM products WHERE status = 'pending' AND deleted = 0");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_users($conn) {
    $stmt = $conn->query("SELECT * FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_total_sales($conn) {
    $stmt = $conn->query("
        SELECT SUM(total_amount) as total_sales 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN products p ON oi.product_id = p.id 
        WHERE p.deleted = 0
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_sales'] ?? 0;
}

function get_total_orders($conn) {
    $stmt = $conn->query("
        SELECT COUNT(DISTINCT o.id) as total_orders 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN products p ON oi.product_id = p.id 
        WHERE p.deleted = 0
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_orders'] ?? 0;
}

function get_vendor_products($conn, $vendor_id) {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.vendor_id = ? AND p.deleted = 0
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$vendor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_vendor_sales($conn, $vendor_id) {
    $stmt = $conn->prepare("
        SELECT SUM(oi.price * oi.quantity) as total_sales 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE p.vendor_id = ? AND p.deleted = 0
    ");
    $stmt->execute([$vendor_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_sales'] ?? 0;
}

function get_vendor_orders($conn, $vendor_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT o.id) as total_orders 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        JOIN products p ON oi.product_id = p.id 
        WHERE p.vendor_id = ? AND p.deleted = 0
    ");
    $stmt->execute([$vendor_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_orders'] ?? 0;
}

// Update user profile
function update_user_profile($conn, $user_id, $name, $email) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $user_id]);
}

function get_all_vendors($conn) {
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE user_type = 'vendor' ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_filtered_products($conn, $vendor_id = null) {
    $sql = "SELECT p.*, u.name as vendor_name 
            FROM products p 
            LEFT JOIN users u ON p.vendor_id = u.id 
            WHERE p.deleted = 0";
    $params = [];

    if ($vendor_id) {
        $sql .= " AND p.vendor_id = ?";
        $params[] = $vendor_id;
    }

    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// checkout

function get_user_addresses($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_shipping_address($conn, $user_id, $data) {
    $stmt = $conn->prepare("
        INSERT INTO shipping_addresses 
        (user_id, full_name, address_line1, address_line2, city, state, postal_code, country, phone, is_default) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $user_id,
        $data['full_name'],
        $data['address_line1'],
        $data['address_line2'],
        $data['city'],
        $data['state'],
        $data['postal_code'],
        $data['country'],
        $data['phone'],
        isset($data['is_default']) ? 1 : 0
    ]);

    if ($result && isset($data['is_default']) && $data['is_default']) {
        // Update other addresses to not be default
        $stmt = $conn->prepare("
            UPDATE shipping_addresses 
            SET is_default = 0 
            WHERE user_id = ? AND id != ?
        ");
        $stmt->execute([$user_id, $conn->lastInsertId()]);
    }

    return $result ? $conn->lastInsertId() : false;
}

function get_shipping_address($conn, $address_id) {
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE id = ?");
    $stmt->execute([$address_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}




// razer 

function create_order_with_shipping($conn, $user_id, $shipping_address, $payment_method, $total_amount) {
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, shipping_address, payment_method, total_amount) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $shipping_address,
        $payment_method,
        $total_amount
    ]);
    
    return $conn->lastInsertId();
}

function save_payment_details($conn, $order_id, $payment_id) {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET payment_id = ?, 
            payment_status = 'completed' 
        WHERE id = ?
    ");
    return $stmt->execute([$payment_id, $order_id]);
}  
function generate_invoice($order, $order_items, $user) {
    $invoice = "=== INVOICE ===\n\n";
    $invoice .= "Order #" . $order['id'] . "\n";
    $invoice .= "Date: " . date('F j, Y, g:i a', strtotime($order['created_at'])) . "\n\n";
    
    $invoice .= "Bill To:\n";
    $invoice .= $user['name'] . "\n";
    $invoice .= "Shipping Address:\n" . $order['shipping_address'] . "\n\n";
    
    $invoice .= "Items:\n";
    $invoice .= str_repeat("-", 60) . "\n";
    $invoice .= sprintf("%-30s %10s %10s %10s\n", "Product", "Quantity", "Price", "Total");
    $invoice .= str_repeat("-", 60) . "\n";
    
    foreach ($order_items as $item) {
        $subtotal = $item['quantity'] * $item['price'];
        $invoice .= sprintf("%-30s %10d %10.2f %10.2f\n",
            substr($item['product_name'], 0, 30),
            $item['quantity'],
            $item['price'],
            $subtotal
        );
    }
    
    $invoice .= str_repeat("-", 60) . "\n";
    $invoice .= sprintf("%52s %7.2f\n", "Total:", $order['total_amount']);
    
    $invoice .= "\nPayment Method: " . ucfirst($order['payment_method']) . "\n";
    $invoice .= "Payment Status: " . ucfirst($order['payment_status']) . "\n\n";
    
    $invoice .= "Thank you for your business!\n";
    
    return $invoice;
}


// __pdf______________________________________________________________________________________________________________________________________

require_once('tcpdf/tcpdf.php');

function generate_pdf_invoice($order, $order_items, $user) {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('PC STORE');
    $pdf->SetAuthor('PC STORE');
    $pdf->SetTitle('Invoice #' . $order['id']);
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    
    // Set colors
    $primaryColor = array(31, 187, 31); // #1fbb1f
    $secondaryColor = array(0, 255, 0); // #00ff00
    $textColor = array(51, 51, 51); // #333333
    
    // Add a page
    $pdf->AddPage();
    
    // Set background color
    $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F', array(), array(248, 249, 250));
    
    // Company Logo and Information
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 15, 'PC STORE', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->Cell(0, 5, 'www.pc-store.com', 0, 1, 'L');
    $pdf->Cell(0, 5, 'contact@pcstore.com', 0, 1, 'L');
    $pdf->Cell(0, 5, '+91 1011002102', 0, 1, 'L');
    
    // Invoice Title and Details
    $pdf->SetY(15);
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 15, 'INVOICE', 0, 1, 'R');
    
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Invoice #: ' . $order['id'], 0, 1, 'R');
    $pdf->Cell(0, 8, 'Date: ' . date('F j, Y', strtotime($order['created_at'])), 0, 1, 'R');
    
    // Add a styled line separator
    $pdf->SetLineStyle(array('width' => 0.5, 'color' => array($primaryColor)));
    $pdf->Line(15, 65, 195, 65);
    
    // Customer Information
    $pdf->SetY(75);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 10, 'Bill To:', 0, 1);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $user['name'] . "\n" . str_replace("\n", ", ", $order['shipping_address']), 0, 'L');
    
    // Items Table Header
    $pdf->SetY(120);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(248, 249, 250); // Light background
    $pdf->SetTextColor(0, 0, 0); // Black text color
    
    // Table headers with rounded corners
    $pdf->SetXY(15, 120);
    $pdf->MultiCell(80, 10, 'Product', 1, 'L', true);
    $pdf->SetXY(95, 120);
    $pdf->MultiCell(30, 10, 'Quantity', 1, 'C', true);
    $pdf->SetXY(125, 120);
    $pdf->MultiCell(35, 10, 'Price', 1, 'R', true);
    $pdf->SetXY(160, 120);
    $pdf->MultiCell(35, 10, 'Total', 1, 'R', true);
    
    
    // Items Table Content
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetFillColor(248, 249, 250);
    
    $y = 130;
    foreach ($order_items as $item) {
        $subtotal = $item['quantity'] * $item['price'];
        $pdf->SetXY(15, $y);
        $pdf->MultiCell(80, 10, substr($item['product_name'], 0, 35), 1, 'L', true);
        $pdf->SetXY(95, $y);
        $pdf->MultiCell(30, 10, $item['quantity'], 1, 'C', true);
        $pdf->SetXY(125, $y);
        $pdf->MultiCell(35, 10, ' ' . number_format($item['price'], 2), 1, 'R', true);
        $pdf->SetXY(160, $y);
        $pdf->MultiCell(35, 10, ' ' . number_format($subtotal, 2), 1, 'R', true);
        $y += 10;
    }
    
    // Total
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(248, 249, 250); // Light background
    $pdf->SetTextColor(0, 0, 0); // Black text color
    $pdf->SetXY(125, $y);
    $pdf->MultiCell(35, 10, 'Total:', 1, 'R', true);
    $pdf->SetXY(160, $y);
    $pdf->MultiCell(35, 10, ' ' . number_format($order['total_amount'], 2), 1, 'R', true);
    
    // Payment Information
    $pdf->SetY($y + 20);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 10, 'Payment Information', 0, 1);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 8, 'Payment Method: ' . ucfirst($order['payment_method']), 0, 1);
    $pdf->Cell(0, 8, 'Payment Status: ' . ucfirst($order['payment_status']), 0, 1);
    
    // Terms and Conditions with styled box
    $pdf->SetY($y + 60);
    $pdf->SetFillColor(248, 249, 250);
    $pdf->RoundedRect(15, $pdf->GetY(), 180, 40, 3.50, '1111', 'DF', array(), array(248, 249, 250));
    
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 10, 'Terms & Conditions:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
    $pdf->MultiCell(0, 5, "1. No return policy\n2. This is a computer generated invoice", 0, 'L');
    
    // Thank you message
    $pdf->SetY(-40);
    $pdf->SetFont('helvetica', 'I', 11);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 10, 'Thank you for shopping with PC STORE!', 0, 1, 'C');
    
    return $pdf->Output('invoice_' . $order['id'] . '.pdf', 'S');
}
?>