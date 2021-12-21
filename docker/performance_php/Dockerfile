FROM lega0208/performance_base:latest
COPY docker/performance_php/php.ini /usr/local/etc/php/php.ini
COPY docker/performance_php/www.conf /usr/local/etc/php-fpm.d
COPY includes/ /var/www/html/includes/
COPY css/ /var/www/html/css/
COPY assets/ /var/www/html/assets/
COPY js/ /var/www/html/js/
COPY php/ /var/www/html/php/
COPY docker/performance_php/secrets/php/config-aa.php /var/www/html/php/config-aa.php
COPY docker/performance_php/secrets/php/config-at.php /var/www/html/php/config-at.php
COPY docker/performance_php/secrets/php/service-account-credentials.json /var/www/html/php/service-account-credentials.json
COPY docker/performance_php/secrets/keys /var/www/html/keys
#COPY docker/performance_php/mongo_connect.php /var/www/html/php/mongo_connect.php
#COPY docker/performance_php/helloworld.php /var/www/html/php/helloworld.php
COPY index.html /var/www/html/index.html
COPY demo.php /var/www/html/demo.php
COPY vendor/ /var/www/html/vendor/
COPY overview.php /var/www/html/overview.php
COPY upd_overview.php /var/www/html/upd_overview.php
COPY upd_overview_orig.php /var/www/html/upd_overview_orig.php
COPY overview_summary.php /var/www/html/overview_summary.php
COPY overview_uxtests.php /var/www/html/overview_uxtests.php
COPY overview_calldrivers.php /var/www/html/overview_calldrivers.php
COPY overview_pagefeedback.php /var/www/html/overview_pagefeedback.php
COPY overview_searchanalytics.php /var/www/html/overview_searchanalytics.php
COPY overview_webtraffic.php /var/www/html/overview_webtraffic.php
COPY test_en.php /var/www/html/test_en.php
COPY test_fr.php /var/www/html/test_fr.php
COPY pages_home.php /var/www/html/pages_home.php
COPY pages_summary.php /var/www/html/pages_summary.php
COPY pages_pagefeedback.php /var/www/html/pages_pagefeedback.php
COPY projects_home.php /var/www/html/projects_home.php
COPY pages_webtraffic.php /var/www/html/pages_webtraffic.php
COPY pages_searchanalytics.php /var/www/html/pages_searchanalytics.php
COPY projects_summary.php /var/www/html/projects_summary.php
COPY projects_pagefeedback.php /var/www/html/projects_pagefeedback.php
COPY projects_details.php /var/www/html/projects_details.php
COPY tasks_home.php /var/www/html/tasks_home.php
COPY tasks_pagefeedback.php /var/www/html/tasks_pagefeedback.php

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /var/www/html/composer.phar
RUN alias composer='/var/www/html/composer.phar'
RUN composer --version
RUN php -r 'opcache_reset();'
