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

namespace HAB\Diglib\API\Error;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use HAB\Diglib\API\LoggerAwareTrait;

use Exception;

/**
 * Error handler.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Handler
{
    use LoggerAwareTrait;

    private $displayErrorDetails;

    public function __construct ($displayErrorDetails = false)
    {
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke (Request $request, Response $response, Exception $exception)
    {
        $details = $exception->getTraceAsString();
        $this->log('critical', $details);

        if (!$exception instanceof Http) {
            $exception = new Http(500, array(), $exception);
        }
        foreach ($exception->getHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response = $response->withStatus($exception->getCode());
        $response = $response->write(
            $this->displayErrorDetails ? $details : sprintf('%03d %s', $response->getStatusCode(), $response->getReasonPhrase())
        );
        return $response;
    }
}
