<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

$teacherId = $_SESSION['user_id'];

$classStmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM class_members cm WHERE cm.class_id = c.id) AS student_count, (SELECT COUNT(*) FROM assignments a WHERE a.class_id = c.id) AS assignments_count FROM classes c WHERE c.teacher_id = ? ORDER BY c.name");
$classStmt->execute([$teacherId]);
$classes = $classStmt->fetchAll();

$submissionStmt = $pdo->prepare("SELECT s.*, u.name AS pupil_name, a.title AS assignment_title, c.name AS class_name, c.id AS class_id FROM submissions s JOIN assignments a ON s.assignment_id = a.id JOIN classes c ON a.class_id = c.id JOIN users u ON s.pupil_id = u.id WHERE c.teacher_id = ? ORDER BY c.name, a.title, s.submitted_at DESC");
$submissionStmt->execute([$teacherId]);
$submissions = $submissionStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="theme-toggle.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Vērtēšana | EduPulse</title>
</head>
<body class="bg-[#050816] text-slate-100 min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-full max-w-[320px] border-r border-slate-800 bg-slate-950/80 p-8 shadow-[10px_0_80px_rgba(15,23,42,0.25)]">
            <div class="mb-10">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Skolotājs</p>
                <h1 class="mt-3 text-4xl font-extrabold text-purple-300">EduPulse</h1>
            </div>
            <nav class="space-y-3 text-slate-300">
                <a href="teacher.php" class="block rounded-3xl px-4 py-3 hover:bg-slate-900 transition">Tavas klases</a>
                <a href="grading.php" class="block rounded-3xl bg-purple-600/10 px-4 py-3 font-semibold text-purple-200">Vērtēšana</a>
            </nav>
            <div class="mt-10">
                <a href="logout.php" class="inline-flex w-full items-center justify-center rounded-3xl bg-purple-600 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-purple-500">Iziet</a>
            </div>
        </aside>

        <main class="flex-1 p-10">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between mb-10">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Skolotāja panelis</p>
                    <h2 class="mt-3 text-4xl font-extrabold text-white">Vērtēšana</h2>
                    <p class="mt-2 text-slate-400">Šeit redzami visi iesniegtie darbi no jūsu klasēm.</p>
                </div>
                <button id="theme-toggle" type="button" class="theme-toggle-btn">🌙 Tumšā</button>
            </div>
            <?php if (count($submissions) === 0): ?>
                <div class="rounded-[40px] border border-slate-700 bg-slate-950/70 p-10 text-center text-slate-400 shadow-[0_25px_80px_rgba(15,23,42,0.2)]">
                    <p class="text-xl font-semibold text-white">Nav vēl iesniegumu vērtēšanai.</p>
                    <p class="mt-3">Kad skolēni iesniegs darbus, tie parādīsies šeit jūsu pārskatam.</p>
                </div>
            <?php else: ?>
                <div class="grid gap-6">
                    <?php foreach ($submissions as $submission): ?>
                        <article class="rounded-[38px] border border-slate-700 bg-slate-950/80 p-8 shadow-[0_25px_80px_rgba(15,23,42,0.25)]">
                            <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-slate-500"><?= htmlspecialchars($submission['class_name']) ?></p>
                                    <h3 class="mt-2 text-2xl font-semibold text-white"><?= htmlspecialchars($submission['assignment_title']) ?></h3>
                                    <p class="mt-2 text-sm text-slate-400">Iesniedzējs: <span class="font-semibold text-white"><?= htmlspecialchars($submission['pupil_name']) ?></span></p>
                                </div>
                                <div class="rounded-3xl bg-slate-900/80 px-4 py-3 text-sm text-slate-300"><?= date('d.m.Y H:i', strtotime($submission['submitted_at'])) ?></div>
                            </div>
                            <p class="mt-4 text-slate-400">Komentārs: <?= nl2br(htmlspecialchars($submission['comment'] ?? 'Nav komentāra')) ?></p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <?php if (!empty($submission['file_path'])): ?>
                                    <a href="../uploads/submissions/<?= htmlspecialchars($submission['file_path']) ?>" class="rounded-full bg-indigo-500/15 px-4 py-3 text-sm text-indigo-200 hover:bg-indigo-500/25">Skatīt iesniegto failu</a>
                                <?php endif; ?>
                                <form action="grade.php" method="POST" class="flex items-center gap-3">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                    <input type="hidden" name="sub_id" value="<?= $submission['id'] ?>">
                                    <input type="number" name="grade" min="0" max="10" placeholder="1-10" class="w-20 rounded-3xl border border-slate-700 bg-slate-900/80 px-4 py-3 text-sm text-slate-100 focus:outline-none focus:border-purple-500" required>
                                    <button type="submit" class="rounded-3xl bg-purple-600 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-purple-500">Saglabāt</button>
                                </form>
                            </div>
                            <?php if ($submission['grade'] !== null): ?>
                                <p class="mt-4 text-sm font-semibold text-green-400">Pašreizējais vērtējums: <?= htmlspecialchars($submission['grade']) ?>/10</p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="theme-toggle.js"></script>
</body>
</html>
