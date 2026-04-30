<div class="max-w-4xl mx-auto p-10">
    <div class="card flex items-center gap-6 mb-10">
        <div class="relative">
            <img src="uploads/avatars/user1.jpg" class="w-24 h-24 rounded-full border-4 border-purple-500 object-cover">
            <button class="absolute bottom-0 right-0 bg-purple-600 p-2 rounded-full text-xs">✎</button>
        </div>
        <div>
            <h2 class="text-2xl font-bold">Jānis Bērziņš</h2>
            <p class="text-gray-400">Loma: Audzēknis</p>
        </div>
    </div>

    <h3 class="text-xl mb-4">Aktīvie uzdevumi</h3>
    <div class="card">
        <h4 class="font-bold text-lg">Mājas darbs: SQL Join</h4>
        <p class="text-gray-400 text-sm mb-4">Lūdzu augšupielādējiet .sql failu ar risinājumiem.</p>
        
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 mb-4"/>
            <textarea placeholder="Komentārs skolotājam..." class="w-full bg-slate-800 border border-slate-700 rounded p-2 mb-4"></textarea>
            <button class="btn-primary w-full">Iesniegt darbu</button>
        </form>
    </div>
</div>