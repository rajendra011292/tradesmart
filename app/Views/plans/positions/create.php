<?php use App\Core\Csrf; ?>

    <h1 class="text-xl font-semibold mb-4">Open New Leg — Plan #<?= (int)$plan['id'] ?> (<?= htmlspecialchars($plan['symbol']) ?>)</h1>

    <?php if (!empty($error)): ?>
      <div class="mb-3 text-red-600"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?php Csrf::inputField(); ?>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm">Side</label>
          <select name="side" class="mt-1 w-full border rounded px-3 py-2">
            <option value="long">long</option>
            <option value="short">short</option>
          </select>
        </div>
        <div>
          <label class="block text-sm">Entry Price</label>
          <input type="number" step="0.0001" name="entry_price" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-sm">Stop (optional)</label>
          <input type="number" step="0.0001" name="stop_price" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm">Quantity</label>
          <input type="number" step="0.0001" name="quantity" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
      </div>

      <label class="block text-sm mt-4">Notes (optional)</label>
      <textarea name="notes" class="mt-1 w-full border rounded px-3 py-2" rows="2"></textarea>

      <div class="mt-4 flex gap-3">
        <button class="px-4 py-2 rounded bg-blue-600 text-white">Open Leg</button>
        <a class="px-4 py-2 border rounded" href="/plans/<?= (int)$plan['id'] ?>">Back</a>
      </div>
    </form>

