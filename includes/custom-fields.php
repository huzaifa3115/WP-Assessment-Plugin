<?php

class Custom_Fields
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'init_meta_boxes_assessment_admin'));
        add_action('admin_init', array($this, 'init_meta_boxes_submission_admin_view'));

        add_action('save_post', array($this, 'question_repeatable_meta_box_save'));
        add_action('save_post', array($this, 'save_assigned_moderator'));
        add_action('save_post', array($this, 'on_save_submission_custom_fields'));

        // moderator user list
    }

    function init_meta_boxes_assessment_admin(): void
    {
        add_meta_box('questions-repeater-field', 'Questions', array($this, 'question_repeatable_meta_box_callback'), 'assessments', 'normal', 'default');

        if (current_user_can('administrator')) {
            add_meta_box('moderator-list', 'Select Moderator', array($this, 'display_moderator_select_list'), 'assessments', 'normal', 'default');
        }
    }

    function init_meta_boxes_submission_admin_view(): void
    {
        add_meta_box('questions-repeater-field', 'Submissions Detail', array($this, 'submission_list_card_section_admin'), 'submissions', 'normal', 'default');
    }


    function question_repeatable_meta_box_callback()
    {
        return include_once MODERATOR_LIST_ADMIN_QUESTIONAIRE_FIELDS;
    }

    function display_moderator_select_list()
    {
        return include_once MODERATOR_LIST_ADMIN_SELECT;
    }

    function submission_list_card_section_admin()
    {
        return include_once ADMIN_SUBMISSION_VIEW;
    }

    function question_repeatable_meta_box_save($post_id): void
    {

        if (!isset($_POST['question_repeater']))
            return;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id))
            return;

        $old = get_post_meta($post_id, 'question_group_repeater', true);

        $new = array();
        $question_description = $_POST['question_description'];
        $question_title = $_POST['question_title'];
        $question_advice = $_POST['question_advice'];
        $question_point = $_POST['question_point'];
        $is_question_supporting = $_POST['is_question_supporting'];
        $is_question_description = $_POST['is_question_description'];


        //multi choice inputs
        $multi_choice_label = $_POST['multi_choice_label'] ?? [];
        $multi_choice_check = $_POST['multi_choice_check'] ?? [];

        $count = count($question_title);

        for ($i = 0; $i < $count; $i++) {
            if ($question_title[$i] != '') {
                $new[$i]['question_description'] = stripslashes(strip_tags($question_description[$i]));
                $new[$i]['question_title'] = stripslashes(strip_tags($question_title[$i]));
                $new[$i]['question_advice'] = stripslashes(strip_tags($question_advice[$i]));
                $new[$i]['question_point'] = stripslashes($question_point[$i]);

                $is_checked = strip_tags($is_question_supporting[$i]);
                $is_description = strip_tags($is_question_description[$i]);

                $new[$i]['is_question_supporting'] = $is_checked == 0 ? 0 : 1;
                $new[$i]['is_question_description'] = $is_description == 0 ? 0 : 1;

                $choiceIndex = $i;
                $choices = array();

                if (array_key_exists($choiceIndex, $multi_choice_label)) {
                    $label = $multi_choice_label[$choiceIndex];
                    $value = $multi_choice_check[$choiceIndex];

                    for ($j = 0; $j < count($label); $j++) {
                        $item = array();

                        $item['label'] = $label[$j];
                        $is_correct = strip_tags($value[$j]);
                        $item['is_correct'] = $is_correct == 0 ? 0 : 1;

                        $choices[] = $item;
                    }
                }

                $new[$i]['multiple_choice'] = $choices;
            }
        }

        if (!empty($new) && $new != $old) {
            update_post_meta($post_id, 'question_group_repeater', $new);
        } elseif (empty($new) && $old) {
            delete_post_meta($post_id, 'question_group_repeater', $old);
        }

        // $repeater_status = $_REQUEST['repeater_status'] ?? null;
        // update_post_meta($post_id, 'repeater_status', $repeater_status);
    }

    function on_save_submission_custom_fields($post_id): void
    {
        $post_type = get_post_type($post_id);
        if ($post_type != 'submissions') return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;

        $submission_points = $_POST['submission_points'] ?? null;
        $quiz_id = $_POST['quiz_id'] ?? [];

        $count = count($quiz_id);
        $new = array();

        for ($i = 0; $i < $count; $i++) {
            if (!empty($quiz_id[$i])) {
                $point = stripslashes(strip_tags($submission_points[$i]));
                $new[$quiz_id[$i]] = empty($point) ? 0 : (int)$point;
            }
        }

        update_post_meta($post_id, 'quiz_points', $new);
    }

    function save_assigned_moderator($post_id): void
    {
        $post_type = get_post_type($post_id);

        if (isset($_POST['assigned_moderator'])) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;
            update_post_meta($post_id, 'assigned_moderator', $_POST['assigned_moderator']);
        } else {
            $init = new WP_Assessment();
            if ($post_type === "assessments" && $init->get_current_user_role() === "moderator") {
                update_post_meta($post_id, 'assigned_moderator', get_current_user_id());
            }
        }
    }
}

new Custom_Fields();
