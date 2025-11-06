<?php use App\Core\Csrf; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Journal Entry</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">
      Journal — Plan #<?= (int)$plan['id'] ?> (<?= htmlspecialchars($plan['symbol']) ?>)
    </h1>

    <?php if (!empty($errors)): ?>
      <div class="mb-4 bg-red-50 text-red-700 border border-red-200 rounded p-3 text-sm">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST">
      <?php Csrf::inputField(); ?>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium">Outcome</label>
          <select name="outcome" class="mt-1 w-full border rounded px-3 py-2" required>
            <option value="">-- select --</option>
            <option value="win">Win</option>
            <option value="loss">Loss</option>
            <option value="breakeven">Breakeven</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Actual P/L (₹)</label>
          <input type="number" step="0.01" name="actual_pl" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g. 1250.50">
        </div>
        <div>
          <label class="block text-sm font-medium">Confidence (1–10)</label>
          <input type="number" min="1" max="10" name="confidence_level" class="mt-1 w-full border rounded px-3 py-2">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium">Emotion</label>
          <input name="emotion_tag" class="mt-1 w-full border rounded px-3 py-2" placeholder="relieved/calm/frustrated">
        </div>
        <div>
          <label class="block text-sm font-medium">Tags</label>
          <input name="tags" class="mt-1 w-full border rounded px-3 py-2" placeholder="breakout, late-entry, news">
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium">What went well?</label>
        <textarea name="went_well" class="mt-1 w-full border rounded px-3 py-2" rows="3"></textarea>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium">What will you improve next time?</label>
        <textarea name="improve_next" class="mt-1 w-full border rounded px-3 py-2" rows="3"></textarea>
      </div>

      <div class="mt-4">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="adhered_rules" class="border rounded">
          <span class="text-sm">I adhered to my rules/playbook</span>
        </label>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium">Link (chart/screenshot)</label>
        <input name="link" class="mt-1 w-full border rounded px-3 py-2" placeholder="https://...">
      </div>

      <div class="mt-6 flex gap-3">
        <button class="px-4 py-2 rounded bg-emerald-600 text-white">Save Journal</button>
        <a class="px-4 py-2 border rounded" href="/plans/<?= (int)$plan['id'] ?>">Back</a>
      </div>
    </form>
  </div>
</body>
</html>
