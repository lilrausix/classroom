<div class="max-w-5xl mx-auto p-10 text-white">
    <div class="flex items-center space-x-6 mb-12 bg-slate-800/50 p-6 rounded-3xl border border-slate-700">
        <div class="relative group">
            <img src="../uploads/avatars/default.png" class="w-24 h-24 rounded-full border-4 border-purple-500">
            <form action="upload_avatar.php" method="POST" enctype="multipart/form-data" class="absolute inset-0 opacity-0 cursor-pointer">
                <input type="file" name="avatar" onchange="this.form.submit()">
            </form>
        </div>
        <div>
            <h1 class="text-2xl font-bold">Sveiks, Audzēknī!</h1>
            <p class="text-gray-400 text-sm">Pievienojies jaunai klasei ar kodu.</p>
        </div>
    </div>

    <div class="card bg-[#1e293b] p-8 rounded-2xl border border-slate-700">
        <h2 class="text-xl font-bold mb-4">Iesniegt darbu</h2>
        <form action="submit_task.php" method="POST" enctype="multipart/form-data">
            <textarea placeholder="Tavs komentārs šeit..." class="w-full bg-slate-800 p-4 rounded-xl border border-slate-700 mb-4 focus:outline-none"></textarea>
            <div class="flex items-center space-x-4">
                <input type="file" name="task_file" class="text-sm text-gray-400">
                <button class="bg-purple-600 px-8 py-2 rounded-xl font-bold">Sūtīt</button>
            </div>
        </form>
    </div>
</div>