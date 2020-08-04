<?php
@include_once 'Repository/CrawlerRepository.php';
@include_once 'Repository/FileHandlerRepository.php';
@include_once 'Configuration/config.php';

$FileHandler = new FileHandler();
$Websites = $FileHandler->ReadFile($path['website']);
$Keywords = $FileHandler->ReadFile($path['keyword']);

//var_dump($Websites);
//var_dump($Keywords);

//Instantiate the Crawler Object
$Crawler = new Crawler($Websites['name'][0], $Keywords['name']);
//Get the JSON Response
$Response = $Crawler->Crawl();
//Write on a speciifc json file
$FileHandler->WriteFile($Response);

?>