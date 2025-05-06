<?php
require_once 'vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Label\Label;

class QRCodeGenerator {
    private $verifier;
    private $outputPath;
    
    public function __construct($verifier, $outputPath = 'assets/qrcodes/') {
        $this->verifier = $verifier;
        $this->outputPath = $outputPath;
        
        // Create output directory if it doesn't exist
        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }
    }
    
    /**
     * Generate QR code for a product
     * 
     * @param string $productId The product ID
     * @param string $serialNumber The product's serial number
     * @return string Path to the generated QR code image
     */
    public function generateQRCode($productId, $serialNumber) {
        // Generate QR code data
        $qrData = $this->verifier->generateQRData($productId, $serialNumber);
        
        // Create QR code
        $qrCode = QrCode::create($qrData)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
            
        // Add label
        $label = Label::create("Product ID: $productId")
            ->setTextColor(new Color(0, 0, 0));
            
        // Create writer
        $writer = new PngWriter();
        
        // Generate QR code
        $result = $writer->write($qrCode, null, $label);
        
        // Save QR code
        $filename = "product_{$productId}_{$serialNumber}.png";
        $filepath = $this->outputPath . $filename;
        $result->saveToFile($filepath);
        
        return $filepath;
    }
    
    /**
     * Generate QR codes for multiple products
     * 
     * @param array $products Array of products with id and serialNumber
     * @return array Array of generated QR code file paths
     */
    public function generateMultipleQRCodes($products) {
        $generatedFiles = [];
        
        foreach ($products as $product) {
            if (isset($product['id']) && isset($product['serialNumber'])) {
                $filepath = $this->generateQRCode($product['id'], $product['serialNumber']);
                $generatedFiles[] = [
                    'productId' => $product['id'],
                    'serialNumber' => $product['serialNumber'],
                    'filepath' => $filepath
                ];
            }
        }
        
        return $generatedFiles;
    }
} 