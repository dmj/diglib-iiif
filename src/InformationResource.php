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

namespace HAB\Diglib\API;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Negotiation\Negotiator;

/**
 * Middleware for information resources.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class InformationResource
{
    private $mediatypes;

    public function __invoke (Request $request, Response $response, $next)
    {
        $negotiator = new Negotiator();
        $client = implode($request->getHeader('Accept'));
        if (!$client) {
            $client = '*/*';
        }

        $type = $negotiator->getBest($client, $this->mediatypes);
        if (!$type) {
            throw new Error\Http(406);
        }
        $response = $next($request, $response);
        return $response->withHeader('Content-Type', $type->getValue());
    }

    public function addMediatype ($mediatype, $priority = null)
    {
        if ($priority) {
            $this->mediatypes[] = $mediatype . ';q=' . $priority;
        } else {
            $this->mediatypes[] = $mediatype;
        }
        return $this;
    }
}
