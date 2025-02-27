<?php

namespace ILI\FAUTemplates;

defined('ABSPATH') || exit;

class Meta {
    
    protected $templates;
    
    public function __construct() {
        add_action( 'add_meta_boxes', array($this, 'ilifautpl_add_meta_boxes') );
        add_action( 'save_post', array($this, 'ilifautpl_save_meta_boxes') );
        add_action( 'wp_ajax_ilifautpl_get_slide_image', array($this, 'wp_ajax_ilifautpl_get_slide_image') );
    }
    
    public function ilifautpl_add_meta_boxes() {
        global $post;
        
        $screens = get_post_types();
        
        foreach ( $screens as $screen ) {
            $template = get_post_meta( $post->ID, '_wp_page_template', true );
            
            // Landing Page slides
            if( 'templates/template-landing-page.php' === $template ) {
                
                wp_enqueue_media();
                
                add_meta_box(
                    'ilifautpl-slides',
                    esc_html__( 'Slider (ILI FAU Templates)', 'ili-fau-templates' ),
                    array($this, 'landing_page_slides_callback'),
                    $screen
                );
                
                add_meta_box(
                    'ilifautpl-topic-boxes',
                    esc_html__( 'Themenboxen', 'ili-fau-templates' ),
                    array($this, 'landing_page_topic_boxes_callback'),
                    $screen
                );

                add_meta_box(
                    'ilifautpl-slider-options',
                    esc_html__( 'Landing Page Optionen', 'ili-fau-templates' ),
                    array($this, 'landing_page_slider_options_callback'),
                    $screen
                );

                if (get_theme_mod('advanced_topevent') == true) {
                    add_meta_box(
                        'fau_metabox_post_topevent', 
                        esc_html__('Top-Event', 'fau'), 
                        'fau_do_metabox_post_topevent', 
                        $screens,
                        'normal',
                        'high'
                    );
                }
            }
        }
    }
    
    public function landing_page_slides_callback() {
        wp_nonce_field( 'ilifautpl_meta_boxes_nonce', 'ilifautpl_meta_boxes_nonce' );
        
        $upload_dir = wp_upload_dir();
        $slides = get_post_meta( get_the_ID(), '_ilifautpl_slides', true );
        
        if( empty( $slides ) ) {
            $slides = array('');
        }

        foreach( $slides as $key => $slide ) {
            $slide_id = (int)$key + 1;

            $id = ! empty( $slide['id'] ) ? $slide['id'] : '';
            $atts = function_exists('fau_get_image_attributs') ? fau_get_image_attributs( $id ) : [];
            $order = isset( $slide['order'] ) ? $slide['order'] : 0;
            $position = isset( $slide['position'] ) ? $slide['position'] : 'center center';
            $url = $upload_dir['baseurl'] . '/' . $atts['attachment_file'];
            $link = isset( $slide['link'] ) ? $slide['link'] : '';
            $headline = isset( $slide['headline'] ) ? $slide['headline'] : '';
            $subtitle = isset( $slide['subtitle'] ) ? $slide['subtitle'] : '';
            
            // print_r( fau_get_image_attributs( $id ) );
            
            echo '<div class="ilifautpl-input-slide-wrapper ilifautpl-input-select-wrapper" data-id="' . $slide_id . '">';
            echo '<label class="ilifautpl-label" for="ilifautpl-landing-page-slides">Slide ' . $slide_id . '</label>';
            
            $basename = basename( plugin_dir_path(  dirname( __FILE__ , 2 ) ) );
            $placeholder = esc_url( plugins_url() . '/' . $basename . '/assets/img/slide-preview.png' );
            
            if( ! empty( $url ) && ! empty( $id ) ) {
                echo '<img class="ilifautpl-slide-preview" src="' . $url . '" alt="" />';
            } else {
                echo '<img class="ilifautpl-slide-preview" src="' . $placeholder . '" alt="" />';
            }
            
            echo '<input class="ilifautpl-input ilifautpl-input-slide ilifautpl-input-select" type="hidden" id="ilifautpl-input-slide-ids" name="ilifautpl-input-slide-ids[]" value="' . $id . '" placeholder="ID&hellip;">';
            echo '<div class="ilifautpl-input-slide-id-buttons"><a class="button ilifautpl-input-slide-media ilifautpl-input-select-media" data-id="' . $slide_id . '">' . __('Bild auswählen', 'ili-fau-templates') . '</a> <a class="button ilifautpl-remove-slide" data-placeholder="' . $placeholder . '">' . __('Löschen', 'ili-fau-templates') . '</a></div>';
            echo '<label class="ilifautpl-label" for="ilifautpl-input-slide-orders">Reihenfolge</label>';
            echo '<input class="ilifautpl-input ilifautpl-input-order" type="text" id="ilifautpl-input-slide-orders" name="ilifautpl-input-slide-orders[]" value="' . $order . '" placeholder="0-9999&hellip;">';
            echo '<label class="ilifautpl-label" for="ilifautpl-input-slide-positions">Position (Layout)</label>';
            echo '<select class="ilifautpl-input ilifautpl-input-slide-positions" id="ilifautpl-input-slide-positions" name="ilifautpl-input-slide-positions[]">';
            
            foreach( array(
                0 => 'left top',
                1 => 'center top',
                2 => 'right top',
                3 => 'left center',
                4 => 'center center',
                5 => 'right center',
                6 => 'left bottom',
                7 => 'center bottom',
                8 => 'right bottom',
            ) as $key => $val ) {
                ?><option value="<?php echo $val; ?>"<?php
                    if( $val === $position ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
                
            echo '</select>';
            echo '<label class="ilifautpl-label" for="ilifautpl-input-slide-links">URL</label>';
            echo '<input class="ilifautpl-input ilifautpl-input-slide-link" type="url" id="ilifautpl-input-slide-links" name="ilifautpl-input-slide-links[]" value="' . $link . '" placeholder="Link&hellip;">';
            echo '<label class="ilifautpl-label" for="ilifautpl-input-slide-headlines">Überschrift</label>';
            echo '<input class="ilifautpl-input ilifautpl-input-slide-headline" type="text" id="ilifautpl-input-slide-headlines" name="ilifautpl-input-slide-headlines[]" value="' . wp_kses($headline, array('&shy;', '&nbsp;')) . '" placeholder="Überschrift&hellip;" maxlength="64">';
            echo '<label class="ilifautpl-label" for="ilifautpl-input-slide-subtitle">Beschreibung</label>';
            echo '<input class="ilifautpl-input ilifautpl-input-slide-subtitle" id="ilifautpl-input-slide-subtitles" name="ilifautpl-input-slide-subtitles[]" value="' . $subtitle . '" placeholder="Schlagzeile&hellip;" maxlength="256">';
            echo '</div>';
        }

        echo '<a class="button ilifautpl-add-slide" data-placeholder="' . $placeholder . '">' . __('Slide hinzufügen', 'ili-fau-templates') . '</a>';
        echo '<br><br><input type="submit" name="submit" id="submit" class="button button-primary button-ilifautpl-save" value="' . __('Änderungen speichern', 'ili-fau-templates' ) . '">';
    }
    
    // Topic Boxes
    function landing_page_topic_boxes_callback() {
        global $post;
        
        $original_query = $post;
        $selected_topic_boxes = get_post_meta($post->ID, '_ilifautpl_topic_boxes', true);
        
        // Topic boxes without selected boxes
        $args = array(
            'post_type' => 'ilifautpl_topic_box',
            'post_status' => 'publish',
            'exclude' => $selected_topic_boxes,
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        );

        $remaining_topic_boxes = get_posts( $args );

        if( empty( $selected_topic_boxes ) )
            $selected_topic_boxes = [];

        echo '<select class="ilifautpl-multi-select" multiple="multiple" name="_ilifautpl_topic_boxes[]" id="_ilifautpl_topic_boxes">';
            echo '<option value="" disabled>Bitte wählen...</option>';
            
            // Output the selected boxes first
            foreach( $selected_topic_boxes as $box_id ):
                if( get_post_status ( $box_id ) !== 'publish' )
                    continue;

                $box = get_post( $box_id );

                echo '<option value="' . $box->ID . '" selected>' . $box->post_title . ' (ID ' . $box->ID . ')</option>';
            endforeach;

            // Output the remaining boxes
            foreach( $remaining_topic_boxes as $box ):
                echo '<option value="' . $box->ID . '">' . $box->post_title . ' (ID ' . $box->ID . ')</option>';
            endforeach;

        echo '</select>';
    }

    // Slider has navigation dots callback
    public function landing_page_slider_options_callback() {
        
        // TODO: DRY options

        // Slider on/off
        $show_slider = get_post_meta( get_the_ID(), '_ilifautpl_show_slider', true);
        if( $show_slider === null || $show_slider === '' ) {
            $show_slider = 1;
        }

        echo '<label class="ilifautpl-label" for="_ilifautpl_show_slider">Slider anzeigen?</label>';
        echo '<select name="_ilifautpl_show_slider" id="_ilifautpl_show_slider">';
            foreach( array(
                0 => 'Verbergen',
                1 => 'Anzeigen',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$show_slider ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Navigation Dots
        $slider_has_dots = get_post_meta( get_the_ID(), '_ilifautpl_slider_has_dots', true);
        if( $slider_has_dots === null || $slider_has_dots === '' ) { $slider_has_dots = 1; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_slider_has_dots">Navigation unter Slider anzeigen?</label>';
        echo '<select name="_ilifautpl_slider_has_dots" id="_ilifautpl_slider_has_dots">';
            foreach( array(
                0 => 'Verbergen',
                1 => 'Anzeigen',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$slider_has_dots ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Navigation Arrows
        $slider_has_arrows = get_post_meta( get_the_ID(), '_ilifautpl_slider_has_arrows', true);
        if( $slider_has_arrows === null || $slider_has_arrows === '' ) { $slider_has_arrows = 1; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_slider_has_arrows">Pfeilnavigation im Slider anzeigen?</label>';
        echo '<select name="_ilifautpl_slider_has_arrows" id="_ilifautpl_slider_has_arrows">';
            foreach( array(
                0 => 'Verbergen',
                1 => 'Anzeigen',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$slider_has_arrows ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Fade
        $slider_fade = get_post_meta( get_the_ID(), '_ilifautpl_slider_fade', true);
        if( $slider_fade === null || $slider_fade === '' ) { $slider_fade = 0; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_slider_fade">Animationstyp?</label>';
        echo '<select name="_ilifautpl_slider_fade" id="_ilifautpl_slider_fade">';
            foreach( array(
                0 => 'slide',
                1 => 'fade',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$slider_fade ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Skew
        $slider_skew = get_post_meta( get_the_ID(), '_ilifautpl_slider_skew', true);
        if( $slider_skew === null || $slider_skew === '' ) { $slider_skew = 0; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_slider_skew">Slider mit Schräge anzeigen?</label>';
        echo '<select name="_ilifautpl_slider_skew" id="_ilifautpl_slider_skew">';
            foreach( array(
                0 => 'Nein, keine Schräge',
                1 => 'Ja, mit Schräge',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$slider_skew ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';

        // Overlay
        $slider_overlay = get_post_meta( get_the_ID(), '_ilifautpl_slider_overlay', true);
        if( ! $slider_overlay || $slider_overlay === null || $slider_overlay === '' ) { $slider_overlay = 'none'; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_slider_overlay">Overlay anzeigen (bessere Lesbarkeit)?</label>';
        echo '<select name="_ilifautpl_slider_overlay" id="_ilifautpl_slider_overlay">';
            foreach( array(
                'none' => 'Nein, kein Overlay.',
                'blue-gradient' => 'Blau (Farbverlauf)',
                'black-gradient' => 'Schwarz (Farbverlauf)',
                'blue' => 'Blaues Overlay',
                'black' => 'Schwarzes Overlay',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === $slider_overlay ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Topic boxes read more
        $read_more = get_post_meta( get_the_ID(), '_ilifautpl_show_topic_boxes_read_more', true);
        if( $read_more === null || $read_more === '' ) {
            $read_more = 1;
        }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_show_topic_boxes_read_more">Link "Weiterlesen" in Themenboxen anzeigen?</label>';
        echo '<select name="_ilifautpl_show_topic_boxes_read_more" id="_ilifautpl_show_topic_boxes_read_more">';
            foreach( array(
                0 => 'Verbergen',
                1 => 'Anzeigen',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$read_more ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Topic Boxes Skew
        $topic_boxes_skew = get_post_meta( get_the_ID(), '_ilifautpl_topic_boxes_skew', true);
        if( $topic_boxes_skew === null || $topic_boxes_skew === '' ) { $topic_boxes_skew = 1; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_topic_boxes_skew">Themenboxen mit Schräge anzeigen?</label>';
        echo '<select name="_ilifautpl_topic_boxes_skew" id="_ilifautpl_topic_boxes_skew">';
            foreach( array(
                0 => 'Nein, keine Schräge',
                1 => 'Ja, mit Schräge',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$topic_boxes_skew ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Show fallback title
        $show_title = get_post_meta( get_the_ID(), '_ilifautpl_show_fallback_title', true);
        if( $show_title === null || $show_title === '' ) {
            $show_title = 1;
        }
        
        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_show_fallback_title">Titel bei Fallback auf Beitrags- oder Default-Bild anzeigen?</label>';
        echo '<select name="_ilifautpl_show_fallback_title" id="_ilifautpl_show_fallback_title">';
            foreach( array(
                0 => 'Verbergen',
                1 => 'Anzeigen',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$show_title ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Toggle Blogroll
        $has_blogroll = get_post_meta( get_the_ID(), '_ilifautpl_has_blogroll', true);
        if( $has_blogroll === null || $has_blogroll === '' ) { $has_blogroll = 1; }

        echo '<br><br><label class="ilifautpl-label" for="_ilifautpl_has_blogroll">Blogroll anzeigen?</label>';
        echo '<select name="_ilifautpl_has_blogroll" id="_ilifautpl_has_blogroll">';
            foreach( array(
                0 => 'Verbergen',
                1 => 'Anzeigen',
            ) as $key => $val ) {
                ?><option value="<?php echo $key; ?>"<?php
                    if( $key === (int)$has_blogroll ) echo ' selected="selected"';
                ?>><?php echo $val; ?></option><?php
            }
        echo '</select>';
        
        // Submit
        echo '<br><br><input type="submit" name="submit" id="submit" class="button button-primary button-ilifautpl-save" value="' . __('Änderungen speichern', 'ilifautpl' ) . '">';
    }
    
    // Refresh slide preview image
    function ilifautpl_get_slide_image() {
        if( isset( $_GET['id'] ) ) {
            $image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'medium', false, array( 'id' => 'ilifautpl-slide-preview' ) );
            
            $data = array(
                'image' => $image,
            );
            
            wp_send_json_success( $data );
        } else {
            wp_send_json_error();
        }
    }
    
    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function ilifautpl_save_meta_boxes( $post_id )
    {
        if ( ! isset( $_POST['ilifautpl_meta_boxes_nonce'] ) )
            return;

        if ( ! wp_verify_nonce( $_POST['ilifautpl_meta_boxes_nonce'], 'ilifautpl_meta_boxes_nonce' ) )
            return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }
        
        // Sanitize user input.
        $ids = $_POST['ilifautpl-input-slide-ids'];
        $positions = $_POST['ilifautpl-input-slide-positions'];
        $orders = $_POST['ilifautpl-input-slide-orders'];
        $links = $_POST['ilifautpl-input-slide-links'];
        $headlines = $_POST['ilifautpl-input-slide-headlines'];
        $subtitles = $_POST['ilifautpl-input-slide-subtitles'];
        
        $slides = array();
        $upload_dir = wp_upload_dir();
        
        foreach( $ids as $key => $id ) {
            $atts = function_exists('fau_get_image_attributs') ? fau_get_image_attributs( $id ) : [];
            
            array_push( $slides, array(
                'id' => absint( $id ),
                'order' => isset( $orders[$key] ) ? absint( $orders[$key] ) : 0,
                'position' => isset( $positions[$key] ) ? sanitize_text_field( $positions[$key] ) : 'center center',
                'url' => esc_url( $upload_dir['baseurl'] . '/' . $atts['attachment_file'] ),
                'link' => isset( $links[$key] ) && filter_var( $links[$key], FILTER_VALIDATE_URL ) ? $links[$key] : '',
                'headline' => isset( $headlines[$key] ) ? wp_kses( $headlines[$key], ['&shy;', '&nbsp;'] ) : '',
                'subtitle' => isset( $subtitles[$key] ) ? wp_kses( $subtitles[$key], ['&shy;', '&nbsp;'] ) : '',
            ) );
        }

        $topic_boxes = $_POST['_ilifautpl_topic_boxes'];
        
        foreach( $topic_boxes as $key => $topic_box ) {
            $topic_boxes[$key] = (int)$topic_box;
        }

        $show_slider = (int)$_POST['_ilifautpl_show_slider'];
        $slider_has_dots = (int)$_POST['_ilifautpl_slider_has_dots'];
        $slider_has_arrows = (int)$_POST['_ilifautpl_slider_has_arrows'];
        $slider_fade = (int)$_POST['_ilifautpl_slider_fade'];
        $slider_skew = (int)$_POST['_ilifautpl_slider_skew'];
        $slider_overlay = sanitize_text_field( $_POST['_ilifautpl_slider_overlay'] );
        $read_more = (int)$_POST['_ilifautpl_show_topic_boxes_read_more'];
        $fallback_title = (int)$_POST['_ilifautpl_show_fallback_title'];
        $has_blogroll = (int)$_POST['_ilifautpl_has_blogroll'];
        $topic_boxes_skew = (int)$_POST['_ilifautpl_topic_boxes_skew'];

        // Save
        update_post_meta( $post_id, '_ilifautpl_slides', $slides );
        update_post_meta( $post_id, '_ilifautpl_topic_boxes', $topic_boxes );
        update_post_meta( $post_id, '_ilifautpl_show_slider', $show_slider );
        update_post_meta( $post_id, '_ilifautpl_slider_has_dots', $slider_has_dots );
        update_post_meta( $post_id, '_ilifautpl_slider_has_arrows', $slider_has_arrows );
        update_post_meta( $post_id, '_ilifautpl_slider_fade', $slider_fade );
        update_post_meta( $post_id, '_ilifautpl_slider_skew', $slider_skew );
        update_post_meta( $post_id, '_ilifautpl_slider_overlay', $slider_overlay );
        update_post_meta( $post_id, '_ilifautpl_show_topic_boxes_read_more', $read_more );
        update_post_meta( $post_id, '_ilifautpl_show_fallback_title', $fallback_title );
        update_post_meta( $post_id, '_ilifautpl_has_blogroll', $has_blogroll );
        update_post_meta( $post_id, '_ilifautpl_topic_boxes_skew', $topic_boxes_skew );
    }
}
