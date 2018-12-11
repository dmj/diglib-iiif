<?php

$app->get('/iiif/{objectId}/manifest.json', 'IIIF.Manifest:asJSON')
    ->setName('iiif.manifest.json')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/manifest', 'IIIF.Manifest')
    ->setName('iiif.manifest')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/canvas/{entityId}.json', 'IIIF.Canvas:asJSON')
    ->setName('iiif.canvas.json')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/canvas/{entityId}', 'IIIF.Canvas')
    ->setName('iiif.canvas')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/annotation/{entityId}.json', 'IIIF.Annotation:asJSON')
    ->setName('iiif.annotation.json')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/annotation/{entityId}', 'IIIF.Annotation')
    ->setName('iiif.annotation')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/sequence/{entityId}.json', 'IIIF.Sequence:asJSON')
    ->setName('iiif.sequence.json')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/sequence/{entityId}', 'IIIF.Sequence')
    ->setName('iiif.sequence')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/image/{entityId}/info.json', 'IIIF.IIPImage:asJSON')
    ->setName('iiif.image.json')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/image/{entityId}/{ops:.*}', 'IIIF.IIPImage:asJPEG')
    ->setName('iiif.image.data')
    ->add('IIIF.Filter');

$app->get('/iiif/{objectId}/image/{entityId}', 'IIIF.Image')
    ->setName('iiif.image')
    ->add('IIIF.Filter');

$app->get('/', function ($req, $res, $args) {
    return $res->write('Nothing to see here...');
})->setName('iiif');
