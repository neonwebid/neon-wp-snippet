<?php

class NeonRelatedPosts {

  public function __construct() {
    add_shortcode('neon_related_post', [$this, 'shortcode']);
  }

  public function shortcode($atts) {
      global $post;
    
      $atts = shortcode_atts( array(
    		'posts_item' => 3,
    		'by' => 'category',
        'show_thumbnail' => false,
        'show_post_meta' => false
    	), $atts );

      ob_start();
    
      self::view($post_id, [
         'posts_item' => $atts['posts_item'],
         'by' => $atts['by'],
         'show_thumbnail' => $atts['show_thumbnail'],
         'show_post_meta' => $atts['show_post_meta']
      ]);
    
      return ob_get_clean();
  }

  public static function get( $post_id, $args = [] ) {

    $default = [
      'posts_item' => 3,
      'by' => 'category',
      'show_thumbnail' => false,
      'show_post_meta' => false
    ];

    $args = wp_parse_args($args, $default);
    
    $query_args = [
      'posts_per_page' => $args['posts_item'],
      'post__not_in' => $post_id,
    ];

    $terms = get_terms([
        'taxonomy' => $args['by'],
    ]);
    
    $taxonomies = [];
		
    if ( $terms && ! is_wp_error($terms) ) {
        foreach($terms as $term) {
          $taxonomies[] = $term->slug;
        }
    }

    $args['tax_query'] = [
        [
          'taxonomy' => $by,
          'field' => 'slug',
          'terms' => $taxonomies,
        ]
      ];

    $related_posts = new WP_Query($query_args);
    if ( $related_posts->have_posts() ) {
      while( $related_posts->have_posts() ) {
        $related_posts->the_post();
        self::view( $args );
    }
  }

  private static function view( $args ) {
    global $post;
    ?>
    <div class="neon-related-item">
      <?php if ( ! empty($args['show_thumbnail']) ) : ?>
      <div class="neon-related-thumbnail">
        <?php if ( has_thumbnail() ) : ?>
          <?php the_post_thumbnail('thumbnail');?>
        <?php else: ?>
          <img src="https://placehold.co/150"/>
        <?php endif;?>
      </div>
      <?php endif;?>
      <div class="neon-related-title">
        <h3><a href="<?php echo get_permalink($post);?>" title="<?php echo $post->post_title;?>"><?php echo $post->post_title;?></a></h3>
      </div>
      <?php if ( ! empty($args['show_post_meta']) ) :?>
      <div class="neon-related-post-meta">
        <span class="neon-post-meta-date"><?php the_date();?></span>
      </div>
      <?php endif;?>
    </div>
    <?php
  }
}

new NeonRelatedPosts();
