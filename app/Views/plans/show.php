<?php
// app/Views/plans/show.php (content-only)
$pid = (int)$plan['id'];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$nf = fn($n, $dec=4) => htmlspecialchars(number_format((float)$n, $dec, '.', ''), ENT_QUOTES, 'UTF-8');
?>

<!-- Header -->
<div class="flex items-start justify-between gap-3 mb-4">
  <div>
    <h1 class="text-2xl font-bold">Plan #<?= $pid ?> — <?= $h($plan['symbol']) ?></h1>
    <p class="text-gray-600 text-sm">
      Date: <?= $h($plan['plan_date']) ?>
      • Status: <span class="font-medium"><?= $h($plan['status']) ?></span>
    </p>
  </div>

  <!-- Quick Actions -->
  <div class="flex flex-wrap gap-2">
    <a class="px-3 py-1 bg-emerald-600 text-white rounded" href="/plans/<?= $pid ?>/validate">Validate</a>
    <a class="px-3 py-1 bg-blue-600 text-white rounded" href="/plans/<?= $pid ?>/execute">Execute</a>
    <a class="px-3 py-1 bg-amber-600 text-white rounded" href="/plans/<?= $pid ?>/adjust">Adjust</a>
    <a class="px-3 py-1 bg-red-600 text-white rounded" href="/plans/<?= $pid ?>/cancel">Cancel</a>
    <a class="px-3 py-1 bg-emerald-700 text-white rounded" href="/plans/<?= $pid ?>/journal">Journal</a>
  </div>
</div>

<!-- Exports -->
<div class="mb-6 flex flex-wrap gap-2">
  <a class="px-3 py-1 bg-gray-800 text-white rounded" href="/plans/<?= $pid ?>/events.csv">Export Events (CSV)</a>
  <a class="px-3 py-1 bg-gray-800 text-white rounded" href="/plans/<?= $pid ?>/positions.csv">Export Positions (CSV)</a>
  <a class="px-3 py-1 bg-gray-800 text-white rounded" href="/plans/<?= $pid ?>/exits.csv">Export Exits (CSV)</a>
</div>

<!-- Info cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <!-- Entry & Risk -->
  <section class="bg-white p-4 rounded shadow">
    <h2 class="font-semibold mb-2">Entry & Risk</h2>
    <div class="grid grid-cols-2 gap-x-6 text-sm">
      <div>Entry: <span class="font-medium"><?= $h($plan['entry_price']) ?></span></div>
      <div>Stop: <span class="font-medium"><?= $h($plan['stop_loss']) ?></span></div>
      <div>Target: <span class="font-medium"><?= $h($plan['target_price']) ?></span></div>
      <div>Capital Allocated: <span class="font-medium"><?= $h($plan['capital_allocated']) ?></span></div>
      <div>Bias: <span class="font-medium"><?= $h($plan['market_bias']) ?></span></div>
      <div>Risk%: <span class="font-medium"><?= $h($plan['risk_percent']) ?></span></div>
    </div>
  </section>

  <!-- Auto Calcs -->
  <section class="bg-white p-4 rounded shadow">
    <h2 class="font-semibold mb-2">Auto Calculations</h2>
    <div class="grid grid-cols-2 gap-x-6 text-sm">
      <div>Position Size: <span class="font-medium"><?= $h($plan['position_size']) ?></span></div>
      <div>Capital Used: <span class="font-medium"><?= $h($plan['capital_used']) ?></span></div>
      <div>RR Ratio: <span class="font-medium"><?= $h($plan['rr_ratio']) ?></span></div>
      <div>Expected Profit: <span class="font-medium"><?= $h($plan['expected_profit']) ?></span></div>
      <div>Expected Loss: <span class="font-medium"><?= $h($plan['expected_loss']) ?></span></div>
    </div>
  </section>

  <!-- Psychology -->
  <section class="bg-white p-4 rounded shadow md:col-span-2">
    <h2 class="font-semibold mb-2">Psychology</h2>
    <div class="text-sm grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-2">
      <div>Confidence: <span class="font-medium"><?= $h($plan['confidence_level']) ?></span></div>
      <div>Emotion: <span class="font-medium"><?= $h($plan['emotion_tag']) ?></span></div>
      <div>Strategy: <span class="font-medium"><?= $h($plan['strategy'] ?? '') ?></span></div>
      <div class="md:col-span-3">
        <div class="text-gray-600">Why Trade</div>
        <div class="font-medium"><?= nl2br($h($plan['why_trade'] ?? '')) ?></div>
      </div>
    </div>
  </section>
</div>

<!-- Positions -->
<section class="bg-white p-4 rounded shadow mt-6">
  <div class="flex items-center justify-between">
    <h2 class="font-semibold">Positions (Legs)</h2>
    <a class="px-3 py-1 bg-indigo-600 text-white rounded" href="/plans/<?= $pid ?>/positions/create">+ Open Leg</a>
  </div>

  <div class="mt-3 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Leg</th>
          <th class="px-3 py-2 text-left">Side</th>
          <th class="px-3 py-2 text-left">Entry</th>
          <th class="px-3 py-2 text-left">Stop</th>
          <th class="px-3 py-2 text-left">Qty</th>
          <th class="px-3 py-2 text-left">Remaining</th>
          <th class="px-3 py-2 text-left">Status</th>
          <th class="px-3 py-2 text-left">Opened</th>
          <th class="px-3 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($positions)): ?>
        <?php foreach ($positions as $pos): ?>
          <tr class="border-t">
            <td class="px-3 py-2"><?= (int)$pos['leg_index'] ?></td>
            <td class="px-3 py-2"><?= $h($pos['side']) ?></td>
            <td class="px-3 py-2"><?= $h($pos['entry_price']) ?></td>
            <td class="px-3 py-2"><?= $h($pos['stop_price'] ?? '-') ?></td>
            <td class="px-3 py-2"><?= $h($pos['quantity']) ?></td>
            <td class="px-3 py-2"><?= $nf($pos['remaining_qty'] ?? 0, 4) ?></td>
            <td class="px-3 py-2"><?= $h($pos['status']) ?></td>
            <td class="px-3 py-2"><?= $h($pos['opened_at']) ?></td>
            <td class="px-3 py-2">
              <?php if ((float)($pos['remaining_qty'] ?? 0) > 0): ?>
                <a class="text-blue-600 underline" href="/plans/positions/<?= (int)$pos['id'] ?>/exits/create">Record Exit</a>
              <?php else: ?>
                <span class="text-gray-500">Completed</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td class="px-3 py-4" colspan="9">No positions yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Timeline -->
<section id="timeline" class="bg-white p-4 rounded shadow mt-6">
  <div class="flex items-center justify-between">
    <h2 class="font-semibold">Timeline</h2>
    <div class="flex gap-2">
      <a class="px-2 py-1 text-xs bg-emerald-600 text-white rounded" href="/plans/<?= $pid ?>/validate">+ Validate</a>
      <a class="px-2 py-1 text-xs bg-blue-600 text-white rounded" href="/plans/<?= $pid ?>/execute">+ Execute</a>
      <a class="px-2 py-1 text-xs bg-amber-600 text-white rounded" href="/plans/<?= $pid ?>/adjust">+ Adjust</a>
      <a class="px-2 py-1 text-xs bg-red-600 text-white rounded" href="/plans/<?= $pid ?>/cancel">+ Cancel</a>
      <a class="px-2 py-1 text-xs bg-emerald-700 text-white rounded" href="/plans/<?= $pid ?>/journal">+ Journal</a>
    </div>
  </div>

  <?php if (!empty($events)): ?>
    <ol class="relative border-s border-gray-200 mt-3">
      <?php foreach ($events as $ev): ?>
        <?php
          $meta = null;
          if (!empty($ev['meta'])) {
              $decoded = json_decode($ev['meta'], true);
              if (json_last_error() === JSON_ERROR_NONE) { $meta = $decoded; }
          }
        ?>
        <li class="mb-8 ms-4">
          <div class="absolute w-3 h-3 bg-blue-500 rounded-full mt-1.5 -start-1.5 border border-white"></div>

          <time class="mb-1 text-xs text-gray-500 block">
            <?= $h($ev['created_at']) ?>
            <?php if (!empty($ev['user_name'])): ?>
              • by <?= $h($ev['user_name']) ?>
            <?php endif; ?>
          </time>

          <h3 class="text-sm font-semibold text-gray-900 uppercase"><?= $h($ev['event_type']) ?></h3>

          <?php if (!empty($ev['reason'])): ?>
            <p class="text-sm text-gray-800 mt-1">
              <span class="font-medium">Reason:</span> <?= nl2br($h($ev['reason'])) ?>
            </p>
          <?php endif; ?>

          <div class="mt-1 text-xs text-gray-600 flex flex-wrap gap-3">
            <?php if ($ev['confidence_level'] !== null && $ev['confidence_level'] !== ''): ?>
              <span>Confidence: <?= $h($ev['confidence_level']) ?>/10</span>
            <?php endif; ?>
            <?php if (!empty($ev['emotion_tag'])): ?>
              <span>Emotion: <?= $h($ev['emotion_tag']) ?></span>
            <?php endif; ?>
          </div>

          <?php if (!empty($ev['note'])): ?>
            <p class="text-sm text-gray-700 mt-2"><?= nl2br($h($ev['note'])) ?></p>
          <?php endif; ?>

          <?php if (is_array($meta) && !empty($meta)): ?>
            <div class="mt-2 bg-gray-50 border rounded p-2 text-xs text-gray-700">
              <div class="font-semibold mb-1">Details</div>
              <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                <?php foreach ($meta as $mk => $mv): ?>
                  <li>
                    <span class="text-gray-500"><?= $h((string)$mk) ?>:</span>
                    <span>
                      <?php
                        if (is_scalar($mv)) {
                          echo $h((string)$mv);
                        } else {
                          echo $h(json_encode($mv, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
                        }
                      ?>
                    </span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php else: ?>
    <p class="text-sm text-gray-600 mt-2">No events yet.</p>
  <?php endif; ?>
</section>

<!-- Back link -->
<div class="mt-6">
  <a href="/plans" class="text-sm text-gray-600 underline">&larr; Back to Plans</a>
</div>
