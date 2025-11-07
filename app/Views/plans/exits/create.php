<?php use App\Core\Csrf; ?>

    <h1 class="text-xl font-semibold mb-4">Record Exit — Position #<?= (int)$position['id'] ?> (<?= htmlspecialchars($position['symbol']) ?>)</h1>

    <?php if (!empty($error)): ?>
      <div class="mb-3 text-red-600"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?php Csrf::inputField(); ?>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm">Exit Price</label>
          <input type="number" step="0.0001" name="exit_price" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-sm">Quantity</label>
          <input type="number" step="0.0001" name="quantity" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-sm">Exit Type</label>
          <select name="exit_type" class="mt-1 w-full border rounded px-3 py-2">
            <option value="partial">partial</option>
            <option value="full">full</option>
          </select>
        </div>
        <div>
          <label class="block text-sm">Confidence (1–10)</label>
          <input type="number" min="1" max="10" name="confidence_level" class="mt-1 w-full border rounded px-3 py-2">
        </div>
      </div>

      <label class="block text-sm mt-4">Emotion</label>
      <input name="emotion_tag" class="mt-1 w-full border rounded px-3 py-2" placeholder="relieved/excited">

      <label class="block text-sm mt-4">Exit Reason (required for reviews)</label>
      <textarea name="exit_reason" class="mt-1 w-full border rounded px-3 py-2" rows="3" required></textarea>

      <div class="mt-4 flex gap-3">
        <button class="px-4 py-2 rounded bg-emerald-600 text-white">Save Exit</button>
        <a class="px-4 py-2 border rounded" href="/plans/<?= (int)$position['plan_id'] ?>">Back</a>
      </div>
    </form>