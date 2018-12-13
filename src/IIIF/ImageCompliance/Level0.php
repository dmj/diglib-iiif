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

namespace HAB\Diglib\API\IIIF\ImageCompliance;

use HAB\Diglib\API\Error;
use HAB\Diglib\API\LoggerAwareTrait;

/**
 * Implements IIIF Image API 2.1 Level 0 compliance.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Level0 implements ImageCompliance
{
    use LoggerAwareTrait;

    public function getComplianceLevel ()
    {
        return 'http://iiif.io/api/image/2/level0.json';
    }

    public function getImageStream ($imageUri, $imageParameters)
    {
        if ($imageParameters != 'full/full/0/default.jpg') {
            $this->log('error', sprintf('Invalid or unsupported image parameters: "%s"', $imageParameters));
            throw new Error\Http(400);
        }
        return fopen($imageUri, 'r');
    }
}
