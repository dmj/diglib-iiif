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
 * @copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */

namespace HAB\Diglib\API\IIIF;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Slim\Interfaces\RouterInterface as Router;
use Slim\Http\Stream;

use HAB\Diglib\API\Error;

use RuntimeException;

/**
 * Image API controller.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Image
{
    private $server;

    public function __construct (Adapter\ImageServer $server, Router $router)
    {
        $this->server = $server;
        $this->router = $router;
    }

    public function getImageInfo (Request $request, Response $response, array $arguments)
    {
        $imageUri = $this->server->getImageUri($arguments['objectId'], $arguments['entityId']);
        if (!$imageUri) {
            throw new Error\Http(404);
        }

        $profile = $this->server->getProfile();
        $profile['supports'] []= 'cors';
        $profile['supports'] []= 'baseUriRedirect';
        $profile['supports'] []= 'jsonldMediatype';
        $profile['supports'] []= 'profileLinkHeader';

        try {
            $info = $this->server->getImageInfo($imageUri);
        } catch (RuntimeException $e) {
            throw new Error\Http(404);
        }

        $info['@id'] = $this->router->pathFor('iiif.image', $arguments);
        $info['profile'] = array($this->server->getComplianceLevel(), $profile);

        $payload = json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }

        return $response->write($payload);
    }

    public function getImageStream (Request $request, Response $response, array $arguments)
    {
        $imageUri = $this->server->getImageUri($arguments['objectId'], $arguments['entityId']);
        if (!$imageUri) {
            throw new Error\Http(404);
        }

        $response = $this->server->getImageStream($imageUri, $arguments['ops']);
        if ($complianceLevelUri = $this->server->getComplianceLevel()) {
            $response = $response->withHeader('Link', sprintf('<%s>; rel="profile"', $complianceLevelUri));
        }

        return $response;
    }

}
