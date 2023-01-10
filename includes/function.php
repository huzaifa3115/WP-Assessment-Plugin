<?php

class WP_Assessment
{
    public $quiz_table_name;

    public function __construct()
    {
        $this->remove_custom_roles();
        $this->add_roles();
        $this->add_assessment_caps_to_admin();

        add_action('pre_get_posts', array($this, 'filter_assessment_list_admin'));
        add_filter('views_edit-assessments', array($this, 'update_assessment_list_filters_view'));
        add_filter('theme_page_templates', array($this, 'register_custom_template_for_quiz'));
        //        add_filter('page_template', array($this, 'quiz_redirect_page_template'));
        add_filter('single_template', array($this, 'quiz_redirect_page_template'));

        $this->set_quiz_table();
        $this->init_quiz_tables_for_users();
    }

    function add_roles(): void
    {
        add_role('moderator', 'Moderator', array(
            'read' => true,
        ));

        add_role('student', 'Student', array(
            'read' => true,
        ));
    }

    function remove_custom_roles(): void
    {
        remove_role('student');
        remove_role('moderator');
    }

    function filter_assessment_list_admin($query): void
    {

        if (current_user_can('administrator')) return;

        $cpt_key = "assigned_moderator";
        $cpt_value = get_current_user_id();

        global $current_page;
        $type = 'assessments';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        if ('assessments' == $type) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => $cpt_key,
                    'value' => $cpt_value,
                    'compare' => 'IN',
                ),
            );

            $query->set('meta_query', $meta_query);
        }
    }

    function add_assessment_caps_to_admin(): void
    {
        $admin_role = get_role('administrator');
        $moderator_role = get_role('moderator');

        $admin_role->add_cap('read_assessment');
        $admin_role->add_cap('publish_assessments');
        $admin_role->add_cap('edit_assessments');
        $admin_role->add_cap('edit_others_assessments');
        $admin_role->add_cap('delete_assessments');
        $admin_role->add_cap('delete_others_assessments');
        $admin_role->add_cap('read_private_assessments');
        $admin_role->add_cap('edit_assessment');
        $admin_role->add_cap('delete_assessment');
        $admin_role->add_cap('edit_published_assessment');
        $admin_role->add_cap('edit_published_assessments');

        //        for moderator
        $moderator_role->add_cap('read_assessment');
        $moderator_role->add_cap('publish_assessments');
        $moderator_role->add_cap('edit_assessments');
        $moderator_role->add_cap('edit_others_assessments');
        $moderator_role->add_cap('delete_assessments');
        $moderator_role->add_cap('edit_assessment');
        $moderator_role->add_cap('delete_assessment');
        $moderator_role->add_cap('edit_published_assessment');
        $moderator_role->add_cap('edit_published_assessments');
    }

    function update_assessment_list_filters_view($views): array
    {
        if (current_user_can('manage_options'))
            return $views;

        $remove_views = ['all', 'publish', 'future', 'sticky', 'draft', 'pending', 'trash'];

        foreach ((array)$remove_views as $view) {
            if (isset($views[$view]))
                unset($views[$view]);
        }
        return $views;
    }

    function get_current_user_role()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = (array)$user->roles;
            return $roles[0];
        } else {
            return array();
        }
    }

    function register_custom_template_for_quiz($templates)
    {
        $templates['custom_quiz_template'] = 'Quiz Template';
        return $templates;
    }

    function quiz_redirect_page_template($template)
    {
        global $post;

        if ($post->post_type == 'assessments')
            return QUIZ_TEMPLATE_VIEW;

        return $template;
    }

    function set_quiz_table(): void
    {
        global $wpdb;
        $this->quiz_table_name = $wpdb->prefix . "user_quiz";
    }

    function get_quiz_table()
    {
        return $this->quiz_table_name;
    }

    function init_quiz_tables_for_users()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->get_quiz_table();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            user_id int(11) NOT NULL,
            quiz_id int(11) NOT NULL,
            assessment_id int(11) NOT NULL,
            answers JSON,
            description LONGTEXT,
            attachment_id int(11) DEFAULT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function get_quiz_by_assessment_id($assessment_id, $quiz_id)
    {
        try {
            global $wpdb;
            $user_id = get_current_user_id();
            $table = $this->get_quiz_table();
            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND quiz_id = $quiz_id AND user_id = $user_id LIMIT 1";
            $result = $wpdb->get_results($sql);

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }

            return $result[0] ?? null;

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_user_quiz_by_assessment_id($assessment_id, $user_id = null)
    {
        try {
            global $wpdb;
            $user_id = $user_id ?? get_current_user_id();
            $table = $this->get_quiz_table();
            $sql = "SELECT * FROM $table WHERE assessment_id = $assessment_id AND user_id = $user_id";
            $result = $wpdb->get_results($sql);

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }

            return !empty($result) ? $result : null;

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function insert_quiz_by_assessment_id($data)
    {
        try {
            global $wpdb;
            $table = $this->get_quiz_table();
            $wpdb->insert($table, $data);

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function update_quiz_assessment($data, $conditions)
    {
        try {
            global $wpdb;
            $table = $this->get_quiz_table();
            $wpdb->update($table, $data, $conditions);

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function is_quiz_exist_in_object($quiz_id, $obj)
    {
        $user_id = get_current_user_id();
        $data = null;

        if ($obj && is_array($obj)) {
            foreach ($obj as $item) {
                if ($item->user_id == $user_id && $item->quiz_id == $quiz_id) {
                    $data['answers'] = json_decode($item->answers);
                    $data['description'] = $item->description;
                    $data['attachment_id'] = $item->attachment_id;

                    break;
                }
            }
        }

        return $data;
    }

    function is_answer_exist($key, $answers): bool
    {
        $is_exist = false;
        if ($answers && is_array($answers)) {
            foreach ($answers as $answer) {
                if ($answer->id == $key) {
                    $is_exist = true;
                    break;
                }
            }
        }

        return $is_exist;
    }

    function wp_insert_attachment_from_url($upload, $parent_post_id = null)
    {

        $file_path = $upload['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name, null);
        $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
        $wp_upload_dir = wp_upload_dir();

        $post_info = array(
            'guid' => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title' => $attachment_title,
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attach_id = wp_insert_attachment($post_info, $file_path, $parent_post_id);

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);

        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }


//     echo $wpdb->last_query;

    // // Print last SQL query result
    // echo $wpdb->last_result;

    // // Print last SQL query Error
    // echo $wpdb->last_error;
}

new WP_Assessment();
