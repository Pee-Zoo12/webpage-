<?php

class Reviews {
    private $db;
    public $productID;
    public $customerID;
    public $reviewContent;
    public $rating;
    public $images;
    public $dateCreated;
    public $status;

    public function __construct($db) {
        $this->db = $db;
    }

    public function submitReview($data) {
        try {
            // Validate input data
            if (!$this->validateReviewData($data)) {
                throw new Exception("Invalid review data");
            }

            // Prepare review data
            $reviewData = [
                'productID' => $data['productID'],
                'customerID' => $data['customerID'],
                'reviewContent' => $data['reviewContent'],
                'rating' => $data['rating'],
                'images' => $data['images'] ?? [],
                'dateCreated' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];

            // Save to database
            $result = $this->db->insert('reviews', $reviewData);
            
            if ($result) {
                // Update product average rating
                $this->updateProductRating($data['productID']);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error submitting review: " . $e->getMessage());
            return false;
        }
    }

    public function respondToCustomer($reviewID, $response) {
        try {
            $data = [
                'response' => $response,
                'responseDate' => date('Y-m-d H:i:s')
            ];

            return $this->db->update('reviews', $data, ['id' => $reviewID]);
        } catch (Exception $e) {
            error_log("Error responding to review: " . $e->getMessage());
            return false;
        }
    }

    public function getProductReviews($productID) {
        try {
            return $this->db->select('reviews', ['productID' => $productID], 'dateCreated DESC');
        } catch (Exception $e) {
            error_log("Error getting product reviews: " . $e->getMessage());
            return [];
        }
    }

    public function getAverageRating($productID) {
        try {
            $reviews = $this->getProductReviews($productID);
            if (empty($reviews)) {
                return 0;
            }

            $totalRating = 0;
            foreach ($reviews as $review) {
                $totalRating += $review['rating'];
            }

            return round($totalRating / count($reviews), 1);
        } catch (Exception $e) {
            error_log("Error calculating average rating: " . $e->getMessage());
            return 0;
        }
    }

    public function getRatingDistribution($productID) {
        try {
            $reviews = $this->getProductReviews($productID);
            $distribution = [
                5 => 0,
                4 => 0,
                3 => 0,
                2 => 0,
                1 => 0
            ];

            foreach ($reviews as $review) {
                $distribution[$review['rating']]++;
            }

            return $distribution;
        } catch (Exception $e) {
            error_log("Error getting rating distribution: " . $e->getMessage());
            return [
                5 => 0,
                4 => 0,
                3 => 0,
                2 => 0,
                1 => 0
            ];
        }
    }

    private function validateReviewData($data) {
        // Check required fields
        if (!isset($data['productID']) || !isset($data['customerID']) || 
            !isset($data['reviewContent']) || !isset($data['rating'])) {
            return false;
        }

        // Validate rating (1-5)
        if (!is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return false;
        }

        // Validate review content length
        if (strlen($data['reviewContent']) < 10 || strlen($data['reviewContent']) > 1000) {
            return false;
        }

        // Validate images if present
        if (isset($data['images']) && !is_array($data['images'])) {
            return false;
        }

        return true;
    }

    private function updateProductRating($productID) {
        try {
            $averageRating = $this->getAverageRating($productID);
            return $this->db->update('products', 
                ['averageRating' => $averageRating], 
                ['id' => $productID]
            );
        } catch (Exception $e) {
            error_log("Error updating product rating: " . $e->getMessage());
            return false;
        }
    }

    public function deleteReview($reviewID) {
        try {
            $review = $this->db->select('reviews', ['id' => $reviewID], null, 1);
            if (!$review) {
                return false;
            }

            $result = $this->db->delete('reviews', ['id' => $reviewID]);
            if ($result) {
                $this->updateProductRating($review['productID']);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting review: " . $e->getMessage());
            return false;
        }
    }

    public function updateReview($reviewID, $data) {
        try {
            if (!$this->validateReviewData($data)) {
                return false;
            }

            $result = $this->db->update('reviews', $data, ['id' => $reviewID]);
            if ($result) {
                $this->updateProductRating($data['productID']);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating review: " . $e->getMessage());
            return false;
        }
    }
} 