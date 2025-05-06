<?php

class Rating {
    private $db;
    private $productID;
    private $customerID;
    private $rating;
    private $dateCreated;

    public function __construct($db) {
        $this->db = $db;
    }

    public function submitRating($data) {
        try {
            if (!$this->validateRatingData($data)) {
                throw new Exception("Invalid rating data");
            }

            $ratingData = [
                'productID' => $data['productID'],
                'customerID' => $data['customerID'],
                'rating' => $data['rating'],
                'dateCreated' => date('Y-m-d H:i:s')
            ];

            // Check if user has already rated this product
            $existingRating = $this->db->select('ratings', [
                'productID' => $data['productID'],
                'customerID' => $data['customerID']
            ], null, 1);

            if ($existingRating) {
                // Update existing rating
                $result = $this->db->update('ratings', $ratingData, [
                    'productID' => $data['productID'],
                    'customerID' => $data['customerID']
                ]);
            } else {
                // Insert new rating
                $result = $this->db->insert('ratings', $ratingData);
            }

            if ($result) {
                $this->updateProductRating($data['productID']);
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log("Error submitting rating: " . $e->getMessage());
            return false;
        }
    }

    public function getProductRating($productID) {
        try {
            $ratings = $this->db->select('ratings', ['productID' => $productID]);
            if (empty($ratings)) {
                return [
                    'average' => 0,
                    'count' => 0,
                    'distribution' => $this->getEmptyDistribution()
                ];
            }

            $totalRating = 0;
            $distribution = $this->getEmptyDistribution();

            foreach ($ratings as $rating) {
                $totalRating += $rating['rating'];
                $distribution[$rating['rating']]++;
            }

            return [
                'average' => round($totalRating / count($ratings), 1),
                'count' => count($ratings),
                'distribution' => $distribution
            ];
        } catch (Exception $e) {
            error_log("Error getting product rating: " . $e->getMessage());
            return [
                'average' => 0,
                'count' => 0,
                'distribution' => $this->getEmptyDistribution()
            ];
        }
    }

    public function getUserRating($productID, $customerID) {
        try {
            $rating = $this->db->select('ratings', [
                'productID' => $productID,
                'customerID' => $customerID
            ], null, 1);

            return $rating ? $rating['rating'] : 0;
        } catch (Exception $e) {
            error_log("Error getting user rating: " . $e->getMessage());
            return 0;
        }
    }

    private function validateRatingData($data) {
        if (!isset($data['productID']) || !isset($data['customerID']) || !isset($data['rating'])) {
            return false;
        }

        if (!is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return false;
        }

        return true;
    }

    private function updateProductRating($productID) {
        try {
            $ratingInfo = $this->getProductRating($productID);
            return $this->db->update('products', [
                'averageRating' => $ratingInfo['average'],
                'ratingCount' => $ratingInfo['count']
            ], ['id' => $productID]);
        } catch (Exception $e) {
            error_log("Error updating product rating: " . $e->getMessage());
            return false;
        }
    }

    private function getEmptyDistribution() {
        return [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];
    }

    public function deleteRating($productID, $customerID) {
        try {
            $result = $this->db->delete('ratings', [
                'productID' => $productID,
                'customerID' => $customerID
            ]);

            if ($result) {
                $this->updateProductRating($productID);
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting rating: " . $e->getMessage());
            return false;
        }
    }
} 