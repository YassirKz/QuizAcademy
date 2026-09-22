<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <?php require __DIR__ . '/../layouts/base.php'; ?>
    <title>Quiz Academy</title>
    <link rel="stylesheet" href="public/css/quiz.css?v=<?= filemtime(__DIR__ . '/../../public/css/quiz.css') ?>">
</head>
<body class="<?= $submitted ? 'quiz-submitted' : '' ?>">
    <div class="container">
        <header>
            <div class="logo">
                <img src="public/images/Q-A.png" alt="Quiz Academy Logo">
                <h1>Quiz Academy</h1>
            </div>
            <div class="user-info-header">
                <i class="fas fa-user-circle"></i>
                <span>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></span>
                <a href="index.php?action=logout" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <div class="user-details-card">
            <?php if ($userInfo): ?>
                <p><i class="fas fa-envelope"></i> Email: <?= htmlspecialchars($userEmail) ?></p>
                <p><i class="fas fa-users"></i> Group: <?= htmlspecialchars($userGroupe) ?></p>
            <?php endif; ?>
        </div>

        <?php
        $scoreClass = '';
        if ($submitted && $totalQuestions > 0) {
            $scoreClass = ($score <= ($totalQuestions / 2)) ? 'low-score' : 'high-score';
        }
        ?>

        <?php if ($submitted && $totalQuestions > 0): ?>
            <div class="score-message <?= $scoreClass ?>">
                <h3>Quiz Results</h3>
                <p>You got <strong><?= $score ?></strong> points out of <strong><?= $totalQuestions ?></strong> questions.</p>
                <p>Score: <strong><?= number_format(($score / $totalQuestions) * 100, 1) ?>%</strong></p>
            </div>
        <?php endif; ?>

        <div class="subject-selection">
            <h2>Choose a Subject</h2>
            <div class="subject-buttons-wrapper">
                <?php foreach ($subjects as $subject): ?>
                    <a href="index.php?action=quiz&subjectId=<?= $subject->subjectId ?>">
                        <button type="button" class="subject-btn <?= ($subjectId == $subject->subjectId) ? 'active-subject' : '' ?>">
                            <i class="fas fa-book"></i> <?= htmlspecialchars($subject->subjectName) ?>
                        </button>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($questions) && $subjectId): ?>
            <?php
            // Group questions by questionId
            $groupedQuestions = [];
            foreach ($questions as $q) {
                if (!isset($groupedQuestions[$q->questionId])) {
                    $groupedQuestions[$q->questionId] = [
                        'name' => $q->questionName,
                        'answers' => []
                    ];
                }
                $groupedQuestions[$q->questionId]['answers'][] = $q;
            }
            $totalQuestionsCount = count($groupedQuestions);
            ?>

            <div class="quiz-container">
                <!-- Top Progress Bar & Indicators -->
                <div class="quiz-progress-section">
                    <div class="progress-header">
                        <div class="progress-info">
                            <span id="current-step-num">Question 1</span> of <span><?= $totalQuestionsCount ?></span>
                        </div>
                        <button type="button" id="toggle-view-btn" class="toggle-view-btn">
                            <i class="fas fa-list-ul"></i> View All Questions
                        </button>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" id="progress-bar-fill"></div>
                    </div>
                    <div class="step-pills" id="step-pills">
                        <?php for ($i = 1; $i <= $totalQuestionsCount; $i++): ?>
                            <button type="button" class="step-pill <?= $i === 1 ? 'active' : '' ?>" data-step="<?= $i ?>"><?= $i ?></button>
                        <?php endfor; ?>
                    </div>
                </div>

                <form method="post" action="index.php?action=quiz" id="quiz-form">
                    <?php echo csrfInputField(); ?>
                    <input type="hidden" name="subjectId" value="<?= $subjectId ?>">

                    <?php
                    $stepIndex = 1;
                    foreach ($groupedQuestions as $qId => $data):
                    ?>
                        <div class="question-card <?= $stepIndex === 1 ? 'active' : '' ?>" data-step="<?= $stepIndex ?>" data-question-id="<?= $qId ?>">
                            <div class="question-text">
                                <span class="question-number">Question <?= $stepIndex ?>:</span>
                                <?= htmlspecialchars($data['name']) ?>
                            </div>
                            <div class="answer-options">
                                <?php foreach ($data['answers'] as $question): 
                                    $answerId        = $question->answerId;
                                    $isSelected      = isset($answersStatus[$qId]) && $answersStatus[$qId]['selected'] == $answerId;
                                    $isCorrectAnswer = isset($answersStatus[$qId]) && $answersStatus[$qId]['correct'] == $answerId;

                                    $class = '';
                                    if ($submitted) {
                                        if ($isCorrectAnswer) {
                                            $class = 'correct';
                                        } elseif ($isSelected && !$isCorrectAnswer) {
                                            $class = 'incorrect';
                                        }
                                    }
                                ?>
                                    <div class="answer-option <?= $class ?>">
                                        <label>
                                            <input type="radio" name="q<?= $qId ?>" value="<?= $answerId ?>"
                                                <?= $isSelected ? 'checked' : '' ?>
                                                <?= $submitted  ? 'disabled' : '' ?> required>
                                            <?= htmlspecialchars($question->answerName) ?>
                                            <?php if ($submitted): ?>
                                                <?php if ($isCorrectAnswer): ?>
                                                    <i class="fas fa-check-circle correct-icon"></i>
                                                <?php elseif ($isSelected && !$isCorrectAnswer): ?>
                                                    <i class="fas fa-times-circle incorrect-icon"></i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php 
                        $stepIndex++;
                    endforeach; 
                    ?>

                    <!-- Bottom Navigation Controls -->
                    <div class="quiz-nav-controls">
                        <button type="button" id="btn-prev" class="nav-btn prev-btn">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>

                        <button type="button" id="btn-next" class="nav-btn next-btn">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>

                        <?php if (!$submitted): ?>
                            <button type="submit" name="submit" id="btn-submit" class="submit-btn" style="display: none;">
                                Submit Answers <i class="fas fa-paper-plane"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($submitted): ?>
                        <div class="quiz-completed-message">
                            <p>Quiz Completed! Review your answers above.</p>
                            <a href="index.php?action=quiz" class="new-quiz-btn">Start New Quiz</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="public/js/quiz.js?v=<?= filemtime(__DIR__ . '/../../public/js/quiz.js') ?>"></script>
</body>
</html>
