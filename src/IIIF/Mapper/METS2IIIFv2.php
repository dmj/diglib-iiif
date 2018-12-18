<?php

/**
 * This file is part of HAB Diglib IIIF.
 *
 * HAB Diglib IIIF is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HAB Diglib IIIF is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HAB Diglib IIIF.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */

namespace HAB\Diglib\API\IIIF\Mapper;

use DOMXPath;
use DOMDocument;
use RuntimeException;

use HAB\XML\Transformation;

/**
 * Map METS to IIIF v2 API.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class METS2IIIFv2
{
    public static $serviceBaseUri;
    public static $imageComplianceLevel = 'http://iiif.io/api/image/2/level1.json';

    private $source;

    public function __construct (DOMDocument $source)
    {
        $this->source = $source;
    }

    public function getManifest ()
    {
        return $this->transform(array('entityType' => 'sc:Manifest'));
    }

    public function getCanvas ($canvasId)
    {
        return $this->transform(array('entityType' => 'sc:Canvas', 'entityId' => $canvasId));
    }

    public function getAnnotation ($annotationId)
    {
        return $this->transform(array('entityType' => 'oa:Annotation', 'entityId' => $annotationId));
    }

    public function getSequence ($sequenceId)
    {
        return $this->transform(array('entityType' => 'sc:Sequence', 'entityId' => $sequenceId));
    }

    public function getImageUri ($imageId)
    {
        $proc = new DOMXPath($this->source);
        $proc->registerNamespace('mets', 'http://www.loc.gov/METS/');
        $proc->registerNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $expr = sprintf("string(//mets:file[@ID = '%s']/mets:FLocat/@xlink:href)", $imageId);
        return $proc->evaluate($expr);
    }

    private function transform (array $parameters)
    {
        $transform = $this->getTransformation();
        $transform->setSource($this->source);
        $transform->setParameters($parameters);
        $transform->setParameter('serviceBaseUri', self::$serviceBaseUri);
        $transform->setParameter('imageComplianceLevel', self::$imageComplianceLevel);
        return $transform->execute();
    }

    private function getTransformation ()
    {
        $stylesheet = new DOMDocument();
        if (!$stylesheet->load(__DIR__ . DIRECTORY_SEPARATOR . 'METS2IIIFv2.xsl')) {
            throw new RuntimeException("Error loading XSL transformation");
        }
        return new Transformation($stylesheet);
    }
}
