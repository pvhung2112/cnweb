<?php
include 'db.php';

$message = "";

if (isset($_POST['upload']) && isset($_FILES['file_csv'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    if ($_FILES['file_csv']['size'] > 0) {
        $handle = fopen($file, "r");

        fgetcsv($handle, 1000, ","); 
        
        $sql = "INSERT INTO students (username, password, lastname, firstname, class_name, email, course) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]]);
            $count++;
        }
        fclose($handle);
        $message = "Đã import thành công $count sinh viên!";
    }
}

$stmt = $conn->query("SELECT * FROM students");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sinh viên (MySQL)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-primary mb-4">Bài 4: Upload CSV & Quản lý Sinh viên</h2>

    <div class="card mb-4 p-3 bg-light">
        <h5>Import danh sách (File .csv)</h5>
        <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
            <input type="file" name="file_csv" class="form-control" accept=".csv" required>
            <button type="submit" name="upload" class="btn btn-success">Upload</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Mã SV</th>
                <th>Họ tên</th>
                <th>Lớp</th>
                <th>Email</th>
                <th>Khóa học</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $sv): ?>
            <tr>
                <td><?= $sv['id'] ?></td>
                <td><?= $sv['username'] ?></td>
                <td><?= $sv['lastname'] . " " . $sv['firstname'] ?></td>
                <td><?= $sv['class_name'] ?></td>
                <td><?= $sv['email'] ?></td>
                <td><?= $sv['course'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>