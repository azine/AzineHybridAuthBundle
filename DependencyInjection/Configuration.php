<?php

namespace Azine\HybridAuthBundle\DependencyInjection;

use Azine\HybridAuthBundle\AzineHybridAuthBundle;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(AzineHybridAuthExtension::PREFIX);

        $rootNode->children()
        	->scalarNode(AzineHybridAuthExtension::ENDPOINT_ROUTE)->defaultValue("azine_hybrid_auth_endpoint")->end()
        	->scalarNode(AzineHybridAuthExtension::DEBUG)->defaultFalse()->end()
        	->scalarNode(AzineHybridAuthExtension::DEBUG_FILE)->defaultValue("%kernel.logs_dir%/hybrid_auth_%kernel.environment%.log")->end()
        	->arrayNode(AzineHybridAuthExtension::PROVIDERS)
        		->useAttributeAsKey(AzineHybridAuthExtension::PROVIDER_NAME)
                ->requiresAtLeastOneElement()
            	//->isRequired()
        		->prototype('array')
        			->children()
				        ->scalarNode(AzineHybridAuthExtension::ENABLED)->defaultTrue()->end()
				        ->scalarNode(AzineHybridAuthExtension::SCOPE)->end()
				        ->arrayNode(AzineHybridAuthExtension::WRAPPER)
				        	->children()
					        	->scalarNode(AzineHybridAuthExtension::WRAPPER_PATH)->end()
					        	->scalarNode(AzineHybridAuthExtension::WRAPPER_CLASS)->end()
					        ->end() // array
				        ->end() // wrapper
				        ->arrayNode(AzineHybridAuthExtension::KEYS)
				        	->children()
					        	->scalarNode(AzineHybridAuthExtension::KEY)->cannotBeEmpty()->end()
					        	->scalarNode(AzineHybridAuthExtension::SECRET)->cannotBeEmpty()->end()
					        ->end() // array
				        ->end() // keys
					->end() //children
				->end() // prototype
			->end() // array
            ->scalarNode(AzineHybridAuthExtension::FILTER)          ->defaultValue("azine_hybrid_auth_contact_filter_default")->end()
			->scalarNode(AzineHybridAuthExtension::MERGER)          ->defaultValue("azine_hybrid_auth_contact_merger_default")->end()
            ->scalarNode(AzineHybridAuthExtension::SORTER)          ->defaultValue("azine_hybrid_auth_contact_sorter_default")->end()
            ->scalarNode(AzineHybridAuthExtension::GENDER_GUESSER)  ->defaultValue("azine_hybrid_auth_gender_guesser_default")->end()
			;
			
        return $treeBuilder;
    }
}
