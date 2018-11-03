<?php

$app->get('/{objectId}/manifest.json', 'IIIF.Presentation.Manifest:asJSON')
    ->setName('iiif.manifest.json')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/manifest', 'IIIF.Presentation.Manifest')
    ->setName('iiif.manifest')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/canvas/{entityId}.json', 'IIIF.Presentation.Canvas:asJSON')
    ->setName('iiif.canvas.json')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/canvas/{entityId}', 'IIIF.Presentation.Canvas')
    ->setName('iiif.canvas')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/annotation/{entityId}.json', 'IIIF.Presentation.Annotation:asJSON')
    ->setName('iiif.annotation.json')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/annotation/{entityId}', 'IIIF.Presentation.Annotation')
    ->setName('iiif.annotation')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/sequence/{entityId}.json', 'IIIF.Presentation.Sequence:asJSON')
    ->setName('iiif.sequence.json')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/sequence/{entityId}', 'IIIF.Presentation.Sequence')
    ->setName('iiif.sequence')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/image/{entityId}/info.json', 'IIIF.Presentation.Image:asJSON')
    ->setName('iiif.image.json')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/image/{entityId}/{ops:.*}', function ($req, $res, $arg) { var_dump($arg); })
    ->setName('iiif.image.data')
    ->add('IIIF.Presentation.Filter');

$app->get('/{objectId}/image/{entityId}', 'IIIF.Presentation.Image')
    ->setName('iiif.image')
    ->add('IIIF.Presentation.Filter');

$app->get('/', function ($req, $res, $args) {
    return $res->write('Nothing to see here...');
})->setName('iiif');
