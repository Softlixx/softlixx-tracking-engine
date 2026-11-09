<div class="container-fluid p-2">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold text-white mb-1">Live Telemetry & Security Audit Stream</h4>
      <p class="text-secondary mb-0 small">Real-time raw requests with IP metadata, headers, and automated crawler analysis.</p>
    </div>
  </div>

  <div class="card-glass p-3">
    <div class="table-responsive">
      <table class="table table-dark-custom align-middle mb-0" style="font-size: 12px;">
        <thead>
          <tr>
            <th>Event Timestamp</th>
            <th>Type</th>
            <th>Recipient</th>
            <th>Subject</th>
            <th>Classification</th>
            <th>Detection Reason</th>
            <th>User Agent</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($events)): ?>
            <tr>
              <td colspan="7" class="text-center py-5 text-secondary">
                No telemetry recorded yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($events as $ev): ?>
              <tr>
                <td class="font-mono text-secondary"><?= htmlspecialchars(date('M d, H:i:s', strtotime($ev['event_timestamp']))) ?></td>
                <td>
                  <?php if ($ev['event_type'] === 'open'): ?>
                    <span class="badge badge-soft-emerald px-2 py-1"><i data-lucide="eye" class="me-1" style="width: 12px; height: 12px;"></i> Open</span>
                  <?php elseif ($ev['event_type'] === 'click'): ?>
                    <span class="badge badge-soft-blue px-2 py-1"><i data-lucide="mouse-pointer-click" class="me-1" style="width: 12px; height: 12px;"></i> Click</span>
                  <?php elseif ($ev['event_type'] === 'reply'): ?>
                    <span class="badge badge-soft-amber px-2 py-1"><i data-lucide="message-square" class="me-1" style="width: 12px; height: 12px;"></i> Reply</span>
                  <?php else: ?>
                    <span class="badge badge-soft-indigo px-2 py-1"><?= htmlspecialchars(strtoupper($ev['event_type'])) ?></span>
                  <?php endif; ?>
                </td>
                <td class="fw-semibold text-white"><?= htmlspecialchars($ev['recipient_email'] ?? '') ?></td>
                <td class="text-secondary text-truncate" style="max-width: 180px;"><?= htmlspecialchars($ev['subject'] ?? '') ?></td>
                <td>
                  <?php if ($ev['is_automated']): ?>
                    <span class="badge bg-secondary bg-opacity-25 text-warning font-mono">🤖 Automated</span>
                  <?php else: ?>
                    <span class="badge badge-soft-emerald font-mono">👤 Human</span>
                  <?php endif; ?>
                </td>
                <td class="text-secondary"><?= htmlspecialchars($ev['detection_reason'] ?: 'Normal Interaction') ?></td>
                <td class="text-secondary font-mono text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($ev['user_agent'] ?? '') ?>">
                  <?= htmlspecialchars($ev['user_agent'] ?: 'Direct Socket') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
