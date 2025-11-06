<?php
use App\Core\Csrf;
/** @var array $old */
/** @var array $errors */
$old = $old ?? [];
$errors = $errors ?? [];
$val = function($k,$def=''){ return htmlspecialchars($old[$k] ?? $def, ENT_QUOTES, 'UTF-8'); };
$err = function($k){ global $errors; if(!empty($errors[$k])) echo '<p class="text-red-500 text-sm">'.$errors[$k].'</p>'; };
?>
<!doctype html>
<html class="h-full">
<head>
  <meta charset="utf-8">
  <title>Create Trade Plan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full bg-gray-100 text-gray-900">
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Create Trade Plan</h1>

    <form method="POST" action="/plans/create" class="space-y-6 bg-white p-6 rounded-xl shadow">
      <?php Csrf::inputField(); ?>

      <!-- Section 1: Basic Info -->
      <div>
        <h2 class="text-lg font-semibold mb-3">1) Basic Info</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium">Plan Date</label>
            <input type="date" name="plan_date" value="<?= $val('plan_date') ?>" class="mt-1 w-full border rounded px-3 py-2">
            <?php $err('plan_date'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Symbol</label>
            <input type="text" name="symbol" value="<?= $val('symbol') ?>" class="mt-1 w-full border rounded px-3 py-2" placeholder="INFY">
            <?php $err('symbol'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Sector</label>
            <input type="text" name="sector" value="<?= $val('sector') ?>" class="mt-1 w-full border rounded px-3 py-2" placeholder="IT">
          </div>
          <div>
            <label class="block text-sm font-medium">Timeframe</label>
            <input type="text" name="timeframe" value="<?= $val('timeframe','daily') ?>" class="mt-1 w-full border rounded px-3 py-2" placeholder="1h / 4h / daily">
            <?php $err('timeframe'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Strategy</label>
            <input type="text" name="strategy" value="<?= $val('strategy') ?>" class="mt-1 w-full border rounded px-3 py-2" placeholder="Breakout / Pullback">
          </div>
        </div>
      </div>

      <!-- Section 2: Entry / Risk -->
      <div>
        <h2 class="text-lg font-semibold mb-3">2) Entry, SL, Target & Risk</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium">Entry Price</label>
            <input type="number" step="0.0001" name="entry_price" value="<?= $val('entry_price') ?>" class="mt-1 w-full border rounded px-3 py-2">
            <?php $err('entry_price'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Stop Loss</label>
            <input type="number" step="0.0001" name="stop_loss" value="<?= $val('stop_loss') ?>" class="mt-1 w-full border rounded px-3 py-2">
            <?php $err('stop_loss'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Target Price</label>
            <input type="number" step="0.0001" name="target_price" value="<?= $val('target_price') ?>" class="mt-1 w-full border rounded px-3 py-2">
            <?php $err('target_price'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Capital Allocated</label>
            <input type="number" step="0.01" name="capital_allocated" value="<?= $val('capital_allocated') ?>" class="mt-1 w-full border rounded px-3 py-2">
            <?php $err('capital_allocated'); ?>
          </div>
          <div>
            <label class="block text-sm font-medium">Market Bias</label>
            <select name="market_bias" class="mt-1 w-full border rounded px-3 py-2">
              <?php
                $opts = ['very_bullish','mild_bullish','sideways','mild_bearish','very_bearish'];
                $cur = $old['market_bias'] ?? 'sideways';
                foreach($opts as $o) {
                  $sel = $cur === $o ? 'selected' : '';
                  echo "<option value=\"$o\" $sel>$o</option>";
                }
              ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium">Risk % (optional)</label>
            <input type="number" step="0.01" name="risk_percent" value="<?= $val('risk_percent') ?>" class="mt-1 w-full border rounded px-3 py-2" placeholder="auto from bias if empty">
          </div>
        </div>
      </div>

      <!-- Section 4: Psychology -->
      <div>
        <h2 class="text-lg font-semibold mb-3">4) Emotions & Notes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium">Confidence (1–10)</label>
            <input type="number" min="1" max="10" name="confidence_level" value="<?= $val('confidence_level') ?>" class="mt-1 w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium">Emotion Tag</label>
            <input type="text" name="emotion_tag" value="<?= $val('emotion_tag') ?>" class="mt-1 w-full border rounded px-3 py-2" placeholder="calm/anxious/excited">
          </div>
          <div class="md:col-span-3">
            <label class="block text-sm font-medium">Why this trade?</label>
            <textarea name="why_trade" class="mt-1 w-full border rounded px-3 py-2" rows="3"><?= $val('why_trade') ?></textarea>
          </div>
          <div class="md:col-span-3">
            <label class="block text-sm font-medium">Emotion Notes</label>
            <textarea name="emotion_notes" class="mt-1 w-full border rounded px-3 py-2" rows="2"><?= $val('emotion_notes') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Section 5: Checklist -->
      <div>
        <h2 class="text-lg font-semibold mb-3">5) Pre-Trade Checklist</h2>
        <?php
          $ck = fn($k)=> !empty($old[$k]) ? 'checked' : '';
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="check_setup_matches_playbook" <?= $ck('check_setup_matches_playbook') ?>> Setup matches playbook
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="check_risk_within_limit" <?= $ck('check_risk_within_limit') ?>> Risk within limit
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="check_no_major_news" <?= $ck('check_no_major_news') ?>> No major news risk
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="check_trend_alignment" <?= $ck('check_trend_alignment') ?>> Trend alignment
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="check_execution_ready" <?= $ck('check_execution_ready') ?>> Execution ready
          </label>
        </div>
      </div>

      <div class="pt-2">
        <button class="px-4 py-2 rounded bg-blue-600 text-white">Save Plan</button>
        <a href="/plans" class="ml-3 text-sm text-gray-600 underline">Back to Plans</a>
      </div>
    </form>
  </div>
</body>
</html>
