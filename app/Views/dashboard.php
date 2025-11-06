<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - TradeSmart</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
  <div class="max-w-6xl mx-auto p-6">
    <!-- Header / Nav -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Dashboard</h1>
      <nav class="flex gap-3">
        <a class="px-3 py-2 bg-blue-600 text-white rounded" href="/plans/create">+ New Plan</a>
        <a class="px-3 py-2 bg-white border rounded" href="/plans">All Plans</a>
        <a class="px-3 py-2 bg-white border rounded" href="/logout">Logout</a>
      </nav>
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
      <?php
        $sc = $statusCounts ?? [];
        $labels = ['planned','validated','executed','ongoing','completed','canceled'];
        foreach ($labels as $lbl):
      ?>
      <div class="bg-white rounded-lg shadow p-3">
        <div class="text-xs uppercase text-gray-500"><?= htmlspecialchars($lbl) ?></div>
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
        <a class="px-3 py-1 bg-indigo-600 text-white rounded" href="/plans/1/positions/create" title="demo: replace 1 with a real plan id">Open Leg (pick a plan)</a>
        <a class="px-3 py-1 bg-amber-600 text-white rounded" href="/plans/1/adjust" title="demo: replace 1">Adjust Plan</a>
        <a class="px-3 py-1 bg-sky-600 text-white rounded" href="/plans/1/validate" title="demo: replace 1">Validate Plan</a>
        <a class="px-3 py-1 bg-violet-600 text-white rounded" href="/plans/1/execute" title="demo: replace 1">Execute Plan</a>
        <a class="px-3 py-1 bg-red-600 text-white rounded" href="/plans/1/cancel" title="demo: replace 1">Cancel Plan</a>
      </div>
      <p class="text-xs text-gray-500 mt-2">Tip: Replace <code>/1/</code> with a real plan id, or click into a plan from the lists below.</p>
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
        <?php if (!empty($recentPlans)): foreach ($recentPlans as $p): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= htmlspecialchars($p['plan_date']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($p['symbol']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($p['rr_ratio']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($p['status']) ?></td>
            <td class="px-4 py-2">
              <a class="text-blue-600 underline" href="/plans/<?= (int)$p['id'] ?>">Open</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td class="px-4 py-4" colspan="5">No recent plans.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Recent Events -->
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
        <?php if (!empty($recentEvents)): foreach ($recentEvents as $e): ?>
          <tr class="border-t">
            <td class="px-4 py-2"><?= htmlspecialchars($e['created_at']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($e['symbol']) ?> (#<?= (int)$e['trade_plan_id'] ?>)</td>
            <td class="px-4 py-2"><?= htmlspecialchars($e['event_type']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($e['reason'] ?? '') ?></td>
            <td class="px-4 py-2">
              <a class="text-blue-600 underline" href="/plans/<?= (int)$e['trade_plan_id'] ?>">Open</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td class="px-4 py-4" colspan="5">No recent activity.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
