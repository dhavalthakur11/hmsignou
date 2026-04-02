<?php
/**
 * Create Booking Form
 * Includes live availability check via fetch().
 */
$old       = $old       ?? [];
$isStaff   = user_role() !== 'customer';
$preRoomId = $old['room_id'] ?? ($room['room_id'] ?? '');
?>

<div class="booking-create-grid">

    <!-- ── Booking Form ── -->
    <div class="card">
        <div class="card-header">
            <h3>New Booking</h3>
            <a href="<?= base_url($isStaff ? 'booking/index' : 'booking/mybookings') ?>"
               class="btn btn-outline btn-sm">← Back</a>
        </div>
        <div class="card-body">

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul class="error-list">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('booking/create') ?>"
                  id="bookingForm" novalidate>

                <!-- Staff: select guest; Customer: hidden self -->
                <?php if ($isStaff && !empty($customers)): ?>
                <div class="form-group">
                    <label for="user_id">Guest <span class="required">*</span></label>
                    <select id="user_id" name="user_id" required>
                        <option value="">Select guest…</option>
                        <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['user_id'] ?>"
                            <?= ($old['user_id'] ?? '') == $c['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                            (<?= htmlspecialchars($c['email']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Room selection -->
                <div class="form-group">
                    <label for="room_id">Room <span class="required">*</span></label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Select room…</option>
                        <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['room_id'] ?>"
                                data-price="<?= $r['price_per_night'] ?>"
                                data-type="<?= htmlspecialchars($r['room_type']) ?>"
                                data-capacity="<?= $r['capacity'] ?>"
                            <?= (string)$preRoomId === (string)$r['room_id'] ? 'selected' : '' ?>>
                            Room <?= htmlspecialchars($r['room_number']) ?>
                            — <?= htmlspecialchars($r['room_type']) ?>
                            (₹<?= number_format($r['price_per_night'], 0) ?>/night)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date range -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="check_in">Check-in Date <span class="required">*</span></label>
                        <input type="date" id="check_in" name="check_in"
                               min="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($old['check_in'] ?? date('Y-m-d')) ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="check_out">Check-out Date <span class="required">*</span></label>
                        <input type="date" id="check_out" name="check_out"
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               value="<?= htmlspecialchars($old['check_out'] ?? date('Y-m-d', strtotime('+1 day'))) ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="guests">Number of Guests <span class="required">*</span></label>
                    <input type="number" id="guests" name="guests"
                           min="1" max="20"
                           value="<?= (int)($old['guests'] ?? 1) ?>" required>
                </div>

                <div class="form-group">
                    <label for="special_req">Special Requests</label>
                    <textarea id="special_req" name="special_req" rows="2"
                              placeholder="Extra pillows, early check-in, dietary needs…"><?= htmlspecialchars($old['special_req'] ?? '') ?></textarea>
                </div>

                <!-- Availability check result -->
                <div id="availabilityResult" class="availability-box" style="display:none"></div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" id="checkAvailBtn">
                        Check Availability
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        Confirm Booking
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- ── Live price summary ── -->
    <div>
        <div class="card" id="priceSummaryCard">
            <div class="card-header"><h3>Price Summary</h3></div>
            <div class="card-body">
                <div class="price-summary">
                    <div class="price-row">
                        <span>Room type</span>
                        <span id="sumRoomType">—</span>
                    </div>
                    <div class="price-row">
                        <span>Price / night</span>
                        <span id="sumPricePerNight">₹—</span>
                    </div>
                    <div class="price-row">
                        <span>Nights</span>
                        <span id="sumNights">—</span>
                    </div>
                    <div class="price-row">
                        <span>Room charges</span>
                        <span id="sumRoomCharges">₹—</span>
                    </div>
                    <div class="price-row">
                        <span>GST (18%)</span>
                        <span id="sumTax">₹—</span>
                    </div>
                    <hr>
                    <div class="price-row price-total">
                        <span>Total Payable</span>
                        <span id="sumTotal">₹—</span>
                    </div>
                </div>
                <p class="form-hint mt-2">
                    Availability must be confirmed before booking.
                </p>
            </div>
        </div>

        <?php if ($room): ?>
        <!-- Pre-selected room info card -->
        <div class="card mt-4">
            <div class="card-header"><h3>Selected Room</h3></div>
            <div class="card-body">
                <div class="room-info-list">
                    <div class="room-info-row">
                        <span>Room</span>
                        <strong><?= htmlspecialchars($room['room_number']) ?></strong>
                    </div>
                    <div class="room-info-row">
                        <span>Type</span>
                        <strong><?= htmlspecialchars($room['room_type']) ?></strong>
                    </div>
                    <div class="room-info-row">
                        <span>Floor</span>
                        <strong><?= (int)$room['floor'] ?></strong>
                    </div>
                    <div class="room-info-row">
                        <span>Capacity</span>
                        <strong><?= (int)$room['capacity'] ?> guests</strong>
                    </div>
                    <div class="room-info-row">
                        <span>Rate</span>
                        <strong>₹<?= number_format((float)$room['price_per_night'], 0) ?>/night</strong>
                    </div>
                    <?php if ($room['amenities']): ?>
                    <div class="room-info-row" style="align-items:flex-start">
                        <span>Amenities</span>
                        <div class="room-amenities">
                            <?php foreach (explode(',', $room['amenities']) as $a): ?>
                            <span class="amenity-tag"><?= htmlspecialchars(trim($a)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
const checkBtn  = document.getElementById('checkAvailBtn');
const submitBtn = document.getElementById('submitBtn');
const resultBox = document.getElementById('availabilityResult');

// Update summary whenever room or dates change
['room_id','check_in','check_out'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
        submitBtn.disabled = true;
        resultBox.style.display = 'none';
        updateSummary();
    });
});

function updateSummary() {
    const roomSel = document.getElementById('room_id');
    const opt     = roomSel.options[roomSel.selectedIndex];
    if (!opt || !opt.value) return;

    const price  = parseFloat(opt.dataset.price) || 0;
    const ci     = document.getElementById('check_in').value;
    const co     = document.getElementById('check_out').value;

    if (!ci || !co) return;

    const nights = Math.max(1, Math.round(
        (new Date(co) - new Date(ci)) / (1000 * 60 * 60 * 24)
    ));
    const room   = price * nights;
    const tax    = room * 0.18;
    const total  = room + tax;

    document.getElementById('sumRoomType').textContent      = opt.dataset.type || '—';
    document.getElementById('sumPricePerNight').textContent = '₹' + price.toLocaleString('en-IN');
    document.getElementById('sumNights').textContent        = nights + (nights === 1 ? ' night' : ' nights');
    document.getElementById('sumRoomCharges').textContent   = '₹' + room.toLocaleString('en-IN');
    document.getElementById('sumTax').textContent           = '₹' + tax.toFixed(0);
    document.getElementById('sumTotal').textContent         = '₹' + total.toFixed(0);
}

// Check availability via AJAX
checkBtn.addEventListener('click', async () => {
    const roomId  = document.getElementById('room_id').value;
    const checkIn = document.getElementById('check_in').value;
    const checkOut= document.getElementById('check_out').value;

    if (!roomId || !checkIn || !checkOut) {
        showResult('warning', 'Please select a room and both dates first.');
        return;
    }

    checkBtn.disabled   = true;
    checkBtn.textContent = 'Checking…';

    try {
        const formData = new FormData();
        formData.append('room_id',   roomId);
        formData.append('check_in',  checkIn);
        formData.append('check_out', checkOut);

        const res  = await fetch('<?= base_url('booking/checkavailability') ?>', {
            method: 'POST', body: formData
        });
        const data = await res.json();

        if (data.available) {
            showResult('success', `✓ ${data.message} (${data.nights} night(s) — ₹${Number(data.total).toLocaleString('en-IN')} total incl. tax)`);
            submitBtn.disabled = false;
        } else {
            showResult('error', `✗ ${data.message}`);
            submitBtn.disabled = true;
        }
    } catch (err) {
        showResult('error', 'Could not check availability. Please try again.');
    } finally {
        checkBtn.disabled    = false;
        checkBtn.textContent = 'Check Availability';
    }
});

function showResult(type, message) {
    resultBox.className       = `availability-box avail-${type}`;
    resultBox.textContent     = message;
    resultBox.style.display   = 'block';
}

// Init summary on page load
updateSummary();
</script>