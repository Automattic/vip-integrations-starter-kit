<?php
/**
 * VIP recommends loading all plugins for your site in code. Loading plugins
 * through code results in more control and greater consistency across
 * development environments. Using this file to do so helps load and activate
 * plugins as early as possible in the WordPress load order.
 *
 * @see https://docs.wpvip.com/how-tos/activate-plugins-through-code/
 * @see https://docs.wpvip.com/technical-references/vip-codebase/client-mu-plugins-directory/
 */

use Automattic\VIP\Integrations\IntegrationsSingleton;
use MyNamespace\TestDemo\Integration;

require_once WP_PLUGIN_DIR . '/my-integration/class-integration.php';

IntegrationsSingleton::instance()->register( new Integration( 'demo-integration' ) );
IntegrationsSingleton::instance()->activate( 'demo-integration', [] );
