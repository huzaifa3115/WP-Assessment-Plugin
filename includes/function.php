<?php

class WP_Assessment
{
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
            $roles = (array) $user->roles;
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
}

new WP_Assessment();
