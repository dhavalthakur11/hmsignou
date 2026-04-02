-- Users
INSERT INTO users (user_id, name, email, password_hash, role, phone, is_active)
VALUES (users_seq.NEXTVAL, 'Admin User', 'admin@hotel.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin', '9000000001', 1);

INSERT INTO users (user_id, name, email, password_hash, role, phone, is_active)
VALUES (users_seq.NEXTVAL, 'Sara Receptionist', 'sara@hotel.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'receptionist', '9000000002', 1);

INSERT INTO users (user_id, name, email, password_hash, role, phone, is_active)
VALUES (users_seq.NEXTVAL, 'John Guest', 'john@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'customer', '9123456789', 1);

INSERT INTO users (user_id, name, email, password_hash, role, phone, is_active)
VALUES (users_seq.NEXTVAL, 'Priya Sharma', 'priya@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'customer', '9876543210', 1);

-- Rooms
INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'101','Standard',1,2,1999,'available',
        'Cosy standard room with garden view.','WiFi,AC,TV,Hot Water');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'102','Standard',1,2,1999,'available',
        'Bright standard room overlooking courtyard.','WiFi,AC,TV,Hot Water');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'103','Standard',1,3,2299,'booked',
        'Standard triple occupancy room.','WiFi,AC,TV,Hot Water,Extra Bed');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'201','Deluxe',2,2,3499,'available',
        'Spacious deluxe room with city view.','WiFi,AC,TV,Mini Bar,Hot Water');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'202','Deluxe',2,3,3999,'available',
        'Deluxe room with extra bed on request.','WiFi,AC,TV,Mini Bar,Jacuzzi');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'203','Deluxe',2,2,3699,'booked',
        'Corner deluxe room with dual aspect windows.','WiFi,AC,TV,Mini Bar,Hot Water');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'301','Suite',3,4,7499,'available',
        'Luxury suite with panoramic city view.','WiFi,AC,TV,Mini Bar,Jacuzzi,Lounge');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'302','Suite',3,2,6999,'maintenance',
        'Premium suite under maintenance.','WiFi,AC,TV,Mini Bar,Jacuzzi');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'401','Presidential',4,6,14999,'available',
        'Ultimate luxury with private terrace.','WiFi,AC,TV,Bar,Jacuzzi,Butler,Terrace,Dining Room');

INSERT INTO rooms (room_id, room_number, room_type, floor, capacity,
                   price_per_night, status, description, amenities)
VALUES (rooms_seq.NEXTVAL,'402','Presidential',4,4,12999,'available',
        'Presidential suite with butler service.','WiFi,AC,TV,Bar,Jacuzzi,Butler,Terrace');

-- Employee (Sara)
INSERT INTO employees (employee_id, user_id, department, designation,
                       salary, hire_date, is_active)
SELECT employees_seq.NEXTVAL, user_id,
       'Front Desk', 'Senior Receptionist', 45000,
       TO_DATE('2023-01-15','YYYY-MM-DD'), 1
  FROM users WHERE email = 'sara@hotel.com';

-- Booking 1: John, Room 103, future confirmed stay
INSERT INTO bookings (booking_id, user_id, room_id,
                      check_in, check_out, guests, status, special_req, booked_by)
SELECT bookings_seq.NEXTVAL,
       (SELECT user_id FROM users WHERE email = 'john@example.com'),
       (SELECT room_id FROM rooms WHERE room_number = '103'),
       TO_DATE('2026-04-10','YYYY-MM-DD'),
       TO_DATE('2026-04-13','YYYY-MM-DD'),
       2, 'confirmed', 'Late check-in after 10pm.',
       (SELECT user_id FROM users WHERE email = 'john@example.com')
  FROM DUAL;

-- Booking 2: Priya, Room 203, currently checked in
INSERT INTO bookings (booking_id, user_id, room_id,
                      check_in, check_out, guests, status, special_req, booked_by)
SELECT bookings_seq.NEXTVAL,
       (SELECT user_id FROM users WHERE email = 'priya@example.com'),
       (SELECT room_id FROM rooms WHERE room_number = '203'),
       TO_DATE('2026-04-02','YYYY-MM-DD'),
       TO_DATE('2026-04-05','YYYY-MM-DD'),
       2, 'checked_in', 'Non-smoking room please.',
       (SELECT user_id FROM users WHERE email = 'priya@example.com')
  FROM DUAL;

-- Booking 3: John, Room 101, past completed stay
INSERT INTO bookings (booking_id, user_id, room_id,
                      check_in, check_out, guests, status, booked_by)
SELECT bookings_seq.NEXTVAL,
       (SELECT user_id FROM users WHERE email = 'john@example.com'),
       (SELECT room_id FROM rooms WHERE room_number = '101'),
       TO_DATE('2026-03-15','YYYY-MM-DD'),
       TO_DATE('2026-03-17','YYYY-MM-DD'),
       1, 'checked_out',
       (SELECT user_id FROM users WHERE email = 'john@example.com')
  FROM DUAL;

-- Bill 1: John/103 confirmed (3 nights × 2299 = 6897, +18% GST)
INSERT INTO billing (bill_id, booking_id, room_charges, extra_charges,
                     tax_amount, total_amount, payment_status)
SELECT billing_seq.NEXTVAL, b.booking_id,
       6897, 0, 1241.46, 8138.46, 'pending'
  FROM bookings b
  JOIN users u ON b.user_id = u.user_id
  JOIN rooms  r ON b.room_id = r.room_id
 WHERE u.email = 'john@example.com'
   AND r.room_number = '103'
   AND b.status = 'confirmed';

-- Bill 2: Priya/203 checked-in (3 nights × 3699 = 11097, extras 500, +18% GST)
INSERT INTO billing (bill_id, booking_id, room_charges, extra_charges,
                     tax_amount, total_amount, payment_status, notes)
SELECT billing_seq.NEXTVAL, b.booking_id,
       11097, 500, 2087.46, 13684.46, 'pending', 'Room service x2, laundry'
  FROM bookings b
  JOIN users u ON b.user_id = u.user_id
  JOIN rooms  r ON b.room_id = r.room_id
 WHERE u.email = 'priya@example.com'
   AND r.room_number = '203'
   AND b.status = 'checked_in';

-- Bill 3: John/101 past stay — PAID (shows revenue on dashboard)
INSERT INTO billing (bill_id, booking_id, room_charges, extra_charges,
                     tax_amount, total_amount,
                     payment_status, payment_method, paid_at)
SELECT billing_seq.NEXTVAL, b.booking_id,
       3998, 0, 719.64, 4717.64,
       'paid', 'card', TO_DATE('2026-03-17','YYYY-MM-DD')
  FROM bookings b
  JOIN users u ON b.user_id = u.user_id
  JOIN rooms  r ON b.room_id = r.room_id
 WHERE u.email = 'john@example.com'
   AND r.room_number = '101'
   AND b.status = 'checked_out';

-- Notifications
INSERT INTO notifications (notif_id, user_id, title, message, is_read)
SELECT notifications_seq.NEXTVAL, user_id,
       'Booking Confirmed',
       'Your booking has been confirmed. Check-in: 10 Apr 2026. Room 103.', 0
  FROM users WHERE email = 'john@example.com';

INSERT INTO notifications (notif_id, user_id, title, message, is_read)
SELECT notifications_seq.NEXTVAL, user_id,
       'Welcome to GrandHotel',
       'Thank you for choosing GrandHotel. Enjoy your stay!', 0
  FROM users WHERE email = 'priya@example.com';

INSERT INTO notifications (notif_id, user_id, title, message, is_read)
SELECT notifications_seq.NEXTVAL, user_id,
       'Payment Received',
       'Payment of Rs.4717.64 received for your stay. Thank you!', 1
  FROM users WHERE email = 'john@example.com';

-- Audit logs
INSERT INTO audit_logs (log_id, action, user_id, detail, ip_address)
SELECT audit_logs_seq.NEXTVAL, 'LOGIN_SUCCESS', user_id,
       'User logged in: admin@hotel.com', '127.0.0.1'
  FROM users WHERE email = 'admin@hotel.com';

INSERT INTO audit_logs (log_id, action, user_id, detail, ip_address)
SELECT audit_logs_seq.NEXTVAL, 'ROOM_CREATE', user_id,
       '10 rooms seeded.', '127.0.0.1'
  FROM users WHERE email = 'admin@hotel.com';

INSERT INTO audit_logs (log_id, action, user_id, detail, ip_address)
SELECT audit_logs_seq.NEXTVAL, 'BOOKING_CREATE', user_id,
       'Sample bookings created.', '127.0.0.1'
  FROM users WHERE email = 'admin@hotel.com';

INSERT INTO audit_logs (log_id, action, user_id, detail, ip_address)
SELECT audit_logs_seq.NEXTVAL, 'LOGIN_SUCCESS', user_id,
       'User logged in: john@example.com', '127.0.0.1'
  FROM users WHERE email = 'john@example.com';

-- Feedback (John's past stay)
INSERT INTO feedback (feedback_id, user_id, booking_id,
                      rating, feedback_comment)
SELECT feedback_seq.NEXTVAL, u.user_id, b.booking_id,
       5, 'Excellent stay! Room was spotless and staff were very helpful.'
  FROM users    u
  JOIN bookings b ON b.user_id  = u.user_id
  JOIN rooms    r ON b.room_id  = r.room_id
 WHERE u.email       = 'john@example.com'
   AND r.room_number = '101'
   AND b.status      = 'checked_out';

COMMIT;

--  Verify
SELECT 'users'         AS tbl, COUNT(*) AS cnt FROM users         UNION ALL
SELECT 'rooms'         AS tbl, COUNT(*) AS cnt FROM rooms         UNION ALL
SELECT 'bookings'      AS tbl, COUNT(*) AS cnt FROM bookings      UNION ALL
SELECT 'billing'       AS tbl, COUNT(*) AS cnt FROM billing       UNION ALL
SELECT 'employees'     AS tbl, COUNT(*) AS cnt FROM employees     UNION ALL
SELECT 'feedback'      AS tbl, COUNT(*) AS cnt FROM feedback      UNION ALL
SELECT 'notifications' AS tbl, COUNT(*) AS cnt FROM notifications UNION ALL
SELECT 'audit_logs'    AS tbl, COUNT(*) AS cnt FROM audit_logs
ORDER BY tbl;