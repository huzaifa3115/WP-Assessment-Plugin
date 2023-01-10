<?php
global $post;

$single_repeater_group = get_post_meta($post->ID, 'question_group_repeater', true);
$i = 0;
?>

<div class="question-field-container">
    <div class="question-row-container row-clone-selector" id="question-main-row-0" style="display: none">
        <input type="hidden" name="question_repeater[]"/>
        <div class="row question-input-area-container">
            <div class="col-10">
                <p class="admin-question-row-label">Question #0</p>
                <input class="form-field question-admin-title form-control" name="question_title[]"/>
                <div class="admin-question-row-textarea">
                    <textarea class="form-control description_area" name="question_description[]"></textarea>
                    <div class="col-12">
                        <div class="question-rule-checkbox-inner-container">
                            <label>Supporting.. </label>
                            <input type="checkbox" class="question-rule-description-checkbox"/>
                            <input type="hidden" class="question-rule-description-checkbox-input"
                                   name="is_question_description[]"/>
                        </div>
                    </div>
                </div>
                <div class="question-advice-row-container">
                    <label for="question-advice-row-0">Advice</label>
                    <textarea id="question-advice-row-0" class="form-control"
                              name="question_advice[]"></textarea>
                </div>
            </div>
            <div class="col-2 question-row-points-container">
                <input type="number" class="question-point-input" name="question_point[]"/>
                <div class="question-points-actions-container">
                    <div class="increment-question-point" aria-hidden="true">
                        <i class="fa fa-plus"></i>
                    </div>
                    <div class="decrement-question-point" aria-hidden="true">
                        <i class="fa fa-minus"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row question-other-info-container">
            <div class="col-12">
                <strong class="checkbox-label-heading">Rules:</strong>
                <div class="question-rule-checkbox-inner-container">
                    <label>Supporting.. </label>
                    <input type="checkbox" class="question-rule-checkbox"/>
                    <input type="hidden" class="question-rule-checkbox-input" name="is_question_supporting[]"/>
                </div>
            </div>
            <div class="col-12 multi-choice-btn-container">
                <button class="button add-multi-choice-btn" type="button">Add multi choice button</button>
                <div class="multi-choice-btn-table-container">
                    <table>
                        <tbody>
                        <tr class="empty-row multi-choice-list-item" style="display: none">
                            <td><input type="text"/></td>
                            <td><input type="checkbox"/></td>
                            <td><a class="button remove-row" href="#">Remove</a></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php if ($single_repeater_group) : ?>
        <?php foreach ($single_repeater_group as $field) : ?>
            <?php
            $i++;
            $multiple_choice = $field['multiple_choice'];

            $title = $field['question_title'] ?? '';
            $question_advice = $field['question_advice'] ?? '';
            $question_description = $field['question_description'] ?? '';
            $is_question_description = $field['is_question_description'] ?? '';
            ?>

            <input type="hidden" name="question_repeater[]"/>
            <div class="question-row-container" id="question-main-row-<?php echo $i; ?>">
                <div class="row question-input-area-container">
                    <div class="col-10">
                        <p class="admin-question-row-label">Question #<?php echo $i; ?></p>
                        <input class="form-field question-admin-title form-control" name="question_title[]"
                               value="<?php echo esc_attr($title); ?>"/>
                        <div class="admin-question-row-textarea">
                            <textarea class="form-control description_area"
                                      name="question_description[]"><?php echo $question_description; ?></textarea>
                            <div class="col-12">
                                <div class="question-rule-checkbox-inner-container">
                                    <label>Need description?</label>
                                    <input type="checkbox"
                                           class="question-rule-description-checkbox" <?php if ($is_question_description == 1) {
                                        echo "checked='checked'";
                                    } ?>/>
                                    <input type="hidden" class="question-rule-description-checkbox-input"
                                           name="is_question_description[]"
                                           value="<?php echo $is_question_description; ?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="question-advice-row-container">
                            <label for="question-advice-row-<?php echo $i; ?>">Advice</label>
                            <textarea id="question-advice-row-<?php echo $i; ?>"
                                      class="form-control"
                                      name="question_advice[]"><?php echo $question_advice; ?></textarea>
                        </div>
                    </div>
                    <div class="col-2 question-row-points-container">
                        <input type="number" class="question-point-input" name="question_point[]"
                               value="<?php if ($field['question_point'] != '') echo esc_attr($field['question_point']); ?>"/>
                        <div class="question-points-actions-container">
                            <div class="increment-question-point" aria-hidden="true">
                                <i class="fa fa-plus"></i>
                            </div>
                            <div class="decrement-question-point" aria-hidden="true">
                                <i class="fa fa-minus"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row question-other-info-container">
                    <div class="col-12">
                        <strong class="checkbox-label-heading">Rules:</strong>
                        <div class="question-rule-checkbox-inner-container">
                            <label>Supporting..</label>
                            <input type="checkbox"
                                   class="question-rule-checkbox" <?php if ($field['is_question_supporting'] == 1) {
                                echo "checked='checked'";
                            } ?> />
                            <input type="hidden" class="question-rule-checkbox-input" name="is_question_supporting[]"
                                   value="<?php echo $field['is_question_supporting']; ?>"/>
                        </div>
                    </div>
                    <div class="col-12 multi-choice-btn-container">
                        <button class="button add-multi-choice-btn" type="button" data-id="<?php echo $i; ?>">Add multi
                            choice button
                        </button>
                        <div class="multi-choice-btn-table-container">
                            <table id="multi-check-table-<?php echo $i; ?>">
                                <tbody>
                                <?php if (is_array($multiple_choice) && count($multiple_choice) > 0) : ?>
                                    <?php foreach ($multiple_choice as $item) : ?>
                                        <tr class="multi-choice-list-item" style="display:table-row">
                                            <td><input type="text" name="multi_choice_label[<?php echo $i; ?>][]"
                                                       value="<?php if ($item['label'] != '') echo esc_attr($item['label']); ?>"/>
                                            </td>
                                            <td>
                                                <input type="checkbox" class="multi-choice-check-input"
                                                    <?php if ($item['is_correct'] == 1) {
                                                        echo "checked='checked'";
                                                    } ?>
                                                />
                                                <input type="hidden" name="multi_choice_check[<?php echo $i; ?>][]"
                                                       value="<?php if ($item['is_correct'] != '') echo esc_attr($item['is_correct']); ?>"/>
                                            </td>
                                            <td><a class="button remove-row" href="#">Remove</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <tr class="empty-row multi-choice-list-item" style="display: none">
                                    <td><input type="text"/></td>
                                    <td>
                                        <input type="checkbox" class="multi-choice-check-input"/>
                                        <input type="hidden"/>
                                    </td>
                                    <td><a class="button remove-row" href="#">Remove</a></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<p><a id="add-row" class="button" href="#">Add row</a></p>