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

use RuntimeException;
use DOMDocument;

/**
 * Data mapper factory.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class MapperFactory
{
    private $resolver;
    private $serviceBaseUri;

    public function __construct (Resolver $resolver, $serviceBaseUri)
    {
        $this->resolver = $resolver;
        $this->serviceBaseUri = $serviceBaseUri;
    }

    public function getObjectLocation ($objectId)
    {
        return $this->resolver->resolve($objectId);
    }

    public function create ($objectId)
    {
        $location = $this->getObjectLocation($objectId);
        if (!$location || !file_exists($location . DIRECTORY_SEPARATOR . 'mets.xml')) {
            throw new RuntimeException("Unable to locate object description: {$location}");
        }
        $source = new DOMDocument();
        if (!$source->load($location . DIRECTORY_SEPARATOR . 'mets.xml')) {
            throw new RuntimeException();
        }
        return new Mapper\METS2IIIFv2($source, $this->serviceBaseUri);
    }
}
