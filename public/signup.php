<?php
require '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Droša parole
    $role = $_POST['role']; // teacher vai pupil

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        header("Location: login.php?registered=1");
    } catch (PDOException $e) {
        $error = "E-pasts jau eksistē!";
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Sign Up | EduPulse</title>
</head>
<body class="bg-[#0f172a] text-white flex items-center justify-center min-h-screen p-6">
    <div class="bg-[#1e293b] p-10 rounded-[40px] shadow-2xl w-full max-w-md border border-slate-700">
        <h2 class="text-4xl font-black mb-2 text-center text-purple-500 italic">EduPulse.</h2>
        <p class="text-slate-400 text-center mb-8">Izveido savu profilu</p>

        <?php if(isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500 text-red-500 p-3 rounded-xl mb-4 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="name" placeholder="Pilns vārds" required 
                   class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl focus:outline-none focus:border-purple-500 transition">
            
            <input type="email" name="email" placeholder="E-pasts" required 
                   class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl focus:outline-none focus:border-purple-500 transition">
            
            <input type="password" name="password" placeholder="Parole" required 
                   class="w-full p-4 bg-slate-800 border border-slate-700 rounded-2xl focus:outline-none focus:border-purple-500 transition">

            <div class="flex gap-4 p-2 bg-slate-800 rounded-2xl border border-slate-700">
                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer p-2 rounded-xl has-[:checked]:bg-purple-600 transition">
                    <input type="radio" name="role" value="pupil" checked class="hidden">
                    <span class="text-sm font-bold">Skolēns</span>
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer p-2 rounded-xl has-[:checked]:bg-purple-600 transition">
                    <input type="radio" name="role" value="teacher" class="hidden">
                    <span class="text-sm font-bold">Skolotājs</span>
                </label>
            </div>

            <button class="w-full bg-purple-600 hover:bg-purple-500 p-4 rounded-2xl font-black text-lg transition shadow-lg shadow-purple-500/20 mt-4">
                Reģistrēties
            </button>
        </form>

        <p class="text-center mt-8 text-slate-500 text-sm">
            Jau ir profils? <a href="login.php" class="text-purple-400 font-bold hover:underline">Ielogoties</a>
        </p>
    </div>
</body>
</html>