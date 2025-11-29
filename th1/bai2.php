<?php
$filename = "data/Quiz.txt";
$questions = [];


if (file_exists($filename)) {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    $current_question = [];
    foreach ($lines as $line) {
        if (strpos($line, "ANSWER:") === 0) {
            $answerStr = trim(substr($line, strpos($line, ":") + 1));

            $current_question['answer'] = array_map('trim', explode(',', $answerStr));
            
            $questions[] = $current_question;
            $current_question = []; 
        } elseif (preg_match('/^[A-D]\./', $line)) {
            $current_question['options'][] = $line;
        } else {
            if (!isset($current_question['question'])) {
                $current_question['question'] = $line;
            } else {
                $current_question['question'] .= " " . $line;
            }
        }
    }
}

$submitted = false;
$score = 0;
$total = count($questions);
$user_answers_list = []; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    
    foreach ($questions as $index => $q) {
    
        $user_input = $_POST['question_' . $index] ?? [];
   
        if (!is_array($user_input)) {
            $user_input = [$user_input];
        }
        
        $user_answers_list[$index] = $user_input;
        $correct_answers = $q['answer'];
        sort($user_input);
        sort($correct_answers);
        
        if ($user_input == $correct_answers) {
            $score++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài thi trắc nghiệm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .correct-answer {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 5px;
        }
        .wrong-answer {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 5px;
        }
        .missing-answer {
            border: 2px dashed #28a745; /* Khung nét đứt màu xanh cho đáp án đúng mà chưa chọn */
            padding: 3px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <h2 class="mb-4">Bài kiểm tra trắc nghiệm</h2>

    <?php if ($submitted): ?>
        <div class="alert alert-info">
            <h4>Kết quả: Bạn làm đúng <?= $score ?> / <?= $total ?> câu.</h4>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <?php foreach ($questions as $index => $q): 
          
            $isMultipleChoice = count($q['answer']) > 1;
            $inputType = $isMultipleChoice ? 'checkbox' : 'radio';
            $inputName = 'question_' . $index . ($isMultipleChoice ? '[]' : '');
        ?>
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Câu <?= $index + 1 ?>:</strong> <?= $q['question'] ?>
                    <?php if($isMultipleChoice): ?>
                        <span class="badge bg-warning text-dark">Chọn nhiều đáp án</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php foreach ($q['options'] as $option): 
                        $answerKey = substr($option, 0, 1);
                        
                        $classInfo = '';
                        $isChecked = '';

                        if ($submitted) {
                            $userSelectedArr = $user_answers_list[$index] ?? [];
                            $userSelected = in_array($answerKey, $userSelectedArr);
                        
                            $isCorrect = in_array($answerKey, $q['answer']);

                            if ($isCorrect) {
                                $classInfo = 'correct-answer'; 
                            }
                            
                            if ($userSelected && !$isCorrect) {
                                $classInfo = 'wrong-answer'; 
                            }
                            
                            if ($isCorrect && !$userSelected) {
                        
                                $classInfo .= ' missing-answer'; 
                            }

                            if ($userSelected) {
                                $isChecked = 'checked';
                            }
                        }
                    ?>
                        <div class="form-check <?= $classInfo ?>">
                            <input class="form-check-input" type="<?= $inputType ?>" 
                                   name="<?= $inputName ?>" 
                                   value="<?= $answerKey ?>" 
                                   <?= $isChecked ?>>
                            <label class="form-check-label"><?= $option ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" class="btn btn-primary">
            <?= $submitted ? 'Làm lại' : 'Nộp bài' ?>
        </button>
    </form>
</div>
</body>
</html>