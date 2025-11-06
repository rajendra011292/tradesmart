<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Plan #<?= (int)$plan['id'] ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900">
  <div class="mt-3 flex flex-wrap gap-2">
    <a class="px-3 py-1 bg-indigo-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/positions/create">+ Open Leg</a>
  </div>
<a class="px-3 py-1 bg-emerald-700 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/journal">+ Journal</a>
  <div class="mt-3 flex gap-2">
    <a class="px-3 py-1 bg-emerald-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/validate">Validate</a>
    <a class="px-3 py-1 bg-blue-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/execute">Execute</a>
    <a class="px-3 py-1 bg-amber-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/adjust">Adjust</a>
    <a class="px-3 py-1 bg-red-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/cancel">Cancel</a>
  </div>

  <div class="max-w-3xl mx-auto p-6">
    <a href="/plans" class="text-sm text-gray-600 underline">&larr; Back</a>
    <h1 class="text-2xl font-bold mt-2">Plan #<?= (int)$plan['id'] ?> — <?= htmlspecialchars($plan['symbol']) ?></h1>
    <p class="text-gray-600">Plan date: <?= htmlspecialchars($plan['plan_date']) ?> | Status: <?= htmlspecialchars($plan['status']) ?></p>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Entry & Risk</h2>
        <p>Entry: <?= htmlspecialchars($plan['entry_price']) ?></p>
        <p>Stop: <?= htmlspecialchars($plan['stop_loss']) ?></p>
        <p>Target: <?= htmlspecialchars($plan['target_price']) ?></p>
        <p>Capital Allocated: <?= htmlspecialchars($plan['capital_allocated']) ?></p>
        <p>Bias: <?= htmlspecialchars($plan['market_bias']) ?> (Risk%: <?= htmlspecialchars($plan['risk_percent']) ?>)</p>
      </div>

      <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Auto Calculations</h2>
        <p>Position Size: <?= htmlspecialchars($plan['position_size']) ?></p>
        <p>Capital Used: <?= htmlspecialchars($plan['capital_used']) ?></p>
        <p>RR Ratio: <?= htmlspecialchars($plan['rr_ratio']) ?></p>
        <p>Expected Profit: <?= htmlspecialchars($plan['expected_profit']) ?></p>
        <p>Expected Loss: <?= htmlspecialchars($plan['expected_loss']) ?></p>
      </div>

      <div class="bg-white p-4 rounded shadow md:col-span-2">
        <h2 class="font-semibold mb-2">Psychology</h2>
        <p>Confidence: <?= htmlspecialchars($plan['confidence_level']) ?></p>
        <p>Emotion: <?= htmlspecialchars($plan['emotion_tag']) ?></p>
        <p class="mt-2"><strong>Why Trade:</strong><br><?= nl2br(htmlspecialchars($plan['why_trade'])) ?></p>
        <p class="mt-2"><strong>Emotion Notes:</strong><br><?= nl2br(htmlspecialchars($plan['emotion_notes'])) ?></p>
      </div>

      <div class="bg-white p-4 rounded shadow md:col-span-2">
        <h2 class="font-semibold mb-2">Checklist</h2>
        <ul class="list-disc list-inside">
          <li>Playbook: <?= $plan['check_setup_matches_playbook'] ? '✅' : '❌' ?></li>
          <li>Risk within limit: <?= $plan['check_risk_within_limit'] ? '✅' : '❌' ?></li>
          <li>No major news: <?= $plan['check_no_major_news'] ? '✅' : '❌' ?></li>
          <li>Trend alignment: <?= $plan['check_trend_alignment'] ? '✅' : '❌' ?></li>
          <li>Execution ready: <?= $plan['check_execution_ready'] ? '✅' : '❌' ?></li>
        </ul>
      </div>
    </div>
    <!-- Positions & Exits Summary -->
    <div class="bg-white p-4 rounded shadow md:col-span-2 mt-6">
      <div class="flex items-center justify-between">
        <h2 class="font-semibold">Positions (Legs)</h2>
        <a class="px-3 py-1 bg-indigo-600 text-white rounded"
          href="/plans/<?= (int)$plan['id'] ?>/positions/create">+ Open Leg</a>
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
                  <td class="px-3 py-2"><?= htmlspecialchars($pos['side']) ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($pos['entry_price']) ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($pos['stop_price'] ?? '-') ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($pos['quantity']) ?></td>
                  <td class="px-3 py-2">
                    <?= htmlspecialchars(number_format((float)$pos['remaining_qty'], 4, '.', '')) ?>
                  </td>
                  <td class="px-3 py-2"><?= htmlspecialchars($pos['status']) ?></td>
                  <td class="px-3 py-2"><?= htmlspecialchars($pos['opened_at']) ?></td>
                  <td class="px-3 py-2">
                    <?php if ((float)$pos['remaining_qty'] > 0): ?>
                      <a class="text-blue-600 underline"
                        href="/plans/positions/<?= (int)$pos['id'] ?>/exits/create">Record Exit</a>
                    <?php else: ?>
                      <span class="text-gray-500">Completed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td class="px-3 py-4" colspan="9">No positions yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
<!-- Timeline -->
<div class="bg-white p-4 rounded shadow md:col-span-2 mt-6">
  <h2 class="font-semibold mb-3">Timeline</h2>
  <div class="mb-3 flex flex-wrap gap-2">
  <a class="px-2 py-1 text-xs bg-emerald-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/validate">+ Validate</a>
  <a class="px-2 py-1 text-xs bg-blue-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/execute">+ Execute</a>
  <a class="px-2 py-1 text-xs bg-amber-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/adjust">+ Adjust</a>
  <a class="px-2 py-1 text-xs bg-red-600 text-white rounded" href="/plans/<?= (int)$plan['id'] ?>/cancel">+ Cancel</a>
</div>


  <?php if (!empty($events)): ?>
    <ol class="relative border-s border-gray-200">
      <?php foreach ($events as $ev): ?>
        <?php
          // parse meta JSON (stored as TEXT for compatibility)
          $meta = null;
          if (!empty($ev['meta'])) {
            $decoded = json_decode($ev['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) { $meta = $decoded; }
          }
        ?>
        <li class="mb-8 ms-4">
          <div class="absolute w-3 h-3 bg-blue-500 rounded-full mt-1.5 -start-1.5 border border-white"></div>

          <time class="mb-1 text-xs text-gray-500 block">
            <?= htmlspecialchars($ev['created_at']) ?>
            <?php if (!empty($ev['user_name'])): ?>
              • by <?= htmlspecialchars($ev['user_name']) ?>
            <?php endif; ?>
          </time>

          <h3 class="text-sm font-semibold text-gray-900 uppercase"><?= htmlspecialchars($ev['event_type']) ?></h3>

          <?php if (!empty($ev['reason'])): ?>
            <p class="text-sm text-gray-800 mt-1"><span class="font-medium">Reason:</span> <?= nl2br(htmlspecialchars($ev['reason'])) ?></p>
          <?php endif; ?>

          <div class="mt-1 text-xs text-gray-600 flex flex-wrap gap-3">
            <?php if ($ev['confidence_level'] !== null && $ev['confidence_level'] !== ''): ?>
              <span>Confidence: <?= htmlspecialchars($ev['confidence_level']) ?>/10</span>
            <?php endif; ?>
            <?php if (!empty($ev['emotion_tag'])): ?>
              <span>Emotion: <?= htmlspecialchars($ev['emotion_tag']) ?></span>
            <?php endif; ?>
          </div>

          <?php if (!empty($ev['note'])): ?>
            <p class="text-sm text-gray-700 mt-2"><?= nl2br(htmlspecialchars($ev['note'])) ?></p>
          <?php endif; ?>

          <?php if (is_array($meta) && !empty($meta)): ?>
            <div class="mt-2 bg-gray-50 border rounded p-2 text-xs text-gray-700">
              <div class="font-semibold mb-1">Details</div>
              <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                <?php foreach ($meta as $mk => $mv): ?>
                  <li>
                    <span class="text-gray-500"><?= htmlspecialchars((string)$mk) ?>:</span>
                    <span>
                      <?php
                        if (is_scalar($mv)) {
                          echo htmlspecialchars((string)$mv);
                        } else {
                          echo htmlspecialchars(json_encode($mv, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
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
    <p class="text-sm text-gray-600">No events yet.</p>
  <?php endif; ?>
</div>

  </div>
</body>

</html>