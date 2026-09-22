document.addEventListener('DOMContentLoaded', function () {
    const questionCards = document.querySelectorAll('.question-card');
    if (!questionCards.length) return;

    const totalSteps = questionCards.length;
    let currentStep = 1;

    const prevBtn = document.getElementById('btn-prev');
    const nextBtn = document.getElementById('btn-next');
    const submitBtn = document.getElementById('btn-submit');
    const progressBar = document.getElementById('progress-bar-fill');
    const stepNumDisplay = document.getElementById('current-step-num');
    const stepPills = document.querySelectorAll('.step-pill');
    const isSubmitted = document.body.classList.contains('quiz-submitted');
    const quizForm = document.getElementById('quiz-form');

    function isStepAnswered(step) {
        const card = document.querySelector(`.question-card[data-step="${step}"]`);
        if (!card) return true;
        const radios = card.querySelectorAll('input[type="radio"]');
        return Array.from(radios).some(r => r.checked);
    }

    function showWarning(card) {
        if (!card) return;

        // Trigger shake animation
        card.classList.remove('shake-warning');
        void card.offsetWidth; // force reflow
        card.classList.add('shake-warning');

        let warningBox = card.querySelector('.answer-warning');
        if (!warningBox) {
            warningBox = document.createElement('div');
            warningBox.className = 'answer-warning';
            warningBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Veuillez sélectionner une réponse avant de continuer !';
            card.insertBefore(warningBox, card.querySelector('.answer-options'));
        }
        warningBox.style.display = 'flex';
    }

    function clearWarning(card) {
        if (!card) return;
        card.classList.remove('shake-warning');
        const warningBox = card.querySelector('.answer-warning');
        if (warningBox) {
            warningBox.style.display = 'none';
        }
    }

    function updateAnsweredStatus() {
        questionCards.forEach((card, index) => {
            const step = index + 1;
            const isAnswered = isStepAnswered(step);
            const pill = document.querySelector(`.step-pill[data-step="${step}"]`);

            if (pill) {
                if (isAnswered) {
                    pill.classList.add('answered');
                } else {
                    pill.classList.remove('answered');
                }
            }
        });
    }

    function showStep(step, isNavigatingForward = false) {
        if (step < 1 || step > totalSteps) return false;

        // Block forward navigation if current question is unanswered
        if (isNavigatingForward && !isSubmitted && !isStepAnswered(currentStep)) {
            const currentCard = document.querySelector(`.question-card[data-step="${currentStep}"]`);
            showWarning(currentCard);
            return false;
        }

        // Hide all cards
        questionCards.forEach(card => {
            card.classList.remove('active', 'fade-in');
        });

        // Show target card
        const targetCard = document.querySelector(`.question-card[data-step="${step}"]`);
        if (targetCard) {
            clearWarning(targetCard);
            targetCard.classList.add('active', 'fade-in');
        }

        currentStep = step;

        // Update progress bar & counter
        if (progressBar) {
            const percentage = Math.round((currentStep / totalSteps) * 100);
            progressBar.style.width = percentage + '%';
        }
        if (stepNumDisplay) {
            stepNumDisplay.textContent = `Question ${currentStep}`;
        }

        // Update step pills
        stepPills.forEach(pill => {
            const pillStep = parseInt(pill.getAttribute('data-step'), 10);
            if (pillStep === currentStep) {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });

        // Update buttons visibility
        if (prevBtn) {
            prevBtn.style.display = (currentStep === 1) ? 'none' : 'inline-flex';
        }
        if (nextBtn) {
            nextBtn.style.display = (currentStep === totalSteps) ? 'none' : 'inline-flex';
        }
        if (submitBtn && !isSubmitted) {
            submitBtn.style.display = (currentStep === totalSteps) ? 'inline-flex' : 'none';
        }

        updateAnsweredStatus();
        return true;
    }

    // Previous Button Click
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            showStep(currentStep - 1, false);
        });
    }

    // Next Button Click
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            showStep(currentStep + 1, true);
        });
    }

    // Pill Button Click
    stepPills.forEach(pill => {
        pill.addEventListener('click', function () {
            const targetStep = parseInt(this.getAttribute('data-step'), 10);
            const isForward = targetStep > currentStep;
            showStep(targetStep, isForward);
        });
    });

    // Radio change event -> update answered pill state & clear warning
    document.querySelectorAll('.question-card input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const card = this.closest('.question-card');
            clearWarning(card);
            updateAnsweredStatus();
        });
    });

    // Form submit check: verify all questions are answered
    if (quizForm && !isSubmitted) {
        quizForm.addEventListener('submit', function (e) {
            for (let i = 1; i <= totalSteps; i++) {
                if (!isStepAnswered(i)) {
                    e.preventDefault();
                    showStep(i, false);
                    const card = document.querySelector(`.question-card[data-step="${i}"]`);
                    showWarning(card);
                    return;
                }
            }
        });
    }

    // Keyboard Arrow navigation (Left/Right)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' && currentStep < totalSteps) {
            showStep(currentStep + 1, true);
        } else if (e.key === 'ArrowLeft' && currentStep > 1) {
            showStep(currentStep - 1, false);
        }
    });

    // View All Toggle Mode
    const toggleViewBtn = document.getElementById('toggle-view-btn');
    if (toggleViewBtn) {
        let isViewAll = false;
        toggleViewBtn.addEventListener('click', function () {
            isViewAll = !isViewAll;
            const container = document.querySelector('.quiz-container');
            if (isViewAll) {
                container.classList.add('view-all-mode');
                toggleViewBtn.innerHTML = '<i class="fas fa-layer-group"></i> Step-by-Step View';
                questionCards.forEach(c => c.classList.add('active'));
            } else {
                container.classList.remove('view-all-mode');
                toggleViewBtn.innerHTML = '<i class="fas fa-list-ul"></i> View All Questions';
                showStep(currentStep);
            }
        });
    }

    // Initialize first step
    showStep(1);
});
