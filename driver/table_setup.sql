CREATE TABLE users (
    id serial,
    username TEXT NOT NULL,
    fname TEXT NOT NULL,
    lname TEXT NOT NULL
);

CREATE TABLE results (
    id serial,
    type VARCHAR(255) NOT NULL,
    num_rows INT UNSIGNED,
    page_size INT UNSIGNED,
    request_start INT UNSIGNED,
    response_end INT UNSIGNED,
    render_time INT UNSIGNED,
    memory INT UNSIGNED
);
