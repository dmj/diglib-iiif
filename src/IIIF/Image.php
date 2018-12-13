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

namespace HAB\Diglib\API\IIIF;

use HAB\Diglib\API\Error;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Slim\Http\Stream;

/**
 * IIIF Image API implementation.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Image extends Controller
{
    protected static $jsonRoute = 'iiif.image.json';

    private $imageCompliance;

    public function asJPEG (Request $request, Response $response, array $arguments)
    {
        $accept = array('image/jpeg');
        $ctype = $this->findRequestedEntityContentType($request, $accept);
        if (!$ctype) {
            throw new Error\Http(406, array('Accept' => $accept));
        }

        $imageUri = $this->resolveImageUri($arguments['objectId'], $arguments['entityId']);
        if (!file_exists($imageUri) || !is_readable($imageUri)) {
            throw new Error\Http(404);
        }

        $image = $this->getImageCompliance()->getImageStream($imageUri, $arguments['ops']);
        if (!is_resource($image)) {
            throw new RuntimeException();
        }

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'image/jpeg')
            ->withBody(new Stream($image));
    }

    protected function getJSON (array $arguments)
    {
        $mapper = $this->getMapper($arguments['objectId']);
        return $this->encodeJSON($mapper->getImage($arguments['entityId']));
    }

    protected function resolveImageUri ($objectId, $entityId)
    {
        $mapper = $this->getMapper($objectId);
        $imageUri = $mapper->getImageUri($entityId);

        // TODO: Use real URI resolver
        return rtrim($this->getLocation($objectId), '/') . '/' . $imageUri;
    }

    public function getImageCompliance ()
    {
        if (!$this->imageCompliance) {
            $this->setImageCompliance(new ImageCompliance\Level1());
        }
        return $this->imageCompliance;
    }

    public function setImageCompliance (ImageCompliance\ImageCompliance $imageCompliance)
    {
        $this->imageCompliance = $imageCompliance;
    }
}
