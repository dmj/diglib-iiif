<?php

$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    $handler = new HAB\Diglib\API\Error\Handler();
    $handler->setLogger($container['Logger']);
    return $handler;
};

$container['Logger'] = function () use ($container) {
    $logger = new Monolog\Logger('diglib-iiif');
    $logger->pushHandler(new Monolog\Handler\StreamHandler('php://stderr', Monolog\Logger::INFO));
    return $logger;
};

$container['IIIF.Resolver'] = function () use ($container) {
    $baseDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'examples';
    $resolver = new HAB\Diglib\API\IIIF\Resolver($baseDirectory);
    $resolver->setLogger($container['Logger']);
    return $resolver;
};
$container['IIIF.Filter'] = function () use ($container) {
    $filter = function ($req, $res, $nxt) use ($container) {
        $resolver = $container['IIIF.Resolver'];
        $router = $container['router'];
        $route = $req->getAttribute('route');
        $args = $route->getArguments();

        $objectLocation = $resolver->resolve($args['objectId']);
        if (!$objectLocation) {
            throw new Slim\Exception\NotFoundException($req, $res);
        }

        $reqUri = $req->getUri();
        $basePath = sprintf('%s://%s', $reqUri->getScheme(), $reqUri->getAuthority());
        $router->setBasePath(rtrim($basePath, '/'));

        $serviceBaseUri = $router->pathFor('iiif');
        HAB\Diglib\API\IIIF\Mapper\METS2IIIFv2::$serviceBaseUri = rtrim($serviceBaseUri, '/');

        return $nxt($req, $res);
    };
    return $filter;
};

$container['IIIF.Manifest'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Manifest($router, $resolver);
    $controller->setLogger($container['Logger']);
    return $controller;
};

$container['IIIF.Canvas'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Canvas($router, $resolver);
    $controller->setLogger($container['Logger']);
    return $controller;
};

$container['IIIF.Annotation'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Annotation($router, $resolver);
    $controller->setLogger($container['Logger']);
    return $controller;
};

$container['IIIF.Sequence'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Sequence($router, $resolver);
    $controller->setLogger($container['Logger']);
    return $controller;
};

$container['IIIF.ImageServer'] = function () use ($container) {
    $features = new HAB\Diglib\API\IIIF\ImageServer\Level2();
    $server = new HAB\Diglib\API\IIIF\NativeBridge($features);
    return $server;
};

$container['IIIF.Image'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $server = $container['IIIF.ImageServer'];
    $controller = new HAB\Diglib\API\IIIF\Image($router, $resolver, $server);
    $controller->setLogger($container['Logger']);
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
