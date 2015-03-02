<?php

/**
 * @package CleanScript
 * @version 1.0
 */
/*
  Plugin Name: Cleanscript - Page Templates Info
  Plugin URI: http://wordpress.org/plugins/cs_page_templates_info
  Description: This is a plugin to find the existing page templates in your theme, page templates that are unused in your theme, gives you a list of page templates to choose from and shows
 * you where they are used (outputs a list of links with pages)
  Author: cleanscript
  Version: 1.0
  Author URI: http://turcuciprian.com
 */
class cs_pti_settings_page {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
// This page will be under "Settings"
        add_options_page(
                'Page Templates Info - Settings page', 'Page Templates Info', 'manage_options', 'cs_page_templates_info', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
// Set class property
        $this->options = get_option('my_option_name');
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Page Templates Info Settings</h2>           
            <h3>Available Custom page templates:</h3>
            <?php
            $templates = get_page_templates();
            foreach ($templates as $template_name => $template_filename) {
                echo "$template_name ($template_filename)<br />";
            }
            ?>
            <h3>Unused Custom Page templates:</h3>
            <?php
            $args = array('post_type' => 'page');
            // The Query
            $the_query = new WP_Query($args);
            foreach ($templates as $template_name => $template_filename) {
                $is_used = false;
// The Loop
                if ($the_query->have_posts()) {
                    echo '<ul>';
                    while ($the_query->have_posts()) {
                        $the_query->the_post();
                        $the_id = get_the_ID();
                        $current_page_template = get_page_template_slug($the_id);
                        if ($template_filename === $current_page_template) {
                            $is_used = true;
                        }

                        //echo "$template_name ($template_filename)<br />";
                    }
                    echo '</ul>';
                } else {
                    // no posts found
                }
                if (!$is_used) {
                    echo "$template_name ($template_filename)<br />";
                }
            }
            /* Restore original Post Data */
            wp_reset_postdata();
            ?>
            <h3>Find pages With Custom page Templates</h3>
            <p>Check the page templates that you want to see the pages for:</p>
            <ul id="cs_pti_check_list">
                <?php
                foreach ($templates as $template_name => $template_filename) {
                    ?>
                    <li><input type="checkbox" value="<?php echo str_replace(' ', '', $template_name); ?>" /><?php echo $template_name; ?> - (<?php echo $template_filename; ?>)</li>
                    <?php
                }
                ?>
            </ul>

            <p class="submit">
                <input type="submit" value="Click to See Pages" id="cs_pti_submit" class="button button-primary" />
            </p>
            <p id="cs_pti_ajax_result">

            </p>

        </div>
        <?php
    }

}

if (is_admin())
    $cs_pti_settings_page = new cs_pti_settings_page();

add_action('admin_enqueue_scripts', 'cs_pti_admin_enqueue');

function cs_pti_admin_enqueue() {
    wp_enqueue_script('cs_pti_main', plugins_url('/script.js', __FILE__), array('jquery'));
}

add_action('admin_enqueue_scripts', 'my_enqueue');

function my_enqueue($hook) {
    if ('index.php' != $hook) {
// Only applies to dashboard panel
        return;
    }

    wp_enqueue_script('ajax-script', plugins_url('/js/my_query.js', __FILE__), array('jquery'));

// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
    wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
}

// Same handler function...
add_action('wp_ajax_cs_pti_action', 'my_action_callback');

function my_action_callback() {
    $result = $_POST['cs_pti_request'];
    $result = str_replace('\\', '', $result);
    $result_decoded = json_decode($result, TRUE);

    $templates = get_page_templates();
    foreach ($templates as $template_name => $template_filename) {
        foreach ($result_decoded['checked_items'] as $item) {
            if ($item === str_replace(' ', '', $template_name)) {
                //echo $template_filename;
                //if template is checked what to do with it
                $args = array('post_type' => 'page');
                // The Query
                $the_query = new WP_Query($args);
                if ($the_query->have_posts()) {
                    echo "<h4>".$template_name."</h4>";
                    echo '<ul>';
                    while ($the_query->have_posts()) {
                        $the_query->the_post();
                        
                        $the_id = get_the_ID();
                        $current_page_template = get_page_template_slug($the_id);
                        if ($template_filename === $current_page_template) {
                            ?> <li><a href="<?php echo get_the_permalink(); ?>" target="_black"><?php echo get_the_permalink(); ?></a></li> <?php
                        }
                        
                    }
                }else{
                    //nothing found
                }
                wp_reset_postdata();
            }
        }
//echo "$template_name ($template_filename)<br />";
    }
    wp_die();
}
