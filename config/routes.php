<?php

$app->get('/iiif/{objectId}/manifest.json', 'IIIF.Manifest:asJSON');

$app->get('/iiif/{objectId}/manifest', 'IIIF.NonInformationResource');

$app->get('/iiif/{objectId}/canvas/{entityId}.json', 'IIIF.Canvas:asJSON');

$app->get('/iiif/{objectId}/canvas/{entityId}', 'IIIF.NonInformationResource');

$app->get('/iiif/{objectId}/annotation/{entityId}.json', 'IIIF.Annotation:asJSON');

$app->get('/iiif/{objectId}/annotation/{entityId}', 'IIIF.NonInformationResource');

$app->get('/iiif/{objectId}/sequence/{entityId}.json', 'IIIF.Sequence:asJSON');

$app->get('/iiif/{objectId}/sequence/{entityId}', 'IIIF.NonInformationResource');

$app->get('/iiif/{objectId}/image/{entityId}/info.json', 'IIIF.Image:asJSON');

$app->get('/iiif/{objectId}/image/{entityId}.json', 'IIIF.Image:asJSON');

$app->get('/iiif/{objectId}/image/{entityId}', 'IIIF.NonInformationResource')
    ->setName('iiif.image');

$app->get('/iiif/{objectId}/image/{entityId}/{ops:.*}', 'IIIF.Image:asJPEG');

$app->get('/iiif/', function ($req, $res, $args) {
    return $res->write('Nothing to see here...');
})->setName('iiif');

$app->add('Slim.RouterBasePath');
