<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Usergroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Yaml\Yaml;

class AppTextManager {
	private $manager;



	public function __construct ( EntityManagerInterface $manager, string $projectDir) {
		$this->manager    = $manager;
		$this->projectDir = $projectDir;

	}


	public function changeText($tab, $key, $value){
		$adminYaml = Yaml::parse(file_get_contents($this->projectDir .'/var/admin-text/administration.yaml'));
		$adminYaml[$tab][$key] =  $value;
		$adminYaml = Yaml::dump($adminYaml, 3);
		file_put_contents($this->projectDir .'/var/admin-text/administration.yaml', $adminYaml);
	}

	public function getTabText($tab){
		$adminYamlTab = Yaml::parse(file_get_contents($this->projectDir .'/var/admin-text/administration.yaml'))[$tab];
		$defaultAdminYamlTab = Yaml::parse(file_get_contents($this->projectDir .'/config/administration-default.yaml'))[$tab];
		foreach($adminYamlTab as $key => $text){
			if(is_null($text)){
				$adminYamlTab[$key] = $defaultAdminYamlTab[$key];
			}
		}
		return $adminYamlTab;
	}


}
