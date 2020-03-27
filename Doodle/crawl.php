<?php 
	include("config.php");
	include("classes/DomDocumentParser.php");
	//src is the collection of sublinks inside our url
	$alreadyCrawled = array();
	$crawling = array();
	$alreadyFoundImages = array();
	function linkExists($url){
		global $con;
		$query = $con->prepare("SELECT * FROM sites WHERE url = :url");
		$query->bindParam(":url", $url);
		$query->execute();
		return $query->rowCount() != 0;
	}
	function insertLink($url,$title,$description,$keywords){
		global $con;
		$query = $con->prepare("INSERT INTO sites(url,title,description,keywords)
			VALUES(:url,:title,:description,:keywords)");//using prepare is much more sequre as it avoids sql injections
		$query->bindParam(":url", $url);//binding placeholders to their respective values
		$query->bindParam(":title", $title);
		$query->bindParam(":description", $description);
		$query->bindParam(":keywords", $keywords);
		return $query->execute();
	}
	function insertImage($url,$src,$alt,$title){
		global $con;
		$query = $con->prepare("INSERT INTO images(siteURL,imageUrl,alt,title)
			VALUES(:siteURL,:imageUrl,:alt,:title)");
		$query->bindParam(":siteURL", $url);
		$query->bindParam(":imageUrl", $src);
		$query->bindParam(":alt", $alt);
		$query->bindParam(":title", $title);
		$query->execute();
	}
	function createlink($src, $url){

		$scheme = parse_url($url)["scheme"]; //http
		$host = parse_url($url)["host"]; //www.bbc.com

		if(substr($src,0,2) == "//"){
			$src=$scheme.":".$src;
		}
		else if(substr($src,0,1) == "/"){
			$src=$scheme."://".$host.$src;
		}
		else if(substr($src,0,2) == "./"){
			$src = $scheme."://".$host.dirname(parse_url($url)["path"]).substr($src, 1);
		}
		else if(substr($src,0,3) == "../"){
			$src = $scheme."://".$host."/".$src;
		}
		else if(substr($src,0,5) != "https" && substr($src, 0, 4) != "http"){
			$src = $scheme."://".$host."/".$src;
		}
		return $src;
	}
	function getDetails($url){
		global $alreadyFoundImages;

		$parser = new DomDocumentParser($url);
		$titleArray = $parser->getTitleTags();

		if(sizeof($titleArray)==0 || $titleArray->item(0)==NULL){
			return;
		}
		$title=$titleArray->item(0)->nodeValue;
		$title=str_replace("\n","", $title);

		if($title == ""){
			return;
		}
		$description = ""; //we have taken empty str as we are allowing the sites with no descriptions
		$keywords = "";
		$metasArray = $parser->getMetaTags();
		foreach ($metasArray as $meta) {
			if($meta->getAttribute("name")=="description"){
				$description = $meta->getAttribute("content");
			}
			if($meta->getAttribute("name")=="keywords"){
				$keywords = $meta->getAttribute("content");
			}
		}
		$description=str_replace("\n","", $description);
		$keywords=str_replace("\n","", $keywords);

		if(linkExists($url)){
			echo "$url Link already exists<br>";
		}
		else if (insertLink($url,$title,$description,$keywords)){
			echo "SUCCESS: $url<br>";
		}
		else{
			echo "ERROR:Failed to insert $url<br>";
		}
		$imagesArray = $parser->getImages();
		foreach ($imagesArray as $image) {
			$src = $image->getAttribute("src");
			$alt = $image->getAttribute("alt");
			$title = $image->getAttribute("title");
			if(!$title && !$alt){
				continue; //ignoring images with no title or alt
			}	
			$src=createlink($src,$url);
			if(!in_array($src, $alreadyFoundImages)){
				$alreadyFoundImages[] = $src;
				insertImage($url, $src, $alt, $title);
			}
		}
	}
	function followLinks($url){
		global $alreadyCrawled;
		global $crawling;
		$parser = new DomDocumentParser($url);
		/*linklist here is name of an array containing the links of the passed url*/
		$linklist = $parser->getlinks();
		foreach ($linklist as $link) {
			$href = $link->getAttribute("href");
			if(strpos($href, "#")!==false){
				continue;
			}
			else if(substr($href, 0, 11) == "javascript:"){
				continue;
			}
			$href=createlink($href, $url);

			if (!in_array($href, $alreadyCrawled)) {
				$alreadyCrawled[] = $href;
				$crawling[] = $href;
				getDetails($href);
			}
		}
		/*since we've been over one of them so we need to knock it off the array*/
		array_shift($crawling);

		foreach ($crawling as $site) {
			followLinks($site); //recursive crawling
		}

	}
	$startUrl = "https://www.bbcearth.com/";
	followLinks($startUrl);
	/*
	scheme : http https
	host: www.bbc.com

	we have to deal with follng cases:
	//www.bbc.com        => http://www.bbc.com
	/about/aboutUs.php   => http + :// + www.bbc.com + /about/aboutUs.php
	./about/aboutUs.php  => http + :// + www.bbc.com + current directory name(path of this url) + /           (current directory)
	../about/aboutUs.php => http://www.bbc.com/../about/aboutUs.php                                           (prev directory)
	about/aboutUs.php    => http://www.bbc.com/about/aboutUs.php
	eg. <a href="/about/aboutUs.php">
	*/
 ?>
 