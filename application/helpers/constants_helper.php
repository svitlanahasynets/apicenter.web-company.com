<?php

// Login time limit in minutes
define('LOGIN_TIME_LIMIT', 720);

// Set template
define('TEMPLATE', 'default3');

// Set max file size for uploads in kb
define('MAX_UPLOAD_FILE_SIZE', 30000);

// Set data directory for saving files
//define('DATA_DIRECTORY', realpath(FCPATH.'../projects_data/'));
define('DATA_DIRECTORY', realpath(FCPATH.'projects_data/'));
define('SOURCE_DIRECTORY', realpath(FCPATH.'data/template/default3/src/'));

// Set data url for files
//define('DATA_URL', dirname(get_instance()->config->base_url()).'/projects_data');
define('DATA_URL', get_instance()->config->base_url().'projects_data');
define('DEFAULT3_THEME_URL', get_instance()->config->base_url().'data/template/default3');

define('DATE_FORMAT', 'd-m-Y');

// Set price formatting data
define('CURRENCY_SYMBOL', '€');
define('DISPLAY_CURRENCY_SYMBOL', false);
define('PRICE_THOUSAND_SEPARATOR', '.');
define('PRICE_DECIMAL_SEPARATOR', ',');

// Email data
define('SMTP_HOST', 'apicenter.web-company.nl');
define('SMTP_PORT', 25);
define('SMTP_USER', 'mailer@apicenter.web-company.nl');
define('SMTP_PASS', 'M2pK0VAr');
define('SMTP_CRYPTO', '');
define('FROM_EMAIL', 'mailer@apicenter.web-company.nl');
define('FROM_NAME', 'API Center');

// Data table settings
define('DISPLAY_TABLE_ROWS', 10);