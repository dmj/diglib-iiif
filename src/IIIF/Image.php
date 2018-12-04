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

use Slim\Exception\NotFoundException;
use Slim\Http\Stream;

/**
 * Provide the IIIF Image.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Image extends Controller
{
    private $imageSource;

    protected static $jsonRoute = 'iiif.image.json';

    public function asJPEG (Request $request, Response $response, array $arguments)
    {
        $location = $this->getLocation($arguments['objectId']);
        $mapper = $this->getMapper($arguments['objectId']);
        $imageUri = $mapper->getImageUri($arguments['entityId']);

        $image = $this->getJPEG('/host/' . $arguments['objectId'] . '/' . $imageUri, $arguments['ops']);

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'image/jpeg')
            ->withBody($image);
    }

    public function setImageSource (ImageSource $imageSource)
    {
        $this->imageSource = $imageSource;
    }

    protected function getJPEG ($imageUri, $options)
    {
        if (!$this->imageSource) {
            return new Stream(fopen($imageUri, 'r'));
        }
        return $this->imageSource->getImage($imageUri, $options);
    }

    protected function getJSON (array $arguments)
    {
        $mapper = $this->getMapper($arguments['objectId']);
        return $mapper->getImage($arguments['entityId']);
    }
}
