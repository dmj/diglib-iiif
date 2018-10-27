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

use DOMDocument;
use RuntimeException;

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
    private $router;
    private $resolver;

    protected static $jsonRoute;

    final public function __construct (Router $router, Resolver $resolver)
    {
        $this->router = $router;
        $this->resolver = $resolver;
    }

    final public function asJSON (Request $request, Response $response, array $arguments)
    {
        $data = $this->getJSON($arguments);
        if (!$data || !$data->documentElement) {
            throw new NotFoundException($request, $response);
        }
        $payload = json_encode(jsonxml2php($data->documentElement), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->write($payload);
    }

    abstract protected function getJSON (array $arguments);

    final public function __invoke (Request $request, Response $response, array $arguments)
    {
        $target = $this->router->pathFor(static::$jsonRoute, $arguments);
        return $response->withRedirect($target, 303);
    }

    protected function createNotAcceptableResponse (Response $response)
    {
        return $response->withStatus(406)->write('<h1>406 Not Acceptable</h1>');
    }

    protected function getMapper ($objectId)
    {
        $location = $this->resolver->resolve($objectId);
        if (!$location || !file_exists($location . DIRECTORY_SEPARATOR . 'mets.xml')) {
            throw new RuntimeException();
        }
        $source = new DOMDocument();
        if (!$source->load($location . DIRECTORY_SEPARATOR . 'mets.xml')) {
            throw new RuntimeException();
        }
        return new Mapper\METS2IIIFv2($source);
    }

}
