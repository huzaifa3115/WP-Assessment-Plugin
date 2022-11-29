jQuery(document).ready(function ($) {
    let current_fs, next_fs, previous_fs;
    let opacity;
    const current = 1;
    const steps = $("quiz").length;
    var activeQuiz;

    initQuizDetail();

    function initQuizDetail() {
        let instance = $('#quiz-item-1');
        activeQuiz = 1;

        instance.animate({opacity: 1}, {
            step: function (now) {
                opacity = 1 - now;
                instance.css({
                    'display': 'block',
                    'position': 'relative'
                });
            },
            duration: 500
        });
    }

    function moveToNextQuizStep(instance) {
        let prevQuiz = instance;
        let nextQuiz = instance.next();

        nextQuiz.show();

        prevQuiz.animate({opacity: 0}, {
            step: function (now) {
                opacity = 1 - now;
                prevQuiz.css({
                    'display': 'none',
                    'position': 'relative'
                });
                nextQuiz.css({'opacity': opacity});
            },
            duration: 500
        });
    }

    function getQuizCount() {
        let quizElement = $('.quizDetails').children('.quiz');
        return quizElement.length;
    }

    function clearAllSteps() {
        let stepElement = $('.stepsWrap').children('.step');
        stepElement.each(function () {
            $(this).removeClass('.completed')
        })
    }

    $('#continue-quiz-btn').click(function (e) {
        e.preventDefault();
        let quizCount = getQuizCount();
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);

        if (activeQuiz > quizCount) {
            alert('quiz finished!')
        } else {
            let checkAnswers = getCheckAnswers(currentQuiz);

            if (checkAnswers.length <= 0) {
                alert('Please select answer')
                return;
            }

            saveQuestion(checkAnswers);
            // moveToNextQuizStep(currentQuiz);
            // let current = $(`.step-${activeQuiz}`);
            // current.addClass('completed');
            //
            // activeQuiz++;
            //
            // $('html, body').animate({
            //     scrollTop: $(".formWrapper").offset().top
            // }, 500);

        }

        // let nextQuiz = $(`#quiz-item-${activeQuiz}`);
    })

    function getCheckAnswers(currentQuizInstance) {
        let checkboxes = currentQuizInstance.find('.checkBox');
        let choices = [];

        checkboxes.each(function () {
            let that = $(this);
            let input = that.find('.form-check-input');
            let isChecked = input.is(':checked');

            if (isChecked) {
                choices.push({quiz: activeQuiz, id: input.data('id'), title: input.data('title')})
            }
        })

        return choices;
    }

    function saveQuestion(answers) {
        let assessmentId = $('#assessment_id').val();
        const data = {
            'action': 'save_question',
            'answers': answers,
            'quiz_id': activeQuiz,
            'assessment_id': assessmentId
        };

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: data,
            success: function (result) {
                alert(result);
            },
            error: function () {
                alert("error");
            }
        });
    }
});
