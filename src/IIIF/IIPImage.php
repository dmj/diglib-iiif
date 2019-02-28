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

use GuzzleHttp\Client;

use RuntimeException;

/**
 * Connect to IIPImage server.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class IIPImage extends ImageServer\Server implements ImageServer
{
    private $client;
    private $mapper;
    private $iipImageUri;

    public function __construct (ImageServer\FeatureSet $features, MapperFactory $mapper, $iipImageUri)
    {
        parent::__construct($features);
        $this->mapper = $mapper;
        $this->iipImageUri = $iipImageUri;
        $this->client = new Client();
    }

    public function getImageStream ($imageUri, $imageParameters)
    {
        $remoteUri = $this->iipImageUri . '?IIIF=' . $imageUri . '/' . $imageParameters;
        $response = $this->request($remoteUri);
        return $response;
    }

    public function getImageInfo ($imageUri)
    {
        $remoteUri = $this->iipImageUri . '?IIIF=' . $imageUri . '/info.json';
        $response = $this->request($remoteUri);
        $info = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(json_last_error_msg());
        }
        return $info;
    }

    public function getImageUri ($objectId, $imageId)
    {
        $location = $this->mapper->getObjectLocation($objectId);
        $mapper = $this->mapper->create($objectId);
        $image = $mapper->getImageUri($imageId);
        if ($image) {
            return strtr($objectId, '_', '/') . '/' . $image;
        }
    }

    private function request ($remoteUri)
    {
        $response = $this->client->get($remoteUri);
        return $response;
    }

}
