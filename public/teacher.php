<?php
// PHP loģika klases izveidei
if(isset($_POST['create_class'])) {
    $code = strtoupper(substr(md5(time()), 0, 6)); // Random 6 zīmju kods
    // SQL INSERT...
}
?>
<div class="flex bg-[#0f172a] min-h-screen text-white">
    <div class="w-64 border-r border-slate-800 p-6">
        <h2 class="text-2xl font-bold text-purple-500 mb-10">EduPulse</h2>
        <nav class="space-y-4">
            <a href="#" class="block text-purple-400 font-bold">Klašu vadība</a>
            <a href="#" class="block text-gray-400 hover:text-white">Vērtējumi</a>
        </nav>
    </div>

    <div class="flex-1 p-10">
        <div class="flex justify-between mb-10">
            <h1 class="text-3xl font-bold">Tavas klases</h1>
            <form method="POST"><button name="create_class" class="bg-purple-600 px-6 py-2 rounded-lg font-bold">+ Jauna klase</button></form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-[#1e293b] p-6 rounded-2xl border border-slate-700">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-bold">Web Dizains I</h2>
                    <span class="bg-indigo-900 text-indigo-300 px-3 py-1 rounded-full text-sm font-mono">85XYQT</span>
                </div>
                <div class="space-y-4">
                    <div class="bg-slate-800 p-4 rounded-lg">
                        <p class="font-bold">Uzdevums: Navigācijas josla</p>
                        <p class="text-sm text-gray-400">Pievienots fails: instructions.pdf</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>