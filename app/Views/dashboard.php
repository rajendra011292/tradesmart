<?php
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$sc = $statusCounts ?? [];
?>

<!-- Header / Quick actions -->
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold">Dashboard</h1>
  <div class="flex gap-2">
    <a class="px-3 py-2 bg-blue-600 text-white rounded" href="/plans/create">+ New Plan</a>
    <a class="px-3 py-2 bg-white border rounded" href="/plans">All Plans</a>
  </div>
</div>

<!-- Stat cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <div class="bg-white rounded-xl shadow p-4">
    <div class="text-sm text-gray-500">Total Plans</div>
    <div class="text-2xl font-bold mt-1"><?= (int)($totPlans ?? 0) ?></div>
  </div>
  <div class="bg-white rounded-xl shadow p-4">
    <div class="text-sm text-gray-500">Open Plans</div>
    <div class="text-2xl font-bold mt-1"><?= (int)($openPlans ?? 0) ?></div>
  </div>
  <div class="bg-white rounded-xl shadow p-4">
    <div class="text-sm text-gray-500">Open Positions</div>
    <div class="text-2xl font-bold mt-1"><?= (int)($openPositions ?? 0) ?></div>
  </div>
  <div class="bg-white rounded-xl shadow p-4">
    <div class="text-sm text-gray-500">Completed (30d)</div>
    <div class="text-2xl font-bold mt-1"><?= (int)($completed30 ?? 0) ?></div>
  </div>
</div>

<!-- Status strip -->
<div class="mt-6 grid grid-cols-2 md:grid-cols-6 gap-3">
  <?php foreach (['planned','validated','executed','ongoing','completed','canceled'] as $lbl): ?>
  <div class="bg-white rounded-lg shadow p-3">
    <div class="text-xs uppercase text-gray-500"><?= $h($lbl) ?></div>
    <div class="text-xl font-semibold"><?= (int)($sc[$lbl] ?? 0) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Quick links -->
<div class="mt-6 bg-white rounded-xl shadow p-4">
  <h2 class="font-semibold mb-3">Quick Links</h2>
  <div class="flex flex-wrap gap-2">
    <a class="px-3 py-1 bg-emerald-600 text-white rounded" href="/plans/create">Create Plan</a>
    <a class="px-3 py-1 bg-blue-600 text-white rounded" href="/plans">View Plans</a>
    <a class="px-3 py-1 bg-gray-800 text-white rounded" href="/plans/export.csv">Export Plans (CSV)</a>
  </div>
</div>

<!-- Recent Plans -->
<div class="mt-6 bg-white rounded-xl shadow overflow-hidden">
  <div class="px-4 py-3 border-b"><h2 class="font-semibold">Recent Plans</h2></div>
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">Date</th>
        <th class="px-4 py-2 text-left">Symbol</th>
        <th class="px-4 py-2 text-left">RR</th>
        <th class="px-4 py-2 text-left">Status</th>
        <th class="px-4 py-2 text-left">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($recentPlans)): ?>
        <?php foreach ($recentPlans as $p): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= $h($p['plan_date']) ?></td>
            <td class="px-4 py-2"><?= $h($p['symbol']) ?></td>
            <td class="px-4 py-2"><?= $h($p['rr_ratio']) ?></td>
            <td class="px-4 py-2"><?= $h($p['status']) ?></td>
            <td class="px-4 py-2"><a class="text-blue-600 underline" href="/plans/<?= (int)$p['id'] ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td class="px-4 py-4" colspan="5">No recent plans.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php
// Safe JSON helpers
$J = fn($v) => json_encode($v, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
?>

<!-- Analytics -->
<div class="mt-6 bg-white rounded-xl shadow p-4">
  <h2 class="font-semibold mb-3">Analytics</h2>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Status distribution -->
    <div>
      <h3 class="text-sm font-medium mb-2">Plan Status Distribution</h3>
      <canvas id="chartStatus" height="200"></canvas>
    </div>

    <!-- Completed per month -->
    <div class="lg:col-span-1">
      <h3 class="text-sm font-medium mb-2">Completed Trades (Last 6 Months)</h3>
      <canvas id="chartMonthly" height="200"></canvas>
    </div>

    <!-- Outcomes -->
    <div>
      <h3 class="text-sm font-medium mb-2">Journal Outcomes</h3>
      <canvas id="chartOutcome" height="200"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
(function () {
  // Data from PHP
  const statusLabels  = <?= $J($statusLabels ?? []) ?>;
  const statusData    = <?= $J($statusData ?? []) ?>;

  const monthLabels   = <?= $J($monthLabels ?? []) ?>;
  const monthData     = <?= $J($monthData ?? []) ?>;

  const outcomeLabels = <?= $J($outcomeLabels ?? []) ?>;
  const outcomeData   = <?= $J($outcomeData ?? []) ?>;

  // Status Pie
  const ctxStatus = document.getElementById('chartStatus');
  if (ctxStatus) {
    new Chart(ctxStatus, {
      type: 'pie',
      data: { labels: statusLabels, datasets: [{ data: statusData }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }

  // Monthly Bar
  const ctxMonthly = document.getElementById('chartMonthly');
  if (ctxMonthly) {
    new Chart(ctxMonthly, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [{ label: 'Completed', data: monthData }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } },
        plugins: { legend: { display: false } }
      }
    });
  }

  // Outcome Pie
  const ctxOutcome = document.getElementById('chartOutcome');
  if (ctxOutcome) {
    new Chart(ctxOutcome, {
      type: 'pie',
      data: { labels: outcomeLabels, datasets: [{ data: outcomeData }] },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
})();
</script>

<!-- Recent Activity -->
<div class="mt-6 bg-white rounded-xl shadow overflow-hidden">
  <div class="px-4 py-3 border-b"><h2 class="font-semibold">Recent Activity</h2></div>
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">When</th>
        <th class="px-4 py-2 text-left">Plan</th>
        <th class="px-4 py-2 text-left">Type</th>
        <th class="px-4 py-2 text-left">Reason</th>
        <th class="px-4 py-2 text-left">Link</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($recentEvents)): ?>
        <?php foreach ($recentEvents as $e): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= $h($e['created_at']) ?></td>
            <td class="px-4 py-2"><?= $h($e['symbol']) ?> (#<?= (int)$e['trade_plan_id'] ?>)</td>
            <td class="px-4 py-2"><?= $h($e['event_type']) ?></td>
            <td class="px-4 py-2"><?= $h($e['reason'] ?? '') ?></td>
            <td class="px-4 py-2"><a class="text-blue-600 underline" href="/plans/<?= (int)$e['trade_plan_id'] ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td class="px-4 py-4" colspan="5">No recent activity.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
