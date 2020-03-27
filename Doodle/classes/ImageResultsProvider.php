<?php 
class ImageResultsProvider{

	private $con;

	public function __construct($con){
		$this->con = $con;
	}

	public function getNumResults($term){

		$query = $this->con->prepare("SELECT COUNT(*) as total
								    FROM images 
			 						WHERE(title LIKE :term
		 	 						OR alt LIKE :term)
		 	 						and broken=0");

		$searchTerm="%".$term."%";
		$query->bindParam(":term", $searchTerm);
		$query->execute();
		/*Now we store the result of query in Associative Array(Array with key-value pair)*/
		$row = $query->fetch(PDO::FETCH_ASSOC);
		return $row["total"];
	}
	public function getResultsHtml($page, $pageSize, $term){

		$fromLimit = ($page-1)*$pageSize;
		//page 1 =(1-1)*20=0
		//page 1 =(2-1)*20=20
		//pageSize=no. of links to show per page
		$query = $this->con->prepare("SELECT *
								    	FROM images 
			 							WHERE (title LIKE :term
		 	 							OR alt LIKE :term)
		 	 							AND broken = 0
		 								ORDER BY clicks DESC
		 								LIMIT :fromLimit, :pageSize");
		
		$searchTerm="%".$term."%";
		$query->bindParam(":term", $searchTerm);
		$query->bindParam(":fromLimit", $fromLimit,PDO::PARAM_INT);//PARAM_INT tells bindParam that from limit is integer type.
		$query->bindParam(":pageSize", $pageSize,PDO::PARAM_INT);
		$query->execute();
		
		$resultsHtml = "<div class='imageResults'>";
		$count=0;//used to differ every image by making a unique image class every time
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$count++;
			$id = $row["id"];
			$imageUrl = $row["imageUrl"];
			$title = $row["title"];
			$siteUrl = $row["siteUrl"];
			$alt = $row["alt"];
			if($title){
				$displayText = $title;
			}
			else if($alt){
				$displayText = $alt;
			}
			else{
				$displayText = $imageUrl;
			}
			//'data-' is used to add custom attributes.
			$resultsHtml.="<div class='gridItem image$count'>
							<a href='$imageUrl' data-fancybox data-caption='$displayText'
								data-siteurl='$siteUrl'>
								<script>
								$(document).ready(function(){
									loadImage(\"$imageUrl\",\"image$count\");
									});
								</script>
								<span class='details'>$displayText</span>	
							</a>

						   </div>";
		}
		$resultsHtml .= "</div>";
		return $resultsHtml;
	}
	
}

?>