<div class="container-fluid p-2">
  <div class="mb-4">
    <a href="/clients" class="btn btn-sm btn-outline-secondary mb-3 d-inline-flex align-items-center gap-1.5 font-mono" style="font-size: 11px;">
      <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Back to Clients
    </a>
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars($client['business_name'] ?? 'Client Profile') ?></h4>
        <div class="d-flex align-items-center gap-3 text-secondary font-mono small">
          <span>✉ <?= htmlspecialchars($client['email'] ?? '') ?></span>
          <span>&bull;</span>
          <span>🌐 <?= htmlspecialchars($client['website'] ?? 'No website') ?></span>
          <span>&bull;</span>
          <span>🏢 <?= htmlspecialchars($client['industry'] ?? 'General') ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- TIMELINE COLUMN -->
    <div class="col-12 col-lg-8">
      <div class="card-glass p-4">
        <h6 class="fw-bold text-white mb-4 d-flex align-items-center gap-2">
          <i data-lucide="git-commit-vertical" class="text-indigo-400" style="width: 18px; height: 18px;"></i>
          Engagement Audit Trail & Chronology
        </h6>

        <?php if (empty($timelineEvents)): ?>
          <div class="text-center py-5 text-secondary">
            No tracked interaction events logged yet for this client.
          </div>
        <?php else: ?>
          <div class="position-relative ps-4" style="border-left: 2px dashed rgba(99, 102, 241, 0.3);">
            <?php foreach ($timelineEvents as $ev): ?>
              <?php
                $isAuto = (bool)($ev['is_automated'] ?? false);
                $nodeColor = '#6366f1';
                $badgeClass = 'badge-soft-indigo';
                if ($ev['event_type'] === 'open') {
                  $nodeColor = $isAuto ? '#64748b' : '#10b981';
                  $badgeClass = $isAuto ? 'bg-secondary bg-opacity-25 text-warning' : 'badge-soft-emerald';
                } elseif ($ev['event_type'] === 'click') {
                  $nodeColor = '#3b82f6';
                  $badgeClass = 'badge-soft-blue';
                } elseif ($ev['event_type'] === 'reply') {
                  $nodeColor = '#f59e0b';
                  $badgeClass = 'badge-soft-amber';
                }
              ?>
              <div class="mb-4 position-relative">
                <!-- Circular timeline node -->
                <div class="position-absolute rounded-circle" style="width: 12px; height: 12px; background: <?= $nodeColor ?>; left: -25px; top: 4px; box-shadow: 0 0 8px <?= $nodeColor ?>;"></div>

                <div class="d-flex align-items-baseline gap-2 mb-1">
                  <span class="font-mono text-secondary fw-medium small" style="font-size: 11px;">
                    <?= htmlspecialchars(date('M d, Y &bull; h:i:s A', strtotime($ev['event_timestamp']))) ?>
                  </span>
                  <span class="badge <?= $badgeClass ?> font-mono text-capitalize" style="font-size: 10px;">
                    <?= htmlspecialchars($ev['event_type']) ?>
                  </span>
                  <?php if ($isAuto): ?>
                    <span class="badge bg-secondary bg-opacity-25 text-warning font-mono" style="font-size: 10px;">
                      🤖 Automated Scanner Suspected
                    </span>
                  <?php else: ?>
                    <span class="badge badge-soft-emerald font-mono" style="font-size: 10px;">
                      👤 Human Interaction
                    </span>
                  <?php endif; ?>
                </div>

                <div class="p-3 rounded-3 bg-dark bg-opacity-40 border border-secondary border-opacity-25 mt-2">
                  <div class="text-white small fw-semibold mb-1">
                    <?= htmlspecialchars($ev['detection_reason'] ?: 'Standard event recorded') ?>
                  </div>
                  <?php if (!empty($ev['user_agent'])): ?>
                    <div class="text-secondary font-mono" style="font-size: 10px; word-break: break-all;">
                      UA: <?= htmlspecialchars($ev['user_agent']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($ev['subject'])): ?>
                    <div class="text-slate-400 small mt-1 font-mono">
                      Subject: "<?= htmlspecialchars($ev['subject']) ?>" (Step <?= $ev['email_sequence'] ?? 1 ?>)
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- SUMMARY DOSSIER COLUMN -->
    <div class="col-12 col-lg-4">
      <div class="card-glass p-3 mb-3">
        <h6 class="fw-bold text-white mb-3">Telemetry Summary</h6>
        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25 small">
          <span class="text-secondary">Emails Dispatched</span>
          <span class="text-white font-mono fw-bold"><?= count($campaignEmails ?? []) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25 small">
          <span class="text-secondary">Human Verified Opens</span>
          <span class="text-emerald-400 font-mono fw-bold"><?= (int)($client['human_opens_count'] ?? 0) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25 small">
          <span class="text-secondary">Scanner / Bot Opens</span>
          <span class="text-indigo-300 font-mono fw-bold"><?= (int)($client['bot_opens_count'] ?? 0) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25 small">
          <span class="text-secondary">Tracked Clicks</span>
          <span class="text-blue-400 font-mono fw-bold"><?= (int)($client['clicks_count'] ?? 0) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 small">
          <span class="text-secondary">Suppression Status</span>
          <span class="text-white font-mono">
            <?= !empty($client['is_suppressed']) ? '<span class="text-rose-400 fw-bold">Suppressed</span>' : '<span class="text-emerald-400">Deliverable</span>' ?>
          </span>
        </div>
      </div>
    </div>
  </div>
</div>
