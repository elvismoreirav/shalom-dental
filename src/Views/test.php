<!DOCTYPE html>
<html>
<head>
    <title><?= $this->e($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-6">
        <h1 class="text-2xl font-bold text-[#1E4D3A] mb-4">
            <?= $this->e($message) ?>
        </h1>
        <p class="text-gray-600">
            El motor de vistas funciona correctamente. Namespace: <code class="bg-gray-200 p-1 rounded">App\Core</code>
        </p>
    </div>
</body>
</html>