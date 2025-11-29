<?php 

include 'data/flower.php'; 


if (!isset($flowers)) {
    die("Lỗi: Không tìm thấy biến \$flowers. Hãy kiểm tra lại file data/flowers.php xem đã có dòng '\$flowers = [...]' chưa.");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách các loài hoa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .flower-img {
            width: 100%;
            height: 200px;
            object-fit: cover; 
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="mb-4">
        <a href="bai1.php" class="btn btn-primary">Xem dạng Khách</a>
        <a href="bai1.php?mode=admin" class="btn btn-danger">Xem dạng Quản trị (Admin)</a>
    </div>

    <?php if (isset($_GET['mode']) && $_GET['mode'] == 'admin'): ?>
        
        <h2 class="text-center text-danger mb-3">Quản trị danh sách hoa</h2>
        <button class="btn btn-success mb-3">+ Thêm hoa mới</button>
        
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Tên hoa</th>
                    <th>Mô tả</th>
                    <th>Hình ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flowers as $index => $flower): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><strong><?= $flower['name'] ?></strong></td>
                    <td><?= $flower['description'] ?></td>
                    <td>
                        <img src="<?= $flower['image'] ?>" alt="<?= $flower['name'] ?>" width="80" height="80" style="object-fit: cover;">
                    </td>
                    <td>
                        <a href="#" class="btn btn-warning btn-sm">Sửa</a>
                        <a href="#" class="btn btn-danger btn-sm">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <h2 class="text-center text-success mb-4">14 loại hoa tuyệt đẹp dịp xuân hè</h2>
        
        <div class="row">
            <?php foreach ($flowers as $flower): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?= $flower['image'] ?>" class="flower-img card-img-top" alt="<?= $flower['name'] ?>">
                    <div class="card-body">
                        <h5 class="card-title text-success"><?= $flower['name'] ?></h5>
                        <p class="card-text text-secondary"><?= $flower['description'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>