<?php

class JsonDatabase {
    private $dbFile;
    private $data;

    public function __construct() {
        $this->dbFile = __DIR__ . '/../data/database.json';
        $this->loadData();
    }

    private function loadData() {
        if (file_exists($this->dbFile)) {
            $jsonContent = file_get_contents($this->dbFile);
            $this->data = json_decode($jsonContent, true);
        } else {
            $this->data = [
                'products' => [],
                'users' => [],
                'orders' => [],
                'cart' => [],
                'reviews' => [],
                'company' => [
                    'name' => 'Nox Apparel',
                    'CEO' => '',
                    'brandIdentity' => '',
                    'missionStatement' => '',
                    'branches' => [],
                    'resellers' => []
                ]
            ];
            $this->saveData();
        }
    }

    private function saveData() {
        file_put_contents($this->dbFile, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    // Product Methods
    public function getProducts($limit = null, $type = null) {
        $products = $this->data['products'];
        
        if ($type === 'featured') {
            $products = array_filter($products, function($product) {
                return $product['featured'] ?? false;
            });
        } elseif ($type === 'new') {
            $products = array_filter($products, function($product) {
                return $product['newArrival'] ?? false;
            });
        }

        if ($limit) {
            $products = array_slice($products, 0, $limit);
        }

        return $products;
    }

    public function getProduct($productId) {
        foreach ($this->data['products'] as $product) {
            if ($product['productID'] === $productId) {
                return $product;
            }
        }
        return null;
    }

    public function addProduct($product) {
        $this->data['products'][] = $product;
        $this->saveData();
    }

    // User Methods
    public function getUser($userId) {
        foreach ($this->data['users'] as $user) {
            if ($user['userID'] === $userId) {
                return $user;
            }
        }
        return null;
    }

    public function addUser($user) {
        $this->data['users'][] = $user;
        $this->saveData();
    }

    // Cart Methods
    public function getCart($userId) {
        return array_filter($this->data['cart'], function($item) use ($userId) {
            return $item['userID'] === $userId;
        });
    }

    public function addToCart($cartItem) {
        $this->data['cart'][] = $cartItem;
        $this->saveData();
    }

    public function updateCart($cartId, $quantity) {
        foreach ($this->data['cart'] as &$item) {
            if ($item['cartID'] === $cartId) {
                $item['quantity'] = $quantity;
                break;
            }
        }
        $this->saveData();
    }

    public function removeFromCart($cartId) {
        $this->data['cart'] = array_filter($this->data['cart'], function($item) use ($cartId) {
            return $item['cartID'] !== $cartId;
        });
        $this->saveData();
    }

    // Order Methods
    public function getOrders($userId) {
        return array_filter($this->data['orders'], function($order) use ($userId) {
            return $order['userID'] === $userId;
        });
    }

    public function addOrder($order) {
        $this->data['orders'][] = $order;
        $this->saveData();
    }

    // Review Methods
    public function getProductReviews($productId) {
        return array_filter($this->data['reviews'], function($review) use ($productId) {
            return $review['productID'] === $productId;
        });
    }

    public function addReview($review) {
        $this->data['reviews'][] = $review;
        $this->saveData();
    }

    // Company Methods
    public function getCompanyInfo() {
        return $this->data['company'];
    }

    public function updateCompanyInfo($info) {
        $this->data['company'] = array_merge($this->data['company'], $info);
        $this->saveData();
    }
}
