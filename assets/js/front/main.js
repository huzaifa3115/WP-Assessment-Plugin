jQuery(document).ready(function ($) {
    let opacity;
    let activeQuiz;
    let isQuizComplete = false;
    let isStepComplete = false;
    let bodySelector = $('body');
    const assessmentIdInstance = $('#assessment_id');

    const current = 1;
    const steps = $("quiz").length;

    const ajaxUrl = ajax_object.ajax_url;

    const continueBtnElement = $("<button>", {id: "continue-quiz-btn", class: "nextPrevBtn next", text: 'Continue'});
    const backBtnElement = $("<button>", {id: "go-back-quiz-btn", class: "nextPrevBtn next", text: 'Go back'});
    const submitBtnElement = $("<button>", {id: "submit-quiz-btn", class: "nextPrevBtn next", text: 'Submit'});

    initQuizDetail();
    updateCallToActions();

    bodySelector.on('click', '#continue-quiz-btn', async function (e) {
        e.preventDefault();
        let quizCount = getQuizCount();
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);

        if (activeQuiz >= quizCount) {
            isQuizComplete = true;
        }

        let checkAnswers = getCheckAnswers(currentQuiz);

        if (checkAnswers.length <= 0) {
            alert('Please select answer')
            return;
        }
        let attachment_id = getAttachmentIdInput();

        if (attachment_id.length > 0 && attachment_id.val().length === 0) {
            alert('Please select attachment')
            return;
        }

        let isQuestionSaved = await saveQuestion(checkAnswers);
        if (!isQuestionSaved) return;

        moveToNextQuizStep(currentQuiz);
        let current = $(`.step-${activeQuiz}`);
        current.addClass('completed');

        activeQuiz++;

        $('html, body').animate({
            scrollTop: $(".formWrapper").offset().top
        }, 500);

        updateCallToActions()
    });

    bodySelector.on('click', '#go-back-quiz-btn', function (e) {
        e.preventDefault();
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);

        if (activeQuiz <= 1) return;
        moveToNextQuizStep(currentQuiz, true);
        // let current = $(`.step-${activeQuiz}`);
        // current.addClass('completed');

        activeQuiz--;

        $('html, body').animate({
            scrollTop: $(".formWrapper").offset().top
        }, 500);

        updateCallToActions();
    });

    bodySelector.on('change', '.assessment-file', async function (e) {
        e.preventDefault();

        let that = $(this);
        let file = e.target.files[0];

        await upload_assessment_attachment(file, that)
    });

    bodySelector.on('click', '#submit-quiz-btn', async function (e) {
        e.preventDefault();
        await submitAssessment()
    });

    $('.step-item-container').click(async function (e) {
        e.preventDefault();
        let that = $(this);
        let targetQuizId = that.data('id');
        if (targetQuizId === activeQuiz) return;

        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let targetQuiz = $(`#quiz-item-${targetQuizId}`);

        console.log('currentQuiz ===>', activeQuiz);
        console.log('targetQuiz ===>', targetQuizId);

        // if (targetQuizId < activeQuiz) {
            moveToSpecificQuizStep(currentQuiz, targetQuiz);
            activeQuiz = targetQuizId;

            return;
        // }

        let checkAnswers = getCheckAnswers(currentQuiz);
        let quizResponse = await getQuizDetails();
        // if (checkAnswers.length > 0 && quizResponse){
        //     moveToSpecificQuizStep(currentQuiz, targetQuiz);
        //     activeQuiz = quizId;
        // }

        if (quizResponse) {
            moveToSpecificQuizStep(currentQuiz, targetQuiz);
            activeQuiz = targetQuizId;
        }
    })

    function initQuizDetail() {
        let allQuizElement = $('.quiz');
        allQuizElement.each(function () {
            let element = $(this);
            if (element.hasClass('quiz-item-show')) {
                activeQuiz = element.data('quiz');
                return false;
            }
        })
    }

    function moveToNextQuizStep(instance, prev = false) {
        let prevQuiz = instance;
        let nextQuiz = prev ? instance.prev() : instance.next();

        nextQuiz.show();

        prevQuiz.animate({opacity: 0}, {
            step: function (now) {
                opacity = 1 - now;
                prevQuiz.css({
                    'display': 'none', 'position': 'relative'
                });
                nextQuiz.css({'opacity': opacity});
            }, duration: 500
        });
    }

    function moveToSpecificQuizStep(instance, targetInstance) {
        targetInstance.show();
        instance.animate({opacity: 0}, {
            step: function (now) {
                opacity = 1 - now;
                instance.css({
                    'display': 'none', 'position': 'relative'
                });
                targetInstance.css({'opacity': opacity});
            }, duration: 500
        });
    }

    function getQuizCount() {
        let quizElement = $('.quizDetails').children('.quiz');
        return quizElement.length;
    }

    function updateCallToActions() {
        let count = getQuizCount();
        let formController = $('.formController');
        let backBtnInstance = $('#go-back-quiz-btn');
        let submitBtnInstance = $('#submit-quiz-btn');

        if (activeQuiz <= 1 || activeQuiz >= count) {
            if (backBtnInstance.length !== 0) backBtnInstance.remove();
        } else {
            if (backBtnInstance.length === 0) {
                formController.prepend(backBtnElement)
            }
        }

        if (isQuizComplete) {
            formController.prepend(submitBtnElement)
        } else {
            submitBtnInstance.remove();
        }
    }

    function getDescriptionValue() {
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let input = currentQuiz.find('.textarea');

        return input.val();
    }

    function getAttachmentIdInput() {
        let currentQuiz = $(`#quiz-item-${activeQuiz}`);
        let input = currentQuiz.find('.assessment-assessment-id');

        return input;
    }

    function getCheckAnswers(currentQuizInstance) {
        let checkboxes = currentQuizInstance.find('.checkBox');
        let choices = [];

        if (checkboxes.length === 0) return true;

        checkboxes.each(function () {
            let that = $(this);
            let input = that.find('.form-check-input');
            let isChecked = input.is(':checked');

            if (isChecked) {
                choices.push({id: input.data('id'), title: input.data('title')})
            }
        })

        return choices;
    }

    async function saveQuestion(answers) {
        let assessmentId = assessmentIdInstance.val();
        let answerDescription = getDescriptionValue();
        let attachmentIdValue = getAttachmentIdInput().val();

        const data = {
            'action': 'save_question',
            'answers': answers,
            'quiz_id': activeQuiz,
            'assessment_id': assessmentId,
            'description': answerDescription,
            'attachment_id': attachmentIdValue
        };

        let response = await $.ajax({type: 'POST', url: ajax_object.ajax_url, data: data});
        const {status, message} = response;

        if (!status) alert(message)

        if (status) {
            $('.progress-message').show();

            setTimeout(function () {
                $('.progress-message').hide();
            }, 3000)
        }

        return status;
    }

    async function getQuizDetails() {
        let assessmentId = assessmentIdInstance.val();

        const data = {
            'action': 'get_quiz_detail', 'quiz_id': activeQuiz, 'assessment_id': assessmentId,
        };

        let res = await $.ajax({type: 'POST', url: ajax_object.ajax_url, data: data});
        return res?.status;
    }

    async function submitAssessment() {
        let assessmentId = assessmentIdInstance.val();
        const data = {
            'action': 'create_assessment_submission', 'assessment_id': assessmentId
        };

        let response = await $.ajax({type: 'POST', url: ajaxUrl, data: data});
        const {status, message} = response;

        alert(message);

        if (status) {
            setTimeout(function () {
                location.reload();
            }, 1000);
            return true;
        }

        return status;
    }

    async function upload_assessment_attachment(file, inputInstance) {

        let formData = new FormData();

        formData.append("file", file)
        formData.append("action", 'upload_assessment_attachment')
        formData.append("security", ajax_object.security)

        let response = await $.ajax({
            type: 'POST', url: ajaxUrl, processData: false, contentType: false, data: formData
        });

        const {status, message} = response;
        alert(message);

        if (status) {
            inputInstance.siblings('.assessment-assessment-id').val(response?.attachment_id)
        }
    }
});
