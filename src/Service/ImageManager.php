<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Class ImageManager
{
    protected $directory;
    protected $subDirectory ="/images";

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function getTargetDirectory() : string
    {
        return $this->directory;
    }

    private function getDirectory($public) : string
    {
        if($public === true)
        {
            $directory = $this->directory.'/public';
        }
        else{
            $directory = $this->directory.'/private';
        }
        return $directory.$this->subDirectory; 
    }

    public function upload(UploadedFile $file, bool $public = false) : string
    {
        $fileName = null;
        //dd($this->getDirectory($public));

        if(file_exists($this->getDirectory($public)) === false)
        {
            mkdir($this->getDirectory($public), 0755, true);
        }
        
        $count = 0;
        while($count < 10 && ($fileName === null || file_exists($this->getDirectory($public).'/'.$fileName)))
        {
            $fileName = md5($file->getClientOriginalName()).
            '_'.
            str_replace('.','-',uniqid('',true)).
            '.'.
            $file->guessExtension();
            $count++;
        }

        if ($count >=10)
        {
            throw new Exception("Impossible de générer un nom de fichier unique après $count tentatives.");
        }
        $file->move($this->getDirectory($public), $fileName);

        return $fileName;
    }

    public function stream(string $path):BinaryFileResponse
    {
        $response = new BinaryFileResponse($this->getDirectory(false).'/'.$path);
        return $response;
    }
}