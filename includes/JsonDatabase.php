<?php
class JsonDatabase {
    private $productsFile = 'data/products.json';
    private $reviewsFile = 'data/reviews.json';
    private $shippingFile = 'data/shipping.json';
    private $ordersFile = 'data/orders.json';
    private $usersFile = 'data/users.json';

    public function __construct() {
        // Ensure the data directory exists
        if (!file_exists('data')) {
            mkdir('data', 0755, true);
        }
        
        // Initialize empty files if they don't exist
        $this->initializeFile($this->productsFile, '{"products":[]}');
        $this->initializeFile($this->reviewsFile, '{"reviews":[]}');
        $this->initializeFile($this->shippingFile, '{"options":[]}');
        $this->initializeFile($this->ordersFile, '{"orders":[]}');
        $this->initializeFile($this->usersFile, '{"users":[]}');
    }

    private function initializeFile($file, $initialContent) {
        if (!file_exists($file)) {
            file_put_contents($file, $initialContent);
        }
    }

    private function readFile($file) {
        return json_decode(file_get_contents($file));
    }

    private function writeFile($file, $data) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    // Product Methods
    public function getProducts($limit = null, $type = null) {
        $data = $this->readFile($this->productsFile);
        $products = $data->products;
        
        // Filter by type if specified
        if ($type !== null) {
            $filtered = [];
            foreach ($products as $product) {
                if (property_exists($product, $type) && $product->$type === true) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        
        // Apply limit if specified
        if ($limit !== null && count($products) > $limit) {
            $products = array_slice($products, 0, $limit);
        }
        
        return $products;
    }

    public function getProductById($id) {
        $data = $this->readFile($this->productsFile);
        foreach ($data->products as $product) {
            if ($product->productID == $id) {
                return $product;
            }
        }
        return null;
    }

    public function getCategories() {
        $data = $this->readFile($this->productsFile);
        $categories = [];
        
        foreach ($data->products as $product) {
            if (!in_array($product->type, $categories)) {
                $categories[] = $product->type;
            }
        }
        
        return $categories;
    }

    public function getFilteredProducts($category = null, $sort = 'newest', $searchQuery = '', $page = 1, $perPage = 12) {
        $data = $this->readFile($this->productsFile);
        $products = $data->products;
        
        // Filter by category
        if ($category !== null) {
            $filtered = [];
            foreach ($products as $product) {
                if ($product->type === $category) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        
        // Filter by search query
        if (!empty($searchQuery)) {
            $filtered = [];
            $searchQuery = strtolower($searchQuery);
            foreach ($products as $product) {
                if (strpos(strtolower($product->productName), $searchQuery) !== false || 
                    strpos(strtolower($product->description), $searchQuery) !== false) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        
        // Sort products
        switch ($sort) {
            case 'price-low':
                usort($products, function($a, $b) {
                    return $a->unitCost - $b->unitCost;
                });
                break;
            case 'price-high':
                usort($products, function($a, $b) {
                    return $b->unitCost - $a->unitCost;
                });
                break;
            case 'popular':
                usort($products, function($a, $b) {
                    return ($b->popular ?? 0) - ($a->popular ?? 0);
                });
                break;
            case 'newest':
            default:
                usort($products, function($a, $b) {
                    return strtotime($b->dateAdded ?? '2023-01-01') - strtotime($a->dateAdded ?? '2023-01-01');
                });
                break;
        }
        
        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $products = array_slice($products, $offset, $perPage);
        
        return $products;
    }

    public function getFilteredProductsCount($category = null, $searchQuery = '') {
        $data = $this->readFile($this->productsFile);
        $products = $data->products;
        
        // Filter by category
        if ($category !== null) {
            $filtered = [];
            foreach ($products as $product) {
                if ($product->type === $category) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        
        // Filter by search query
        if (!empty($searchQuery)) {
            $filtered = [];
            $searchQuery = strtolower($searchQuery);
            foreach ($products as $product) {
                if (strpos(strtolower($product->productName), $searchQuery) !== false || 
                    strpos(strtolower($product->description), $searchQuery) !== false) {
                    $filtered[] = $product;
                }
            }
            $products = $filtered;
        }
        
        return count($products);
    }

    public function getRelatedProducts($product, $limit = 4) {
        $data = $this->readFile($this->productsFile);
        $allProducts = $data->products;
        $relatedProducts = [];
        
        // Find products of the same type
        foreach ($allProducts as $p) {
            if ($p->productID != $product->productID && $p->type === $product->type) {
                $relatedProducts[] = $p;
                if (count($relatedProducts) >= $limit) {
                    break;
                }
            }
        }
        
        // If not enough products of the same type, add products of other types
        if (count($relatedProducts) < $limit) {
            foreach ($allProducts as $p) {
                if ($p->productID != $product->productID && $p->type !== $product->type && !in_array($p, $relatedProducts)) {
                    $relatedProducts[] = $p;
                    if (count($relatedProducts) >= $limit) {
                        break;
                    }
                }
            }
        }
        
        return $relatedProducts;
    }

    // Review Methods
    public function getProductReviews($productID) {
        $data = $this->readFile($this->reviewsFile);
        $reviews = [];
        
        foreach ($data->reviews as $review) {
            if ($review->productID == $productID) {
                $reviews[] = $review;
            }
        }
        
        // Sort reviews by date (newest first)
        usort($reviews, function($a, $b) {
            return strtotime($b->date) - strtotime($a->date);
        });
        
        return $reviews;
    }

    public function addReview($review) {
        $data = $this->readFile($this->reviewsFile);
        $review->reviewID = $this->generateID($data->reviews, 'reviewID');
        $review->date = date('Y-m-d H:i:s');
        $data->reviews[] = $review;
        $this->writeFile($this->reviewsFile, $data);
        return $review->reviewID;
    }

    // Shipping Methods
    public function getShippingOptions() {
        $data = $this->readFile($this->shippingFile);
        return $data->options;
    }

    // Order Methods
    public function createOrder($order) {
        $data = $this->readFile($this->ordersFile);
        $order->orderID = $this->generateID($data->orders, 'orderID');
        $order->orderDate = date('Y-m-d H:i:s');
        $order->status = 'pending';
        $data->orders[] = $order;
        $this->writeFile($this->ordersFile, $data);
        return $order->orderID;
    }

    public function getOrderById($id) {
        $data = $this->readFile($this->ordersFile);
        foreach ($data->orders as $order) {
            if ($order->orderID == $id) {
                return $order;
            }
        }
        return null;
    }

    public function getUserOrders($userID) {
        $data = $this->readFile($this->ordersFile);
        $orders = [];
        
        foreach ($data->orders as $order) {
            if ($order->userID == $userID) {
                $orders[] = $order;
            }
        }
        
        // Sort orders by date (newest first)
        usort($orders, function($a, $b) {
            return strtotime($b->orderDate) - strtotime($a->orderDate);
        });
        
        return $orders;
    }

    // User Methods
    public function getUserByEmail($email) {
        $data = $this->readFile($this->usersFile);
        foreach ($data->users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }
        return null;
    }

    public function createUser($user) {
        $data = $this->readFile($this->usersFile);
        $user->userID = $this->generateID($data->users, 'userID');
        $user->created = date('Y-m-d H:i:s');
        $data->users[] = $user;
        $this->writeFile($this->usersFile, $data);
        return $user->userID;
    }

    public function updateUser($user) {
        $data = $this->readFile($this->usersFile);
        foreach ($data->users as $key => $u) {
            if ($u->userID == $user->userID) {
                $data->users[$key] = $user;
                $this->writeFile($this->usersFile, $data);
                return true;
            }
        }
        return false;
    }

    // Helper Methods
    private function generateID($items, $idField) {
        $maxID = 0;
        foreach ($items as $item) {
            if ($item->$idField > $maxID) {
                $maxID = $item->$idField;
            }
        }
        return $maxID + 1;
    }
}
