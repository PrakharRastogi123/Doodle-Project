<?php
include("config.php");
include("classes/SiteResultsProvider.php");
include("classes/ImageResultsProvider.php");
	
if(isset($_GET["term"])){
	$term = $_GET["term"];
}
else{
	exit("You must enter a search term");
}
$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
$page = isset($_GET["page"]) ? $_GET["page"] : 1;//if page is set , that page will be displayed(that page value will be set) else page 1 will be displayed by default
?>
<!DOCTYPE html>
<html>
<head>
	<title>Welcome To Doodle</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
	<link rel="stylesheet" type="text/css" href="assets/CSS/style.css">
	<script src="https://code.jquery.com/jquery-3.4.1.min.js"integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="crossorigin="anonymous"></script>
</head>
<body>
	<div class="wrapper">
		<div class="header">
			<div class="headerContent">
				<div class="logoContainer">
					<a href="index.php">
						<img src="assets/images/logo.png">
					</a>
				</div>

				<div class="searchContainer">
					<form action="search.php" method="GET">
						<div class="searchBarContainer">
							<input type="hidden" name="type" value="<?php echo $type ?>">
							<input class="searchBox" type="text" name="term" value="<?php echo $term; ?>">
							<button class="searchButton">
								<img src="assets/images/icons.png">
							</button>
							
						</div>
					</form>
				</div>
			</div>
			<div class="tabsContainer">
				<u1 class="tablist">
					<li class="<?php echo $type=='sites'? 'active' : '' ?>">
						<a href= '<?php echo "search.php?term=$term&type=sites"; ?>'>
						Sites	
						</a>
					</li>
					<li class="<?php echo $type=='images'? 'active' : '' ?>">
						<a href= '<?php echo "search.php?term=$term&type=images"; ?>'>
						Images	
						</a>
					</li>
				</u1>
			</div>
		</div>
		<div class="mainResultsSection">
			<?php 
			if($type == 'sites'){
				$resultsProvider = new SiteResultsProvider($con);
				$pageSize=20;	
			}
			else{
				$resultsProvider = new ImageResultsProvider($con);
				$pageSize=40;
			}
			 $numResults = $resultsProvider->getNumResults($term);
			 echo "<p class='resultsCount'>$numResults results found</p>";

			 echo $resultsProvider->getResultsHtml($page,$pageSize,$term);
			 ?>
		</div>
		<div class="paginationContainer">

			<div class="pageButtons">
				<div class="pageNumberContainer">
					<img src="assets/images/pageStart.png">
				</div>
				<?php
				$pagesToShow=10;//no. og pages to show at a time;
				$numPages=ceil($numResults/$pageSize);//tells no. of total pages required to show, according to no. of search results.
				$pageLeft = min($pagesToShow,$numPages);
				$currentPage=$page - floor($pagesToShow/2);//currentPage= 6-(10/2)=1 , currentPage= 12-(10/2)=7 
				//$page--page we are on, $currentPage--starting no. in range of pages we show at a time.
				if($currentPage + $pageLeft > $numPages+1){//handles edge case of end pages :)
					$currentPage = $numPages+1 - $pageLeft;
				}									
				if($currentPage<1){
					$currentPage = 1;
				}
				while($pageLeft!=0 && $currentPage <= $numPages){
					if($currentPage==$page){
					echo "<div class='pageNumberContainer'>
					<img src='assets/images/pageSelected.png'>
					<span class='pageNumber'->$currentPage</span>
					</div>";
					}
					else{
					echo "<div class='pageNumberContainer'>
						<a href='search.php?term=$term&type=$type&page=$currentPage'>
							<img src='assets/images/page.png'>
						<span class='pageNumber'->$currentPage</span>
						</a>
						</div>";	
					}
					$currentPage++;
					$pageLeft--;
				}
				  ?>
				<div class="pageNumberContainer">
					<img src="assets/images/pageEnd.png">
					
				</div>
			</div>
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
	<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
	<script type="text/javascript" src="assets/script.js"></script>
</body>
</html>