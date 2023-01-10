INSERT INTO user_role (id, name, hierarchy)
VALUES (1, 'admin', 1);
INSERT INTO user_role (id, name, hierarchy)
VALUES (2, 'managing_advisor', 2);
INSERT INTO user_role (id, name, hierarchy)
VALUES (3, 'advisor', 3);
INSERT INTO user_role (id, name, hierarchy)
VALUES (4, 'newcomer', 4);

INSERT INTO client_status (id, name, deleted_at)
VALUES (1, 'Action pending', null);
INSERT INTO client_status (id, name, deleted_at)
VALUES (2, 'In progress', null);
INSERT INTO client_status (id, name, deleted_at)
VALUES (3, 'In care', null);
INSERT INTO client_status (id, name, deleted_at)
VALUES (4, 'Cannot help', null);

INSERT INTO user (id, first_name, surname, user_role_id, status, email, password_hash, updated_at, created_at,
                  deleted_at)
VALUES (1, 'Admin', null, 1, 'active', 'admin@admin.ch',
        '$2y$10$bHOxtOEs/vBsVnzDLqP3oexZp2yi9aO.DvIloFo0/UZAksMn.VBKm', null, '2023-01-01 00:00:01',
        null);
-- password: 12345678
