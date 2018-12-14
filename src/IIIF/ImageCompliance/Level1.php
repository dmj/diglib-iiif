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
 * Implements IIIF Image API 2.1 Level 1 compliance.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Level1 implements ImageCompliance
{
    use LoggerAwareTrait;

    public function getComplianceLevel ()
    {
        return 'http://iiif.io/api/image/2/level1.json';
    }

    public function getImageStream ($imageUri, $imageParameters)
    {
        $imageParameters = explode('/', $imageParameters);
        if (count($imageParameters) != 4) {
            $this->log('error', sprintf('Invalid image parameters: "%s"', implode($imageParameters)));
            throw new Error\Http(400);
        }

        $targetParameters = explode('.', $imageParameters[3]);
        if (count($targetParameters) != 2) {
            throw new Error\Http(400);
        }

        $imageinfo = getimagesize($imageUri);
        $transformations = array();

        $region = new Region(Region::regionByPx | Region::regionByPct);
        $transformations []= $region->createTransform($imageParameters[0]) ?: array($this, 'identity');
        $size = new Size(Size::sizeByW | Size::sizeByH | Size::sizeByPct);
        $transformations []= $size->createTransform($imageParameters[1]) ?: array($this, 'identity');

        $rotation = new Rotation();
        $transformations []= $rotation->createTransform($imageParameters[2]) ?: array($this, 'identity');

        $quality = new Quality();
        $transformations []= $quality->createTransform($targetParameters[0]) ?: array($this, 'identity');

        $format = new Format(Format::png);
        $serialize = $format->createTransform($targetParameters[1]);

        $image = imagecreatefromjpeg($imageUri);
        if (!is_resource($image)) {
            throw new RuntimeException();
        }

        foreach ($transformations as $transformation) {
            $image = $transformation($image);
            if (!is_resource($image)) {
                throw new RuntimeException();
            }
        }

        $outbuf = fopen('php://temp', 'rw');
        $serialize($image, $outbuf);
        return $outbuf;
    }

    public function identity ($argument)
    {
        return $argument;
    }

}
