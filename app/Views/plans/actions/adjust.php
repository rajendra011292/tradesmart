<?php use App\Core\Csrf; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Adjust Plan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-semibold mb-4">Adjust Plan #<?= (int)$plan['id'] ?> (<?= htmlspecialchars($plan['symbol']) ?>)</h1>

    <?php if (!empty($errors['reason'])): ?>
      <div class="mb-3 text-red-600"><?= htmlspecialchars($errors['reason']) ?></div>
    <?php endif; ?>

    <form method="POST">
      <?php Csrf::inputField(); ?>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Entry</label>
          <input type="number" step="0.0001" name="entry_price" value="<?= htmlspecialchars($plan['entry_price']) ?>" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Stop</label>
          <input type="number" step="0.0001" name="stop_loss" value="<?= htmlspecialchars($plan['stop_loss']) ?>" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Target</label>
          <input type="number" step="0.0001" name="target_price" value="<?= htmlspecialchars($plan['target_price']) ?>" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Capital Allocated</label>
          <input type="number" step="0.01" name="capital_allocated" value="<?= htmlspecialchars($plan['capital_allocated']) ?>" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium">Market Bias</label>
          <select name="market_bias" class="mt-1 w-full border rounded px-3 py-2">
            <?php
              $opts = ['very_bullish','mild_bullish','sideways','mild_bearish','very_bearish'];
              foreach($opts as $o) {
                $sel = ($plan['market_bias'] === $o) ? 'selected' : '';
                echo "<option value=\"$o\" $sel>$o</option>";
              }
            ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Risk %</label>
          <input type="number" step="0.01" name="risk_percent" value="<?= htmlspecialchars($plan['risk_percent']) ?>" class="mt-1 w-full border rounded px-3 py-2">
        </div>
      </div>

      <label class="block text-sm font-medium mt-4">Adjustment Reason (required)</label>
      <textarea name="reason" class="mt-1 w-full border rounded px-3 py-2" rows="3" required placeholder="Moved stop to swing low / news change"><?= htmlspecialchars($plan['last_adjust_reason'] ?? '') ?></textarea>

      <div class="mt-4 flex gap-3">
        <button class="px-4 py-2 rounded bg-amber-600 text-white">Save Adjustments</button>
        <a href="/plans/<?= (int)$plan['id'] ?>" class="px-4 py-2 rounded border">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
