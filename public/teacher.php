<?php
session_start();
require '../config/session_check.php';
require '../config/db.php';
checkRole('teacher');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $className = trim($_POST['class_name']);
    $teacherId = $_SESSION['user_id'];

    if ($className !== '') {
        $joinCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        $stmt = $pdo->prepare("INSERT INTO classes (name, join_code, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$className, $joinCode, $teacherId]);
    }

    header('Location: teacher.php?created=1');
    exit();
}

$classStmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM class_members cm WHERE cm.class_id = c.id) AS student_count, (SELECT COUNT(*) FROM assignments a WHERE a.class_id = c.id) AS assignments_count FROM classes c WHERE c.teacher_id = ? ORDER BY c.id DESC");
$classStmt->execute([$_SESSION['user_id']]);
$classes = $classStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Teacher Panel | EduPulse</title>
</head>
<body class="bg-[#050816] text-slate-100 min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-full max-w-[320px] border-r border-slate-800 bg-slate-950/80 p-8 shadow-[10px_0_80px_rgba(15,23,42,0.25)]">
            <div class="mb-10">
                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Skolotājs</p>
                <h1 class="mt-3 text-4xl font-extrabold text-purple-300">EduPulse</h1>
            </div>
            <nav class="space-y-3 text-slate-300">
                <a href="teacher.php" class="block rounded-3xl bg-purple-600/10 px-4 py-3 font-semibold text-purple-200">Tavas klases</a>
                <a href="#classes" class="block rounded-3xl px-4 py-3 hover:bg-slate-900 transition">Vērtēšana</a>
            </nav>
            <div class="mt-10">
                <a href="logout.php" class="inline-flex w-full items-center justify-center rounded-3xl bg-purple-600 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-purple-500">Iziet</a>
            </div>
        </aside>

        <main class="flex-1 p-10">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between mb-10">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Skolotāja panelis</p>
                    <h2 class="mt-3 text-4xl font-extrabold text-white">Tavas klases</h2>
                </div>
                <form method="POST" class="grid w-full max-w-2xl gap-3 sm:grid-cols-[1fr_auto]">
                    <input type="text" name="class_name" placeholder="Pievienot jaunu klasi" required class="rounded-3xl border border-slate-700 bg-slate-900/80 px-5 py-3 text-slate-100 focus:outline-none focus:border-purple-500">
                    <button name="create_class" class="rounded-3xl bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-3 font-semibold text-slate-950 transition hover:opacity-90">+ Izveidot klasi</button>
                </form>
            </div>

            <?php if (count($classes) === 0): ?>
                <div class="rounded-[40px] border border-slate-700 bg-slate-950/70 p-10 text-center text-slate-400 shadow-[0_25px_80px_rgba(15,23,42,0.2)]">
                    <p class="text-xl font-semibold text-white">Nav vēl izveidotu klašu.</p>
                    <p class="mt-3">Izveido klasi, lai sāktu uzaicināt audzēkņus ar QR kodu vai pievienošanās kodu.</p>
                </div>
            <?php else: ?>
                <div class="grid gap-6 xl:grid-cols-2">
                    <?php foreach ($classes as $class): ?>
                        <?php $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($class['join_code']); ?>
                        <article class="rounded-[38px] border border-slate-700 bg-slate-950/80 p-8 shadow-[0_25px_80px_rgba(15,23,42,0.25)]">
                            <div class="flex items-start justify-between gap-6">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Klase</p>
                                    <h3 class="mt-3 text-3xl font-semibold text-white"><?= htmlspecialchars($class['name']) ?></h3>
                                </div>
                                <img src="<?= $qrUrl ?>" alt="QR kods" class="h-24 w-24 rounded-3xl bg-white p-2">
                            </div>
                            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-3xl bg-slate-900/80 p-5">
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Pievienošanās kods</p>
                                    <p class="mt-3 font-mono text-xl text-purple-300"><?= htmlspecialchars($class['join_code']) ?></p>
                                </div>
                                <div class="rounded-3xl bg-slate-900/80 p-5">
                                    <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Skolēni</p>
                                    <p class="mt-3 text-3xl font-semibold text-white"><?= $class['student_count'] ?></p>
                                </div>
                            </div>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <span class="rounded-full bg-purple-600/10 px-4 py-3 text-sm text-purple-200">Uzdevumi: <?= $class['assignments_count'] ?></span>
                                <a href="class_view.php?id=<?= $class['id'] ?>" class="rounded-full border border-purple-600/30 bg-purple-600/10 px-4 py-3 text-sm font-semibold text-purple-200 transition hover:bg-purple-600/20">Atvērt klasi</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
