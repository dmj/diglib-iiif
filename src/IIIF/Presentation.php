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

use Slim\Http\Response;

use HAB\Diglib\API\Error;

use RuntimeException;

/**
 * Presentation API controller.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Presentation
{
    private $mapper;

    public function __construct (MapperFactory $mapper)
    {
        $this->mapper = $mapper;
    }

    public function __invoke (Request $request, Response $response, array $arguments)
    {
        $entityType = $arguments['entityType'];
        $entityId = $arguments['entityId'] ?? null;

        try {
            $mapper = $this->mapper->create($arguments['objectId']);
        } catch (RuntimeException $e) {
            throw new Error\Http(404, [], $e);
        }

        $payload = $mapper->getEntity($entityType, $entityId);
        if (!$payload) {
            throw new Error\Http(404);
        }

        return $response->write($payload);
    }
}
