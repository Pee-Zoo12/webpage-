<?php
require_once 'includes/config.php';
require_once 'includes/JsonDatabase.php';
require_once 'includes/functions.php';
require_once 'includes/QRCodeVerifier.php';

$db = new JsonDatabase();
$verifier = new QRCodeVerifier($db);

$pageTitle = "Verify Product - " . SITE_NAME;
$verificationResult = null;
$product = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qrData = sanitizeInput($_POST['qr_data']);
    $result = $verifier->verify($qrData);
    
    if ($result['isValid']) {
        $product = $db->getProduct($result['productId']);
    }
    
    $verificationResult = $result;
}

include 'includes/header.php';
?>

<div class="verify-header">
    <div class="container">
        <h1>Verify Product Authenticity</h1>
        <p>Scan or enter the QR code to verify your product</p>
    </div>
</div>

<div class="verify-grid">
    <div class="verify-form">
        <form method="POST" action="">
            <div class="form-group">
                <label for="qr_data">QR Code Data</label>
                <input type="text" id="qr_data" name="qr_data" required>
            </div>
            
            <div class="qr-scanner-container">
                <div id="reader"></div>
                <button type="button" id="startScan" class="btn btn-secondary">Start Scanner</button>
                <button type="button" id="stopScan" class="btn btn-secondary" style="display: none;">Stop Scanner</button>
            </div>
            
            <button type="submit" class="btn btn-primary">Verify</button>
        </form>
    </div>
    
    <?php if ($verificationResult): ?>
    <div class="verification-result <?php echo $verificationResult['isValid'] ? 'valid' : 'invalid'; ?>">
        <div class="result-icon">
            <?php if ($verificationResult['isValid']): ?>
                <i class="fas fa-check-circle"></i>
            <?php else: ?>
                <i class="fas fa-times-circle"></i>
            <?php endif; ?>
        </div>
        <h3><?php echo $verificationResult['message']; ?></h3>
        
        <?php if ($verificationResult['isValid'] && $product): ?>
        <div class="product-details">
            <h4>Product Details</h4>
            <ul>
                <li><strong>Name:</strong> <?php echo $product['name']; ?></li>
                <li><strong>Serial Number:</strong> <?php echo $product['serialNumber']; ?></li>
                <li><strong>Collection:</strong> <?php echo $product['collection']; ?></li>
                <li><strong>Manufacturing Date:</strong> <?php echo $product['manufacturingDate']; ?></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="verify-info">
        <h3>Why Verify Your Product?</h3>
        <p>Verifying your product ensures its authenticity and protects you from counterfeit items. Each product comes with a unique QR code that can be verified through our system.</p>
        
        <h3>How to Verify</h3>
        <ol>
            <li>Locate the QR code on your product or packaging</li>
            <li>Scan the QR code using your device's camera or enter the code manually</li>
            <li>Our system will verify the product's authenticity</li>
            <li>View the verification results and product details</li>
        </ol>
    </div>
</div>

<!-- Include HTML5 QR Code library -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startScanBtn = document.getElementById('startScan');
    const stopScanBtn = document.getElementById('stopScan');
    const qrInput = document.getElementById('qr_data');
    let html5QrCode = null;
    
    startScanBtn.addEventListener('click', function() {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }
        
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onScanSuccess,
            onScanFailure
        );
        
        startScanBtn.style.display = 'none';
        stopScanBtn.style.display = 'inline-block';
    });
    
    stopScanBtn.addEventListener('click', function() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                startScanBtn.style.display = 'inline-block';
                stopScanBtn.style.display = 'none';
            });
        }
    });
    
    function onScanSuccess(decodedText, decodedResult) {
        qrInput.value = decodedText;
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                startScanBtn.style.display = 'inline-block';
                stopScanBtn.style.display = 'none';
            });
        }
    }
    
    function onScanFailure(error) {
        // Handle scan failure silently
        console.warn(`QR Code scan failed: ${error}`);
    }
});
</script>

<?php include 'includes/footer.php'; ?> 