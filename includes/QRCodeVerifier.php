<?php

class QRCodeVerifier {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Verify QR code data
     * 
     * @param string $qrData The QR code data to verify
     * @return array Verification result with status and message
     */
    public function verify($qrData) {
        // Decode QR data
        $data = json_decode($qrData, true);
        
        if (!$data) {
            return [
                'isValid' => false,
                'message' => 'Invalid QR code format'
            ];
        }
        
        // Validate required fields
        if (!isset($data['product_id']) || !isset($data['serial_number']) || !isset($data['timestamp'])) {
            return [
                'isValid' => false,
                'message' => 'Missing required data in QR code'
            ];
        }
        
        // Get product from database
        $product = $this->db->getProduct($data['product_id']);
        
        if (!$product) {
            return [
                'isValid' => false,
                'message' => 'Product not found in our database'
            ];
        }
        
        // Verify serial number
        if ($product['serialNumber'] !== $data['serial_number']) {
            return [
                'isValid' => false,
                'message' => 'Invalid serial number'
            ];
        }
        
        // Verify timestamp (optional: check if QR code is not too old)
        $qrAge = time() - $data['timestamp'];
        if ($qrAge > (30 * 24 * 60 * 60)) { // 30 days
            return [
                'isValid' => false,
                'message' => 'QR code has expired'
            ];
        }
        
        // All verifications passed
        return [
            'isValid' => true,
            'message' => 'Product verified as authentic',
            'productId' => $data['product_id'],
            'serialNumber' => $data['serial_number']
        ];
    }
    
    /**
     * Generate QR code data for a product
     * 
     * @param string $productId The product ID
     * @param string $serialNumber The product's serial number
     * @return string JSON encoded QR code data
     */
    public function generateQRData($productId, $serialNumber) {
        return json_encode([
            'product_id' => $productId,
            'serial_number' => $serialNumber,
            'timestamp' => time()
        ]);
    }
} 