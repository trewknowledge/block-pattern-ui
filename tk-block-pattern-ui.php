<?php
/**
 * Plugin Name:       TK Block Pattern UI
 * Plugin URI:        https://trewknowledge.com
 * Description:       Provides a UI for creating block patterns.
 * Author:            Trew Knowledge
 * Author URI:        https://trewknowledge.com
 * Version:           0.0.1
 *
 *
 * @package         TK_BlockPatternUI
 */

/**
 * Registers the block patterns post type.
 *
 */
function tk_block_pattern_register_post_type() {
	$args = array(
			'label'     					=> __( 'Block Patterns', 'tk_block_patterns' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_in_rest'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => null,
			'menu_icon'           => 'dashicons-screenoptions',
			'can_export'          => true,
			'delete_with_user'    => false,
			'hierarchical'        => false,
			'has_archive'         => false,
			'rewrite' => false,
			'supports' => array( 'title', 'editor', 'revisions' ),
	);
	register_post_type( 'tk_block_patterns', $args );
}
add_action( 'init', 'tk_block_pattern_register_post_type' );

/**
 * Registers the block patterns taxonomy.
 *
 */
function tk_block_pattern_register_taxonomy() {
	$labels = array(
		'name'                => __( 'Category', 'tk_block_patterns' ),
		'singular_name'       => __( 'Category', 'tk_block_patterns' ),
		'menu_name'           => __( 'Category', 'tk_block_patterns' ),
	);

	$args = array(
		'hierarchical'        => true,
		'labels'              => $labels,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'show_in_nav_menus'   => false,
		'show_admin_column'   => true,
		'query_var'           => true,
	);

	register_taxonomy( 'tk_block_pattern_category', 'tk_block_patterns', $args );
}
add_action( 'init', 'tk_block_pattern_register_taxonomy' );

/**
 * Creates the block Patterns.
 *
 */
function tk_register_block_patterns() {

	/**
	 * Creates the block pattern categories from the Block Pattern taxonomy.
	 *
	 */
	$terms = get_terms( array(
    'taxonomy' => 'tk_block_pattern_category',
    'hide_empty' => true,
	) );

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
		foreach ( $terms as $term ) {
			register_block_pattern_category(
				$term->slug,
				array( 'label' => __( $term->name, 'tk_block_patterns' ) )
			);
		}
	}


	/**
	 * Creates the block patterns from the Block Patterns post type
	 *
	 */
	$patterns = new WP_Query( [
		'post_type'      => 'tk_block_patterns',
		'posts_per_page' => -1
	] );

	if ( $patterns->have_posts() ) {

		while ( $patterns->have_posts() ) {
			$patterns->the_post();
			global $post;

			$term_obj_list = get_the_terms( $post->ID, 'tk_block_pattern_category' );
			$terms_string  = implode(', ', wp_list_pluck( $term_obj_list, 'slug' ) );

			register_block_pattern(
				'tk/' . sanitize_key( $post->post_name ),
				array(
						'title'       =>  wp_strip_all_tags( $post->post_title ) ,
						'content'     =>  $post->post_content,
						'categories'	=>  array( $terms_string ),
				)
			);
		}
	}
	wp_reset_postdata();

}
add_action( 'init', 'tk_register_block_patterns' );
