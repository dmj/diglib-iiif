<?php

$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    $handler = new HAB\Diglib\API\Error\Handler();
    return $handler;
};

$container['Logger'] = new Psr\Log\NullLogger();

$container['IIIF.Resolver'] = function () use ($container) {
    $resolver = new HAB\Diglib\API\IIIF\Resolver();
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
        $serviceBaseUri = sprintf('%s://%s%s', $reqUri->getScheme(), $reqUri->getAuthority(), $router->pathFor('iiif'));
        $router->setBasePath(rtrim($serviceBaseUri, '/'));
        HAB\Diglib\API\IIIF\Mapper\METS2IIIFv2::$serviceBaseUri = rtrim($serviceBaseUri, '/');

        return $nxt($req, $res);
    };
    return $filter;
};

$container['IIIF.Manifest'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Manifest($router, $resolver);
    return $controller;
};

$container['IIIF.Canvas'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Canvas($router, $resolver);
    return $controller;
};

$container['IIIF.Annotation'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Annotation($router, $resolver);
    return $controller;
};

$container['IIIF.Sequence'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Sequence($router, $resolver);
    return $controller;
};

$container['IIIF.Image'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Image($router, $resolver);
    return $controller;
};

$container['IIIF.IIPImage.URL'] = 'http://127.0.0.1:8080/fcgi-bin/iipsrv.fcgi';
$container['IIIF.IIPImage'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\IIPImage($router, $resolver, $container['IIIF.IIPImage.URL']);
    return $controller;
};

$container['CORS.Middleware'] = function () use ($container) {
    $options = array(
        'origin' => array('*'),
        'methods' => array('GET'),
    );
    return new Tuupola\Middleware\CorsMiddleware($options);
};

$app->add($container['CORS.Middleware']);
