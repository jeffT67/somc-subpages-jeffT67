<?php
/**
 * @package somcsubpages_jeffT67
 * @version 1.0
 */
/*
  Plugin Name: somcsubpages_jeffT67
  Plugin URI:
  Description: Display subpages
  Author: Jeff Thier
  Version: 1.0
  Author URI: http://www.klixo.se
 */

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

add_action('widgets_init', 'somcsubpages_jeffT67', 1);
add_shortcode('somcsubpages_jeffT67', 'SubPCode');

function somcsubpages_jeffT67() {
    register_widget('somcsubpages_jeffT67');
}

function SubPCode() {
    $subPages = new somcsubpages_jeffT67();
    $subPages->widget($args, $instance);
}

class somcsubpages_jeffT67 extends WP_Widget {

    public function __construct() {

        $widget_ops = array('classname' => 'somcsubpages_jeffT67', 'description' => __('Display all sub pages for the current page', 'subpage')
        );

        $this->WP_Widget('somcsubpages_jeffT67', __('somcsubpages_jeffT67 Widget', 'subpage'), $widget_ops);
    }

    public function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'subpage'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
        <p>
            <label for="<?php echo $this->get_field_id('sort_order'); ?>"><?php _e('Sorting Order', 'subpage'); ?></label>
            <select name="<?php echo $this->get_field_name('sort_order'); ?>" id="<?php echo $this->get_field_id('sort_order'); ?>" class="widefat">	
                <?php
                $order_options = array(
                    'ASC' => __('Ascending', 'subpage'),
                    'DESC' => __('Descending', 'subpage'),
                );
                foreach ($order_options as $option_key => $option_value) {
                    ?>
                    <option <?php selected($instance['sort_order'], $option_key); ?> value="<?php echo esc_attr($option_key); ?>"><?php echo __($option_value, 'subpage'); ?></option>
                <?php } ?>      
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumb_size'); ?>"><?php _e('Thumbnail Size', 'subpage'); ?></label>
            <select name="<?php echo $this->get_field_name('thumb_size'); ?>" id="<?php echo $this->get_field_id('thumbSize'); ?>" class="widefat">	
                <?php
                $thumbSizeOpts = array(
                    '8' => __('8x8', 'subpage'),
                    '16' => __('16x16', 'subpage'),
                    '24' => __('24x24', 'subpage'),
                    '32' => __('32x32', 'subpage'),
                    '48' => __('48x48', 'subpage'),
                );
                foreach ($thumbSizeOpts as $option_key => $option_value) {
                    ?>
                    <option <?php selected($instance['thumb_size'], $option_key); ?> value="<?php echo esc_attr($option_key); ?>"><?php echo __($option_value, 'subpage'); ?></option>
                <?php } ?>      
            </select>
        </p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['sort_order'] = $new_instance['sort_order'];
        $instance['thumb_size'] = $new_instance['thumb_size'];
        return $instance;
    }

    function widget($args, $instance) {

        global $post;
        
        if (!is_null($args)) {
            extract($args, EXTR_SKIP);
        }

        echo $before_widget;
        $title = empty($instance['title']) ? 'Pages' : apply_filters('widget_title', $instance['title']);
        $sort_order = empty($instance['sort_order']) ? 'ASC' : apply_filters('widget_sort', $instance['sort_order']);
        $this->thumbSize = empty($instance['thumb_size']) ? '24' : $instance['thumb_size'];

        if (!empty($title)) {
            echo $before_title . $title . $after_title;
        }

        $page_id = $post->ID;
        $args = array(
            'order' => $sort_order,
            'post_parent' => $page_id,
            'post_status' => 'publish',
            'post_type' => 'page',
        );

        $subPages = get_children($args);


        if ($subPages) {
            $this->listPages($subPages);
        }

        echo $after_widget;
    }

    function listPages($pages) {
        echo '<ul class="ls_page_list">';
        foreach ($pages as $page) {
            echo '<li><a href="' . $page->guid . '">' .
            $this->makeThumbnail($page->ID) .
            substr($page->post_title, 0, 20) .
            '</a></li>';
        }
        echo '</ul>';
    }

    //Create a thumbnail image of featured image, saves it and returns an image tag.
    
    function makeThumbnail($pageID) {
        $featuredImage = wp_get_attachment_image_src(get_post_thumbnail_id($pageID), 'full');
        $featuredImageUrl = $featuredImage[0];

        if ($featuredImageUrl != "") {

            $featuredImagePath = get_attached_file(get_post_thumbnail_id($pageID));
            $imgInfo = pathinfo($featuredImageUrl);
            $OrigName = $imgInfo['filename'];

            $resizedImgName = $OrigName . "-" . $this->thumbSize . "x" . $this->thumbSize;
            $newThumbUrl = str_replace($OrigName, $resizedImgName, $featuredImageUrl);
            $newThumbPath = str_replace($OrigName, $resizedImgName, $featuredImagePath);

            if (!file_exists($newThumbPath)) {
                //Avoid to recreate thumbnails each time page loads!
                $resizedImg = wp_get_image_editor($featuredImagePath);
                if (!is_wp_error($resizedImg)) {
                    $resizedImg->resize($this->thumbSize, $this->thumbSize, true);
                    $resizedImg->save($newThumbPath);
                }
            }

            return "<img src ='" . $newThumbUrl . "'>";
        }
    }
}
?>