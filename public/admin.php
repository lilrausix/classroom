<?php
session_start();
if ($_SESSION['role'] !== 'admin') die("Pieeja liegta");
require '../config/db.php';

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<div class="p-10 bg-[#0f172a] min-h-screen text-white">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>
    
    <div class="overflow-x-auto bg-[#1e293b] rounded-xl border border-slate-700">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-slate-700">
                    <th class="p-4">Vārds</th>
                    <th class="p-4">Loma</th>
                    <th class="p-4">Darbības</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr class="border-b border-slate-800 hover:bg-slate-800/50">
                    <td class="p-4"><?= $u['name'] ?></td>
                    <td class="p-4"><span class="px-2 py-1 bg-slate-700 rounded text-xs uppercase"><?= $u['role'] ?></span></td>
                    <td class="p-4">
                        <button class="text-purple-400 hover:underline">Edit Role</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-10">
        <h3 class="text-xl font-bold mb-4">Action History</h3>
        <div class="p-4 bg-slate-800 rounded-lg text-sm text-gray-400">
            [2024-05-20 14:20] Admin changed User #4 role to Teacher
        </div>
    </div>
</div>

<?php
require '../config/session_check.php';
require '../config/db.php';

// Tikai admins drīkst redzēt šo lapu
checkRole('admin');

// Ja tiek nosūtīta forma par lomas maiņu
if (isset($_POST['update_role'])) {
    $u_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];
    
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$new_role, $u_id]);
    
    // Ierakstām vēsturē (Action History)
    $log_stmt = $pdo->prepare("INSERT INTO action_history (admin_id, action_text) VALUES (?, ?)");
    $log_stmt->execute([$_SESSION['user_id'], "Mainīta lietotāja #$u_id loma uz $new_role"]);
    
    header("Location: admin.php?success=1");
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Panelis</title>
</head>
<body class="bg-[#0f172a] text-white p-10 font-sans">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-3xl font-bold text-purple-400">Lietotāju pārvaldība</h1>
            <a href="logout.php" class="text-gray-400 hover:text-white">Iziet</a>
        </div>

        <div class="bg-[#1e293b] rounded-2xl border border-slate-700 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-800 text-gray-300">
                    <tr>
                        <th class="p-4">Vārds</th>
                        <th class="p-4">E-pasts</th>
                        <th class="p-4">Pašreizējā Loma</th>
                        <th class="p-4 text-right">Darbība</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr class="border-b border-slate-700 hover:bg-slate-800/40 transition">
                        <td class="p-4 font-medium"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="p-4 text-gray-400"><?= $user['email'] ?></td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold 
                                <?= $user['role'] == 'admin' ? 'bg-red-900 text-red-200' : ($user['role'] == 'teacher' ? 'bg-green-900 text-green-200' : 'bg-blue-900 text-blue-200') ?>">
                                <?= strtoupper($user['role']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <form method="POST" class="inline-flex gap-2">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="new_role" class="bg-slate-900 border border-slate-600 rounded px-2 py-1 text-sm">
                                    <option value="pupil">Pupil</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <button name="update_role" class="bg-purple-600 hover:bg-purple-500 px-3 py-1 rounded text-sm font-bold transition">Saglabāt</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>





<?php
$logs = $pdo->query("SELECT action_history.*, users.name FROM action_history JOIN users ON action_history.admin_id = users.id ORDER BY created_at DESC")->fetchAll();
?>

<div class="mt-12">
    <h2 class="text-2xl font-bold mb-4 text-slate-400">Pēdējās darbības</h2>
    <div class="space-y-2">
        <?php foreach($logs as $log): ?>
        <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700 flex justify-between">
            <span><strong><?= $log['name'] ?></strong>: <?= $log['action_text'] ?></span>
            <span class="text-gray-500 text-sm"><?= $log['created_at'] ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>