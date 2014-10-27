CREATE TABLE users (
    id serial,
    username VARCHAR(255) NOT NULL,
    fname VARCHAR(255) NOT NULL,
    lname VARCHAR(255) NOT NULL
);

CREATE TABLE results (
    type VARCHAR(255) NOT NULL,
    num_rows INT UNSIGNED,
    page_size INT UNSIGNED,
    request_start INT UNSIGNED,
    response_end INT UNSIGNED,
    render_time INT UNSIGNED
);
