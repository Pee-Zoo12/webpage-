<?php

class Website {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getCompanyInfo() {
        return $this->db->getCompanyInfo();
    }
    
    public function getNavigation() {
        return [
            ['name' => 'Home', 'url' => 'index.php'],
            ['name' => 'Shop', 'url' => 'shop.php'],
            ['name' => 'About', 'url' => 'about.php'],
            ['name' => 'Contact', 'url' => 'contact.php']
        ];
    }
    
    public function getFooterLinks() {
        return [
            'company' => [
                ['name' => 'About Us', 'url' => 'about.php'],
                ['name' => 'Contact', 'url' => 'contact.php'],
                ['name' => 'Careers', 'url' => 'careers.php']
            ],
            'customer' => [
                ['name' => 'My Account', 'url' => 'account.php'],
                ['name' => 'Order History', 'url' => 'orders.php'],
                ['name' => 'Shipping', 'url' => 'shipping.php']
            ],
            'legal' => [
                ['name' => 'Terms & Conditions', 'url' => 'terms.php'],
                ['name' => 'Privacy Policy', 'url' => 'privacy.php'],
                ['name' => 'Returns', 'url' => 'returns.php']
            ]
        ];
    }
} 