<?php

$app->get('/object/{objectId}/image/{entityId}.json', 'IIIF.Image:getImageInfo')
    ->add('IIIF.InformationResource.JSON');
$app->get('/object/{objectId}/image/{entityId}/info.json', 'IIIF.Image:getImageInfo')
    ->add('IIIF.InformationResource.JSON');
$app->get('/object/{objectId}/image/{entityId}/{ops:.*}', 'IIIF.Image:getImageStream');

$app->get('/object/{objectId}/image/{entityId}', 'IIIF.NonInformationResource')
    ->setName('iiif.image');

$app->get('/object/{objectId}/{entityType}.json', 'IIIF.Presentation')
    ->add('IIIF.InformationResource.JSON');

$app->get('/object/{objectId}/{entityType}', 'IIIF.NonInformationResource');

$app->get('/object/{objectId}/{entityType}/{entityId}.json', 'IIIF.Presentation')
        ->add('IIIF.InformationResource.JSON');

$app->get('/object/{objectId}/{entityType}/{entityId}', 'IIIF.NonInformationResource');

$app->get('/object/', function ($req, $res, $args) {
    return $res->write('Nothing to see here...');
})->setName('iiif');

$app->get('/collection/project/mssox', 'IIIF.NonInformationResource');
$app->get('/collection/project/mssox.json', 'IIIF.StaticCollection')
    ->add('IIIF.InformationResource.JSON');

$app->add('Slim.RouterBasePath');
