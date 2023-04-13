<?php

include_once 'app/Review.php';

$reviewGuide = explode('/', $uri);

$Review = new Review;

if ($reviewGuide[1] === 'reviews' || $reviewGuide[1] === 'review') {
    if (!isset($reviewGuide[2])) {
        // URI should be /reviews in here.
        $reviewsHome = $Review -> getReviews();
    } else if (isset($reviewGuide[2]) && is_numeric($reviewGuide[2]) && !isset($reviewGuide[3])) {
        // URI should be /review/46 in here.
        $review_id = $reviewGuide[2];
        $review = $Review -> getReview($review_id);
    } else if (isset($reviewGuide[2]) && $reviewGuide[2] === 'new') {
        if (isset($reviewGuide[3]) && ($reviewGuide[3] === 'anime' || $reviewGuide[3] === 'manga')) {
            $titles = $Review -> getTitles($reviewGuide[3]);
        } else if (isset($reviewGuide[3])) {
            
        }
        
    }
    require('resources/views/reviews/review.view.php');
} else {
    header('Location: /404');
}