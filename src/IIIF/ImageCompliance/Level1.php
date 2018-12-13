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

        $imageinfo = getimagesize($imageUri);
        $transformations = array();
        $transformations []= $this->createRegionTransform($imageParameters[0]);
        $transformations []= $this->createSizeTransform($imageParameters[1], $imageinfo[0], $imageinfo[1]);
        $transformations []= $this->createRotationTransform($imageParameters[2]);
        $transformations []= $this->createFormatTransform($imageParameters[3]);

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
        imagejpeg($image, $outbuf);
        rewind($outbuf);
        return $outbuf;
    }

    protected function createFormatTransform ($spec)
    {
        if ($spec != 'default.jpg') {
            $this->log('warning', sprintf('Invalid image quality and format: "%s"', $spec));
            throw new Error\Http(400);
        }
        return array($this, 'identity');
    }

    protected function createRotationTransform ($spec)
    {
        if ($spec != '0') {
            $this->log('warning', sprintf('Invalid image rotation: "%s"', $spec));
            throw new Error\Http(400);
        }
        return array($this, 'identity');
    }

    protected function createRegionTransform ($spec)
    {
        if ($spec == 'full') {
            return array($this, 'identity');
        } else if (preg_match('@^(?<x>[0-9]+),(?<y>[0-9]+),(?<width>[0-9]+),(?<height>[0-9]+)$@u', $spec, $match)) {
            $rect = $match;
            return function ($image) use ($rect) {
                return imagecrop($image, $rect);
            };
        } else {
            $this->log('warning', sprintf('Invalid image region: "%s"', $spec));
            throw new Error\Http(400);
        }
    }

    protected function createSizeTransform ($spec, $sourceWidth, $sourceHeight)
    {
        if ($spec == 'full') {
            return array($this, 'identity');
        } else if (preg_match('@^(?<w>[0-9]+),$@u', $spec, $match)) {
            $width = $match['w'];
            $height = round($sourceHeight * ($width / $sourceWidth));
            return function ($image) use ($width, $height) {
                return imagescale($image, $width, $height);
            };
        } else if (preg_match('@^,(?<h>[0-9]+)$@u', $spec, $match)) {
            $height = $match['h'];
            $width = round($sourceWidth * ($height / $sourceHeight));
            return function ($image) use ($width, $height) {
                return imagescale($image, $width, $height);
            };
        } else if (preg_match('@^pct:(?<n>[0-9]+)$@u', $spec, $match)) {
            $pct = $match['n'] / 100;
            $width = round($sourceWidth * $pct);
            $height = round($sourceHeight * $pct);
            return function ($image) use ($width, $height) {
                return imagescale($image, $width, $height);
            };
        } else {
            $this->log('warning', sprintf('Invalid image size: "%s"', $spec));
            throw new Error\Http(400);
        }
    }

    public function identity ($argument)
    {
        return $argument;
    }

}
