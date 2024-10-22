ALTER TABLE `tbl_customers`
ADD COLUMN `user_type` ENUM ('admin', 'customer', 'staff') NOT NULL DEFAULT 'customer';

ALTER TABLE `tbl_customers` ADD `token` VARCHAR(255) NULL DEFAULT NULL AFTER `user_type`;

ALTER TABLE `tbl_customers` ADD `full_name` VARCHAR(255) NULL DEFAULT NULL AFTER `last_name`;

INSERT INTO
    tbl_countries (country_name, country_code, continent, currency)
VALUES
    ('India', 'IN', 'Asia', 'INR');

INSERT INTO
    tbl_states (state_name, state_code, country_id)
VALUES
    ('Andhra Pradesh', 'AP', 1),
    ('Arunachal Pradesh', 'AR', 1),
    ('Assam', 'AS', 1),
    ('Bihar', 'BR', 1),
    ('Chhattisgarh', 'CG', 1),
    ('Goa', 'GA', 1),
    ('Gujarat', 'GJ', 1),
    ('Haryana', 'HR', 1),
    ('Himachal Pradesh', 'HP', 1),
    ('Jharkhand', 'JH', 1),
    ('Karnataka', 'KA', 1),
    ('Kerala', 'KL', 1),
    ('Madhya Pradesh', 'MP', 1),
    ('Maharashtra', 'MH', 1),
    ('Manipur', 'MN', 1),
    ('Meghalaya', 'ML', 1),
    ('Mizoram', 'MZ', 1),
    ('Nagaland', 'NL', 1),
    ('Odisha', 'OR', 1),
    ('Punjab', 'PB', 1),
    ('Rajasthan', 'RJ', 1),
    ('Sikkim', 'SK', 1),
    ('Tamil Nadu', 'TN', 1),
    ('Telangana', 'TG', 1),
    ('Tripura', 'TR', 1),
    ('Uttar Pradesh', 'UP', 1),
    ('Uttarakhand', 'UK', 1),
    ('West Bengal', 'WB', 1),
    -- Union Territories
    ('Andaman and Nicobar Islands', 'AN', 1),
    ('Chandigarh', 'CH', 1),
    (
        'Dadra and Nagar Haveli and Daman and Diu',
        'DN',
        1
    ),
    ('Lakshadweep', 'LD', 1),
    ('Delhi', 'DL', 1),
    ('Puducherry', 'PY', 1),
    ('Ladakh', 'LA', 1),
    ('Jammu and Kashmir', 'JK', 1);

INSERT INTO
    tbl_cities (city_name, state_id, postal_code)
VALUES
    (
        'Visakhapatnam',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Andhra Pradesh'
        ),
        '530001'
    ),
    (
        'Vijayawada',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Andhra Pradesh'
        ),
        '520001'
    ),
    -- Maharashtra (state_id = ?)
    (
        'Mumbai',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Maharashtra'
        ),
        '400001'
    ),
    (
        'Pune',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Maharashtra'
        ),
        '411001'
    ),
    -- Karnataka (state_id = ?)
    (
        'Bengaluru',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Karnataka'
        ),
        '560001'
    ),
    (
        'Mysuru',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Karnataka'
        ),
        '570001'
    ),
    -- Tamil Nadu (state_id = ?)
    (
        'Chennai',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Tamil Nadu'
        ),
        '600001'
    ),
    (
        'Coimbatore',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Tamil Nadu'
        ),
        '641001'
    ),
    -- Uttar Pradesh (state_id = ?)
    (
        'Lucknow',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Uttar Pradesh'
        ),
        '226001'
    ),
    (
        'Kanpur',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Uttar Pradesh'
        ),
        '208001'
    ),
    -- West Bengal (state_id = ?)
    (
        'Kolkata',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'West Bengal'
        ),
        '700001'
    ),
    (
        'Asansol',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'West Bengal'
        ),
        '713301'
    ),
    -- Delhi (Union Territory, state_id = ?)
    (
        'New Delhi',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Delhi'
        ),
        '110001'
    ),
    -- Punjab (state_id = ?)
    (
        'Amritsar',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Punjab'
        ),
        '143001'
    ),
    (
        'Ludhiana',
        (
            SELECT
                state_id
            FROM
                tbl_states
            WHERE
                state_name = 'Punjab'
        ),
        '141001'
    );

INSERT INTO
    `tbl_customers` (
        `customer_id`,
        `first_name`,
        `last_name`,
        `email`,
        `phone`,
        `date_of_birth`,
        `street_address`,
        `city`,
        `state_province`,
        `postal_code`,
        `country`,
        `username`,
        `password_hash`,
        `company_name`,
        `tax_identification_number`,
        `business_type`,
        `preferred_contact_method`,
        `referral_source`,
        `newsletter_subscription`,
        `security_question`,
        `security_answer`,
        `agreed_to_terms`,
        `registration_date`,
        `user_type`,
        `token`
    )
VALUES
    (
        1, -- customer_id (Assuming it's auto-incremented, use NULL or omit this column if so)
        'Admin', -- first_name
        'User', -- last_name
        'admin@example.com', -- email
        '123-456-7890', -- phone
        '1980-01-01', -- date_of_birth
        '123 Admin St', -- street_address
        'Admin City', -- city
        'Admin State', -- state_province
        '12345', -- postal_code
        'Admin Country', -- country
        'adminUser', -- username
        '$2y$10$i8dcVDv0dNTIx08zRnWRr.v1XzAF914v6iyWCKrtXSQar6oCzXNXK', -- password_hash (ensure to use a secure hash) hashedPassword123
        'Admin Company', -- company_name (optional)
        '123-45-6789', -- tax_identification_number (optional)
        'admin', -- business_type (optional)
        'email', -- preferred_contact_method
        'website', -- referral_source (optional)
        1, -- newsletter_subscription (1 for yes, 0 for no)
        'What is your favorite color?', -- security_question
        'Blue', -- security_answer
        1, -- agreed_to_terms (1 for yes, 0 for no)
        NOW (), -- registration_date
        'admin', -- user_type (indicating this is an admin user)
        NULL -- token (ensure it is unique)
    );