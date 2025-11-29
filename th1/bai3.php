<?php
$filename = "data/data.csv";
$students = [];

if (($handle = fopen($filename, "r")) !== FALSE) {

    $headers = fgetcsv($handle, 1000, ",");

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $students[] = $data;
    }
    fclose($handle);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách lớp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Danh sách sinh viên (Từ file CSV)</h2>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Username</th>
                <th>Password</th>
                <th>Họ đệm</th>
                <th>Tên</th>
                <th>Lớp</th>
                <th>Email</th>
                <th>Khóa học</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $sv): ?>
            <tr>
                <td><?= $sv[0] ?></td>
                <td><?= $sv[1] ?></td>
                <td><?= $sv[2] ?></td>
                <td><?= $sv[3] ?></td>
                <td><?= $sv[4] ?></td>
                <td><?= $sv[5] ?></td>
                <td><?= $sv[6] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>