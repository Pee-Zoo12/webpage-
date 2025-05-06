<?php

class Product {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getProduct($productId) {
        return $this->db->getProduct($productId);
    }
    
    public function getProducts($limit = null, $type = null) {
        return $this->db->getProducts($limit, $type);
    }
    
    public function getRelatedProducts($productId, $limit = 4) {
        $product = $this->getProduct($productId);
        if (!$product) {
            return [];
        }
        
        $products = $this->getProducts();
        $related = [];
        
        foreach ($products as $p) {
            if ($p['id'] !== $productId && 
                ($p['collection'] === $product['collection'] || 
                 $p['type'] === $product['type'])) {
                $related[] = $p;
                if (count($related) >= $limit) {
                    break;
                }
            }
        }
        
        return $related;
    }
    
    public function searchProducts($query) {
        $products = $this->getProducts();
        $results = [];
        
        foreach ($products as $product) {
            if (stripos($product['name'], $query) !== false ||
                stripos($product['description'], $query) !== false ||
                stripos($product['collection'], $query) !== false) {
                $results[] = $product;
            }
        }
        
        return $results;
    }
    
    public function getProductCategories() {
        $products = $this->getProducts();
        $categories = [];
        
        foreach ($products as $product) {
            if (!in_array($product['type'], $categories)) {
                $categories[] = $product['type'];
            }
        }
        
        return $categories;
    }
    
    public function getProductCollections() {
        $products = $this->getProducts();
        $collections = [];
        
        foreach ($products as $product) {
            if (!in_array($product['collection'], $collections)) {
                $collections[] = $product['collection'];
            }
        }
        
        return $collections;
    }
} 