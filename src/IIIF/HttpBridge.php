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

use HAB\Diglib\API\Error;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use RuntimeException;

/**
 * Abstract base class of http-based bridges.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
abstract class HttpBridge extends ImageServer\Server implements ImageServer
{
    private $client;
    private $mapper;
    private $baseUri;

    abstract protected function getImageStreamUri ($imageUri, $imageParameters);
    abstract protected function getImageInfoUri ($imageUri);

    public function __construct (ImageServer\FeatureSet $features, MapperFactory $mapper, $baseUri)
    {
        parent::__construct($features);
        $this->mapper = $mapper;
        $this->baseUri = $baseUri;
        $this->client = new Client();
    }

    public function getImageStream ($imageUri, $imageParameters)
    {
        $remoteUri = $this->baseUri . $this->getImageStreamUri($imageUri, $imageParameters);
        $response = $this->request($remoteUri, true);
        return $response;
    }

    public function getImageInfo ($imageUri)
    {
        $remoteUri = $this->baseUri . $this->getImageInfoUri($imageUri);
        $response = $this->request($remoteUri);
        $info = json_decode((string)$response->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }
        return $info;
    }

    public function getImageUri ($objectId, $imageId)
    {
        $mapper = $this->mapper->create($objectId);
        $image = $mapper->getImageUri($imageId);
        if ($image) {
            return strtr($objectId, '_', '/') . '/' . $image;
        }
    }

    private function request ($remoteUri, $stream = false)
    {
        try {
            $response = $this->client->get($remoteUri, ['stream' => $stream]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response) {
                $code = $response->getStatusCode();
            } else {
                $code = 500;
            }
            throw new Error\Http($code, [], $e);
        } catch (ServerException $e) {
            throw new Error\Http(502, [], $e);
        }
        return $response;
    }
}
