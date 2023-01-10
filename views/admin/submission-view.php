<?php
global $post;

$post_id = $post->ID;

$user_id = get_post_meta($post_id, 'user_id', true);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);

if (empty($user_id) || empty($assessment_id)) return;

$main = new WP_Assessment();
$quiz = $main->get_user_quiz_by_assessment_id($assessment_id, $user_id);

$i = 0;

$questions = get_post_meta($assessment_id, 'question_group_repeater', true);

function get_field($array, $index, $key)
{
    if (!key_exists($index, $array) || !key_exists($key, $array[$index])) return;
    return $array[$index][$key];
}

$submission_points = get_post_meta($post_id, 'quiz_points', true);
?>

<div class="container">
    <?php if ($quiz && is_array($quiz)) : ?>
        <?php foreach ($quiz as $field) :
            $i++;
            $attachment_id = null;
            $attachment_type = null;
            $url = null;
//            $point = 0;
            $answers = [];

            if ($field->answers) {
                $answers = json_decode($field->answers);
            }

            if ($field->attachment_id) {
                $attachment_id = $field->attachment_id;

                $url = wp_get_attachment_url($attachment_id);
                $attachment_type = get_post_mime_type($attachment_id);
            }
            $max_point = get_field($questions, $i, 'question_point');
            $point = $submission_points[$i] ?? 0;
            ?>
            <div class="card">
                <div class="card-body">
                    <h4><?php echo get_field($questions, $i, 'question_title'); ?></h4>
                    <?php if (is_array($answers) && count($answers) > 0) : ?>
                        <div class="submission-answers-list">
                            <strong>Selected answers</strong>
                            <ul class="ul-square">
                                <?php foreach ($answers as $answer) : ?>
                                    <li><?php echo $answer->title; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <div>
                        <strong>Description :</strong>
                        <p class="description-thin"><?php echo $field->description; ?></p>
                    </div>
                    <?php if ($attachment_id) : ?>
                        <img class="submission-image-attachment-view" src="<?php echo $url ?>">
                    <?php endif; ?>
                    <div class="row">
                        <strong>Enter Points</strong>
                        <input type="number" max="<?php echo $max_point ?>" placeholder="Points"
                               name="submission_points[]" value="<?php echo $point; ?>"/>
                    </div>
                    <input type="hidden" name="assessment_id" value="<?php echo $assessment_id ?>"/>
                    <input type="hidden" name="quiz_id[]" value="<?php echo $i ?>"/>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>