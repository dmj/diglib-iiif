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

use ArrayObject;

/**
 * Image API feature set
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
abstract class FeatureSet
{
    public function getComplianceLevelUri ()
    {
        return static::$complianceLevelUri;
    }

    public function apply ($image, $region, $size, $rotation, $quality)
    {
        $chain = new ArrayObject();
        $this->push($chain, (new Region(static::$region))->createTransform($region));
        $this->push($chain, (new Size(static::$size))->createTransform($size));
        $this->push($chain, (new Rotation(static::$rotation))->createTransform($rotation));
        $this->push($chain, (new Quality(static::$quality))->createTransform($quality));

        foreach ($chain as $function) {
            $image = call_user_func($function, $image);
            if (!is_resource($image)) {
                throw new RuntimeException("Error in image transformation");
            }
        }
        return $image;
    }

    public function serialize ($image, $buffer, $format)
    {
        $serializer = (new Format(static::$format))->createTransform($format);
        call_user_func($serializer, $image, $buffer);
        return $buffer;
    }

    private function push (ArrayObject $chain, $function)
    {
        if ($function) {
            $chain->append($function);
        }
    }
}
