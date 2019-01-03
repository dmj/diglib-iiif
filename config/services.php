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
    $baseDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'examples';
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

$container['IIIF.Manifest'] = function () use ($container) {
    $router = $container['router'];
    $mapper = $container['IIIF.Mapper'];
    $controller = new HAB\Diglib\API\IIIF\Manifest($router, $mapper);
    return $controller;
};

$container['IIIF.Canvas'] = function () use ($container) {
    $router = $container['router'];
    $mapper = $container['IIIF.Mapper'];
    $controller = new HAB\Diglib\API\IIIF\Canvas($router, $mapper);
    return $controller;
};

$container['IIIF.Annotation'] = function () use ($container) {
    $router = $container['router'];
    $mapper = $container['IIIF.Mapper'];
    $controller = new HAB\Diglib\API\IIIF\Annotation($router, $mapper);
    return $controller;
};

$container['IIIF.Sequence'] = function () use ($container) {
    $router = $container['router'];
    $mapper = $container['IIIF.Mapper'];
    $controller = new HAB\Diglib\API\IIIF\Sequence($router, $mapper);
    return $controller;
};

$container['IIIF.ImageServer.Features'] = function () use ($container) {
    $base = new HAB\Diglib\API\IIIF\ImageServer\Level2();
    $features = new HAB\Diglib\API\IIIF\ImageServer\Custom($base);
    $features->addRotationFeatures(HAB\Diglib\API\IIIF\ImageServer\Rotation::rotationArbitrary);
    return $features;
};
$container['IIIF.ImageServer'] = function () use ($container) {
    $features = $container['IIIF.ImageServer.Features'];
    $server = new HAB\Diglib\API\IIIF\NativeBridge($features);
    return $server;
};

$container['IIIF.Image'] = function () use ($container) {
    $router = $container['router'];
    $mapper = $container['IIIF.Mapper'];
    $server = $container['IIIF.ImageServer'];
    $controller = new HAB\Diglib\API\IIIF\Image($router, $mapper, $server);
    return $controller;
};

$container['IIIF.IIPImage.URL'] = 'http://127.0.0.1:8080/fcgi-bin/iipsrv.fcgi';
$container['IIIF.IIPImage'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\IIPImage($router, $resolver, $container['IIIF.IIPImage.URL']);
    $controller->setLogger($container['Logger']);
    return $controller;
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
