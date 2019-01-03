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
use Slim\Interfaces\RouterInterface as Router;

use Negotiation\Negotiator;

use DOMDocument;
use RuntimeException;

use HAB\Diglib\API\Error;
use HAB\Diglib\API\LoggerAwareTrait;

use function HAB\XML\jsonxml2php;

/**
 * Abstract base class of IIIF controllers.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
abstract class Controller
{
    use LoggerAwareTrait;

    protected $router;
    protected $mapper;

    public function __construct (Router $router, MapperFactory $mapper)
    {
        $this->router = $router;
        $this->mapper = $mapper;
    }

    public function asJSON (Request $request, Response $response, array $arguments)
    {
        $accept = array('application/json', 'application/ld+json');
        $ctype = $this->findRequestedEntityContentType($request, $accept);
        if (!$ctype) {
            throw new Error\Http(406, array('Accept' => $accept));
        }

        $payload = $this->getJSON($arguments);
        if (!$payload) {
            throw new Error\Http(404);
        }

        return $response
            ->withHeader('Content-Type', $ctype->getValue())
            ->write($payload);
    }

    abstract protected function getJSON (array $arguments);

    protected function findRequestedEntityContentType (Request $request, $priorities)
    {
        $neg = new Negotiator();
        $accept = implode($request->getHeader('Accept')) ?: '*/*';
        return $neg->getBest($accept, $priorities);
    }

    protected function getMapper ($objectId)
    {
        return $this->mapper->create($objectId);
    }

    protected function encodeJSON ($data)
    {
        if ($data instanceof DOMDocument) {
            $data = jsonxml2php($data->documentElement);
        }
        if ($data) {
            $payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(json_last_error_msg());
            }
            return $payload;
        }
    }
}
