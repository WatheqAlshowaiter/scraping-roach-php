<?php

namespace App\Traits;

trait ImageDownloader
{
    private function getImageName($link, $name = null)
    {
        if (! $name) {
            return pathinfo($link)['filename'];
        }

        return $name;
    }

    public function getExtension($link)
    {
        return '.'.pathinfo($link)['extension'];
    }

    private function getImageNameWithExtension($link, $name)
    {
        return $this->getImageName($link, $name).$this->getExtension($link);
    }

    private function checkFolderExists($path)
    {
        return is_dir($path);
    }

    private function noFolderExists($path)
    {
        return ! $this->checkFolderExists($path);
    }

    private function getImagePath($imageName, $destination = null)
    {
        if ($this->noFolderExists($destination) && isset($destination)) {
            mkdir($destination);

            return $destination.'/'.$imageName;
        }

        return $imageName;
    }

    private function arrContextOptions()
    {
        return [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
    }

    private function getImageContent($imageLink)
    {
        return file_get_contents(
            $imageLink,
            false,
            stream_context_create($this->arrContextOptions())
        );
    }

    /**
     * Download images by given the online link, name, and location
     *
     * @param  string  $imageLink
     * @param  string  $imageName
     * @param  string  $destination
     * @return int|false
     */
    public function downloadImage($imageLink, $imageName = null, $destination = null)
    {
        $imageNameWithExtension = $this->getImageNameWithExtension($imageLink, $imageName);
        $imagePath = $this->getImagePath($imageNameWithExtension, $destination);
        $imageContent = $this->getImageContent($imageLink);

        return file_put_contents($imagePath, $imageContent);
    }
}
