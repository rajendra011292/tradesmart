<?php use App\Core\Csrf; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Cancel Plan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">Cancel Plan #<?= (int)$plan['id'] ?> (<?= htmlspecialchars($plan['symbol']) ?>)</h1>

    <?php if (!empty($error)): ?>
      <div class="mb-3 text-red-600"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?php Csrf::inputField(); ?>
      <label class="block text-sm font-medium">Cancel Reason (required)</label>
      <textarea class="mt-1 w-full border rounded px-3 py-2 mb-3" name="cancel_reason" rows="3" required placeholder="Earnings today / invalidation / news risk"></textarea>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm">Confidence (1–10)</label>
          <input type="number" min="1" max="10" name="confidence_level" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm">Emotion</label>
          <input name="emotion_tag" class="mt-1 w-full border rounded px-3 py-2" placeholder="anxious/cautious">
        </div>
      </div>

      <div class="mt-4 flex gap-3">
        <button class="px-4 py-2 rounded bg-red-600 text-white">Confirm Cancel</button>
        <a href="/plans/<?= (int)$plan['id'] ?>" class="px-4 py-2 rounded border">Back</a>
      </div>
    </form>
  </div>
</body>
</html>
