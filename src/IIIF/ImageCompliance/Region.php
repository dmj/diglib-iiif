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

/**
 * Image API region transform.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Region implements Feature
{
    const regionByPx  = 0b0001;
    const regionByPct = 0b0010;

    private $features;

    public function __construct ($features = 0)
    {
        $this->features = $features;
    }

    public function createTransform ($spec)
    {
        if ($spec == 'full') {
            return null;
        }
        if ($this->features & Region::regionByPx) {
            if (preg_match('@^(?<x>[0-9]+),(?<y>[0-9]+),(?<width>[0-9]+),(?<height>[0-9]+)$@u', $spec, $match)) {
                return function ($image) use ($match) {
                    return imagecrop($image, $match);
                };
            }
        }
        if ($this->features & Region::regionByPct) {
            if (preg_match('@^pct:(?<x>[0-9]+(\.[0-9]+)?),(?<y>[0-9]+(\.[0-9]+)?),(?<width>[0-9]+(\.[0-9]+)?),(?<height>[0-9]+(\.[0-9]+)?)$@u', $spec, $match)) {
                return function ($image) use ($match) {
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $rect = array(
                        'x' => round($width * floatval($match['x']) / 100),
                        'y' => round($height * floatval($match['y']) / 100),
                        'width' => round($width * floatval($match['width']) / 100),
                        'height' => round($height * floatval($match['height']) / 100)
                    );
                    return imagecrop($image, $rect);
                };
            }
        }
        throw new UnsupportedFeature(sprintf('Unsupported image region feature request: %s', $spec));
    }
}
