<?php
  /**
   * Configuration file used by ActiveCollab installer
   */

  defined('APPLICATION_UNIQUE_KEY') or define('APPLICATION_UNIQUE_KEY', '9784e43fc2b39666b6b03ea7458c252369c85914');
  defined('LICENSE_KEY') or define('LICENSE_KEY', 'JOeV7h2HKl0qS5PVhL6g7pem8hTwiv0S0cC6ksCU/196570');
  defined('APPLICATION_MODE') or define('APPLICATION_MODE', 'production'); // set to 'development' if you need to debug installer

  defined('CONFIG_PATH') or define('CONFIG_PATH', __DIR__);
  defined('ROOT') or define('ROOT', dirname(CONFIG_PATH) . '/activecollab');
  defined('ROOT_URL') or define('ROOT_URL', 'http://activecollab.dev/public');
  defined('FORCE_ROOT_URL') or define('FORCE_ROOT_URL', false);

  require_once CONFIG_PATH . '/version.php';
  require_once CONFIG_PATH . '/defaults.php';
