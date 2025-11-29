<?php
include 'db.php';

$message = "";
if (isset($_POST['upload']) && isset($_FILES['file_txt'])) {
    $file = $_FILES['file_txt']['tmp_name'];
    
    if ($_FILES['file_txt']['size'] > 0) {

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $current_question = [];
        $count = 0;
        $conn->exec("TRUNCATE TABLE questions");

        $sql = "INSERT INTO questions (question_content, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        foreach ($lines as $line) {
            if (strpos($line, "ANSWER:") === 0) {
                $answerStr = trim(substr($line, strpos($line, ":") + 1));
            
                $q_text = $current_question['question'];
                $op_a = $current_question['options'][0] ?? '';
                $op_b = $current_question['options'][1] ?? '';
                $op_c = $current_question['options'][2] ?? '';
                $op_d = $current_question['options'][3] ?? '';
                
                $stmt->execute([$q_text, $op_a, $op_b, $op_c, $op_d, $answerStr]);
                
                $count++;
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
        $message = "Đã import thành công $count câu hỏi vào CSDL!";
    }
}


$stmt = $conn->query("SELECT * FROM questions");
$db_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$questions = [];
foreach ($db_questions as $row) {
    $questions[] = [
        'question' => $row['question_content'],
        'options'  => [$row['option_a'], $row['option_b'], $row['option_c'], $row['option_d']],
        'answer'   => array_map('trim', explode(',', $row['correct_answer'])) 
    ];
}

$submitted = false;
$score = 0;
$total = count($questions);
$user_answers_list = []; 

if (isset($_POST['submit_quiz'])) {
    $submitted = true;
    foreach ($questions as $index => $q) {
        $user_input = $_POST['question_' . $index] ?? [];
        if (!is_array($user_input)) { $user_input = [$user_input]; }
        
        $user_answers_list[$index] = $user_input;
        $correct_answers = $q['answer'];
        
        sort($user_input);
        sort($correct_answers);
        if ($user_input == $correct_answers) { $score++; }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài thi trắc nghiệm (MySQL)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .correct-answer { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .wrong-answer { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .missing-answer { border: 2px dashed #28a745; }
    </style>
</head>
<body>
<div class="container mt-4 mb-5">
    <h2 class="text-primary">Bài 4: Upload Quiz & Lưu vào CSDL</h2>
    
    <div class="card p-3 mb-4 bg-light">
        <h5>1. Upload đề thi (File .txt)</h5>
        <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
            <input type="file" name="file_txt" class="form-control" accept=".txt" required>
            <button type="submit" name="upload" class="btn btn-success">Upload & Import</button>
        </form>
    </div>

    <?php if (count($questions) > 0): ?>
        <hr>
        <h3 class="mb-3">2. Làm bài thi</h3>
        <?php if ($submitted): ?>
            <div class="alert alert-info"><h4>Kết quả: <?= $score ?> / <?= $total ?> câu.</h4></div>
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
                        <?php if($isMultipleChoice): ?><span class="badge bg-warning text-dark">Chọn nhiều</span><?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php foreach ($q['options'] as $option): 
                            
                             if(empty($option)) continue; 
                             $answerKey = substr($option, 0, 1);
                             $classInfo = ''; $isChecked = '';
                             if ($submitted) {
                                $userSelectedArr = $user_answers_list[$index] ?? [];
                                $userSelected = in_array($answerKey, $userSelectedArr);
                                $isCorrect = in_array($answerKey, $q['answer']);
                                if ($isCorrect) $classInfo = 'correct-answer';
                                if ($userSelected && !$isCorrect) $classInfo = 'wrong-answer';
                                if ($isCorrect && !$userSelected) $classInfo .= ' missing-answer';
                                if ($userSelected) $isChecked = 'checked';
                             }
                        ?>
                            <div class="form-check <?= $classInfo ?>">
                                <input class="form-check-input" type="<?= $inputType ?>" name="<?= $inputName ?>" value="<?= $answerKey ?>" <?= $isChecked ?>>
                                <label class="form-check-label"><?= $option ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" name="submit_quiz" class="btn btn-primary"><?= $submitted ? 'Làm lại' : 'Nộp bài' ?></button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">Chưa có câu hỏi nào trong CSDL. Hãy upload file Quiz.txt!</div>
    <?php endif; ?>
</div>
</body>
</html>