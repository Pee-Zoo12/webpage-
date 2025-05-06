<?php
require_once '../includes/config.php';
require_once '../includes/JsonDatabase.php';
require_once '../includes/QRCodeVerifier.php';
require_once '../includes/QRCodeGenerator.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = new JsonDatabase();
$verifier = new QRCodeVerifier($db);
$generator = new QRCodeGenerator($verifier);

$pageTitle = "Generate QR Codes - " . SITE_NAME;
$message = '';
$generatedFiles = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_all'])) {
        // Generate QR codes for all products
        $products = $db->getProducts();
        $generatedFiles = $generator->generateMultipleQRCodes($products);
        $message = "Generated QR codes for " . count($generatedFiles) . " products.";
    } elseif (isset($_POST['product_id'])) {
        // Generate QR code for specific product
        $productId = sanitizeInput($_POST['product_id']);
        $product = $db->getProduct($productId);
        
        if ($product) {
            $filepath = $generator->generateQRCode($productId, $product['serialNumber']);
            $generatedFiles[] = [
                'productId' => $productId,
                'serialNumber' => $product['serialNumber'],
                'filepath' => $filepath
            ];
            $message = "Generated QR code for product ID: $productId";
        } else {
            $message = "Product not found.";
        }
    }
}

include '../includes/header.php';
?>

<div class="admin-header">
    <div class="container">
        <h1>Generate QR Codes</h1>
        <p>Generate QR codes for product verification</p>
    </div>
</div>

<div class="admin-content">
    <div class="container">
        <?php if ($message): ?>
        <div class="alert alert-info">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="admin-grid">
            <div class="admin-form">
                <h2>Generate QR Code</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="product_id">Select Product</label>
                        <select name="product_id" id="product_id" class="form-control">
                            <option value="">Select a product...</option>
                            <?php
                            $products = $db->getProducts();
                            foreach ($products as $product) {
                                echo "<option value=\"{$product['id']}\">{$product['name']} (ID: {$product['id']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Generate QR Code</button>
                </form>
                
                <div class="form-divider">
                    <span>OR</span>
                </div>
                
                <form method="POST" action="">
                    <button type="submit" name="generate_all" class="btn btn-secondary">Generate All QR Codes</button>
                </form>
            </div>
            
            <?php if (!empty($generatedFiles)): ?>
            <div class="generated-codes">
                <h2>Generated QR Codes</h2>
                <div class="qr-grid">
                    <?php foreach ($generatedFiles as $file): ?>
                    <div class="qr-item">
                        <img src="<?php echo str_replace('../', '', $file['filepath']); ?>" alt="QR Code">
                        <div class="qr-info">
                            <p><strong>Product ID:</strong> <?php echo $file['productId']; ?></p>
                            <p><strong>Serial Number:</strong> <?php echo $file['serialNumber']; ?></p>
                            <a href="<?php echo str_replace('../', '', $file['filepath']); ?>" download class="btn btn-sm btn-outline">Download</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 