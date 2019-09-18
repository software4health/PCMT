<?php
declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\Util\Adapter;

class DirectoryCreator
{
    public static function createDirectory(string $path): void
    {
        $path_split = explode('/', $path); //array
        $buildPath = '';
        foreach($path_split as $pathElem){
            if($pathElem === "") { continue; }
            $buildPath .= $pathElem . '/';
            if(is_dir($buildPath)){
                continue;
            }
            try{
                mkdir($buildPath, 0777);
            } catch (\Exception $exception) {
                throw new \Exception('Error creating directory: ' . $exception->getMessage());
            }
        }
    }
}