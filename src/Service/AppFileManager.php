<?php

namespace App\Service;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;

class AppFileManager
{
    private $manager;

    /**
     * @var \Gaufrette\Filesystem
     */
    private $filesystem;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container, string $projectDir, string $assetPath)
    {
        $this->manager = $manager;
        $this->filesystem = $container->get('gaufrette.appfiles_filesystem');
        $this->projectDir = $projectDir;
        $this->assetPath = $assetPath;
    }

    public function getFileSystem()
    {
        return $this->filesystem;
    }

    /**
     * Writes file in filesystem.
     *
     * @param $content
     *
     * @return bool|string
     */
    public function writeFile(string $file, string $directoryString, $content)
    {
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        try {
            $n = 1;
            do {
                $basename = $filename.($n > 1 ? '-'.$n : '').'.'.$extension;
                $fullname = $directoryString.'/'.$basename;
                ++$n;
            } while ($this->filesystem->getAdapter()->exists($fullname));

            // $this->filesystem->delete('logo.png');
            $this->filesystem->write($fullname, $content);

            return $fullname;
        } catch (FileException $e) {
            return false;
        }
    }

    /**
     * Writes uploaded file in file system.
     *
     * @return bool|string
     */
    public function moveUploadedFile(UploadedFile $file, string $directoryString)
    {
        return $this->writeFile($file->getClientOriginalName(), $directoryString, file_get_contents($file->getRealPath()));
    }

    /**
     * Writes uploaded file in file system and return File object.
     *
     * @return \App\Entity\File
     */
    public function changeWithUploadedFile(UploadedFile $uploadedFile, string $directoryString)
    {
        $filename;

        switch ($directoryString) {
            case 'logo':
                $filename = $this->moveUploadedFile($uploadedFile, 'logos');
                break;
            case 'front':
                $filename = $this->moveUploadedFile($uploadedFile, 'fronts');
                break;
            case 'frontgroup':
                $filename = $this->moveUploadedFile($uploadedFile, 'frontsgroup');
                break;
            case 'frontgroup':
                $filename = $this->moveUploadedFile($uploadedFile, 'frontsgroup');
                break;
            case 'home':
                $filename = $this->moveUploadedFile($uploadedFile, 'home');
                break;
            default:
                $filename = $this->moveUploadedFile($uploadedFile, 'logos');
                break;
        }

        $file = new File();
        $file->setFilesystem(File::APP_FILES);
        $file->setName($uploadedFile->getClientOriginalName());
        $file->setPath($filename);
        $file->setType($uploadedFile->getMimeType());
        $file->setSize($uploadedFile->getSize());

        return $file;
    }

    public function setAppImageId(string $tab, string $imageType, int $imageId)
    {
        $adminYaml = Yaml::parse(file_get_contents($this->projectDir.'/config/platform/config.yaml'));
        $adminYaml[$tab][$imageType]['fileId'] = $imageId;
        $adminYaml = Yaml::dump($adminYaml, 5);
        file_put_contents($this->projectDir.'/config/platform/config.yaml', $adminYaml);
    }

    public function getAppImageId(string $tab, string $imageType)
    {
        $adminYaml = Yaml::parse(file_get_contents($this->projectDir.'/config/platform/config.yaml'));

        return $adminYaml[$tab][$imageType]['fileId'];
    }

    public function getFileById(int $fileId): File
    {
        $fileManager = $this->manager->getRepository(File::class);

        return $fileManager->getFileById($fileId);
    }

    public function getDefaultFile(string $tab, string $image_type)
    {
        $fullPath = $this->projectDir.'/'.$this->assetPath;

        $adminYaml = Yaml::parse(file_get_contents($this->projectDir.'/config/platform/default.config.yaml'));

        return $fullPath.$adminYaml[$tab][$image_type]['fileId'];
    }
}