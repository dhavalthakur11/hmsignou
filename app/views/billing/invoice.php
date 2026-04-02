<?php
/**
 * Invoice View — printable, full breakdown.
 * Also handles payment recording and extra charges (staff only).
 */

$bill    = $bill ?? [];
$booking = $booking ?? [];
$nights  = $nights ?? 0;

$isStaff = in_array(user_role(), ['admin', 'receptionist'], true);
$isPaid  = ($bill['payment_status'] ?? '') === 'paid';
?>

<!-- Print / action bar -->
<div class="invoice-toolbar no-print">
    <div>
        <a href="<?= base_url($isStaff ? 'booking/index' : 'booking/mybookings') ?>"
           class="btn btn-outline btn-sm">← Back</a>
    </div>

    <div style="display:flex;gap:8px;">
        <button onclick="window.print()" class="btn btn-outline btn-sm">🖨 Print</button>

        <?php if ($isStaff && !$isPaid): ?>
            <button class="btn btn-success btn-sm" onclick="openPayModal()">
                Mark as Paid
            </button>
        <?php endif; ?>

        <?php if ($isStaff && !$isPaid): ?>
            <button class="btn btn-outline btn-sm" onclick="openExtrasModal()">
                + Extra Charges
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="invoice-wrapper" id="invoiceDoc">

    <div class="invoice-header">
        <div class="invoice-brand">
            <div class="invoice-logo">🏨</div>
            <div>
                <h1>GrandHotel</h1>
                <p>Management System</p>
                <p class="text-muted" style="font-size:.82rem">
                    123 Grand Avenue, Mumbai, MH 400001<br>
                    GST: 27AAAAA0000A1Z5 | info@grandhotel.com
                </p>
            </div>
        </div>

        <div class="invoice-meta">
            <h2>INVOICE</h2>
            <table class="invoice-meta-table">
                <tr>
                    <td>Invoice #</td>
                    <td>
                        <strong>INV-<?= str_pad((string)($bill['bill_id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong>
                    </td>
                </tr>
                <tr>
                    <td>Booking #</td>
                    <td><strong>#<?= (int)($booking['booking_id'] ?? 0) ?></strong></td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>
                        <?= !empty($bill['created_at']) ? date('d M Y', strtotime($bill['created_at'])) : '-' ?>
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="badge <?= $isPaid ? 'badge-green' : 'badge-orange' ?>">
                            <?= ucfirst($bill['payment_status'] ?? 'unpaid') ?>
                        </span>
                    </td>
                </tr>

                <?php if ($isPaid && !empty($bill['paid_at'])): ?>
                    <tr>
                        <td>Paid on</td>
                        <td><?= date('d M Y', strtotime($bill['paid_at'])) ?></td>
                    </tr>
                    <tr>
                        <td>Method</td>
                        <td><?= ucwords(str_replace('_', ' ', $bill['payment_method'] ?? '—')) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <hr class="invoice-divider">

    <div class="invoice-parties">
        <div class="invoice-section">
            <h4>Bill To</h4>
            <p><strong><?= htmlspecialchars($booking['guest_name'] ?? '') ?></strong></p>
            <p><?= htmlspecialchars($booking['guest_email'] ?? '') ?></p>
            <?php if (!empty($booking['guest_phone'])): ?>
                <p><?= htmlspecialchars($booking['guest_phone']) ?></p>
            <?php endif; ?>
        </div>

        <div class="invoice-section">
            <h4>Stay Details</h4>
            <p>
                <strong>Room <?= htmlspecialchars($booking['room_number'] ?? '—') ?></strong>
                — <?= htmlspecialchars($booking['room_type'] ?? '') ?>
            </p>
            <p>
                Floor <?= (int)($booking['floor'] ?? 0) ?>,
                Capacity <?= (int)($booking['capacity'] ?? 0) ?> guests
            </p>
            <p>
                Check-in:
                <strong>
                    <?= !empty($booking['check_in']) ? date('D, d M Y', strtotime($booking['check_in'])) : '-' ?>
                </strong>
            </p>
            <p>
                Check-out:
                <strong>
                    <?= !empty($booking['check_out']) ? date('D, d M Y', strtotime($booking['check_out'])) : '-' ?>
                </strong>
            </p>
            <p>
                Duration:
                <strong><?= (int)$nights ?> night<?= (int)$nights !== 1 ? 's' : '' ?></strong>
            </p>
        </div>
    </div>

    <hr class="invoice-divider">

    <table class="invoice-table">
        <thead>
            <tr>
                <th style="text-align:left">Description</th>
                <th style="text-align:right">Rate</th>
                <th style="text-align:right">Qty</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    Room Charges —
                    <?= htmlspecialchars($booking['room_type'] ?? '') ?>
                    (Room <?= htmlspecialchars($booking['room_number'] ?? '') ?>)
                </td>
                <td style="text-align:right">
                    ₹<?= number_format((float)($booking['price_per_night'] ?? 0), 2) ?>
                </td>
                <td style="text-align:right"><?= (int)$nights ?> nights</td>
                <td style="text-align:right">
                    <strong>₹<?= number_format((float)($bill['room_charges'] ?? 0), 2) ?></strong>
                </td>
            </tr>

            <?php if ((float)($bill['extra_charges'] ?? 0) > 0): ?>
                <tr>
                    <td>
                        Extra Charges
                        <?php if (!empty($bill['notes'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($bill['notes']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right">—</td>
                    <td style="text-align:right">—</td>
                    <td style="text-align:right">
                        <strong>₹<?= number_format((float)($bill['extra_charges'] ?? 0), 2) ?></strong>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

        <tfoot>
            <tr class="invoice-subtotal">
                <td colspan="3" style="text-align:right">Subtotal</td>
                <td style="text-align:right">
                    ₹<?= number_format((float)($bill['room_charges'] ?? 0) + (float)($bill['extra_charges'] ?? 0), 2) ?>
                </td>
            </tr>
            <tr class="invoice-subtotal">
                <td colspan="3" style="text-align:right">GST (18%)</td>
                <td style="text-align:right">
                    ₹<?= number_format((float)($bill['tax_amount'] ?? 0), 2) ?>
                </td>
            </tr>
            <tr class="invoice-total">
                <td colspan="3" style="text-align:right"><strong>Total Payable</strong></td>
                <td style="text-align:right">
                    <strong>₹<?= number_format((float)($bill['total_amount'] ?? 0), 2) ?></strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($booking['amenities'])): ?>
        <div class="invoice-note">
            <strong>Room Amenities:</strong>
            <?= htmlspecialchars($booking['amenities']) ?>
        </div>
    <?php endif; ?>

    <div class="invoice-footer">
        <p>Thank you for staying with GrandHotel. We hope to welcome you back soon!</p>
        <p class="text-muted" style="font-size:.8rem;margin-top:6px">
            This is a computer-generated invoice and does not require a signature.
        </p>
    </div>
</div>

<?php if ($isStaff && !$isPaid): ?>
    <div class="modal-overlay" id="payModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Record Payment</h3>
                <button onclick="closePayModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>
                    Confirm payment of
                    <strong>₹<?= number_format((float)($bill['total_amount'] ?? 0), 2) ?></strong>
                    for booking #<?= (int)($booking['booking_id'] ?? 0) ?>.
                </p>

                <form method="POST" action="<?= base_url('billing/pay/' . (int)($bill['bill_id'] ?? 0)) ?>">
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Confirm Payment</button>
                        <button type="button" onclick="closePayModal()" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="extrasModal">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Add Extra Charges</h3>
                <button onclick="closeExtrasModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= base_url('billing/extras/' . (int)($bill['bill_id'] ?? 0)) ?>">
                    <div class="form-group">
                        <label for="extra_charges">Extra Charges (₹)</label>
                        <input
                            type="number"
                            id="extra_charges"
                            name="extra_charges"
                            min="0"
                            step="0.01"
                            value="<?= number_format((float)($bill['extra_charges'] ?? 0), 2, '.', '') ?>"
                            placeholder="0.00"
                            required
                        >
                        <small class="form-hint">
                            Room service, laundry, minibar, etc.
                            Total will be recalculated including 18% GST.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="e.g. Room service ×2, Laundry"
                        ><?= htmlspecialchars($bill['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Charges</button>
                        <button type="button" onclick="closeExtrasModal()" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPayModal() {
            document.getElementById('payModal').classList.add('open');
        }

        function closePayModal() {
            document.getElementById('payModal').classList.remove('open');
        }

        function openExtrasModal() {
            document.getElementById('extrasModal').classList.add('open');
        }

        function closeExtrasModal() {
            document.getElementById('extrasModal').classList.remove('open');
        }

        document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    overlay.classList.remove('open');
                }
            });
        });
    </script>
<?php endif; ?>