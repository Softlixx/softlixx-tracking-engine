<div class="container-fluid p-2">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold text-white mb-1">Email Campaigns</h4>
      <p class="text-secondary mb-0 small">Overview of all active and completed email sequences.</p>
    </div>
  </div>

  <div class="card-glass p-3">
    <div class="table-responsive">
      <table class="table table-dark-custom align-middle mb-0" style="font-size: 13px;">
        <thead>
          <tr>
            <th>Campaign Name</th>
            <th>Status</th>
            <th>Created</th>
            <th class="text-center">Sent</th>
            <th class="text-center">Human Opens</th>
            <th class="text-center">Clicks</th>
            <th class="text-center">Replies</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($campaigns)): ?>
            <tr>
              <td colspan="8" class="text-center py-5 text-secondary">
                No campaigns registered yet. When you launch or register a campaign in the Python outreach suite, it will automatically populate here.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($campaigns as $c): ?>
              <tr>
                <td class="fw-bold text-white"><?= htmlspecialchars($c['campaign_name']) ?></td>
                <td>
                  <span class="badge badge-soft-emerald text-capitalize px-2 py-1 font-mono"><?= htmlspecialchars($c['status']) ?></span>
                </td>
                <td class="font-mono text-secondary"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($c['created_at']))) ?></td>
                <td class="text-center font-mono fw-semibold text-white"><?= (int)($c['sent_count'] ?? 0) ?></td>
                <td class="text-center font-mono text-emerald-400"><?= (int)($c['human_opens'] ?? 0) ?></td>
                <td class="text-center font-mono text-blue-400"><?= (int)($c['clicks_count'] ?? 0) ?></td>
                <td class="text-center font-mono text-amber-400"><?= (int)($c['replies_count'] ?? 0) ?></td>
                <td>
                  <a href="/campaigns/view?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-2 font-mono" style="font-size: 11px;">View Emails</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
