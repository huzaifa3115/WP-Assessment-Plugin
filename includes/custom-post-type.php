<?php

class CustomPostType
{
    function __construct()
    {
        add_action('init', array($this, 'register_assessment_custom_post_type'));
        add_filter('manage_assessments_posts_columns', array($this, 'customize_assessments_admin_column'));
        add_action('manage_assessments_posts_custom_column', array($this, 'customize_assessments_admin_column_value'), 10, 2);
    }

    function activate(): void
    {
        flush_rewrite_rules();
    }

    function register(): void
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    function deactivate(): void
    {
        flush_rewrite_rules();
    }

    function register_assessment_custom_post_type(): void
    {
        $labels = array(
            'name' => _x('Assessments', 'assessment'),
            'singular_name' => _x('Assessment', 'assessment'),
            'add_new' => _x('Add New', 'assessment'),
            'add_new_item' => _x('Add New Assessment', 'assessment'),
            'edit_item' => _x('Edit Assessment', 'assessment'),
            'new_item' => _x('New Assessment', 'assessment'),
            'view_item' => _x('View Assessment', 'assessment'),
            'search_items' => _x('Search Assessments', 'assessment'),
            'not_found' => _x('No assessments found', 'assessment'),
            'not_found_in_trash' => _x('No assessments found in Trash', 'assessment'),
            'parent_item_colon' => _x('Parent Assessment:', 'assessment'),
            'menu_name' => _x('Assessments', 'assessment'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'public' => true,
            'capabilities' => array(
                'read_post' => 'read_assessment',
                'publish_posts' => 'publish_assessments',
                'edit_posts' => 'edit_assessments',
                'edit_others_posts' => 'edit_others_assessments',
                'delete_posts' => 'delete_assessments',
                'delete_others_posts' => 'delete_others_assessments',
                'read_private_posts' => 'read_private_assessments',
                'edit_post' => 'edit_assessment',
                'delete_post' => 'delete_assessment',
                'edit_published_post' => 'edit_published_assessment',
                'edit_published_posts' => 'edit_published_assessments',

            ),
            'map_meta_cap' => true
        );

        register_post_type('assessments', $args);
    }

    function admin_enqueue_scripts(): void
    {
        wp_enqueue_style('admin-css', WP_ASSESSMENT_ASSETS . '/css/style.css');
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');

        wp_enqueue_script('admin-js', WP_ASSESSMENT_ASSETS . '/js/admin/main.js');
    }

    function enqueue_scripts(): void
    {
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        wp_enqueue_style('front-style', WP_ASSESSMENT_ASSETS . '/css/front/style.css');
        wp_enqueue_style('front-responsive', WP_ASSESSMENT_ASSETS . '/css/front/responsive.css');

        wp_enqueue_script('jquery', WP_ASSESSMENT_ASSETS . '/js/jquery.min.js');
        wp_enqueue_script('bootstrap-min-js', WP_ASSESSMENT_ASSETS . '/js/bootstrap.min.js');
        wp_enqueue_script('main-js', WP_ASSESSMENT_ASSETS . '/js/front/main.js');
        wp_localize_script('main-js', 'ajax_object',
            array('ajax_url' => admin_url('admin-ajax.php')));
    }

    function customize_assessments_admin_column($columns)
    {
        $columns['assigned_moderator'] = 'Assigned To';
        return $columns;
    }

    function customize_assessments_admin_column_value($column_key, $post_id): void
    {
        if ($column_key == 'assigned_moderator') {
            $moderator_id = get_post_meta($post_id, 'assigned_moderator', true);
            if ($moderator_id) {
                $user = get_user_by('id', $moderator_id);
                echo $user->display_name;
            } else {
                echo 'N/A';
            }
        }
    }
}

if (class_exists('CustomPostType')) {
    $instance = new CustomPostType();
    $instance->register();
}

register_activation_hook(__FILE__, array($instance, 'activate'));
register_deactivation_hook(__FILE__, array($instance, 'deactivate'));
//register_uninstall_hook(__FILE__, array($book, 'uninstall'));