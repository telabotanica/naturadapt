<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
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
		$adminYaml = Yaml::dump($adminYaml, 5);
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

	public function getTabSectionText($tab, $section){
		$adminYamlTab = Yaml::parse(file_get_contents($this->projectDir .'/var/admin-text/administration.yaml'))[$tab][$section];
		$defaultAdminYamlTab = Yaml::parse(file_get_contents($this->projectDir .'/config/administration-default.yaml'))[$tab][$section];
		foreach($adminYamlTab as $key => $text){
			if(is_null($text)){
				$adminYamlTab[$key] = $defaultAdminYamlTab[$key];
			}
		}
		return $adminYamlTab;
	}

	public function changeLiens($tab, $liens, $liensType){
		$adminYaml = Yaml::parse(file_get_contents($this->projectDir .'/var/admin-text/administration.yaml'));

		$adminYaml[$tab][$liensType]['liens'] = [];
		foreach ( $liens as $index => $lienObj ) {
			if(!is_null($lienObj->getNom()) & !is_null($lienObj->getLien())){
				$adminYaml[$tab][$liensType]['liens'][$index]['nom'] =  $lienObj->getNom();
				$adminYaml[$tab][$liensType]['liens'][$index]['lien'] =  $lienObj->getLien();
			}
		}
		$adminYaml = Yaml::dump($adminYaml, 5);
		file_put_contents($this->projectDir .'/var/admin-text/administration.yaml', $adminYaml);
	}

}
