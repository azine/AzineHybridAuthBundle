<?php

namespace Azine\HybridAuthBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AzineHybridAuthExtension extends Extension {

	const PREFIX = "azine_hybrid_auth";
	const ENDPOINT_ROUTE = "endpoint_route";
	const BASE_URL = "base_url";
	const DEBUG = "debug";
	const DEBUG_FILE = "debug_file";
	const PROVIDERS = "providers";
	const PROVIDER_NAME = "name";
	const ENABLED = "enabled";
	const SCOPE = "scope";
	const KEYS = "keys";
	const KEY = "key";
	const SECRET = "secret";
	const WRAPPER = "wrapper";
	const WRAPPER_PATH = "path";
	const WRAPPER_CLASS = "class";


    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::PREFIX."_".self::ENDPOINT_ROUTE,	$config[self::ENDPOINT_ROUTE]);
        $container->setParameter(self::PREFIX."_".self::DEBUG, 			$config[self::DEBUG]);
        $container->setParameter(self::PREFIX."_".self::DEBUG_FILE,		$config[self::DEBUG_FILE]);
        $container->setParameter(self::PREFIX."_".self::PROVIDERS,			$config[self::PROVIDERS]);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
