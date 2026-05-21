<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

$class_id = $_GET['id'] ?? null;
if (!$class_id) die("Klase nav atrasta.");

// Iegūstam klases datus
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ? AND teacher_id = ?");
$stmt->execute([$class_id, $_SESSION['user_id']]);
$class = $stmt->fetch();
if (!$class) die("Piekļuve liegta.");

// Iegūstam visus uzdevumus šai klasei
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE class_id = ? ORDER BY created_at DESC");
$stmt->execute([$class_id]);
$assignments = $stmt->fetchAll();

// Iegūstam visus iesniegtos darbus (Submissions)
$stmt = $pdo->prepare("
    SELECT s.*, u.name as pupil_name, a.title as task_title 
    FROM submissions s
    JOIN users u ON s.pupil_id = u.id
    JOIN assignments a ON s.assignment_id = a.id
    WHERE a.class_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$class_id]);
$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <link rel="stylesheet" href="theme-toggle.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title><?= htmlspecialchars($class['name']) ?> | EduPulse</title>
</head>
<body class="bg-[#0f172a] text-white flex min-h-screen">
    <main class="flex-1 p-10">
        <div class="flex justify-between items-center mb-10">
            <div>
                <a href="teacher.php" class="text-purple-400 text-sm hover:underline">← Atpakaļ uz dashboard</a>
                <h1 class="text-4xl font-black mt-2"><?= htmlspecialchars($class['name']) ?></h1>
                <p class="text-slate-500 font-mono">Pievienošanās kods: <?= $class['join_code'] ?></p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button id="theme-toggle" type="button" class="theme-toggle-btn">🌙 Tumšā</button>
                <button onclick="document.getElementById('task-modal').classList.toggle('hidden')" class="bg-purple-600 hover:bg-purple-500 px-6 py-3 rounded-2xl font-bold transition shadow-lg shadow-purple-500/20">
                    + Jauns uzdevums
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-6">
                <h2 class="text-2xl font-bold mb-4">Aktīvie uzdevumi</h2>
                <?php foreach($assignments as $a): ?>
                <div class="bg-slate-800/50 border border-slate-700 p-6 rounded-3xl">
                    <h3 class="text-xl font-bold text-purple-300"><?= htmlspecialchars($a['title']) ?></h3>
                    <p class="text-slate-400 mt-2 mb-4"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
                    <?php if($a['file_path']): ?>
                        <a href="../uploads/assignments/<?= htmlspecialchars($a['file_path']) ?>" class="inline-flex items-center text-sm text-indigo-400 bg-indigo-400/10 px-3 py-1 rounded-full border border-indigo-400/20">
                            📎 Pievienotais fails
                        </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-[#1e293b] p-8 rounded-[40px] border border-slate-700 h-fit">
                <h2 class="text-2xl font-bold mb-6 italic">Iesniegtie darbi</h2>
                <div class="space-y-4">
                    <?php foreach($submissions as $s): ?>
                    <div class="p-4 bg-slate-800 rounded-2xl border border-slate-700">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-white"><?= $s['pupil_name'] ?></span>
                            <span class="text-xs text-slate-500"><?= date('H:i, d.m', strtotime($s['submitted_at'])) ?></span>
                        </div>
                        <p class="text-xs text-purple-400 mb-2 uppercase tracking-tighter">Uzdevums: <?= $s['task_title'] ?></p>
                        <p class="text-sm text-slate-400 mb-3 italic">"<?= htmlspecialchars($s['comment']) ?>"</p>
                        
                        <div class="flex gap-2 items-center">
                            <a href="../uploads/submissions/<?= htmlspecialchars($s['file_path']) ?>" class="text-xs bg-slate-700 p-2 rounded hover:bg-slate-600 transition">Lejuplādēt failu</a>
                            <form action="grade.php" method="POST" class="flex gap-1">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <input type="hidden" name="sub_id" value="<?= $s['id'] ?>">
                                <input type="number" name="grade" placeholder="1-10" class="w-12 bg-slate-900 text-xs p-2 rounded border border-slate-600 text-center">
                                <button class="bg-green-600 text-[10px] px-2 rounded font-bold uppercase">OK</button>
                            </form>
                        </div>
                        <?php if($s['grade']): ?>
                            <p class="mt-2 text-xs font-bold text-green-400">Vērtējums: <?= $s['grade'] ?>/10</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div id="task-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center p-6">
            <div class="bg-slate-900 border border-slate-700 p-10 rounded-[40px] w-full max-w-lg">
                <h2 class="text-3xl font-black mb-6">Izveidot uzdevumu</h2>
                <form action="create_assignment.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                    <input type="hidden" name="class_id" value="<?= $class_id ?>">
                    <input type="text" name="title" placeholder="Uzdevuma nosaukums" required class="w-full bg-slate-800 border border-slate-700 p-4 rounded-2xl focus:outline-none focus:border-purple-500">
                    <textarea name="description" placeholder="Apraksts..." rows="4" class="w-full bg-slate-800 border border-slate-700 p-4 rounded-2xl focus:outline-none focus:border-purple-500"></textarea>
                    <input type="file" name="assignment_file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-purple-600 file:text-white hover:file:bg-purple-700">
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="flex-1 bg-purple-600 py-4 rounded-2xl font-bold shadow-lg">Publicēt</button>
                        <button type="button" onclick="document.getElementById('task-modal').classList.add('hidden')" class="flex-1 bg-slate-700 py-4 rounded-2xl font-bold">Atcelt</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="theme-toggle.js"></script>
</body>
</html>