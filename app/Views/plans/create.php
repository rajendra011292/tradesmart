<?php use App\Core\Csrf; ?>

<h1 class="text-2xl font-bold mb-4">Create New Trade Plan</h1>

<form method="POST" class="space-y-6" action="/plans/store">
  <?php Csrf::inputField(); ?>
  <input type="hidden" name="plan_date" value="<?= date('Y-m-d') ?>">

  <!-- 1️⃣ Basic Info -->
  <section class="bg-white rounded-xl p-5 shadow">
    <h2 class="text-lg font-semibold mb-3">Basic Info</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm">Symbol</label>
        <input name="symbol" required class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g. INFY">
      </div>
      <div>
        <label class="block text-sm">Sector</label>
        <input name="sector" class="mt-1 w-full border rounded px-3 py-2" placeholder="Technology">
      </div>
      <div>
        <label class="block text-sm">Timeframe</label>
        <select name="timeframe" class="mt-1 w-full border rounded px-3 py-2">
          <option value="daily">Daily</option>
          <option value="swing">Swing</option>
          <option value="weekly">Weekly</option>
        </select>
      </div>
    </div>
  </section>

  <!-- 2️⃣ Entry & Risk -->
  <section class="bg-white rounded-xl p-5 shadow">
    <h2 class="text-lg font-semibold mb-3">Entry & Risk</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm">Entry Price</label>
        <input type="number" step="0.01" name="entry_price" required class="mt-1 w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm">Stop Loss</label>
        <input type="number" step="0.01" name="stop_loss" required class="mt-1 w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm">Target Price</label>
        <input type="number" step="0.01" name="target_price" required class="mt-1 w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm">Capital Allocated (₹)</label>
        <input type="number" step="0.01" name="capital_allocated" class="mt-1 w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm">Market Bias</label>
        <select name="market_bias" class="mt-1 w-full border rounded px-3 py-2">
          <option value="very_bullish">Very Bullish</option>
          <option value="mild_bullish">Mild Bullish</option>
          <option value="sideways">Sideways</option>
          <option value="mild_bearish">Mild Bearish</option>
          <option value="very_bearish">Very Bearish</option>
        </select>
      </div>
      <div>
        <label class="block text-sm">Risk per Trade (%)</label>
        <input type="number" step="0.1" name="risk_percent" class="mt-1 w-full border rounded px-3 py-2" value="2.0">
      </div>
    </div>
  </section>

  <!-- 3️⃣ Calculated Fields (auto) -->
  <section class="bg-white rounded-xl p-5 shadow">
    <h2 class="text-lg font-semibold mb-3">Auto Calculated</h2>
    <p class="text-sm text-gray-600">Will auto-fill after entry details (backend or JS later)</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
      <input type="text" disabled placeholder="Position Size" class="border rounded px-3 py-2 bg-gray-50">
      <input type="text" disabled placeholder="Capital Used" class="border rounded px-3 py-2 bg-gray-50">
      <input type="text" disabled placeholder="RR Ratio" class="border rounded px-3 py-2 bg-gray-50">
    </div>
  </section>

  <!-- 4️⃣ Psychology -->
  <section class="bg-white rounded-xl p-5 shadow">
    <h2 class="text-lg font-semibold mb-3">Psychology</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm">Confidence (1–10)</label>
        <input type="number" min="1" max="10" name="confidence_level" class="mt-1 w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-sm">Emotion</label>
        <input name="emotion_tag" class="mt-1 w-full border rounded px-3 py-2" placeholder="calm, anxious, excited">
      </div>
      <div>
        <label class="block text-sm">Why Trade</label>
        <input name="why_trade" class="mt-1 w-full border rounded px-3 py-2" placeholder="Setup reasoning">
      </div>
    </div>
  </section>

  <!-- 5️⃣ Checklist -->
  <section class="bg-white rounded-xl p-5 shadow">
    <h2 class="text-lg font-semibold mb-3">Pre-Trade Checklist</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
      <label class="flex items-center gap-2"><input type="checkbox" name="check_setup_matches_playbook" value="1" class="border rounded"> Setup matches playbook</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="check_risk_within_limit" value="1" class="border rounded"> Risk within limit</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="check_no_major_news" value="1" class="border rounded"> No major news event</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="check_trend_alignment" value="1" class="border rounded"> Trend alignment confirmed</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="check_execution_ready" value="1" class="border rounded"> Execution ready</label>
    </div>
  </section>

  <!-- Actions -->
  <div class="flex gap-3">
    <button class="px-5 py-2 bg-emerald-600 text-white rounded">Save Plan</button>
    <a href="/plans" class="px-5 py-2 border rounded">Cancel</a>
  </div>
</form>
