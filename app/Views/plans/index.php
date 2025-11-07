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