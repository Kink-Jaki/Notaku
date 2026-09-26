<?php

// This script clears OPcache for the web server
opcache_reset();
echo "OPcache reset from web server context.\n";
echo 'PHP Version: '.PHP_VERSION."\n";
echo 'OPcache enabled: '.(ini_get('opcache.enable') ? 'Yes' : 'No')."\n";
