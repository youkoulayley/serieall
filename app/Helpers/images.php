<?php

class ResizeImage
{
    private $ext;
    private $image;
    private $newImage;
    private $origWidth;
    private $origHeight;
    private $resizeWidth;
    private $resizeHeight;

    /**
     * Class constructor requires to send through the image filename.
     *
     * @param string $filename - Filename of the image you want to resize
     *
     * @throws Exception
     */
    public function __construct($filename)
    {
        if (file_exists($filename)) {
            $this->setImage($filename);
        } else {
            throw new Exception('Image '.$filename.' can not be found, try another image.');
        }
    }

    /**
     * Set the image variable by using image create.
     *
     * @param string $filename - The image filename
     *
     * @throws Exception
     */
    private function setImage($filename)
    {
        $size = getimagesize($filename);
        $this->ext = $size['mime'];
        switch ($this->ext) {
            // Image is a JPG
            case 'image/jpg':
            case 'image/jpeg':
                // create a jpeg extension
                $this->image = imagecreatefromjpeg($filename);
                break;
                // Image is a GIF
            case 'image/gif':
                $this->image = @imagecreatefromgif($filename);
                break;
                // Image is a PNG
            case 'image/png':
                $this->image = @imagecreatefrompng($filename);
                break;
                // Mime type not found
            default:
                throw new Exception('File is not an image, please use another file type.', 1);
        }
        $this->origWidth = imagesx($this->image);
        $this->origHeight = imagesy($this->image);
    }

    /**
     * Save the image as the image type the original image was.
     *
     * @param $savePath
     * @param string $imageQuality - The qulaity level of image to create
     * @param bool   $download
     *
     * @return void the image
     */
    public function saveImage($savePath, $imageQuality = '100', $download = false)
    {
        switch ($this->ext) {
            case 'image/jpg':
            case 'image/jpeg':
                // Check PHP supports this file type
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->newImage, $savePath, $imageQuality);
                }
                break;
            case 'image/gif':
                // Check PHP supports this file type
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->newImage, $savePath);
                }
                break;
            case 'image/png':
                $invertScaleQuality = 9 - round(($imageQuality / 100) * 9);
                // Check PHP supports this file type
                if (imagetypes() & IMG_PNG) {
                    imagepng($this->newImage, $savePath, $invertScaleQuality);
                }
                break;
        }
        if ($download) {
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            header('Content-disposition: attachment; filename= '.$savePath.'');
            readfile($savePath);
        }
        imagedestroy($this->newImage);
    }

    /**
     * Resize the image to these set dimensions.
     *
     * @param int    $width        - Max width of the image
     * @param int    $height       - Max height of the image
     * @param string $resizeOption - Scale option for the image
     *
     * @return void new image
     */
    public function resizeTo($width, $height, $resizeOption = 'default')
    {
        switch (strtolower($resizeOption)) {
            case 'exact':
                $this->resizeWidth = $width;
                $this->resizeHeight = $height;
                break;
            case 'maxwidth':
                $this->resizeWidth = $width;
                $this->resizeHeight = $this->resizeHeightByWidth($width);
                break;
            case 'maxheight':
                $this->resizeWidth = $this->resizeWidthByHeight($height);
                $this->resizeHeight = $height;
                break;
            default:
                if ($this->origWidth > $width || $this->origHeight > $height) {
                    if ($this->origWidth > $this->origHeight) {
                        $this->resizeHeight = $this->resizeHeightByWidth($width);
                        $this->resizeWidth = $width;
                    } elseif ($this->origWidth < $this->origHeight) {
                        $this->resizeWidth = $this->resizeWidthByHeight($height);
                        $this->resizeHeight = $height;
                    }
                } else {
                    $this->resizeWidth = $width;
                    $this->resizeHeight = $height;
                }
                break;
        }
        $this->newImage = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
        imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->origWidth, $this->origHeight);
    }

    /**
     * Get the resized height from the width keeping the aspect ratio.
     *
     * @param int $width - Max image width
     *
     * @return false|float keeping aspect ratio
     */
    private function resizeHeightByWidth($width)
    {
        return floor(($this->origHeight / $this->origWidth) * $width);
    }

    /**
     * Get the resized width from the height keeping the aspect ratio.
     *
     * @param int $height - Max image height
     *
     * @return false|float
     */
    private function resizeWidthByHeight($height)
    {
        return floor(($this->origWidth / $this->origHeight) * $height);
    }
}

/**
 * Download a specifi Image and save it with type information.
 *
 * @param $url
 * @param $name
 * @param $type
 */
function downloadImage($url, $name, $type)
{
    $imageName = $name.'-'.$type.'.jpg';
    $originalFile = public_path('/images/original/'.$imageName);

    $fileHeaders = get_headers($url);
    if (!$fileHeaders || 'HTTP/1.1 404 Not Found' == $fileHeaders[0] || 'HTTP/1.1 403 Forbidden' == $fileHeaders[0]) {
        Log::info('Images : No image for '.$imageName.'.');
    } else {
        copy($url, $originalFile);
        Log::debug('Images : Image '.$imageName.' was downloaded.');
    }
}

/**
 * Resize an original image to all the size in its type.
 *
 * @param $name
 * @param $type
 */
function resizeImage($name, $type)
{
    $imageName = $name.'-'.$type.'.jpg';
    try {
        $originalFile = new ResizeImage(public_path('/images/original/'.$imageName));
        foreach (config('images.'.$type) as $size) {
            createDirectoryIfNotExists(public_path('/images/'.$size));

            $width = explode('_', $size)[0];
            $height = explode('_', $size)[1];
            $resizedFile = public_path('/images/'.$size.'/'.$imageName);

            $originalFile->resizeTo($width, $height);
            $originalFile->saveImage($resizedFile);
            Log::debug('Images : Image '.$imageName.' was resized to '.$size.'.');
        }
    } catch (Exception $e) {
        Log::Error('Images : Impossible to resize image : '.$e);
    }
}

/**
 * Choose an image between original, default and resized.
 *
 * @param $name
 * @param $type
 * @param $size
 */
function chooseImage($name, $type, $size): string
{
    $imageName = $name.'-'.$type.'.jpg';
    $originalPath = '/images/original/'.$imageName;
    $originalFile = public_path($originalPath);
    $resizedPath = '/images/'.$size.'/'.$imageName;
    $resizedFile = public_path($resizedPath);
    $defaultPath = '/images/original/default.jpg';

    if (file_exists($originalFile)) {
        if (file_exists($resizedFile)) {
            return $resizedPath;
        } else {
            return $originalPath;
        }
    } else {
        return $defaultPath;
    }
}
