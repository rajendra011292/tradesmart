<?php use App\Core\Csrf; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Validate Plan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">Validate Plan #<?= (int)$plan['id'] ?> (<?= htmlspecialchars($plan['symbol']) ?>)</h1>
    <form method="POST">
      <?php Csrf::inputField(); ?>
      <label class="block text-sm font-medium">Reason (optional)</label>
      <input class="mt-1 w-full border rounded px-3 py-2 mb-3" name="reason" placeholder="Checklist complete, setup confirmed">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm">Confidence (1–10)</label>
          <input type="number" min="1" max="10" name="confidence_level" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm">Emotion</label>
          <input name="emotion_tag" class="mt-1 w-full border rounded px-3 py-2" placeholder="focused/calm">
        </div>
      </div>
      <div class="mt-4 flex gap-3">
        <button class="px-4 py-2 rounded bg-emerald-600 text-white">Validate</button>
        <a href="/plans/<?= (int)$plan['id'] ?>" class="px-4 py-2 rounded border">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
