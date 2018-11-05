<?php

$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    $handler = function ($req, $res, $err) {
        $handler = new Slim\Handlers\Error();
        return $handler($req, $res, $err);
    };
    return $handler;
};

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
        HAB\Diglib\API\IIIF\Presentation\Mapper\METS2IIIFv2::$serviceBaseUri = rtrim($serviceBaseUri, '/');

        return $nxt($req, $res);
    };
    return $filter;
};

$container['IIIF.Manifest'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Presentation\Manifest($router, $resolver);
    return $controller;
};

$container['IIIF.Canvas'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Presentation\Canvas($router, $resolver);
    return $controller;
};

$container['IIIF.Annotation'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Presentation\Annotation($router, $resolver);
    return $controller;
};

$container['IIIF.Sequence'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Presentation\Sequence($router, $resolver);
    return $controller;
};

$container['IIIF.Image'] = function () use ($container) {
    $router = $container['router'];
    $resolver = $container['IIIF.Resolver'];
    $controller = new HAB\Diglib\API\IIIF\Presentation\Image($router, $resolver);
    return $controller;
};
