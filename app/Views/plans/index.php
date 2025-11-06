<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Trade Plans</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900">
  <div class="max-w-5xl mx-auto p-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Your Trade Plans</h1>
      <a href="/plans/create" class="px-3 py-2 rounded bg-blue-600 text-white">New Plan</a>
    </div>
    


    <div class="mt-6 bg-white rounded-xl shadow overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left">Date</th>
            <th class="px-4 py-2 text-left">Symbol</th>
            <th class="px-4 py-2 text-left">RR</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($plans)): foreach ($plans as $p): ?>
              <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($p['plan_date']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($p['symbol']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($p['rr_ratio']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($p['status']) ?></td>
                <td class="px-4 py-2">
                  <a class="text-blue-600 underline" href="/plans/<?= (int)$p['id'] ?>">View</a>
                </td>
              </tr>
            <?php endforeach;
          else: ?>
            <tr>
              <td class="px-4 py-4" colspan="5">No plans yet. <a class="text-blue-600 underline" href="/plans/create">Create one</a>.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>