<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // Pārvirzām atkarībā no lomas
        if($user['role'] == 'admin') header("Location: admin.php");
        elseif($user['role'] == 'teacher') header("Location: teacher.php");
        else header("Location: pupil.php");
        exit();
    } else {
        $error = "Nepareizs e-pasts vai parole!";
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login | EduPulse</title>
</head>
<body class="bg-[#0f172a] text-white flex items-center justify-center h-screen">
    <div class="bg-[#1e293b] p-8 rounded-2xl shadow-xl w-96 border border-slate-700">
        <h2 class="text-3xl font-bold mb-6 text-center text-purple-400">EduPulse</h2>
        <a href="signup.php" class="text-purple-400 font-bold">Izveidot jaunu profilu</a>
        <form method="POST">
            <input type="email" name="email" placeholder="E-pasts" class="w-full p-3 mb-4 bg-slate-800 border border-slate-600 rounded-lg focus:outline-none focus:border-purple-500">
            <input type="password" name="password" placeholder="Parole" class="w-full p-3 mb-6 bg-slate-800 border border-slate-600 rounded-lg focus:outline-none focus:border-purple-500">
            <button class="w-full bg-purple-600 hover:bg-purple-700 p-3 rounded-lg font-bold transition">Ienākt</button>
        </form>
    </div>
</body>
</html>