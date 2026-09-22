<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <?php require __DIR__ . '/../layouts/base.php'; ?>
    <title>Quiz Academy</title>
    <link rel="stylesheet" href="public/css/quiz.css">
</head>
<body>
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
                        <button type="button" class="subject-btn">
                            <i class="fas fa-book"></i> <?= htmlspecialchars($subject->subjectName) ?>
                        </button>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($questions) && $subjectId): ?>
            <div class="quiz-container">
                <form method="post" action="index.php?action=quiz">
                    <?php echo csrfInputField(); ?>
                    <input type="hidden" name="subjectId" value="<?= $subjectId ?>">

                    <?php
                    $currentQuestionId = null;
                    $questionIds = array_unique(array_column($questions, 'questionId'));

                    foreach ($questions as $question):
                        if ($currentQuestionId !== $question->questionId):
                            if ($currentQuestionId !== null):
                                echo '</div></div>';
                            endif;
                            $currentQuestionId = $question->questionId;
                            $questionNumber = array_search($question->questionId, $questionIds) + 1;
                    ?>
                        <div class="question-card">
                            <div class="question-text">
                                <span class="question-number">Question <?= $questionNumber ?>:</span>
                                <?= htmlspecialchars($question->questionName) ?>
                            </div>
                            <div class="answer-options">
                    <?php endif;

                    $questionId      = $question->questionId;
                    $answerId        = $question->answerId;
                    $isSelected      = isset($answersStatus[$questionId]) && $answersStatus[$questionId]['selected'] == $answerId;
                    $isCorrectAnswer = isset($answersStatus[$questionId]) && $answersStatus[$questionId]['correct'] == $answerId;

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
                                <input type="radio" name="q<?= $questionId ?>" value="<?= $answerId ?>"
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
                    <?php if (!empty($questions)) echo '</div></div>'; ?>

                    <?php if (!$submitted): ?>
                        <button type="submit" name="submit" class="submit-btn">
                            Submit Answers <i class="fas fa-paper-plane"></i>
                        </button>
                    <?php else: ?>
                        <div class="quiz-completed-message">
                            <p>Quiz Completed! Review your answers above.</p>
                            <a href="index.php?action=quiz" class="new-quiz-btn">Start New Quiz</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
