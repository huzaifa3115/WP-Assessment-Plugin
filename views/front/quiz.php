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

$i = 0;
$j = 0;
?>
    <section class="formWrapper">
        <div class="container">
            <div class="topBar">
                <h1><?php echo $quiz_title; ?></h1>
                <div>
                    <button class="progressBtn">Save Progress</button>
                    <span class="notify">Your progress has been saved</span>
                </div>
            </div>
            <?php if ($questions) : ?>
                <input type="hidden" id="assessment_id" value="<?php echo $post_id; ?>"/>
                <div class="stepperFormWrap" id="main-quiz-form">
                    <form onsubmit="return false">
                        <div class="stepsWrap">
                            <?php foreach ($questions as $field) : $i++; ?>
                                <div class="step step-<?php echo $i; ?>">
                                    <span class="editImg"><img src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/edit.svg"
                                                               alt="edit"></span>
                                    <span class="completedImg"><img
                                                src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/completed.svg"
                                                alt="completed"></span>
                                    <p class="count"><span class="title">Quiz <?php echo $i; ?></span></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="quizDetails">
                            <?php foreach ($questions as $field) : $j++; ?>
                                <?php
                                $multiple_choice = $field['multiple_choice'];
                                $choices_index = 0;
                                $is_attachment = $field['is_question_supporting'] == 1;
                                ?>
                                <div class="quiz quiz-item-hide quiz-<?php echo $j; ?>" id="quiz-item-<?php echo $j ?>">
                                    <div class="quizTitle">Quiz <?php echo $j; ?></div>
                                    <div class="fieldsWrapper">
                                        <div class="fieldDetails">
                                            <p><?php echo $field['question_description']; ?></p>
                                        </div>
                                        <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                            <?php foreach ($multiple_choice as $item) : $choices_index++; ?>
                                                <div class="checkBox">
                                                    <input class="form-check-input" type="checkbox" value=""
                                                           id="checkbox-<?php echo $j ?>-<?php echo $choices_index; ?>"
                                                           data-title="<?php echo $item['label']; ?>"
                                                           data-id="<?php echo $choices_index; ?>">
                                                    <label class="form-check-label"
                                                           for="Check1"><?php echo $item['label']; ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <div class="textAreaWrap">
                                            <textarea name="i" class="textarea medium"
                                                      placeholder="Enter answer"
                                                      rows="10"></textarea>
                                        </div>

                                        <?php if ($is_attachment) : ?>
                                            <div class="fileUploaderWrap">
                                                <input type="file">
                                                <!-- <p class="fileInstruct">Maximum file size: 50MB <br> File types allowed: .ppt, .pdf, .docx</p>-->
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="quizAdvice">
                                        <p>Advice</p>
                                        <p></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="formController">
                                <button id="continue-quiz-btn" class="nextPrevBtn next">Continue</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php get_footer(); ?>