BEGIN
    -- Drop tables in reverse FK order
    FOR t IN (
        SELECT table_name FROM user_tables
        WHERE table_name IN (
            'AUDIT_LOGS','NOTIFICATIONS','FEEDBACK',
            'EMPLOYEES','BILLING','BOOKINGS','ROOMS','USERS'
        )
    ) LOOP
        EXECUTE IMMEDIATE
            'DROP TABLE ' || t.table_name || ' CASCADE CONSTRAINTS PURGE';
        DBMS_OUTPUT.PUT_LINE('Dropped table: ' || t.table_name);
    END LOOP;
END;
/

BEGIN
    -- Drop sequences
    FOR s IN (
        SELECT sequence_name FROM user_sequences
        WHERE sequence_name IN (
            'USERS_SEQ','ROOMS_SEQ','BOOKINGS_SEQ','BILLING_SEQ',
            'EMPLOYEES_SEQ','FEEDBACK_SEQ','NOTIFICATIONS_SEQ','AUDIT_LOGS_SEQ'
        )
    ) LOOP
        EXECUTE IMMEDIATE 'DROP SEQUENCE ' || s.sequence_name;
        DBMS_OUTPUT.PUT_LINE('Dropped sequence: ' || s.sequence_name);
    END LOOP;
END;
/

-- CREATE sequences 

CREATE SEQUENCE users_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE rooms_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE bookings_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE billing_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE employees_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE feedback_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE notifications_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE SEQUENCE audit_logs_seq
    START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

-- CREATE tables 

CREATE TABLE users (
    user_id         NUMBER          NOT NULL,
    name            VARCHAR2(100)   NOT NULL,
    email           VARCHAR2(150)   NOT NULL,
    password_hash   VARCHAR2(255)   NOT NULL,
    role            VARCHAR2(20)    DEFAULT 'customer' NOT NULL,
    phone           VARCHAR2(20),
    is_active       NUMBER(1,0)     DEFAULT 1 NOT NULL,
    created_at      DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_users        PRIMARY KEY (user_id),
    CONSTRAINT uq_users_email  UNIQUE      (email),
    CONSTRAINT ck_users_role   CHECK (role IN ('admin','receptionist','customer')),
    CONSTRAINT ck_users_active CHECK (is_active IN (0,1))
);

CREATE TABLE rooms (
    room_id         NUMBER          NOT NULL,
    room_number     VARCHAR2(10)    NOT NULL,
    room_type       VARCHAR2(50)    NOT NULL,
    floor           NUMBER(2,0)     DEFAULT 1  NOT NULL,
    capacity        NUMBER(2,0)     DEFAULT 2  NOT NULL,
    price_per_night NUMBER(10,2)    NOT NULL,
    status          VARCHAR2(20)    DEFAULT 'available' NOT NULL,
    description     VARCHAR2(500),
    amenities       VARCHAR2(300),
    image_url       VARCHAR2(300),
    created_at      DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_rooms          PRIMARY KEY (room_id),
    CONSTRAINT uq_rooms_number   UNIQUE      (room_number),
    CONSTRAINT ck_rooms_status   CHECK (status IN
                                   ('available','booked','maintenance','checkout')),
    CONSTRAINT ck_rooms_price    CHECK (price_per_night > 0),
    CONSTRAINT ck_rooms_capacity CHECK (capacity >= 1),
    CONSTRAINT ck_rooms_floor    CHECK (floor >= 1)
);

CREATE TABLE bookings (
    booking_id      NUMBER          NOT NULL,
    user_id         NUMBER          NOT NULL,
    room_id         NUMBER          NOT NULL,
    check_in        DATE            NOT NULL,
    check_out       DATE            NOT NULL,
    guests          NUMBER(2,0)     DEFAULT 1 NOT NULL,
    status          VARCHAR2(20)    DEFAULT 'confirmed' NOT NULL,
    special_req     VARCHAR2(500),
    booked_by       NUMBER,
    created_at      DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_bookings      PRIMARY KEY (booking_id),
    CONSTRAINT fk_book_user     FOREIGN KEY (user_id)   REFERENCES users(user_id),
    CONSTRAINT fk_book_room     FOREIGN KEY (room_id)   REFERENCES rooms(room_id),
    CONSTRAINT fk_book_bookedby FOREIGN KEY (booked_by) REFERENCES users(user_id),
    CONSTRAINT ck_book_status   CHECK (status IN
                                   ('confirmed','checked_in','checked_out','cancelled')),
    CONSTRAINT ck_book_dates    CHECK (check_out > check_in),
    CONSTRAINT ck_book_guests   CHECK (guests >= 1)
);

CREATE TABLE billing (
    bill_id         NUMBER          NOT NULL,
    booking_id      NUMBER          NOT NULL,
    room_charges    NUMBER(10,2)    DEFAULT 0 NOT NULL,
    extra_charges   NUMBER(10,2)    DEFAULT 0 NOT NULL,
    tax_amount      NUMBER(10,2)    DEFAULT 0 NOT NULL,
    total_amount    NUMBER(10,2)    DEFAULT 0 NOT NULL,
    payment_status  VARCHAR2(20)    DEFAULT 'pending' NOT NULL,
    payment_method  VARCHAR2(30),
    paid_at         DATE,
    notes           VARCHAR2(300),
    created_at      DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_billing       PRIMARY KEY (bill_id),
    CONSTRAINT fk_bill_booking  FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    CONSTRAINT uq_bill_booking  UNIQUE      (booking_id),
    CONSTRAINT ck_bill_pstatus  CHECK (payment_status IN
                                   ('pending','paid','partial','refunded')),
    CONSTRAINT ck_bill_rcharge  CHECK (room_charges  >= 0),
    CONSTRAINT ck_bill_echarge  CHECK (extra_charges >= 0),
    CONSTRAINT ck_bill_tax      CHECK (tax_amount    >= 0),
    CONSTRAINT ck_bill_total    CHECK (total_amount  >= 0)
);

CREATE TABLE employees (
    employee_id     NUMBER          NOT NULL,
    user_id         NUMBER,
    department      VARCHAR2(50),
    designation     VARCHAR2(100),
    salary          NUMBER(10,2),
    hire_date       DATE            DEFAULT SYSDATE,
    is_active       NUMBER(1,0)     DEFAULT 1 NOT NULL,
    CONSTRAINT pk_employees  PRIMARY KEY (employee_id),
    CONSTRAINT fk_emp_user   FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT ck_emp_active CHECK (is_active IN (0,1)),
    CONSTRAINT ck_emp_salary CHECK (salary >= 0)
);

CREATE TABLE feedback (
    feedback_id      NUMBER          NOT NULL,
    user_id          NUMBER,
    booking_id       NUMBER,
    rating           NUMBER(1,0),
    feedback_comment VARCHAR2(1000),
    created_at       DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_feedback   PRIMARY KEY (feedback_id),
    CONSTRAINT fk_fb_user    FOREIGN KEY (user_id)    REFERENCES users(user_id),
    CONSTRAINT fk_fb_booking FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    CONSTRAINT ck_fb_rating  CHECK (rating BETWEEN 1 AND 5)
);

CREATE TABLE notifications (
    notif_id        NUMBER          NOT NULL,
    user_id         NUMBER,
    title           VARCHAR2(150)   NOT NULL,
    message         VARCHAR2(500),
    is_read         NUMBER(1,0)     DEFAULT 0 NOT NULL,
    created_at      DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_notifications PRIMARY KEY (notif_id),
    CONSTRAINT fk_notif_user    FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT ck_notif_read    CHECK (is_read IN (0,1))
);

CREATE TABLE audit_logs (
    log_id          NUMBER          NOT NULL,
    action          VARCHAR2(50)    NOT NULL,
    user_id         NUMBER,
    detail          VARCHAR2(500),
    ip_address      VARCHAR2(45),
    created_at      DATE            DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_audit_logs PRIMARY KEY (log_id)
);

-- Indexes

CREATE INDEX idx_bookings_user    ON bookings      (user_id);
CREATE INDEX idx_bookings_room    ON bookings      (room_id);
CREATE INDEX idx_bookings_status  ON bookings      (status);
CREATE INDEX idx_bookings_checkin ON bookings      (check_in);
CREATE INDEX idx_billing_booking  ON billing       (booking_id);
CREATE INDEX idx_billing_status   ON billing       (payment_status);
CREATE INDEX idx_notif_user       ON notifications (user_id, is_read);
CREATE INDEX idx_logs_created     ON audit_logs    (created_at);
CREATE INDEX idx_logs_action      ON audit_logs    (action);

-- Verify tables exist before seeding 
SELECT table_name FROM user_tables
 WHERE table_name IN (
       'USERS','ROOMS','BOOKINGS','BILLING',
       'EMPLOYEES','FEEDBACK','NOTIFICATIONS','AUDIT_LOGS')
 ORDER BY table_name;


