<?php
require ('connect.php');
require ('Notification/sendEmail.php');

date_default_timezone_set('Asia/Kuala_Lumpur');

$name = $_POST['name'];
$email = $_POST['email'];
$item = $_POST['item'];

// 1. 插入申请记录（借用表）
// $stmt = $conn->prepare("INSERT INTO borrow_requests (name, email, item) VALUES (?, ?, ?)");
// $stmt->bind_param("sss", $name, $email, $item);
// $stmt->execute();

// 2. 从数据库获取所有 admin email
$adminEmails = [];
$sql = "SELECT Email FROM users WHERE role = 'Admin'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $adminEmails[] = $row['Email'];
}

// 3. 添加学生自己也在通知里
$adminEmails[] = $email;

// 4. 构建邮件内容
$subject = "New Application";
$body = "Person: $name<br>Things:$item<br>Date:" . date("Y-m-d H:i");

// 5. 循环发信
foreach ($adminEmails as $to) {
    sendNotification($to, $subject, $body);
}

echo "申请已提交，并已发送通知。";
