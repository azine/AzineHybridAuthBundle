AzineHybridAuthBundle
==================

Symfony2 bundle to integrate the HybridAuth library to get access the API of various social networks. 

See https://github.com/hybridauth/hybridauth for more details on the integrated library.

## Available APIs
A lot of providers are available. See the following two links for a complete list:

https://github.com/hybridauth/hybridauth/tree/master/hybridauth/Hybrid/Providers
https://github.com/hybridauth/hybridauth/tree/master/additional-providers


## Installation
To install AzineHybridAuthBundle with Composer just add the following to your `composer.json` file:

```
// composer.json
{
    // ...
    require: {
        // ...
        "azine/hybridauth-bundle": "dev-master"
    }
}
```
Then, you can install the new dependencies by running Composer’s update command from 
the directory where your `composer.json` file is located:

```
php composer.phar update
```
Now, Composer will automatically download all required files, and install them for you. 
All that is left to do is to update your AppKernel.php file, and register the new bundle:

```
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Azine\HybridAuthBundle\AzineHybridAuthBundle(),
    // ...
);
```

Register the routes of the AzineEmailBundle:

```
// in app/config/routing.yml

azine_hybrid_auth_bundle:
    resource: "@AzineHybridAuthBundle/Resources/config/routing.yml"
    prefix:   /hybrid-auth/
    
    
```

## Update from 0.9 or before to dev-master
There is a minor BC-break in the commit 4bcb5c061e3df9188a92b3a047a2a8a6cb9245ee.
The *URL fields of the Entity/UserContact.php class have been renamed to *Url.

```
$webSiteURL => $webSiteUrl
$profileURL => $profileUrl
$photoUrl => $photoURL
```

If you use these fields in php or in JavaScript/JSON, you probably need to update your code accordingly.

## Create apps on your preferred social networks/providers
 Xing => https://dev.xing.com/applications/dashboard
 LinkedIn => https://www.linkedin.com/secure/developer
 

## Configuration
Configure at least one provider. See the links above for a list of available providers.

The providers in the "hybridauth\hybridauth\Hybrid\Providers"-folder are available by default via their ID,
the providers in the "hybridauth\additional-providers"-folder must be configured. => see the configuration 
of the wrapper for the xing provider below.

For the Xing and the LinkedIn provider there are some extra functionalities implemented. 
For all others, there's the default functionality from the HybridAuth available.

```
//app/config/config.yml

// Default configuration for "AzineHybridAuthBundle"
azine_hybrid_auth:
    endpoint_route:       azine_hybrid_auth_endpoint # the route_name where your endpoint controller (e.g. HybridEndPointController) is available
    debug:                false # set to true to log debug-information to the debug_file
    debug_file:           '%kernel.logs_dir%/hybrid_auth_%kernel.environment%.log' # location of the debug-file
    providers:

        # Prototype (at least one provider has to be defined)
        name: 
            enabled:              true
            scope:                ~ # comma-separated list of required 'access rights'
            wrapper: 
              path: ~               # full path to the file containing the wrapper class
              class: ~              # the wrapper class
            keys:
                key:                  ~ # your api-key for this provider
                secret:               ~ # your secret for this provider
```

Here's the example for the xing and linkedin provider:
```
//app/config/config.yml
azine_hybrid_auth:
        xing:
            enabled: true
            scope: ~
            wrapper: 
              path: "%kernel.root_dir%/../vendor/hybridauth/hybridauth/additional-providers/hybridauth-xing/Providers/XING.php"
              class: Hybrid_Providers_XING
            keys:
                key: %xing_api_consumer_key%
                secret: %xing_api_secret%
        linkedin:
            enabled: true
            scope: "r_ basicprofile, r_network"
            keys:
                key: %linkedin_api_key%
                secret: %linkedin_api_secret%
```
Define the keys and secrets for xing and linkedin in you parameters.yml.dist file.

## AzineMergedBusinessNetworksProvider
This service / provider offers some confienience methods to work with business-networks (Xing & LinkedIn).
All methods expect the user to be "connected" to xing/linkedin. If the user has not yet authorized your app
to access the data, a http-redirect will be output directly by setting the html-header-location and calling "die". 

### getLinkedInContacts()
Get all xing contacts of the current user.

Not cached, not paged.

### getLinkedInContacts()
Get all linkedIn contacts of the current user.

Not cached, not paged.

### getContactProfiles($pageSize = 50, $offset = 0)
Get all xing and linkedIn contacts of the current user. Cached and paged.

The function getContactProfiles($pageSize = 50, $offset = 0) get's one page of contacts from the business networks.

The first call will take a fair bit longer than the following ones, because on the first call, ALL contacts from
both networks are fetched and stored in one big array, sorted by last name. This collection is then stored in the 
user session. 


## Build-Status ec.

[![Build Status](https://travis-ci.org/azine/hybridauth-bundle.png)](https://travis-ci.org/azine/hybridauth-bundle)
[![Total Downloads](https://poser.pugx.org/azine/hybridauth-bundle/downloads.png)](https://packagist.org/packages/azine/hybridauth-bundle)
[![Latest Stable Version](https://poser.pugx.org/azine/hybridauth-bundle/v/stable.png)](https://packagist.org/packages/azine/hybridauth-bundle)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/azine/hybridauth-bundle/badges/quality-score.png?s=6190311a47fa9ab8cfb45bfce5c5dcc49fc75256)](https://scrutinizer-ci.com/g/azine/hybridauth-bundle/)
[![Code Coverage](https://scrutinizer-ci.com/g/azine/hybridauth-bundle/badges/coverage.png?s=57b026ec89fdc0767c1255c4a23b9e87a337a205)](https://scrutinizer-ci.com/g/azine/hybridauth-bundle/)


