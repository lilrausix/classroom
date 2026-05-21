<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('pupil');

$user_id = $_SESSION['user_id'];
$error = $_GET['error'] ?? null;
$success = $_GET['joined'] ?? null;
$submitted = $_GET['submitted'] ?? null;

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$current_user = $userStmt->fetch();

$avatar_filename = $current_user['avatar'] ?? '';
$avatar_path = __DIR__ . "/../uploads/avatars/" . $avatar_filename;
$avatar_url = (!empty($avatar_filename) && file_exists($avatar_path))
    ? "../uploads/avatars/" . $avatar_filename
    : "https://ui-avatars.com/api/?name=" . urlencode($current_user['name']) . "&background=312e81&color=ede9fe&size=256";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
    requireCsrf();
    $avatarDir = __DIR__ . "/../uploads/avatars/";
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];

    $savedName = saveUploadedFile($_FILES['avatar'], $avatarDir, $allowedExtensions);
    if ($savedName === false) {
        $uploadError = 'Atļauts tikai attēla formāts: png, jpg, jpeg, gif.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$savedName, $user_id]);

        header("Location: pupil.php?updated=1");
        exit();
    }
}

$classStmt = $pdo->prepare("SELECT c.*, cm.joined_at FROM classes c
    JOIN class_members cm ON cm.class_id = c.id
    WHERE cm.pupil_id = ?");
$classStmt->execute([$user_id]);
$classes = $classStmt->fetchAll();

$assignmentStmt = $pdo->prepare("SELECT a.*, a.file_path AS assignment_file, c.name AS class_name,
    s.id AS submission_id, s.file_path AS submission_file, s.comment AS submission_comment, s.grade
    FROM assignments a
    JOIN classes c ON c.id = a.class_id
    JOIN class_members cm ON cm.class_id = c.id AND cm.pupil_id = ?
    LEFT JOIN submissions s ON s.assignment_id = a.id AND s.pupil_id = ?
    ORDER BY a.created_at DESC");
$assignmentStmt->execute([$user_id, $user_id]);
$assignments = $assignmentStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="theme-toggle.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Pupil Panel | EduPulse</title>
</head>
<body class="bg-[#050816] text-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <header class="mb-10 rounded-[40px] border border-slate-700 bg-slate-950/60 p-8 shadow-[0_30px_80px_rgba(15,23,42,0.35)] backdrop-blur-sm">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <img src="<?= $avatar_url ?>" alt="Avatar" class="h-28 w-28 rounded-full border-4 border-purple-500 object-cover bg-slate-800">
                        <form method="POST" enctype="multipart/form-data" class="absolute inset-0 z-10">
                            <label for="avatar-upload" class="absolute inset-0 cursor-pointer">
                                <input id="avatar-upload" type="file" name="avatar" onchange="this.form.submit()" class="sr-only" accept="image/*">
                                <div class="absolute -bottom-1 -right-1 rounded-full bg-purple-500 px-3 py-2 text-[10px] uppercase tracking-[0.2em] text-slate-950 shadow-lg transition hover:bg-purple-400">Edit</div>
                            </label>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                        </form>
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Audzēknis</p>
                        <h1 class="text-4xl font-extrabold tracking-tight text-white"><?= htmlspecialchars($current_user['name']) ?></h1>
                        <p class="mt-2 text-slate-400">Jūsu profils ir gatavs, lai pievienotos klasei un iesniegtu uzdevumus.</p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-end">
                    <button id="theme-toggle" type="button" class="theme-toggle-btn">🌙 Tumšā</button>
                    <a href="logout.php" class="inline-flex items-center justify-center rounded-full border border-purple-600 bg-purple-600 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-purple-500">Iziet</a>
                </div>
        </header>

        <?php if ($error): ?>
            <div class="mb-6 rounded-3xl border border-red-500/30 bg-red-500/10 p-5 text-sm text-red-200">Kods nav atrasts. Lūdzu pārbaudiet un mēģiniet vēlreiz.</div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-6 rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-5 text-sm text-emerald-200">Jūs veiksmīgi pievienojāties klasei.</div>
        <?php endif; ?>
        <?php if ($submitted): ?>
            <div class="mb-6 rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-5 text-sm text-emerald-200">Uzdevums iesniegts veiksmīgi.</div>
        <?php endif; ?>
        <?php if (!empty($uploadError)): ?>
            <div class="mb-6 rounded-3xl border border-red-500/30 bg-red-500/10 p-5 text-sm text-red-200"><?= htmlspecialchars($uploadError) ?></div>
        <?php endif; ?>

        <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6">
                <div class="rounded-[40px] border border-slate-700 bg-slate-950/70 p-8 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
                    <div class="flex flex-col gap-6 lg:flex-row lg:justify-between lg:items-center">
                        <div>
                            <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Klases</p>
                            <h2 class="mt-3 text-3xl font-bold text-white">Jūsu studiju grupa</h2>
                        </div>
                        <div class="rounded-full bg-slate-900/80 px-5 py-3 text-sm text-slate-300">Pievienotās klases: <?= count($classes) ?></div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <?php if (count($classes) === 0): ?>
                            <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/60 p-8 text-center text-slate-400">Šobrīd neesat pievienots nevienai klasei.</div>
                        <?php endif; ?>

                        <?php foreach ($classes as $class): ?>
                            <article class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Klase</p>
                                        <h3 class="mt-2 text-2xl font-semibold text-white"><?= htmlspecialchars($class['name']) ?></h3>
                                    </div>
                                    <div class="rounded-3xl bg-slate-950/80 px-4 py-3 text-sm text-slate-300">Kods: <span class="font-mono text-purple-300"><?= htmlspecialchars($class['join_code']) ?></span></div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[40px] border border-slate-700 bg-slate-950/70 p-8 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Uzdevumi</p>
                                <h2 class="mt-3 text-3xl font-bold text-white">Tavi jaunākie darbi</h2>
                            </div>
                            <span class="rounded-full bg-purple-600/10 px-4 py-2 text-sm text-purple-200"><?= count($assignments) ?> kopā</span>
                        </div>

                        <div class="mt-8 space-y-4">
                            <?php if (count($assignments) === 0): ?>
                                <div class="rounded-3xl border border-dashed border-slate-700 bg-slate-900/60 p-8 text-center text-slate-400">Nav pieejamu uzdevumu. Pievienojieties klasei vai pārbaudiet jaunākos atjauninājumus vēlāk.</div>
                            <?php endif; ?>

                            <?php foreach ($assignments as $assignment): ?>
                                <article class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.35em] text-slate-500"><?= htmlspecialchars($assignment['class_name']) ?></p>
                                            <h3 class="mt-2 text-2xl font-semibold text-white"><?= htmlspecialchars($assignment['title']) ?></h3>
                                        </div>
                                        <div class="rounded-3xl bg-slate-950/80 px-4 py-2 text-sm text-slate-300"><?= date('d.m.Y', strtotime($assignment['created_at'])) ?></div>
                                    </div>
                                    <p class="mt-4 text-slate-400 leading-relaxed"><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
                                    <?php if (!empty($assignment['assignment_file'])): ?>
                                        <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-300">
                                            <a href="../uploads/assignments/<?= htmlspecialchars($assignment['assignment_file']) ?>" class="rounded-full bg-indigo-500/15 px-3 py-2 text-indigo-200 hover:bg-indigo-500/25">Lejuplādēt uzdevuma failu</a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($assignment['submission_id']): ?>
                                        <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-300">
                                            <span class="rounded-full bg-slate-800 px-3 py-2">Iesniegts</span>
                                            <span class="rounded-full bg-slate-800 px-3 py-2">Vērtējums: <?= $assignment['grade'] ?? 'Nav' ?></span>
                                            <?php if ($assignment['submission_file']): ?>
                                                <a href="../uploads/submissions/<?= htmlspecialchars($assignment['submission_file']) ?>" class="rounded-full bg-purple-600/15 px-3 py-2 text-purple-200 hover:bg-purple-600/25">Skatīt iesniegto failu</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="submit_task.php" method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                        <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                        <label class="block text-sm text-slate-400">Komentārs skolotājam</label>
                                        <textarea name="comment" rows="3" placeholder="Raksti komentāru..." class="w-full rounded-3xl border border-slate-700 bg-slate-950/80 p-4 text-slate-100 focus:border-purple-500" required><?= htmlspecialchars($assignment['submission_comment'] ?? '') ?></textarea>
                                        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                                            <input type="file" name="task_file" class="rounded-3xl border border-slate-700 bg-slate-950/80 p-4 text-sm text-slate-300 file:rounded-full file:border-0 file:bg-purple-600 file:px-4 file:py-2 file:text-sm file:text-white" accept="*/*">
                                            <button type="submit" class="rounded-3xl bg-purple-600 px-6 py-3 font-semibold text-slate-950 transition hover:bg-purple-500">Iesniegt</button>
                                        </div>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[40px] border border-slate-700 bg-slate-950/70 p-8 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
                    <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Pievienoties klasei</p>
                    <h2 class="mt-3 text-3xl font-bold text-white">Ievadiet kodu</h2>
                    <form action="join_class.php" method="POST" class="mt-6 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                        <input type="text" name="code" maxlength="6" placeholder="85XYQT" class="w-full rounded-3xl border border-slate-700 bg-slate-950/80 px-4 py-4 text-white placeholder:text-slate-500 focus:border-purple-500 focus:outline-none" required>
                        <button type="submit" class="w-full rounded-3xl bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4 font-semibold text-slate-950 transition hover:opacity-90">Pievienoties</button>
                    </form>
                </div>

                <div class="rounded-[40px] border border-slate-700 bg-slate-950/70 p-8 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
                    <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Kopsavilkums</p>
                    <div class="mt-6 space-y-3 text-slate-300">
                        <p><span class="font-semibold text-white">Aktīvas klases:</span> <?= count($classes) ?></p>
                        <p><span class="font-semibold text-white">Pieejami uzdevumi:</span> <?= count($assignments) ?></p>
                        <p><span class="font-semibold text-white">Profila statuss:</span> Aktīvs</p>
                    </div>
                </div>
            </aside>
        </section>
    </div>
    <script src="theme-toggle.js"></script>
</body>
</html>
