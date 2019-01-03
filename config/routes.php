<?php

$app->get('/iiif/{objectId}/manifest.json', 'IIIF.Manifest:asJSON')
    ->setName('iiif.manifest.json');

$app->get('/iiif/{objectId}/manifest', 'IIIF.Manifest')
    ->setName('iiif.manifest');

$app->get('/iiif/{objectId}/canvas/{entityId}.json', 'IIIF.Canvas:asJSON')
    ->setName('iiif.canvas.json');

$app->get('/iiif/{objectId}/canvas/{entityId}', 'IIIF.Canvas')
    ->setName('iiif.canvas');

$app->get('/iiif/{objectId}/annotation/{entityId}.json', 'IIIF.Annotation:asJSON')
    ->setName('iiif.annotation.json');

$app->get('/iiif/{objectId}/annotation/{entityId}', 'IIIF.Annotation')
    ->setName('iiif.annotation');

$app->get('/iiif/{objectId}/sequence/{entityId}.json', 'IIIF.Sequence:asJSON')
    ->setName('iiif.sequence.json');

$app->get('/iiif/{objectId}/sequence/{entityId}', 'IIIF.Sequence')
    ->setName('iiif.sequence');

$app->get('/iiif/{objectId}/image/{entityId}/info.json', 'IIIF.Image:asJSON')
    ->setName('iiif.image.json');

$app->get('/iiif/{objectId}/image/{entityId}/{ops:.*}', 'IIIF.Image:asJPEG')
    ->setName('iiif.image.data');

$app->get('/iiif/{objectId}/image/{entityId}', 'IIIF.Image')
    ->setName('iiif.image');

$app->get('/iiif/', function ($req, $res, $args) {
    return $res->write('Nothing to see here...');
})->setName('iiif');

$app->add('Slim.RouterBasePath');
