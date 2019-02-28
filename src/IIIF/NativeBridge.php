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

use Slim\Http\Response;
use Slim\Http\Headers;
use Slim\Http\Stream;

/**
 * Connects a IIIF Image server.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class NativeBridge extends ImageServer\Native implements ImageServer
{
    private $mapper;

    public function __construct (ImageServer\FeatureSet $features, MapperFactory $mapper)
    {
        parent::__construct($features);
        $this->mapper = $mapper;
    }

    public function getImageStream ($imageUri, $imageParameters)
    {
        $stream = parent::getImageStream($imageUri, $imageParameters);
        $headers = new Header(array('Content-Type' => $stream->getMediaType()));
        $body = new Stream($stream->getStream());
        return new Response(200, $headers, $body);
    }

    public function getImageUri ($objectId, $imageId)
    {
        $location = $this->mapper->getObjectLocation($objectId);
        $mapper = $this->mapper->create($objectId);
        $image = $mapper->getImageUri($imageId);
        if (preg_match('@https?://@u', $image)) {
            return $image;
        } else {
            $image = rtrim($location, '/\\') . DIRECTORY_SEPARATOR . $image;
            if (file_exists($image) && is_readable($image)) {
                return  $image;
            }
        }
    }
}
