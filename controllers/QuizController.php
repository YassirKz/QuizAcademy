<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/helpers.php';

class QuizController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // --------------------------------------------------------
    // Action : afficher le quiz (+ traiter la soumission)
    // --------------------------------------------------------
    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php");
            exit();
        }

        $username = $_SESSION['user'];

        // Get full user info
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE userName = ?");
        $stmt->execute([$username]);
        $userInfo = $stmt->fetch(PDO::FETCH_OBJ);

        $userEmail  = $userInfo ? $userInfo->userEmail  : '';
        $userGroupe = $userInfo ? $userInfo->userGroupe : '';

        // Load all subjects for the selection panel
        $subjects = $this->pdo->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_OBJ);

        $questions      = [];
        $submitted      = false;
        $score          = 0;
        $answersStatus  = [];
        $totalQuestions = 0;
        $subjectId      = null;

        // ── Handle form submission ────────────────────────────
        if (isset($_POST['submit'])) {
            if (!validateCsrfToken()) {
                die("Invalid request.");
            }
            $submitted = true;

            $subjectId = isset($_POST['subjectId'])
                ? (int)$_POST['subjectId']
                : (isset($_GET['subjectId']) ? (int)$_GET['subjectId'] : null);

            if ($subjectId) {
                $questions      = $this->loadQuestions($subjectId);
                $questionIds    = array_unique(array_column($questions, 'questionId'));
                $totalQuestions = count($questionIds);

                // Calculate score
                foreach ($questionIds as $questionId) {
                    $inputName = "q" . $questionId;
                    if (isset($_POST[$inputName])) {
                        $selectedAnswerId = (int)$_POST[$inputName];

                        $stmt = $this->pdo->prepare(
                            "SELECT answerId FROM answers WHERE questionId = ? AND isCorrect = 1 LIMIT 1"
                        );
                        $stmt->execute([$questionId]);
                        $correctAnswerId = (int)$stmt->fetchColumn();

                        if ($selectedAnswerId === $correctAnswerId) {
                            $score++;
                        }

                        $answersStatus[$questionId] = [
                            'selected' => $selectedAnswerId,
                            'correct'  => $correctAnswerId,
                        ];
                    }
                }

                // Save score to DB
                if ($userInfo && isset($userInfo->userId)) {
                    $note     = $totalQuestions > 0 ? ($score / $totalQuestions) * 10 : 0.00;
                    $examDate = date('Y-m-d');

                    $stmt = $this->pdo->prepare(
                        "INSERT INTO scores (subjectId, userId, note, examDate) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([$subjectId, $userInfo->userId, $note, $examDate]);
                }
            }

        // ── Load quiz on GET ──────────────────────────────────
        } elseif (isset($_GET['subjectId'])) {
            $subjectId      = (int)$_GET['subjectId'];
            $questions      = $this->loadQuestions($subjectId);
            $totalQuestions = count(array_unique(array_column($questions, 'questionId')));
        }

        // Pass all variables to view
        require __DIR__ . '/../views/quiz/quiz.php';
    }

    // --------------------------------------------------------
    // Private helper : load questions + answers for a subject
    // --------------------------------------------------------
    private function loadQuestions(int $subjectId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.questionId, q.questionName, a.answerId, a.answerName
             FROM questions q
             JOIN answers a ON a.questionId = q.questionId
             WHERE q.subjectId = ?"
        );
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
