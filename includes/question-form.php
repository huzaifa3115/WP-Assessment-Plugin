<?php

class Question_Form
{
    public int $user_id;

    public function __construct()
    {
        add_action('wp_ajax_save_question', array($this, 'save_question'));
        add_action('wp_ajax_get_quiz_detail', array($this, 'get_quiz_detail'));
        add_action('wp_ajax_create_assessment_submission', array($this, 'create_assessment_submission'));
        add_action('wp_ajax_upload_assessment_attachment', array($this, 'upload_assessment_attachment'));

        $this->user_id = get_current_user_id();
    }

    function save_question()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $user_id = get_current_user_id();

            $is_submission_exist = $this->is_submission_exist($user_id, $assessment_id);
            if ($is_submission_exist)
                throw new Exception('Submission is already disabled');

            $quiz_id = intval($_POST['quiz_id']);
            $answers = $_POST['answers'];
            $description = $_POST['description'] ?? null;
            $attachment_id = $_POST['attachment_id'] ?? null;

            $is_options_exist = $this->check_multiple_choice_exist_in_assessment($assessment_id, $quiz_id);

            if ($is_options_exist && !is_array($answers))
                throw new Exception('Invalid answers');

            $main = new WP_Assessment();
            $assessment = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id);
            $is_new = !$assessment;
            $input = [];

            if ($is_options_exist)
                $input['answers'] = json_encode($answers);

            if (!empty($description))
                $input['description'] = $description;

            if (!empty($attachment_id))
                $input['attachment_id'] = $attachment_id;

            if (count($input) === 0)
                throw new Exception('Please complete the answer');

            $conditions = array(
                'user_id' => $user_id,
                'assessment_id' => $assessment_id,
                'quiz_id' => $quiz_id,
            );

            if ($is_new) {
                $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
            } else {
                $main->update_quiz_assessment($input, $conditions);
            }

            return wp_send_json(array('message' => 'Progress has been updated', 'status' => true, 'data' => array_merge($input, $conditions)));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_quiz_detail()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $quiz_id = intval($_POST['quiz_id']);

            $main = new WP_Assessment();
            $assessment = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id);
            if (!$assessment)
                throw new Exception('Quiz not found.');

            $assessment->answers = json_decode($assessment->answers);

            return wp_send_json(array('message' => 'Progress has been updated', 'status' => true, 'data' => $assessment->answers));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function upload_assessment_attachment()
    {
        try {
            if (!isset($_FILES["file"]))
                throw new Exception('File not found.');

            $file = $_FILES["file"];

            $fileName = preg_replace('/\s+/', '-', $file["name"]);
            $fileName = preg_replace('/[^A-Za-z0-9.\-]/', '', $fileName);

            $fileName = time() . '-' . $fileName;

            check_ajax_referer('assessment_attachment_upload', 'security');
            $attachment = wp_upload_bits($fileName, null, file_get_contents($file["tmp_name"]));
            if (!empty($attachment['error'])) {
                throw new Exception($attachment['error']);
            }

            $main = new WP_Assessment();
            $attachment_id = $main->wp_insert_attachment_from_url($attachment);

            return wp_send_json(array('message' => 'Attachment has uploaded', 'status' => true, 'attachment_id' => $attachment_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function create_assessment_submission()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $assessment = get_post($assessment_id);
            $assessment_title = $assessment->post_title;
            $user_id = get_current_user_id();

            $is_submission_exist = $this->is_submission_exist($user_id, $assessment_id);

            $post_id = $is_submission_exist;
            if (!$is_submission_exist) {
                $submission = wp_insert_post(array(
                    'post_type' => 'submissions',
                    'post_title' => 'Submission on ' . $assessment_title,
                    'post_status' => 'publish'
                ));

                if (!$submission) throw new Exception('Cannot submit assessment');

                $post_id = $submission;
            }

            update_post_meta($post_id, 'user_id', $user_id);
            update_post_meta($post_id, 'assessment_id', $assessment_id);
            update_post_meta($post_id, 'assessment_status', 'pending');

            return wp_send_json(array('message' => 'Assessment has been submitted', 'status' => true, 'submission_id' => $post_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function check_multiple_choice_exist_in_assessment($assessment_id, $quiz_id): ?bool
    {
        try {
            $questions = get_post_meta($assessment_id, 'question_group_repeater', true);
            if (!is_array($questions) || !array_key_exists($quiz_id, $questions))
                throw new Exception('Invalid quiz or assessment');

            $quiz = $questions[$quiz_id];
            return
                array_key_exists('multiple_choice', $quiz)
                && is_array($quiz['multiple_choice'])
                && count($quiz['multiple_choice']) > 0;
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function is_submission_exist($user_id, $assessment_id)
    {
        $post_id = null;

        $args = array(
            'post_type' => 'submissions',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'user_id',
                    'value' => $user_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                ),
                array(
                    'key' => 'assessment_status',
                    'value' => 'pending',
                ),
            ),
        );
        $query = new WP_Query($args);
        $post = $query->get_posts();

        if (is_array($post) && count($post) > 0) {
            $post_id = $post[0]->ID;
        }

        return $post_id;
    }
}

new Question_Form();
