<?php

class Question_Form
{

    public function __construct()
    {
        add_action('wp_ajax_save_question', array($this, 'save_question'));
    }

    function save_question()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $quiz_id = intval($_POST['quiz_id']);
            $answers = $_POST['answers'];

            if (!is_array($answers))
                throw new Exception('Invalid answers');

            $user_id = get_current_user_id();

            $key = 'user_assessment_' . $assessment_id;
//            update_user_meta($user_id, $key, $answers);

            var_dump(get_user_meta($user_id, $key));

            return wp_send_json(array('message' => 'Progress has been updated', 'status' => true));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }
}

new Question_Form();
