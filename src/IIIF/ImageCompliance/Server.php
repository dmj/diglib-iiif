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

use RuntimeException;

/**
 * Serve image bitstream.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Server implements ImageCompliance
{
    private $features;

    public function __construct (FeatureSet $features)
    {
        $this->features = $features;
    }

    public function getComplianceLevel ()
    {
        return $this->features->getComplianceLevelUri();
    }

    public function getImageStream ($imageUri, $imageParameters)
    {
        if (!preg_match('@^(?<region>[^/]+)/(?<size>[^/]+)/(?<rotation>[^/]+)/(?<quality>[^.]+)\.(?<format>.+)$@u', $imageParameters, $match)) {
            throw new UnsupportedFeature(sprintf('Unsupported feature request: %s', $imageParameters));
        }
        extract($match);
        $image = imagecreatefromjpeg($imageUri);
        if (!is_resource($image)) {
            throw new RuntimeException();
        }

        $image = $this->features->apply($image, $region, $size, $rotation, $quality);
        if (!is_resource($image)) {
            throw new RuntimeException();
        }

        $buffer = fopen('php://temp', 'rw');
        $this->features->serialize($image, $buffer, $format);
        return $buffer;
    }
}
