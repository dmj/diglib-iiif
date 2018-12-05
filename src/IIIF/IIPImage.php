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

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Slim\Interfaces\RouterInterface as Router;

use Slim\Exception\NotFoundException;
use Slim\Http\Stream;

use RuntimeException;

/**
 * IIIF Image API using a IIPImage backend.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class IIPImage extends Controller
{
    protected static $jsonRoute = 'iiif.image.json';

    private $iipImageUri;

    public function __construct (Router $router, Resolver $resolver, $iipImageUri)
    {
        parent::__construct($router, $resolver);
        $this->iipImageUri = $iipImageUri;
    }

    public function asJPEG (Request $request, Response $response, array $arguments)
    {
        $mapper = $this->getMapper($arguments['objectId']);
        $imageUri = $mapper->getImageUri($arguments['entityId']);

        $imageUri = sprintf('%s?IIIF=/%s/%s/%s', $this->iipImageUri, strtr($arguments['objectId'], '_', '/'), $imageUri, $arguments['ops']);

        $image = new Stream(fopen($imageUri, 'r'));
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'image/jpeg')
            ->withBody($image);
    }

    protected function getJSON (array $arguments)
    {
        $mapper = $this->getMapper($arguments['objectId']);
        $imageUri = $mapper->getImageUri($arguments['entityId']);

        $iipImageUri = sprintf('%s?IIIF=/%s/%s/info.json', $this->iipImageUri, strtr($arguments['objectId'], '_', '/'), $imageUri);

        $json = json_decode(@file_get_contents($iipImageUri), JSON_OBJECT_AS_ARRAY);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }

        $json['@id'] = $this->router->pathFor(static::$jsonRoute, $arguments);

        $json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }

        return $json;
    }
}
