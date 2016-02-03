<!DOCTYPE html>
<html>
<head>
<link rel="shortcut icon" href="favicon.ico"/>
<link href="css/style.css" rel="stylesheet" />
<meta charset=utf-8 />
<title>Transcriptome metabolic viewer</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>

<link href="css/bootstrap.min.css" rel="stylesheet" />

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries 
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>

<?php


	if (!array_key_exists('categ1Name',$_POST)){
		echo "Error, go to <a href='index.php'>index page</a>";
		exit();
	}


	$categ1Name = $_POST["categ1Name"];
	$categ2Name = $_POST["categ2Name"];
	
	$categ1Indices = $_POST["Categ1Select"];
	$categ2Indices = $_POST["Categ2Select"];

	if( $categ1Indices==""){

		echo "Error : you have not chosen any sample for condition 1";	
		exit();

	}
	if( $categ2Indices==""){

		echo "Error : you have not chosen any sample for condition 2";	
		exit();

	}
	
	
	$categ1Indices = implode(",", $categ1Indices);
	$categ2Indices = implode(",", $categ2Indices);
	
	$uniqueNumber = $_POST["uniqueNumber"];
	
	if (!is_dir("analyses/".$uniqueNumber)){
		echo "Error. You have been inactive for too long, please relaunch your analysis.";
		exit();
	 }
	
	//update folder modification time, important for deletion of old files
	touch("analyses/".$uniqueNumber);
	//
	
	$transFile = $_POST["transFile"];
	$SBMLFile = $_POST["SBMLFile"];


	//~ echo 'python diffAnalysisVisu.py "'.$SBMLFile.'" "'.$transFile.'" "'.$categ1Indices.'" "'.$categ2Indices.'" '.$uniqueNumber.' 2>&1';

	$output = shell_exec('python diffAnalysisVisu.py "'.$SBMLFile.'" "'.$transFile.'" "'.$categ1Indices.'" "'.$categ2Indices.'" '.$uniqueNumber.' 2>&1');
    	
	

?>





</head>
<body style="background-color: #f5f5f5;">
    <nav class="navbar-collapse collapse" id="top" role="banner" style="background-color: #d2d2d2; height:100px;">
	  <div class="container">
		  
	   
		  <div class="col-md-3">
			  <a class="" href="index.php"><img src="img/logo_withName.png" alt="Logo"  style="height:80px;margin-top:10px;"></a>
		  </div>
		  <div class="col-md-6">
			   <a class="" href="index.php" style=""><img src="img/name.png" alt="Logo" style="height:95px;margin-top:2px;"></a>
			   
		  </div>
		  <div class="col-md-3"></div>
	  </div>
    </nav>

<div class="container" style="background-color: #ffffff;">

	<p class="text-center"><?php  //echo $output;  ?></p>
	<h3 class="text-center">Analysis over !</h3>

	<div class="row text-center">
		
		
			<form action="visu.php" method="post">
				<input class="btn btn-success" type="submit" value="Go to visualisation">
				<input type="hidden" name="uniqueNumber" value="<?php echo "$uniqueNumber"?>">
				<input type="hidden" name="categ1Name" value="<?php echo "$categ1Name"?>">
				<input type="hidden" name="categ2Name" value="<?php echo "$categ2Name"?>">
				<input type="hidden" name="uniqueNumberAnalysis" value="<?php echo "$output"?>">
			</form>
		
	</div>

	<footer class="footer">
			<div class="container">
				<p>
					This project was developed at <a
						href="http://www6.toulouse.inra.fr/lipm_eng/">LIPM</a>
					by Lucas Marmiesse. Copyright &copy; 2015, INRA
				</p>
			</div>
	</footer>
</div>

</body>

</html>
