<div class="container-fluid p-2">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold text-white mb-1">Client CRM & Outreach Dossiers</h4>
      <p class="text-secondary mb-0 small">Synchronized client records with verified delivery and full event timelines.</p>
    </div>
  </div>

  <div class="card-glass p-3">
    <div class="table-responsive">
      <table class="table table-dark-custom align-middle mb-0" style="font-size: 13px;">
        <thead>
          <tr>
            <th>Business & Presence</th>
            <th>Industry</th>
            <th>Email</th>
            <th>Website</th>
            <th class="text-center">Touchpoints</th>
            <th class="text-center">Engagement</th>
            <th>Audit</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($clients)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-secondary">
                No clients synchronized yet. As you harvest and target leads locally, they sync seamlessly here.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($clients as $cl): ?>
              <tr>
                <td>
                  <div class="fw-bold text-white"><?= htmlspecialchars($cl['business_name']) ?></div>
                  <small class="text-secondary font-mono" style="font-size: 11px;">ID: <?= htmlspecialchars($cl['external_client_id']) ?></small>
                </td>
                <td class="text-secondary"><?= htmlspecialchars($cl['industry'] ?: 'General Services') ?></td>
                <td class="font-mono text-emerald-300"><?= htmlspecialchars($cl['email']) ?></td>
                <td class="font-mono text-blue-400">
                  <?php if ($cl['website']): ?>
                    <a href="<?= htmlspecialchars($cl['website']) ?>" target="_blank" class="text-decoration-none text-blue-400">
                      <?= htmlspecialchars(parse_url($cl['website'], PHP_URL_HOST) ?: $cl['website']) ?>
                    </a>
                  <?php else: ?>
                    <span class="text-secondary">None</span>
                  <?php endif; ?>
                </td>
                <td class="text-center font-mono text-white"><?= (int)($cl['emails_sent_count'] ?? 0) ?></td>
                <td class="text-center">
                  <?php if (($cl['replied_count'] ?? 0) > 0): ?>
                    <span class="badge badge-soft-amber px-2 py-1 font-mono">Replied</span>
                  <?php elseif (($cl['clicks_count'] ?? 0) > 0): ?>
                    <span class="badge badge-soft-blue px-2 py-1 font-mono">Clicked</span>
                  <?php elseif (($cl['human_opens_count'] ?? 0) > 0): ?>
                    <span class="badge badge-soft-emerald px-2 py-1 font-mono">Opened</span>
                  <?php else: ?>
                    <span class="badge bg-secondary bg-opacity-25 text-secondary font-mono">Pitched</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="/clients/view?id=<?= $cl['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-2 font-mono" style="font-size: 11px;">Timeline</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
