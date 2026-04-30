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