<?php

add_action( 'wp_enqueue_scripts', 'rehub_child_enqueue_styles' );
function rehub_child_enqueue_styles() {
    $parenthandle = 'rehub-theme';
    $theme = wp_get_theme();
    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css',
                      array(),  // if the parent theme code has a dependency, copy it to here
                      $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
                      array( $parenthandle ),
                      $theme->get('Version') // this only works if you have Version in the style header
    );
}

if( !function_exists('rh_post_header_meta') ) { //post meta
    function rh_post_header_meta ($admin_exist = true, $time_exist = true, $view_exist = true, $comment_exist = true, $cats_post = true ){
        global $post;
        if(rehub_option('exclude_author_meta') != 1 && ($admin_exist != false)){ ?>
            <span class="admin_meta">
                <?php do_action('pp_multiple_authors_show_author_box', false, 'inline_avatar', false, true, $post->ID); ?>
		    </span>
        <?php }
        if(rehub_option('exclude_date_meta') != 1 && ($time_exist != false)){ ?>
            <span class="date_meta">
  			<?php if(rehub_option('date_publish')):?>
                <?php the_time(get_option( 'date_format' )); ?>
            <?php else:?>
                <?php the_modified_time(get_option( 'date_format' )); ?>
            <?php endif;?>
 		</span>
        <?php }
        if(rehub_option('post_view_disable') != 1 && ($view_exist != false) && function_exists('RH_get_post_views')){ ?>
            <?php $rehub_views = RH_get_post_views($post->ID); if ($rehub_views !='') :?>
                <span class="postview_meta"><?php echo (int)$rehub_views; ?> </span>
            <?php endif ;?>
        <?php }
        if(rehub_option('exclude_comments_meta') != 1 && ($comment_exist != false)){ ?>
            <?php if($comment_exist=='compact'):?>
                <span class="comm_count_meta"><?php comments_popup_link( esc_html__('0','rehub-theme'), esc_html__('1','rehub-theme'), esc_html__('%','rehub-theme'), 'comm_meta', ''); ?></span>
            <?php elseif ($comment_exist == 'compactnoempty'):?>
                <?php if($post->comment_count > 0):?>
                    <span class="comm_count_meta"><?php comments_popup_link( esc_html__('0','rehub-theme'), esc_html__('1','rehub-theme'), esc_html__('%','rehub-theme'), 'comm_meta', ''); ?></span>
                <?php endif;?>
            <?php else:?>
                <span class="comm_count_meta"><?php comments_popup_link( esc_html__('no comments','rehub-theme'), esc_html__('1 comment','rehub-theme'), esc_html__('% comments','rehub-theme'), 'comm_meta', ''); ?></span>
            <?php endif;?>
        <?php }
        if(rehub_option('exclude_cat_meta') != 1 && ($cats_post != false)){
            $postidforcat = $post->ID;
            if ('post' == $post->post_type) {
                $categories = get_the_category($postidforcat);
                $separator = ', ';
                $output = '';
                if ( ! empty( $categories ) ) {
                    echo '<span class="cat_link_meta">';
                    foreach( $categories as $category ) {
                        $output .= '<a class="cat" href="' . esc_url( get_category_link( $category->term_id ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all posts in %s', 'rehub-theme' ), $category->name ) ) . '">' . esc_html( $category->name ) . '</a>' . $separator;
                    }
                    echo trim( $output, $separator );
                    echo '</span>';
                }
            }
            elseif ('blog' == $post->post_type) {
                $term_list = get_the_term_list( $post->ID, 'blog_category', '<span class="date_meta">', ', ', '</span>' );
                if(!is_wp_error($term_list)){
                    echo ''.$term_list;
                }
            }
            elseif ('product' == $post->post_type) {
                $term_list = get_the_term_list( $post->ID, 'product_cat', '<span class="date_meta">', ', ', '</span>' );
                if(!is_wp_error($term_list)){
                    echo ''.$term_list;
                }
            }
        }
    }
}

if( !function_exists('rh_post_header_meta_big') ) { //post meta_big
    function rh_post_header_meta_big (){
        global $post;
        ?>
        <div class="floatleft mr15 rtlml15">
            <span class="floatleft authortimemeta">
				<?php if(rehub_option('exclude_author_meta') != 1):?>
                    <?php do_action('pp_multiple_authors_show_author_box', false, 'inline_avatar', false, true, $post->ID); ?>
                <?php endif;?>
                <?php if(rehub_option('exclude_date_meta') != 1):?>
                    <div class="date_time_post">
			 			<?php if(rehub_option('date_publish')):?>
                            <?php the_time(get_option( 'date_format' )); ?>
                        <?php else:?>
                            <?php the_modified_time(get_option( 'date_format' )); ?>
                        <?php endif;?>
					</div>
                <?php endif;?>
			</span>

        </div>
        <div class="floatright ml15 postviewcomm mt5">
            <?php if(rehub_option('post_view_disable') != 1 && function_exists('RH_get_post_views')):?>
                <?php $rehub_views = RH_get_post_views($post->ID);?>
                <span class="postview_meta mr15 ml15"><strong><?php echo (int)$rehub_views; ?></strong> <?php esc_html_e('Views', 'rehub-theme');?></span>
            <?php endif;?>
            <?php if(rehub_option('exclude_comments_meta') != 1):?>
                <span class="comm_count_meta"><strong><?php comments_popup_link( esc_html__('0','rehub-theme'), esc_html__('1 comment','rehub-theme'), esc_html__('% comments','rehub-theme'), 'comm_meta', ''); ?></strong></span>
            <?php endif;?>
        </div>
        <?php
    }
}

if(!function_exists('rh_author_detail_box')){
    function rh_author_detail_box (){
        ?>
        <?php
        $author_ID = get_the_author_meta('ID');
        if(function_exists('mycred_get_users_rank')){
            if(rehub_option('rh_mycred_custom_points')){
                $custompoint = rehub_option('rh_mycred_custom_points');
                $mycredrank = mycred_get_users_rank($author_ID, $custompoint );
            }
            else{
                $mycredrank = mycred_get_users_rank($author_ID);
            }
        }
        if(function_exists('mycred_display_users_total_balance') && function_exists('mycred_render_shortcode_my_balance')){
            if(rehub_option('rh_mycred_custom_points')){
                $custompoint = rehub_option('rh_mycred_custom_points');
                $mycredpoint = mycred_render_shortcode_my_balance(array('type'=>$custompoint, 'user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
            }
            else{
                $mycredpoint = mycred_render_shortcode_my_balance(array('user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
            }
        }
        ?>
        <div class="author_detail_box clearfix">
            <?php do_action('pp_multiple_authors_show_author_box', false, 'boxed', false, true, $post->ID); ?>
        </div>
        <?php
    }
}
