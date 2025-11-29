<?php
$filename = "data/Quiz.txt";
$questions = [];

// 1. Đọc file và xử lý dữ liệu (Cập nhật để tách nhiều đáp án)
if (file_exists($filename)) {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    $current_question = [];
    foreach ($lines as $line) {
        if (strpos($line, "ANSWER:") === 0) {
            $answerStr = trim(substr($line, strpos($line, ":") + 1));
            // Tách chuỗi đáp án bằng dấu phẩy (ví dụ: "A, B" -> mảng ["A", "B"])
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

// 2. Xử lý khi người dùng nhấn Nộp bài
$submitted = false;
$score = 0;
$total = count($questions);
$user_answers_list = []; // Lưu lại lịch sử chọn của người dùng

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
    
    foreach ($questions as $index => $q) {
        // Lấy dữ liệu người dùng gửi lên
        // Nếu là checkbox (nhiều đáp án), $_POST sẽ trả về mảng. Nếu radio thì là chuỗi.
        $user_input = $_POST['question_' . $index] ?? [];
        
        // Chuyển tất cả về dạng mảng để dễ so sánh
        if (!is_array($user_input)) {
            $user_input = [$user_input];
        }
        
        $user_answers_list[$index] = $user_input;

        // So sánh: Mảng đáp án đúng vs Mảng người dùng chọn
        // Dùng array_diff để kiểm tra sự khác biệt
        // (Phải chọn đủ và đúng mới được điểm)
        $correct_answers = $q['answer'];
        
        // Sắp xếp lại để so sánh không quan tâm thứ tự (A,B == B,A)
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
            // Kiểm tra xem câu này có mấy đáp án đúng
            $isMultipleChoice = count($q['answer']) > 1;
            // Nếu nhiều đáp án -> Checkbox, 1 đáp án -> Radio
            $inputType = $isMultipleChoice ? 'checkbox' : 'radio';
            // Tên input: Nếu checkbox thì thêm [] để nhận mảng
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
                        $answerKey = substr($option, 0, 1); // Lấy A, B, C, D
                        
                        $classInfo = '';
                        $isChecked = '';

                        if ($submitted) {
                            $userSelectedArr = $user_answers_list[$index] ?? [];
                            
                            // Kiểm tra: Người dùng có chọn đáp án này không?
                            $userSelected = in_array($answerKey, $userSelectedArr);
                            
                            // Kiểm tra: Đây có phải là đáp án đúng không?
                            $isCorrect = in_array($answerKey, $q['answer']);

                            if ($isCorrect) {
                                $classInfo = 'correct-answer'; // Luôn tô xanh đáp án đúng
                            }
                            
                            if ($userSelected && !$isCorrect) {
                                $classInfo = 'wrong-answer'; // Tô đỏ nếu chọn sai
                            }
                            
                            if ($isCorrect && !$userSelected) {
                                // Đáp án đúng nhưng người dùng không chọn -> Nhắc nhở
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