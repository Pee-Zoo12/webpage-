document.addEventListener('DOMContentLoaded', function() {
    const ratingContainers = document.querySelectorAll('.rating-container');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const ratingInput = container.querySelector('input[type="hidden"]');
        const productID = container.dataset.productId;
        
        // Initialize stars based on current rating
        if (ratingInput && ratingInput.value) {
            highlightStars(stars, parseInt(ratingInput.value));
        }
        
        // Add click event to stars
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const rating = index + 1;
                submitRating(productID, rating);
                highlightStars(stars, rating);
            });
            
            // Add hover effects
            star.addEventListener('mouseenter', () => {
                highlightStars(stars, index + 1);
            });
            
            star.addEventListener('mouseleave', () => {
                if (ratingInput && ratingInput.value) {
                    highlightStars(stars, parseInt(ratingInput.value));
                } else {
                    resetStars(stars);
                }
            });
        });
    });
});

function highlightStars(stars, rating) {
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function resetStars(stars) {
    stars.forEach(star => star.classList.remove('active'));
}

function submitRating(productID, rating) {
    fetch('process/rating.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            productID: productID,
            rating: rating
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update rating display
            updateRatingDisplay(productID, data.ratingInfo);
        } else {
            showError(data.message || 'Error submitting rating');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error submitting rating');
    });
}

function updateRatingDisplay(productID, ratingInfo) {
    const container = document.querySelector(`.rating-container[data-product-id="${productID}"]`);
    if (!container) return;

    // Update average rating
    const averageRating = container.querySelector('.average-rating');
    if (averageRating) {
        averageRating.textContent = ratingInfo.average.toFixed(1);
    }

    // Update rating count
    const ratingCount = container.querySelector('.rating-count');
    if (ratingCount) {
        ratingCount.textContent = `(${ratingInfo.count} ratings)`;
    }

    // Update distribution bars
    const distribution = ratingInfo.distribution;
    Object.keys(distribution).forEach(rating => {
        const bar = container.querySelector(`.rating-bar[data-rating="${rating}"] .fill`);
        if (bar) {
            const percentage = ratingInfo.count > 0 ? 
                (distribution[rating] / ratingInfo.count) * 100 : 0;
            bar.style.width = `${percentage}%`;
        }
    });
}

function showError(message) {
    // You can implement your preferred error display method here
    alert(message);
} 