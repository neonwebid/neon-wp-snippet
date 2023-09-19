<?php

add_action('wp_head', 'neon_fake_rating');

function neon_fake_rating() {
  if ( is_single() ) :
    global $post;
    $meta_rating_store = '_neon_fake_rating';
    $range_vote_start  = 50;
    $range_vote_end    = 900;
    
    $get_rating = get_post_meta($post->ID, $meta_rating_store, true);
    if ( empty($get_rating) || $get_rating == '5' ) {
      $rating = [
        '3.8', '3.9', '4', '4.2', '4.2', '4.3', '4.5',
        '4.6', '4.7', '4.8'
      ];

      shuffle($rating);
      shuffle($rating);
      shuffle($rating);
      shuffle($rating);
      shuffle($rating);

      $get_rating = $rating[0];
      update_post_meta($post->ID, $meta_rating_store, $get_rating);
    }

    $get_count = (int) get_post_meta($post->ID, $meta_rating_store . '_count', true);
    if ( empty($get_count) || $get_count < ($range_vote_start - 1) || $get_count >= ($range_vote_end + 1) ) {
      $get_count = rand(50, 900);
      update_post_meta($post->ID, $meta_rating_store . '_count', $get_count);
    }

    $rating["@context"] = "https://schema.org/";
    $rating["@type"] = "Book";
    $rating["name"]  = get_bloginfo('name');
    $rating["aggregateRating"] = [
      "@type" => "AggregateRating",
      "ratingValue" => $get_rating,
      "ratingCount" => $get_count,
      "bestRating"  => "5",
      "worstRating" => "1"
    ];
  ?>
  <script type='application/ld+json'><?php echo json_encode($rating, 128);?></script>
  <?php
 endif;
}
