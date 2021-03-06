<?php

$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    $handler = new HAB\Diglib\API\Error\Handler();
    $handler->setLogger($container['Logger']);
    return $handler;
};

$container['Logger'] = function () use ($container) {
    $logger = new Monolog\Logger('diglib-iiif');
    $logfile = __DIR__ . '/../logs/application.log';
    $logger->pushHandler(new Monolog\Handler\RotatingFileHandler($logfile, 1, Monolog\Logger::ERROR));
    return $logger;
};

$container['IIIF.Resolver'] = function () use ($container) {
    $baseDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data';
    $resolver = new HAB\Diglib\API\IIIF\Resolver($baseDirectory);
    return $resolver;
};
$container['IIIF.Mapper'] = function () use ($container) {
    $resolver = $container['IIIF.Resolver'];
    $router = $container['router'];
    $serviceBaseUri = rtrim($router->pathFor('iiif'), '/');
    return new HAB\Diglib\API\IIIF\MapperFactory($resolver, $serviceBaseUri);
};

$container['Slim.RouterBasePath'] = function () use ($container) {
    // This is a workaround for Slim 3.x whose router does not provide
    // a method to create an absolute URI
    //
    // See https://github.com/slimphp/Slim/issues/2258
    //
    return function ($req, $res, $nxt) use ($container) {
        $router = $container['router'];
        $reqUri = $req->getUri();
        $basePath = sprintf('%s://%s', $reqUri->getScheme(), $reqUri->getAuthority());
        $router->setBasePath(rtrim($basePath, '/'));
        return $nxt($req, $res);
    };
};

$container['IIIF.NonInformationResource'] = function () {
    $controller = new HAB\Diglib\API\NonInformationResource();
    $controller->addMediatype('application/json', 'json');
    $controller->addMediatype('application/ld+json', 'json');
    return $controller;
};

$container['IIIF.Presentation'] = function () use ($container) {
    $mapper = $container['IIIF.Mapper'];
    $controller = new HAB\Diglib\API\IIIF\Presentation($mapper);
    return $controller;
};

$container['IIIF.InformationResource.JSON'] = function () {
    $middleware = new HAB\Diglib\API\InformationResource();
    $middleware->addMediatype('application/json', 'json');
    $middleware->addMediatype('application/ld+json', 'json');
    return $middleware;
};

$container['IIIF.InformationResource.Image'] = function () {
    $middleware = new HAB\Diglib\API\InformationResource();
    $middleware->addMediatype('image/jpeg');
    $middleware->addMediatype('image/png');
    return $middleware;
};

$container['IIIF.ImageServer.Features'] = function () use ($container) {
    $base = new HAB\Diglib\API\IIIF\ImageServer\Level2();
    $features = new HAB\Diglib\API\IIIF\ImageServer\Custom($base);
    $features->addRotationFeatures(HAB\Diglib\API\IIIF\ImageServer\Rotation::rotationArbitrary);
    return $features;
};

$container['IIIF.IIPImage.URL'] = 'http://image2.hab.de/fcgi-bin/iipsrv.fcgi';
$container['IIIF.ImageServer'] = function () use ($container) {
    $features = $container['IIIF.ImageServer.Features'];
    $mapper = $container['IIIF.Mapper'];
    $uri = $container['IIIF.IIPImage.URL'];
    // $server = new HAB\Diglib\API\IIIF\NativeBridge($features, $mapper);
    $server = new HAB\Diglib\API\IIIF\IIPImageBridge($features, $mapper, $uri);
    return $server;
};

$container['IIIF.Image'] = function () use ($container) {
    $server = $container['IIIF.ImageServer'];
    $router = $container['router'];
    $controller = new HAB\Diglib\API\IIIF\Image($server, $router);
    return $controller;
};

$container['IIIF.StaticCollection'] = function () use ($container) {

    $location = $container['IIIF.Resolver']->resolve('mssox.xml');
    $xml = simplexml_load_file($location);

    $members = array();
    foreach ($xml->children() as $entry) {
        $attributes = $entry->attributes();
        $members[] = array(
            '@id' => (string)$attributes['id'],
            '@type' => (string)$attributes['type'],
            'label' => (string)$attributes['label'],
        );
    }
    
    $collection = array(
        '@context' => 'https://iiif.io/api/presentation/2/context.json',
        '@id' => 'https://iiif.hab.de/collection/project/mssox',
        '@type' => 'sc:Collection',
        'label' => 'The Polonsky Foundation Oxford-Wolfenbüttel German Manuscripts Digitization Project (2018-2021)',
        'members' => $members,
    );
    return new HAB\Diglib\API\IIIF\StaticCollection($collection);
};

$container['CORS.Middleware'] = function () use ($container) {
    $options = array(
        'origin' => array('*'),
        'methods' => array('GET'),
        'logger' => $container['Logger'],
    );
    return new Tuupola\Middleware\CorsMiddleware($options);
};

$app->add($container['CORS.Middleware']);
