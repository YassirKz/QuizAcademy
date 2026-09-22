<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <?php require __DIR__ . '/../layouts/base.php'; ?>
    <title>Quiz Academy</title>
    <link rel="stylesheet" href="public/css/quiz.css?v=<?= filemtime(__DIR__ . '/../../public/css/quiz.css') ?>">
</head>
<body class="<?= $submitted ? 'quiz-submitted' : '' ?>">
    <!-- Ambient Background Blobs -->
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    <div class="bg-blob blob-3"></div>

    <div class="container">
        <header>
            <div class="logo">
                <div class="logo-icon-wrapper">
                    <img src="public/images/Q-A.png" alt="Quiz Academy Logo">
                </div>
                <div class="logo-text">
                    <h1>Quiz Academy</h1>
                    <span class="logo-subtitle">Interactive Learning Platform</span>
                </div>
            </div>
            <div class="user-info-header">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']) ?></span>
                    <?php if ($userInfo && !empty($userGroupe)): ?>
                        <span class="group-badge"><i class="fas fa-users"></i> <?= htmlspecialchars($userGroupe) ?></span>
                    <?php endif; ?>
                </div>
                <a href="index.php?action=logout" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <div class="user-details-card">
            <?php if ($userInfo): ?>
                <div class="user-detail-pill">
                    <i class="fas fa-envelope"></i>
                    <span><?= htmlspecialchars($userEmail) ?></span>
                </div>
                <div class="user-detail-pill">
                    <i class="fas fa-layer-group"></i>
                    <span><?= htmlspecialchars($userGroupe) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php
        $scoreClass = '';
        $percentage = 0;
        if ($submitted && $totalQuestions > 0) {
            $percentage = round(($score / $totalQuestions) * 100, 1);
            if ($percentage >= 80) {
                $scoreClass = 'score-excellent';
                $scoreBadge = '🏆 Excellent !';
            } elseif ($percentage >= 50) {
                $scoreClass = 'score-good';
                $scoreBadge = '👍 Good Job !';
            } else {
                $scoreClass = 'score-low';
                $scoreBadge = '💡 Keep Practicing !';
            }
        }
        ?>

        <?php if ($submitted && $totalQuestions > 0): ?>
            <div class="score-card <?= $scoreClass ?>">
                <div class="score-badge"><?= $scoreBadge ?></div>
                <div class="score-body">
                    <div class="score-circle">
                        <span class="score-percent"><?= $percentage ?>%</span>
                    </div>
                    <div class="score-info">
                        <h3>Quiz Results</h3>
                        <p>You scored <strong><?= $score ?></strong> out of <strong><?= $totalQuestions ?></strong> correct answers.</p>
                        <a href="index.php?action=quiz" class="new-quiz-btn">
                            <i class="fas fa-redo-alt"></i> Try Another Quiz
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="subject-selection">
            <h2>Choose a Subject</h2>
            <div class="subject-buttons-wrapper">
                <?php 
                foreach ($subjects as $subject): 
                    $name = strtolower($subject->subjectName);
                    $iconClass = 'fas fa-code';
                    $subjectClass = 'subject-default';

                    if (str_contains($name, 'html')) {
                        $iconClass = 'fab fa-html5';
                        $subjectClass = 'subject-html';
                    } elseif (str_contains($name, 'css')) {
                        $iconClass = 'fab fa-css3-alt';
                        $subjectClass = 'subject-css';
                    } elseif (str_contains($name, 'js') || str_contains($name, 'javascript')) {
                        $iconClass = 'fab fa-js-square';
                        $subjectClass = 'subject-js';
                    } elseif (str_contains($name, 'php')) {
                        $iconClass = 'fab fa-php';
                        $subjectClass = 'subject-php';
                    }
                    $isActive = ($subjectId == $subject->subjectId);
                ?>
                    <a href="index.php?action=quiz&subjectId=<?= $subject->subjectId ?>" class="subject-link">
                        <button type="button" class="subject-btn <?= $isActive ? 'active-subject' : '' ?>">
                            <i class="<?= $iconClass ?>"></i>
                            <span><?= htmlspecialchars($subject->subjectName) ?></span>
                            <?php if ($isActive): ?>
                                <i class="fas fa-check-circle active-check"></i>
                            <?php endif; ?>
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
            $optionLetters = ['A', 'B', 'C', 'D', 'E', 'F'];
            ?>

            <div class="quiz-container">
                <!-- Top Progress Bar & Indicators -->
                <div class="quiz-progress-section">
                    <div class="progress-header">
                        <div class="progress-info">
                            <i class="fas fa-question-circle"></i>
                            <span id="current-step-num">Question 1</span> of <span><?= $totalQuestionsCount ?></span>
                        </div>
                        <button type="button" id="toggle-view-btn" class="toggle-view-btn">
                            <i class="fas fa-list-ul"></i> View All
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
                            <div class="question-header">
                                <span class="question-number">Question <?= sprintf("%02d", $stepIndex) ?></span>
                                <h3 class="question-text"><?= htmlspecialchars($data['name']) ?></h3>
                            </div>
                            <div class="answer-options">
                                <?php 
                                $optIdx = 0;
                                foreach ($data['answers'] as $question): 
                                    $answerId        = $question->answerId;
                                    $isSelected      = isset($answersStatus[$qId]) && $answersStatus[$qId]['selected'] == $answerId;
                                    $isCorrectAnswer = isset($answersStatus[$qId]) && $answersStatus[$qId]['correct'] == $answerId;
                                    $prefixLetter    = $optionLetters[$optIdx % count($optionLetters)];
                                    $optIdx++;

                                    $class = '';
                                    if ($submitted) {
                                        if ($isCorrectAnswer) {
                                            $class = 'correct';
                                        } elseif ($isSelected && !$isCorrectAnswer) {
                                            $class = 'incorrect';
                                        }
                                    }
                                ?>
                                    <div class="answer-option <?= $class ?> <?= $isSelected ? 'selected-option' : '' ?>">
                                        <label>
                                            <span class="option-prefix"><?= $prefixLetter ?></span>
                                            <input type="radio" name="q<?= $qId ?>" value="<?= $answerId ?>"
                                                <?= $isSelected ? 'checked' : '' ?>
                                                <?= $submitted  ? 'disabled' : '' ?> required>
                                            <span class="answer-text"><?= htmlspecialchars($question->answerName) ?></span>
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
                            Next Question <i class="fas fa-arrow-right"></i>
                        </button>

                        <?php if (!$submitted): ?>
                            <button type="submit" name="submit" id="btn-submit" class="submit-btn" style="display: none;">
                                Submit Quiz <i class="fas fa-paper-plane"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($submitted): ?>
                        <div class="quiz-completed-message">
                            <p><i class="fas fa-check-double"></i> Quiz Completed! Review your answers above.</p>
                            <a href="index.php?action=quiz" class="new-quiz-btn">
                                <i class="fas fa-sync-alt"></i> Start New Quiz
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="public/js/quiz.js?v=<?= filemtime(__DIR__ . '/../../public/js/quiz.js') ?>"></script>
</body>
</html>
