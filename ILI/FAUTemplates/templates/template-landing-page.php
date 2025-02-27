<?php
/**
 * Template Name: ILI Startseite
 *
 * @package WordPress
 * @subpackage FAU
 * @since FAU 1.0
 */

get_header();
?>
    <?php // Template part "Slider"
        if( get_post_meta($post->ID, '_ilifautpl_show_slider', true ) === '1' ) {
            include 'template-parts/template-slider.php';
        } else {
            echo '<div class="ilifautpl-slider-fallback"></div>';
        } ?>
    
	<div id="content" class="start">
		<div class="content-container">	   
		    <?php get_template_part('template-parts/content', 'portalmenu-oben'); ?>
			 <div class="content-row">
			    <div class="portalpage-content">	
    				    <main <?php if( function_exists('fau_get_page_langcode') ) { echo fau_get_page_langcode($post->ID); } ?>>
                        
    					<h1 class="screen-reader-text"><?php the_title(); ?></h1>
    					
    					<?php
					
					// The Content
					while ( have_posts() ) : the_post();
					    echo '<div class="ilifautpl-landing-page-content">';
							    the_content();
					    echo '</div>';
					endwhile;
								
					// iilifautpl options blogroll
					if( get_post_meta($post->ID, '_ilifautpl_has_blogroll', true ) !== '0' ): 		
    					
					    $number = 0;
					    $max = get_theme_mod('start_max_newspertag');
					    $maxall = get_theme_mod('start_max_newscontent');
					    $displayedposts = array();
					    $newscat = get_theme_mod('start_link_news_cat');
					    for($j = 1; $j <= 3; $j++) {
						    $i = 0;
						    $thistag = get_theme_mod('start_prefix_tag_newscontent').$j;    
						    $query = new WP_Query( 'tag='.$thistag );

						     while ($query->have_posts() && ($i<$max) && ($number<$maxall) ) { 
							$query->the_post();

							if( function_exists('fau_display_news_teaser') ) {
							    echo fau_display_news_teaser($post->ID);
							}

							$i++;
							$number++;
							$displayedposts[] = $post->ID;
						    }
						    wp_reset_postdata();
						    wp_reset_query();

					    }
					    if (($number==0) || ($number < $maxall)) {   
						if ($number < $maxall) {
						    $num = $maxall - $number;
						    if ($num <=0 ) {
							$num=1;
						    }
						    if (isset($newscat)) {
							$query = new WP_Query(  array( 'post__not_in' => $displayedposts, 'posts_per_page'  => $num, 'has_password' => false, 'post_type' => 'post', 'cat' => $newscat  ) );
						    } else {
							$query = new WP_Query(  array( 'post__not_in' => $displayedposts, 'posts_per_page'  => $num, 'has_password' => false, 'post_type' => 'post'  ) );							    
						    }
						} else {
						    $args = '';

						    if (isset($newscat)) {
								    $args = 'cat='.$newscat;	
						    }
						    if (isset($args)) {
								    $args .= '&';
						    }

						    $args .= 'post_type=post&has_password=0&posts_per_page='.get_theme_mod('start_max_newscontent');	
						    $query = new WP_Query( $args );
						}
						while ($query->have_posts() ) { 
							$query->the_post();

							if( function_exists('fau_display_news_teaser') ) {
								echo fau_display_news_teaser($post->ID);
							}

							wp_reset_postdata();
						}
					    }
					    $showcatlink = get_theme_mod('start_link_news_show');
					    if (($showcatlink==true) && ($newscat>0)) {
							    if( function_exists('fau_get_category_links') ) {
									    echo fau_get_category_links();
							    }
					    }
					
					
					endif;
					?>			    
				    </main>	
				</div>
				<?php get_template_part('template-parts/sidebar', 'portal'); ?>
			</div> <!-- /row -->
				
			<?php  
			
			 get_template_part('template-parts/content', 'portalmenu-unten');
			 

			    if( function_exists('fau_imagelink_get') ) {
				$logoliste = get_post_meta( $post->ID, 'fauval_imagelink_catid', true );			
				if ($logoliste) { 
					    /* New since 1.10.57 */
					    $logos = fau_imagelink_get(array('size' => "logo-thumb", 'catid' => $logoliste, "autoplay" => true, "dots" => true));


					    if ((isset($logos) && (!empty($logos)))) {
					    echo "<hr>\n";
					    echo $logos;
				}
			    }
			}		
			 ?>			
		</div> <!-- /container -->
	</div> <!-- /content -->
<?php get_template_part('template-parts/footer', 'social'); ?>	
<?php 
get_footer();
