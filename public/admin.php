<?php
require '../config/session_check.php';
require '../config/db.php';
checkRole('admin');

if (isset($_POST['update_role'])) {
    requireCsrf();
    $userId = $_POST['user_id'];
    $name = trim($_POST['name']);
    $newRole = $_POST['new_role'];
    $password = $_POST['password'] ?? '';

    if (!in_array($newRole, ['admin', 'teacher', 'pupil'])) {
        $newRole = 'pupil';
    }

    if ($password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $newRole, $hashedPassword, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $newRole, $userId]);
    }

    $logStmt = $pdo->prepare("INSERT INTO action_history (admin_id, action_text) VALUES (?, ?)");
    $logStmt->execute([$_SESSION['user_id'], "Lietotāja #$userId profila atjauninājums: vārds='$name', loma='$newRole'."]);

    header('Location: admin.php?success=1');
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$logs = $pdo->query("SELECT action_history.*, users.name AS admin_name FROM action_history JOIN users ON action_history.admin_id = users.id ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="theme-toggle.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Panelis | EduPulse</title>
</head>
<body class="bg-[#050816] text-slate-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <header class="mb-10 rounded-[40px] border border-slate-700 bg-slate-950/80 p-8 shadow-[0_30px_80px_rgba(15,23,42,0.35)]">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-slate-500">Admin Panelis</p>
                    <h1 class="mt-3 text-4xl font-extrabold text-purple-300">Lietotāju pārvaldība</h1>
                    <p class="mt-2 text-slate-400">Pārvaldiet profila datus, lomas un sekojiet darbību vēsturei.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <button id="theme-toggle" type="button" class="theme-toggle-btn">🌙 Tumšā</button>
                    <a href="logout.php" class="inline-flex items-center justify-center rounded-full border border-purple-600 bg-purple-600 px-6 py-3 text-sm font-semibold text-slate-950 transition hover:bg-purple-500">Iziet</a>
                </div>
        </header>

        <section class="rounded-[40px] border border-slate-700 bg-slate-950/80 p-6 shadow-[0_25px_70px_rgba(15,23,42,0.35)]">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-300">
                    <thead class="bg-slate-900 text-slate-400">
                        <tr>
                            <th class="p-4">Vārds</th>
                            <th class="p-4">E-pasts</th>
                            <th class="p-4">Loma</th>
                            <th class="p-4 text-right">Darbība</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-slate-900/80 transition">
                                <td class="p-4 font-medium text-white"><?= htmlspecialchars($user['name']) ?></td>
                                <td class="p-4 text-slate-400"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="p-4">
                                    <span class="inline-flex rounded-full bg-slate-800 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-300"><?= strtoupper($user['role']) ?></span>
                                </td>
                                <td class="p-4 text-right">
                                    <form method="POST" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full rounded-2xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white focus:outline-none sm:w-32">
                                        <select name="new_role" class="w-full rounded-2xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white focus:outline-none sm:w-28">
                                            <option value="pupil" <?= $user['role'] === 'pupil' ? 'selected' : '' ?>>Pupil</option>
                                            <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <input type="password" name="password" placeholder="Jauna parole" class="w-full rounded-2xl border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-white focus:outline-none sm:w-44">
                                        <button type="submit" name="update_role" class="rounded-2xl bg-purple-600 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-purple-500">Saglabāt</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <article class="rounded-[40px] border border-slate-700 bg-slate-950/80 p-6 shadow-[0_25px_70px_rgba(15,23,42,0.35)]">
                <h2 class="text-2xl font-bold text-white">Darbību vēsture</h2>
                <?php if (count($logs) === 0): ?>
                    <div class="mt-6 rounded-3xl border border-dashed border-slate-700 bg-slate-900/60 p-6 text-slate-400">Šobrīd nav ierakstu.</div>
                <?php else: ?>
                    <div class="mt-6 space-y-4">
                        <?php foreach ($logs as $log): ?>
                            <article class="rounded-3xl border border-slate-800 bg-slate-900/90 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm text-slate-300"><span class="font-semibold text-white"><?= htmlspecialchars($log['admin_name']) ?></span> <?= htmlspecialchars($log['action_text']) ?></p>
                                    <span class="text-xs uppercase tracking-[0.2em] text-slate-500"><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
            <article class="rounded-[40px] border border-slate-700 bg-slate-950/80 p-6 shadow-[0_25px_70px_rgba(15,23,42,0.35)]">
                <h2 class="text-2xl font-bold text-white">Padoms</h2>
                <p class="mt-4 text-slate-400">Izmantojiet šo paneli, lai pārvaldītu skolotājus un skolēnus, atiestātu paroles un saglabātu skaidru darbību žurnālu.</p>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-slate-300">
                    <li>Pārskatiet lomas un atjauniniet tās tieši tabulā.</li>
                    <li>Paroles mainīšana darbojas tikai tad, ja ievadāt jaunu paroli.</li>
                    <li>Darbību vēsture automātiski reģistrē izmaiņas.</li>
                </ul>
            </article>
        </section>
    </div>
    <script src="theme-toggle.js"></script>
</body>
</html>
