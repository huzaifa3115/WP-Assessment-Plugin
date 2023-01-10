jQuery(document).ready(function ($) {
    $('#add-row').on('click', function () {
        // const row = $('.empty-row.custom-repeater-text').clone(true);
        // row.removeClass('empty-row custom-repeater-text').css('display', 'table-row');
        // row.insertBefore('#repeatable-fieldset-one tbody>tr:last');

        let rowCount = $('.question-row-container').length;
        const row = $('.row-clone-selector').clone(true);

        row.removeClass('row-clone-selector').css('display', 'block');
        row.attr('id', `question-main-row-${rowCount}`)
        row.find('.admin-question-row-label').html(`Question #${rowCount}`);
        row.find('table').attr('id', `multi-check-table-${rowCount}`);
        row.find('.add-multi-choice-btn').attr('data-id', `${rowCount}`);
        row.appendTo('.question-field-container');

        return false;
    });

    $('.remove-row').on('click', function () {
        $(this).parents('tr').remove();
        return false;
    });

    $('.increment-question-point').on('click', function () {
        const that = $(this);
        const parent = that.parent('.question-points-actions-container');
        const input = parent.siblings('.question-point-input');

        let value = input.val();
        value = value !== '' ? value : '0';
        input.val(parseInt(value) + 1)
    });

    $('.decrement-question-point').on('click', function () {
        const that = $(this);
        const parent = that.parent('.question-points-actions-container');
        const input = parent.siblings('.question-point-input');

        let value = input.val();
        value = value !== '' ? (value) : '0';
        value = parseInt(value);
        if (value <= 0) return;

        input.val(value - 1)
    });

    $('.question-rule-checkbox').on('change', function () {
        const that = $(this);
        const input = that.siblings('.question-rule-checkbox-input');
        let val = 0;

        if (that.is(':checked')) {
            val = 1;
        }
        input.val(val);
    });

    $('.question-rule-description-checkbox').on('change', function () {
        const that = $(this);
        const input = that.siblings('.question-rule-description-checkbox-input');
        let val = 0;

        if (that.is(':checked')) {
            val = 1;
        }
        input.val(val);
    });

    $('.add-multi-choice-btn').on('click', function () {
        let that = $(this);
        let index = that.data('id');
        let currentIndex = index;
        let table = $(`#multi-check-table-${index}`);

        const row = table.find('.empty-row.multi-choice-list-item').clone(true);
        row.removeClass('empty-row').css('display', 'table-row');
        row.find('input[type=text]').attr('name', `multi_choice_label[${currentIndex}][]`);
        row.find('input[type=hidden]').attr('name', `multi_choice_check[${currentIndex}][]`);
        row.insertBefore(`#multi-check-table-${index} tbody>tr:last`);

        return false;
    });

    $('.multi-choice-check-input').on('change', function () {
        const that = $(this);
        const input = that.siblings('input[type=hidden]');
        let val = 0;

        if (that.is(':checked')) {
            val = 1;
        }
        input.val(val);
    });
});