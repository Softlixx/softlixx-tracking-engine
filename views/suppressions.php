<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-white mb-1"><i class="bi bi-shield-x text-danger me-2"></i>Email Suppressions & Opt-Outs</h3>
            <p class="text-secondary small mb-0">Recipients who have unsubscribed or bounced. All future campaign sends to these addresses are blocked.</p>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addSuppressionModal">
                <i class="bi bi-plus-lg me-1"></i> Add Suppression
            </button>
        </div>
    </div>
</div>

<div class="card bg-dark border-secondary border-opacity-25 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead class="table-dark text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Suppressed Email</th>
                        <th>Reason</th>
                        <th>Source</th>
                        <th>Suppressed At</th>
                    </tr>
                </thead>
                <tbody class="border-top border-secondary border-opacity-25">
                    <?php if (empty($suppressions)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary">
                            <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                            No suppressed emails found. Your list reputation is clean!
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($suppressions as $idx => $s): ?>
                    <tr>
                        <td class="ps-3 text-secondary font-monospace"><?= $idx + 1 ?></td>
                        <td>
                            <span class="font-monospace text-danger fw-semibold"><?= htmlspecialchars($s['email']) ?></span>
                        </td>
                        <td>
                            <?php if ($s['suppression_reason'] === 'unsubscribe'): ?>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Unsubscribed</span>
                            <?php elseif ($s['suppression_reason'] === 'bounce'): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Hard Bounce</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($s['suppression_reason'] ?? 'manual') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-secondary small"><?= htmlspecialchars($s['source'] ?? 'System') ?></td>
                        <td class="text-secondary small font-monospace"><?= htmlspecialchars($s['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Manual Suppression -->
<div class="modal fade" id="addSuppressionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/suppressions">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title text-white"><i class="bi bi-shield-plus text-danger me-2"></i>Manually Suppress Email</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <input type="email" name="email" required class="form-control bg-darker text-white border-secondary" placeholder="contact@domain.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Reason</label>
                        <select name="reason" class="form-select bg-darker text-white border-secondary">
                            <option value="unsubscribe">Unsubscribed / Opt-Out</option>
                            <option value="bounce">Bounced / Non-existent Mailbox</option>
                            <option value="complaint">Spam Complaint</option>
                            <option value="manual" selected>Manual Exclusion</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Add to Suppression List</button>
                </div>
            </div>
        </form>
    </div>
</div>
