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
 * Image API quality transform.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Quality implements Feature
{
    const color   = 0b00000001;
    const gray    = 0b00000010;
    const bitonal = 0b00000100;

    private $features;

    public function __construct ($features = 0)
    {
        $this->features = $features;
    }

    public function createTransform ($spec)
    {
        if ($spec == 'default' || $spec == 'color') {
            return null;
        }
        if ($this->features & Quality::gray) {
            if ($spec == 'gray') {
                return function ($image) {
                    if (imagefilter($image, IMG_FILTER_GRAYSCALE)) {
                        return $image;
                    }
                };
            }
        }
        if ($this->features & Quality::bitonal) {
            if ($spec == 'bitonal') {
                return function ($image) {
                    if (imagefilter($image, IMG_FILTER_GRAYSCALE) && imagefilter($image, IMG_FILTER_CONTRAST, -100)) {
                        return $image;
                    }
                };
            }
        }
        throw new UnsupportedFeature(sprintf('Unsupported image quality request: %s', $spec));
    }
}
