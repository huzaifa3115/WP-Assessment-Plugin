<?php
//if (!isset($_GET['quiz_id'])) {
//    header("HTTP/1.1 404 Moved Permanently");
//    header("Location: ".get_bloginfo('url'));
//    exit();
//};
?>
<?php get_header(); ?>
<?php
global $post;
$post_id = $post->ID;
$questions = get_post_meta($post_id, 'question_group_repeater', true);
$quiz_title = get_the_title($post_id);
$user_id = get_current_user_id();

$i = 0;
$j = 0;

$main = new WP_Assessment();
$question_form = new Question_Form();

$quiz = $main->get_user_quiz_by_assessment_id($post_id);

$show_first_active_view = false;
$total_quiz = is_array($questions) ? count($questions) : 0;

$is_submission_exist = $question_form->is_submission_exist($user_id, $post_id);

$is_disabled = (bool)$is_submission_exist;
?>
    <section class="formWrapper">
        <div class="container">
            <div class="topBar">
                <h1><?php echo $quiz_title; ?></h1>
                <div>
                    <button class="progressBtn">Save Progress</button>
                    <span class="progress-message" style="display:none;">Your progress has been saved</span>
                </div>
            </div>
            <?php if ($questions) : ?>
                <input type="hidden" id="assessment_id" value="<?php echo $post_id; ?>"/>
                <div class="stepperFormWrap" id="main-quiz-form">
                    <form onsubmit="return false">
                        <div class="stepsWrap">
                            <?php foreach ($questions as $field) :
                                $i++;
                                $is_step_completed = $main->is_quiz_exist_in_object($i, $quiz);
                                $step_completed_class = $is_step_completed ? 'completed' : '';
                                $question_title = $field['question_title'] ?? '';
                                ?>
                                <div class="step step-item-container <?php echo $step_completed_class; ?> step-<?php echo $i; ?>"
                                     data-id="<?php echo $i; ?>">
                                    <span class="editImg"><img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/edit.svg"
                                                               alt="edit"></span>
                                    <span class="completedImg"><img
                                                src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/completed.svg"
                                                alt="completed"></span>
                                    <p class="count">
                                        <span class="title">
                                            <?php echo $question_title; ?>
                                        </span>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="quizDetails">
                            <?php foreach ($questions as $field) : $j++; ?>
                                <?php
                                $multiple_choice = $field['multiple_choice'];
                                $title = $field['question_title'] ?? '';
                                $question_advice = $field['question_advice'] ?? '';
                                $choices_index = 0;

                                $is_attachment = $field['is_question_supporting'] == 1;
                                $is_question_description = $field['is_question_description'] == 1;
                                $question_title = $field['question_title'] ?? '';

                                $item_class = $j === 1 ? 'quiz-item-show' : 'quiz-item-hide';
                                $answers = null;
                                $description = null;
                                $attachment_id = null;

                                $current_quiz = $main->is_quiz_exist_in_object($j, $quiz);

                                if ($current_quiz) {
                                    $item_class = $total_quiz === $j ? 'quiz-item-show' : 'quiz-item-hide';

                                    if (array_key_exists('answers', $current_quiz)) {
                                        $answers = $current_quiz['answers'];
                                    }

                                    if (array_key_exists('description', $current_quiz)) {
                                        $description = $current_quiz['description'];
                                    }

                                    if (array_key_exists('attachment_id', $current_quiz)) {
                                        $attachment_id = $current_quiz['attachment_id'];
                                    }
                                } else {
                                    if (!$show_first_active_view) {
                                        $show_first_active_view = true;
                                        $item_class = 'quiz-item-show';
                                    }
                                }
                                ?>
                                <div class="quiz <?php echo $item_class; ?> quiz-<?php echo $j; ?>"
                                     id="quiz-item-<?php echo $j ?>" data-quiz="<?php echo $j ?>">
                                    <div class="quizTitle"><?php echo $question_title; ?></div>
                                    <div class="fieldsWrapper">
                                        <div class="fieldDetails">
                                            <p class="pre-space"><?php echo $field['question_description']; ?></p>
                                        </div>
                                        <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                            <?php foreach ($multiple_choice as $item) :
                                                $choices_index++;
                                                $is_checked = $main->is_answer_exist($choices_index, $answers) ? 'checked' : '';
                                                ?>
                                                <div class="checkBox">
                                                    <input class="form-check-input" type="checkbox"
                                                           value="" <?php echo $is_disabled ? 'disabled' : '' ?>
                                                           id="checkbox-<?php echo $j ?>-<?php echo $choices_index; ?>"
                                                           data-title="<?php echo $item['label']; ?>"
                                                           data-id="<?php echo $choices_index; ?>" <?php echo $is_checked; ?>>
                                                    <label class="form-check-label"
                                                           for="Check1"><?php echo $item['label']; ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if ($is_question_description) : ?>
                                            <div class="textAreaWrap">
                                                <textarea
                                                        name="description" <?php echo $is_disabled ? 'disabled' : '' ?> class="textarea medium"
                                                        placeholder="Enter answer"
                                                        rows="10"><?php echo $description; ?></textarea>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($is_attachment) : ?>
                                            <div class="fileUploaderWrap">
                                                <input type="file"
                                                       class="assessment-file" <?php echo $is_disabled ? 'disabled' : '' ?> />
                                                <input name="attachment_id" type="hidden"
                                                       class="assessment-assessment-id" <?php echo $is_disabled ? 'disabled' : '' ?>
                                                       value="<?php echo $attachment_id; ?>"/>
                                                <p class=" fileInstruct">Maximum file size: 50MB <br> File types
                                                    allowed:
                                                    .ppt, .pdf, .docx</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="quizAdvice">
                                        <p>Advice</p>
                                        <p class="pre-space"><?php echo $question_advice; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="formController">
                                <button <?php echo $is_disabled ? 'disabled' : '' ?> id="continue-quiz-btn"
                                                                                     class="nextPrevBtn next">Continue
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php get_footer(); ?>