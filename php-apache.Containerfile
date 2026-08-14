# php-apache.Containerfile - Image PHP 8 + Apache custom
# Tugas 2 Cloud Computing - M. Rizqullah (qullah)
# Soal 3: ext mysqli + editor nano
FROM php:8-apache

# Soal 3a: install ekstensi mysqli
RUN docker-php-ext-install mysqli

# Soal 3b: install editor nano (+ dependencies, bersihkan apt cache)
RUN apt-get update \
    && apt-get install -y --no-install-recommends nano \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan mod_rewrite Apache (untuk routing bersih jika dibutuhkan)
RUN a2enmod rewrite
