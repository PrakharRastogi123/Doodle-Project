<?php 
class DomDocumentParser{

	private $doc;/*contains html of the loaded url we have visited*/ 

	public function __construct($url){
		$options = array(
			'http'=>array('method'=>"GET", 'header'=>"User-Agent: prakhardoodleBot/0.1\n")
				);
		/*to make requests*/
		$context = stream_context_create($options);
		$this->doc = new DomDocument(); /*DomDocument class let us to perform actions on webpages*/
		/* @ means dont show me warnings*/
		@$this->doc->loadHTML(file_get_contents($url, false, $context));
	}
	public function getlinks(){
		/*getElementsByTagName is the func of DomDocument, since <a> is an anchor tag, so it will return the links of all anchor tags*/
		return $this->doc->getElementsByTagName("a");
	}
	public function getTitleTags(){
		return $this->doc->getElementsByTagName("title");
	}
	public function getMetaTags(){
		return $this->doc->getElementsByTagName("meta");
	}
	public function getImages(){
		return $this->doc->getElementsByTagName("img");
	}
}

 ?>