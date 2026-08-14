# mariadb.Containerfile - Image MariaDB 10 custom
# Tugas 2 Cloud Computing - M. Rizqullah (qullah)
# Soal 4: auto-import SQL saat container pertama kali init
FROM mariadb:10

# Soal 4: SQL di sini akan dijalankan otomatis oleh entrypoint MariaDB
# saat database dibuat pertama kali (docker-entrypoint-initdb.d)
COPY db_kos.sql /docker-entrypoint-initdb.d/

# Default charset utf8mb4 (dukungan penuh emoji/simbol di data)
ENV MARIADB_CHARACTER_SET=utf8mb4 \
    MARIADB_COLLATE=utf8mb4_unicode_ci
