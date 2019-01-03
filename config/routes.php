<?php

$app->get('/iiif/{objectId}/image/{entityId}.json', 'IIIF.Image:asJSON');
$app->get('/iiif/{objectId}/image/{entityId}/info.json', 'IIIF.Image:asJSON');

$app->get('/iiif/{objectId}/image/{entityId}', 'IIIF.NonInformationResource')
    ->setName('iiif.image');

$app->get('/iiif/{objectId}/{entityType}.json', 'IIIF.Presentation');

$app->get('/iiif/{objectId}/{entityType}', 'IIIF.NonInformationResource');

$app->get('/iiif/{objectId}/{entityType}/{entityId}.json', 'IIIF.Presentation');

$app->get('/iiif/{objectId}/{entityType}/{entityId}', 'IIIF.NonInformationResource');

$app->get('/iiif/{objectId}/image/{entityId}/{ops:.*}', 'IIIF.Image:asJPEG');

$app->get('/iiif/', function ($req, $res, $args) {
    return $res->write('Nothing to see here...');
})->setName('iiif');

$app->add('Slim.RouterBasePath');
