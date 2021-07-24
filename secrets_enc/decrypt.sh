#!/bin/sh
mkdir ../keys/
mkdir ../php/

gpg --quiet --batch --yes --decrypt --passphrase-file "passphrase.txt" \
--output ../keys/secret.pem ./keys/secret.pem.gpg

gpg --quiet --batch --yes --decrypt --passphrase-file "passphrase.txt" \
--output ../php/config-aa.php ./php/config-aa.php.gpg

gpg --quiet --batch --yes --decrypt --passphrase-file "passphrase.txt" \
--output ../php/config-at.php ./php/config-at.php.gpg

gpg --quiet --batch --yes --decrypt --passphrase-file "passphrase.txt" \
--output ../php/service-account-credentials.json ./php/service-account-credentials.json.gpg
